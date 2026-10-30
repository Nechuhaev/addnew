<?php

namespace App\Services\Seo;

use App\Ad;

/**
 * JSON-LD Product + Offer для сторінки оголошення / товару магазину — для розширених
 * результатів і безкоштовних товарних оголошень Google (merchant listings).
 *
 * Замінює мікророзмітку без itemscope, яку Google ігнорував. Без ціни розмітки немає
 * (Offer без price невалідний); чутливі оголошення (SeoRules) — теж без неї.
 * Доставку (shippingDetails) вказуємо лише там, де вона точно відома: безкоштовна
 * доставка магазину від суми, і ціна товару не менша за неї.
 */
class ProductSchema
{
    const CONDITIONS = [
        'new' => 'https://schema.org/NewCondition',
        'used' => 'https://schema.org/UsedCondition',
        'refurbished' => 'https://schema.org/RefurbishedCondition',
    ];

    /**
     * @param array $prices Ad::pricesInCurrencies(): [['currency', 'value', 'selected'], ...]
     */
    public static function forAd(Ad $ad, array $prices, string $pageUrl): ?array
    {
        if (!empty($ad->is_sensitive)) {
            return null;
        }
        $price = null;
        foreach ($prices as $p) {
            if (!empty($p['selected']) && (int) $p['value'] > 0) {
                $price = $p;
            }
        }
        $name = trim((string) $ad->name);
        if (!$price || $name === '') {
            return null;
        }

        $offer = [
            '@type' => 'Offer',
            'url' => $pageUrl,
            'price' => (string) (int) $price['value'],
            'priceCurrency' => (string) $price['currency'],
            'availability' => ($ad->is_product && $ad->stock === 'out_of_stock')
                ? 'https://schema.org/OutOfStock' : 'https://schema.org/InStock',
        ];
        if (isset(self::CONDITIONS[$ad->condition])) {
            $offer['itemCondition'] = self::CONDITIONS[$ad->condition];
        }
        $user = $ad->user;
        if ($user && trim((string) $user->username) !== '') {
            $offer['seller'] = [
                '@type' => $user->is_shop_owner ? 'Organization' : 'Person',
                'name' => trim((string) $user->username),
                'url' => url((app()->getLocale() === 'ru' ? '/ru' : '') . '/author/' . $user->id),
            ];
        }
        $shipping = self::freeShipping($ad, (int) $price['value'], (string) $price['currency']);
        if ($shipping) {
            $offer['shippingDetails'] = $shipping;
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => mb_substr($name, 0, 150),
            'url' => $pageUrl,
        ];
        $images = self::images($ad);
        if ($images) {
            $schema['image'] = $images;
        }
        $description = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) $ad->content), ENT_QUOTES, 'UTF-8')));
        if ($description !== '') {
            $schema['description'] = mb_substr($description, 0, 5000);
        }
        $schema['sku'] = (string) ($ad->is_product && $ad->source_id ? $ad->source_id : $ad->id);
        if (trim((string) $ad->code) !== '') {
            $schema['mpn'] = trim((string) $ad->code);
        }
        if (preg_match('/^\d{8,14}$/', (string) $ad->gtin)) {
            $schema['gtin'] = (string) $ad->gtin;
        }
        if (trim((string) $ad->brand) !== '') {
            $schema['brand'] = ['@type' => 'Brand', 'name' => self::brandName((string) $ad->brand)];
        }
        $schema['offers'] = $offer;

        return $schema;
    }

    protected static function images(Ad $ad): array
    {
        $out = [];
        foreach (array_merge([$ad->image], is_array($ad->images) ? $ad->images : []) as $src) {
            $src = trim((string) $src);
            if ($src === '' || stripos($src, 'placeholder') !== false) {
                continue;
            }
            $out[] = preg_match('~^https?://~i', $src) ? $src : url($src);
        }

        return array_slice(array_values(array_unique($out)), 0, 10);
    }

    /** Безкоштовна доставка по Україні, якщо в магазину вказано «безкоштовно від» і ціна досягає порогу */
    protected static function freeShipping(Ad $ad, int $price, string $currency): ?array
    {
        $user = $ad->user;
        if (!$ad->is_product || !$user || $currency !== 'UAH') {
            return null;
        }
        $from = (int) ($user->free_delivery_from ?? 0);
        if ($from <= 0 || $price < $from) {
            return null;
        }

        return [
            '@type' => 'OfferShippingDetails',
            'shippingRate' => ['@type' => 'MonetaryAmount', 'value' => '0', 'currency' => 'UAH'],
            'shippingDestination' => ['@type' => 'DefinedRegion', 'addressCountry' => 'UA'],
        ];
    }

    /** Імпорт зберігає бренд у нижньому регістрі («bosch») — для розмітки «Bosch»; абревіатури (LG) лишаються */
    protected static function brandName(string $brand): string
    {
        $brand = trim($brand);
        if ($brand !== mb_strtolower($brand)) {
            return $brand;
        }

        return mb_strlen($brand) <= 3 ? mb_strtoupper($brand) : mb_convert_case($brand, MB_CASE_TITLE);
    }
}
