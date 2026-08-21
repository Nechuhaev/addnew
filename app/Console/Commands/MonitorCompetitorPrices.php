<?php

namespace App\Console\Commands;

use App\Ad;
use App\AdCurrency;
use App\ProductPriceCheck;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MonitorCompetitorPrices extends Command
{
    /**
     * php artisan products:monitor-prices
     *
     * Для товарів з посиланням на джерело (competitor_url — вручну, або
     * url — авто з фіда імпорту) перевіряє ЦІНУ і НАЯВНІСТЬ через публічні
     * структуровані дані сторінки (JSON-LD Product/Offer, Open Graph,
     * itemprop="price"/"availability" — те, що сайт сам публікує для
     * Google). При збігу валюти оновлює ціну; при виявленні "немає в
     * наявності" — виставляє stock=out_of_stock (і навпаки, якщо товар
     * знову з'явився). Кожна перевірка логується в product_price_checks.
     */
    protected $signature = 'products:monitor-prices {--limit=} {--shop=}';

    protected $description = 'Перевіряє ціну й наявність товару в магазині-джерелі та синхронізує з дошкою';

    /** @var Client */
    protected $http;

    public function __construct()
    {
        parent::__construct();
        $this->http = new Client();
    }

    protected function isSkippedDomain(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (!$host) {
            return false;
        }
        foreach (\App\SkippedDomain::list() as $domain) {
            if (Str::contains($host, $domain)) {
                return true;
            }
        }
        return false;
    }

    public function handle()
    {
        $limit = (int) ($this->option('limit') ?: env('PRICE_MONITOR_PER_RUN', 30));
        $shopId = $this->option('shop');

        $query = Ad::where('is_product', 1)
            ->where('status', 1) // тільки активні оголошення — призупинені й архівні
            // не показуються покупцям, моніторити їх немає сенсу
            ->where(function ($q) {
                $q->whereNotNull('competitor_url')->where('competitor_url', '!=', '')
                  ->orWhere(function ($q2) {
                      $q2->whereNotNull('url')->where('url', '!=', '');
                  });
            });

        if ($shopId) {
            // Ручна перевірка ОДНОГО магазину — беремо всі його товари,
            // ігноруючи чергу "давно не перевірені" (яка застосовується
            // тільки для загального автоматичного прогону).
            $query->where('user_id', $shopId);
            $this->info("Перевіряю магазин ID={$shopId} (усі товари з посиланням, без ліміту черги)");
        } else {
            $query->orderByRaw('(SELECT MAX(checked_at) FROM product_price_checks WHERE product_price_checks.ad_id = ads.id) IS NOT NULL')
                ->orderByRaw('(SELECT MAX(checked_at) FROM product_price_checks WHERE product_price_checks.ad_id = ads.id) ASC')
                ->limit($limit);
        }

        $products = $query->get();

        if ($products->isEmpty()) {
            $this->info('Немає товарів з посиланням для перевірки.');
            return 0;
        }

        $this->info('Перевіряю ' . $products->count() . ' товар(ів)');

        foreach ($products as $i => $product) {
            // ВАЖЛИВО: $product->url пройшло б через аксесор Ad::getUrlAttribute(),
            // який завжди повертає внутрішній шлях "/ads/slug" — нам потрібне
            // СИРЕ значення стовпця url з БД (посилання на джерело імпорту).
            $sourceUrl = $product->competitor_url ?: $product->getOriginal('url');

            if (empty($sourceUrl)) {
                continue;
            }

            if ($this->isSkippedDomain($sourceUrl)) {
                $this->info('[' . ($i + 1) . '/' . $products->count() . "] ID={$product->id}: пропущено (захищений від ботів домен)");
                continue;
            }

            $this->info('[' . ($i + 1) . '/' . $products->count() . "] ID={$product->id}, {$sourceUrl}");

            try {
                $this->checkOne($product, $sourceUrl);
            } catch (\Throwable $e) {
                $this->logCheck($product, null, null, null, false, 'fetch_error', $e->getMessage());
                $this->error('Помилка: ' . $e->getMessage());
                $this->maybeAutoSuspend($product);
            }

            usleep(300000);
        }

        return 0;
    }

    protected function checkOne(Ad $product, string $sourceUrl): void
    {
        $response = $this->http->get($sourceUrl, [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (compatible; AddnewPriceMonitor/1.0; +https://addnew.biz)',
            ],
            'timeout' => 20,
            'verify' => false,
        ]);

        $html = (string) $response->getBody();
        $found = $this->extractProductData($html);

        if (!$found['price'] && !$found['availability']) {
            $this->logCheck($product, $product->price, null, null, false, 'not_found', 'Структуровані дані не знайдено на сторінці (можливо, тихий редирект на іншу сторінку)');
            $this->warn('Ані ціни, ані наявності не знайдено на сторінці');
            $this->maybeAutoSuspend($product);
            return;
        }

        $notes = [];
        $priceApplied = false;
        $oldPriceForLog = (float) $product->price;

        if ($found['availability']) {
            if ($found['availability'] !== $product->stock) {
                $oldStock = $product->stock;
                $product->stock = $found['availability'];
                $notes[] = "Наявність: {$oldStock} -> {$found['availability']}";
            } else {
                $notes[] = 'Наявність без змін (' . $found['availability'] . ')';
            }
        }

        $foundCurrency = null;
        if ($found['price']) {
            $productCurrency = strtoupper(optional($product->currency)->code ?? '');
            $foundCurrency = strtoupper($found['currency'] ?? '');

            if (!$productCurrency || $foundCurrency !== $productCurrency) {
                $notes[] = "Валюта не збігається (товар={$productCurrency}, джерело={$foundCurrency}) — ціну НЕ оновлено";
            } else {
                if (abs($oldPriceForLog - $found['price']) >= 0.01) {
                    $product->price = $found['price'];
                    $priceApplied = true;
                    $notes[] = "Ціна: {$oldPriceForLog} -> {$found['price']} {$foundCurrency}";
                } else {
                    $notes[] = 'Ціна без змін';
                }
            }
        }

        $product->save();

        $note = implode('; ', $notes);
        $this->logCheck($product, $oldPriceForLog, $found['price'], $foundCurrency, $priceApplied, 'success', $note);
        $this->info('  ' . $note);
    }

    /**
     * @return array{price: float|null, currency: string|null, availability: string|null}
     */
    protected function extractProductData(string $html): array
    {
        $result = ['price' => null, 'currency' => null, 'availability' => null];

        if (preg_match_all('/<script[^>]+type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is', $html, $matches)) {
            foreach ($matches[1] as $jsonBlock) {
                $data = json_decode(trim($jsonBlock), true);
                if (!is_array($data)) {
                    continue;
                }
                $found = $this->findOfferInJsonLd($data);
                if ($found) {
                    return array_merge($result, $found);
                }
            }
        }

        if (preg_match('/<meta[^>]+property=["\']product:price:amount["\'][^>]+content=["\']([\d.,]+)["\']/i', $html, $m)) {
            $result['price'] = $this->normalizeNumber($m[1]);
            $result['currency'] = 'USD';
            if (preg_match('/<meta[^>]+property=["\']product:price:currency["\'][^>]+content=["\']([A-Za-z]{3})["\']/i', $html, $mc)) {
                $result['currency'] = $mc[1];
            }
        }
        if (preg_match('/<meta[^>]+property=["\']product:availability["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $ma)) {
            $result['availability'] = $this->normalizeAvailability($ma[1]);
        }

        if (!$result['price'] && preg_match('/<meta[^>]+itemprop=["\']price["\'][^>]+content=["\']([\d.,]+)["\']/i', $html, $m)) {
            $result['price'] = $this->normalizeNumber($m[1]);
            $result['currency'] = 'USD';
            if (preg_match('/<meta[^>]+itemprop=["\']priceCurrency["\'][^>]+content=["\']([A-Za-z]{3})["\']/i', $html, $mc)) {
                $result['currency'] = $mc[1];
            }
        }
        if (!$result['availability']) {
            if (preg_match('/itemprop=["\']availability["\'][^>]+(?:href|content)=["\']([^"\']+)["\']/i', $html, $ma)) {
                $result['availability'] = $this->normalizeAvailability($ma[1]);
            }
        }

        return $result;
    }

    protected function findOfferInJsonLd($data): ?array
    {
        if (!is_array($data)) {
            return null;
        }

        if (isset($data['offers'])) {
            $offers = $data['offers'];
            if (isset($offers[0])) {
                $offers = $offers[0];
            }
            if (isset($offers['price']) || isset($offers['availability'])) {
                $price = isset($offers['price']) ? $this->normalizeNumber((string) $offers['price']) : null;
                $currency = $offers['priceCurrency'] ?? null;
                $availability = isset($offers['availability']) ? $this->normalizeAvailability((string) $offers['availability']) : null;

                if ($price || $availability) {
                    return ['price' => $price, 'currency' => $currency, 'availability' => $availability];
                }
            }
        }

        if (isset($data['@graph']) && is_array($data['@graph'])) {
            foreach ($data['@graph'] as $item) {
                $result = $this->findOfferInJsonLd($item);
                if ($result) {
                    return $result;
                }
            }
        }

        if (isset($data[0]) && is_array($data[0])) {
            foreach ($data as $item) {
                $result = $this->findOfferInJsonLd($item);
                if ($result) {
                    return $result;
                }
            }
        }

        return null;
    }

    protected function normalizeAvailability(string $raw): ?string
    {
        $normalized = strtolower(preg_replace('/[^a-z]/i', '', $raw));

        if (strpos($normalized, 'outofstock') !== false
            || strpos($normalized, 'soldout') !== false
            || strpos($normalized, 'discontinued') !== false) {
            return 'out_of_stock';
        }

        if (strpos($normalized, 'instock') !== false
            || strpos($normalized, 'limitedavailability') !== false
            || strpos($normalized, 'presale') !== false
            || strpos($normalized, 'preorder') !== false) {
            return 'in_stock';
        }

        return null;
    }

    protected function normalizeNumber(string $raw): ?float
    {
        $raw = trim($raw);
        $raw = str_replace(' ', '', $raw);
        if (substr_count($raw, ',') === 1 && substr_count($raw, '.') === 0) {
            $raw = str_replace(',', '.', $raw);
        } else {
            $raw = str_replace(',', '', $raw);
        }
        return is_numeric($raw) ? (float) $raw : null;
    }

    /**
     * Якщо джерело товару N разів ПОСПІЛЬ (за замовчуванням 3) не дало
     * корисних даних — або сайт узагалі недоступний (fetch_error), або
     * відповів, але без ціни/наявності (not_found — часто означає тихий
     * редирект на головну/іншу сторінку замість чесного 404) —
     * автоматично призупиняємо оголошення. Одна вдала перевірка між
     * ними скидає лічильник.
     */
    protected function maybeAutoSuspend(Ad $product): void
    {
        $threshold = (int) env('AUTO_SUSPEND_AFTER_FAILURES', 3);

        if ((int) $product->getOriginal('status') === 0) {
            return; // вже призупинено — нічого робити не треба
        }

        $recentStatuses = ProductPriceCheck::where('ad_id', $product->id)
            ->orderByDesc('checked_at')
            ->limit($threshold)
            ->pluck('status');

        if ($recentStatuses->count() < $threshold) {
            return;
        }

        $failureStatuses = ['fetch_error', 'not_found'];

        if ($recentStatuses->every(function ($s) use ($failureStatuses) {
            return in_array($s, $failureStatuses, true);
        })) {
            $product->status = 0;
            $product->save();

            $source = $product->competitor_url ?: $product->getOriginal('url');
            $this->logCheck($product, null, null, null, false, 'auto_suspended',
                "Оголошення автоматично призупинено після {$threshold} невдалих спроб підряд отримати корисні дані з джерела ({$source})");
            $this->warn("Оголошення ID={$product->id} автоматично ПРИЗУПИНЕНО (джерело недоступне/порожнє {$threshold} рази підряд)");
        }
    }

    protected function logCheck(Ad $product, ?float $oldPrice, ?float $foundPrice, ?string $foundCurrency, bool $applied, string $status, ?string $note): void
    {
        ProductPriceCheck::create([
            'ad_id' => $product->id,
            'old_price' => $oldPrice,
            'found_price' => $foundPrice,
            'found_currency' => $foundCurrency,
            'price_applied' => $applied,
            'status' => $status,
            'note' => $note,
            'checked_at' => now(),
        ]);
    }
}