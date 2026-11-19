<?php

namespace App\Console\Commands;

use App\Ad;
use App\AdCategory;
use App\AdCountry;
use App\AdCurrency;
use App\Services\Seo\ProductSchema;
use Illuminate\Console\Command;

/**
 * php artisan merchant:feed [--preview]
 *
 * Фід товарів магазинів для Google Merchant Center (безкоштовні товарні оголошення): public/merchant-feed.xml,
 * RSS 2.0 з простором імен g:. Дані беруться з тієї самої логіки, що й розмітка Product на сторінці товару
 * (ProductSchema::forAd), тож фід і сторінка не розходяться.
 * У фід ідуть: активні товари магазинів (is_product) в Україні, у наявності, з ціною й фото, не чутливі.
 * Ціна — у гривнях (інші валюти переводяться за курсом сайту, як і на сторінці товару).
 * --preview пише в storage/app/merchant-feed.xml і public/ не чіпає.
 */
class BuildMerchantFeed extends Command
{
    protected $signature = 'merchant:feed {--preview}';

    protected $description = 'Фід товарів для Google Merchant Center (public/merchant-feed.xml)';

    const COUNTRY_ID = RebuildSitemap::COUNTRY_ID;

    const FILE = 'merchant-feed.xml';

    public function handle()
    {
        app()->setLocale('uk');   // назви категорій і посилання — українська версія сайту
        $country = AdCountry::find(self::COUNTRY_ID);
        if (!$country) {
            $this->error('Країну не знайдено.');
            return 1;
        }
        $cityIds = $country->cities->pluck('id')->all();
        $currencies = AdCurrency::all()->keyBy('id');
        $uah = $currencies->first(function ($c) {
            return strtoupper((string) $c->code) === 'UAH';
        });
        if (!$uah) {
            $this->error('Немає валюти UAH.');
            return 1;
        }
        $types = $this->productTypes();

        $target = $this->option('preview') ? storage_path('app/' . self::FILE) : public_path(self::FILE);
        $tmp = $target . '.tmp';
        $x = new \XMLWriter();
        $x->openUri($tmp);
        $x->setIndent(true);
        $x->startDocument('1.0', 'UTF-8');
        $x->startElement('rss');
        $x->writeAttribute('version', '2.0');
        $x->writeAttribute('xmlns:g', 'http://base.google.com/ns/1.0');
        $x->startElement('channel');
        $x->writeElement('title', 'Addnew.biz — товари магазинів');
        $x->writeElement('link', url('/'));
        $x->writeElement('description', 'Товари інтернет-магазинів на дошці оголошень Addnew.biz');

        $stats = ['total' => 0, 'written' => 0, 'no_price' => 0, 'no_image' => 0, 'sensitive' => 0];
        Ad::query()->with('user')
            ->where('status', 1)
            ->where('is_product', 1)
            ->where(function ($q) {
                $q->whereNull('stock')->orWhere('stock', '!=', 'out_of_stock');
            })
            ->where('is_sensitive', 0)
            ->whereIn('city_id', $cityIds)
            ->whereNotNull('slug')->where('slug', '!=', '')
            ->chunkById(1000, function ($ads) use ($x, $currencies, $uah, $types, &$stats) {
                foreach ($ads as $ad) {
                    $stats['total']++;
                    $item = $this->item($ad, $currencies, $uah, $types);
                    if (is_string($item)) {
                        $stats[$item]++;
                        continue;
                    }
                    $x->startElement('item');
                    foreach ($item as $tag => $value) {
                        foreach ((array) $value as $v) {
                            if (is_array($v)) {   // вкладений елемент (g:shipping)
                                $x->startElement($tag);
                                foreach ($v as $k => $vv) {
                                    $x->writeElement($k, $vv);
                                }
                                $x->endElement();
                            } else {
                                $x->writeElement($tag, $v);
                            }
                        }
                    }
                    $x->endElement();
                    $stats['written']++;
                }
                $x->flush();
            });

        $x->endElement();
        $x->endElement();
        $x->endDocument();
        $x->flush();
        unset($x);
        rename($tmp, $target);

        $this->info(sprintf('Товарів перевірено: %d, у фіді: %d (без ціни: %d, без фото: %d, чутливих: %d)',
            $stats['total'], $stats['written'], $stats['no_price'], $stats['no_image'], $stats['sensitive']));
        $this->line('Файл: ' . $target . ($this->option('preview') ? '' : ' → ' . url('/' . self::FILE)));

        return 0;
    }

    /** @return array|string поля товару або причина пропуску */
    protected function item(Ad $ad, $currencies, $uah, array $types)
    {
        $cur = $currencies->get($ad->currency_id);
        if (!$cur || !$ad->price) {
            return 'no_price';
        }
        // Ціна в гривнях — так само, як Ad::pricesInCurrencies на сторінці
        $value = strtoupper((string) $cur->code) === 'UAH'
            ? (int) $ad->price
            : (int) ((float) $ad->price * (float) $cur->rate / (float) $uah->rate);
        $link = route('ad.page', ['slug' => $ad->slug]);
        $schema = ProductSchema::forAd($ad, [['currency' => 'UAH', 'value' => $value, 'selected' => true]], $link);
        if (!$schema) {
            return !empty($ad->is_sensitive) ? 'sensitive' : 'no_price';
        }
        $images = (array) ($schema['image'] ?? []);
        if (!$images) {
            return 'no_image';
        }
        $offer = $schema['offers'];

        $item = [
            'g:id' => 'ad-' . $ad->id,
            'g:title' => mb_substr($schema['name'], 0, 150),
            'g:description' => mb_substr($schema['description'] ?? $schema['name'], 0, 5000),
            'g:link' => $link,
            'g:image_link' => $images[0],
        ];
        if (count($images) > 1) {
            $item['g:additional_image_link'] = array_slice($images, 1, 10);
        }
        $item['g:availability'] = 'in_stock';
        $item['g:price'] = number_format((float) $offer['price'], 2, '.', '') . ' UAH';
        $item['g:condition'] = in_array($ad->condition, ['used', 'refurbished'], true) ? $ad->condition : 'new';
        if (!empty($schema['brand']['name'])) {
            $item['g:brand'] = $schema['brand']['name'];
        }
        if (!empty($schema['gtin'])) {
            $item['g:gtin'] = $schema['gtin'];
        }
        if (!empty($schema['mpn'])) {
            $item['g:mpn'] = $schema['mpn'];
        }
        // Без бренду й коду товар не ідентифікується — Google просить так і вказати
        if (empty($item['g:gtin']) && (empty($item['g:brand']) || empty($item['g:mpn']))) {
            $item['g:identifier_exists'] = 'no';
        }
        if (isset($types[$ad->category_id])) {
            $item['g:product_type'] = $types[$ad->category_id];
        }
        if (!empty($offer['shippingDetails'])) {
            $item['g:shipping'] = [['g:country' => 'UA', 'g:price' => '0.00 UAH']];
        }

        return $item;
    }

    /** [category_id => «Батьківська > Дочірня»] українською */
    protected function productTypes(): array
    {
        $cats = AdCategory::all(['id', 'parent_id', 'name', 'name_uk'])->keyBy('id');
        $out = [];
        foreach ($cats as $id => $c) {
            $path = [];
            $node = $c;
            for ($i = 0; $node && $i < 5; $i++) {
                array_unshift($path, trim((string) $node->name));
                $node = $node->parent_id ? $cats->get($node->parent_id) : null;
            }
            $out[$id] = implode(' > ', array_filter($path));
        }

        return $out;
    }
}
