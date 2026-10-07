<?php

namespace App\Console\Commands;

use App\Ad;
use App\AdCurrency;
use App\Console\Commands\Concerns\UpdatesDailyReport;
use App\ProductPriceCheck;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MonitorCompetitorPrices extends Command
{
    use UpdatesDailyReport;

    /**
     * php artisan products:monitor-prices
     *
     * Два різні джерела, які НЕ можна змішувати:
     *
     * 1. url — сторінка товару на сайті САМОГО магазину (з фіда імпорту).
     *    Звідси беремо наявність; 404 означає, що магазин прибрав товар
     *    (видаляємо оголошення), кілька невдалих спроб поспіль — призупиняємо.
     *    Ціну беремо звідси лише тоді, коли не задано посилання на конкурента.
     *
     * 2. competitor_url — аналогічний товар у ІНШОМУ магазині (вказує власник).
     *    Звідси беремо ЛИШЕ ціну (рівно як у конкурента, якщо валюта збігається).
     *    Наявність конкурента, його 404 чи недоступність на наш товар не впливають.
     *
     * Дані читаються з публічної розмітки (JSON-LD Product/Offer/AggregateOffer,
     * Open Graph, itemprop). Ціна, що відрізняється від поточної більш ніж у
     * PRICE_MONITOR_MAX_RATIO разів (за замовчуванням 2), не застосовується —
     * захист від помилок розбору й «акцій за 1 грн». Кожна перевірка
     * логується в product_price_checks (статуси competitor_* — для конкурента).
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

        $baseQuery = Ad::where('is_product', 1)
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
            // тільки для загального автоматичного прогону), і НЕ
            // фільтруємо захищені домени — якщо власник просить
            // перевірити конкретний магазин, хай побачить актуальний
            // статус, а не тиху відсутність результату.
            $baseQuery->where('user_id', $shopId);
            $this->info("Перевіряю магазин ID={$shopId} (усі товари з посиланням, без ліміту черги)");

            $products = $baseQuery->get();
        } else {
            // ВАЖЛИВО: беремо кандидатів БІЛЬШЕ, ніж --limit, і одразу
            // відсіюємо товари із заздалегідь відомих захищених доменів
            // (SkippedDomain) — ДО того, як застосовується ліміт. Раніше
            // такі товари все одно потрапляли у вибірку, "з'їдали" слоти
            // ліміту на очевидний skip і нічого корисного не перевіряли.
            // Тепер --limit=30 означає "30 РЕАЛЬНИХ спроб перевірки",
            // а не "30 товарів, частина з яких завідомо буде пропущена".
            $candidatePoolSize = min($limit * 5, 500);

            $candidates = $baseQuery
                ->orderByRaw('(SELECT MAX(checked_at) FROM product_price_checks WHERE product_price_checks.ad_id = ads.id) IS NOT NULL')
                ->orderByRaw('(SELECT MAX(checked_at) FROM product_price_checks WHERE product_price_checks.ad_id = ads.id) ASC')
                ->limit($candidatePoolSize)
                ->get();

            $skippedDomainCount = 0;
            $products = $candidates->filter(function ($product) use (&$skippedDomainCount) {
                $checkable = array_filter($this->sourcesOf($product), function ($url) {
                    return !$this->isSkippedDomain($url);
                });
                if (!$checkable) {
                    $skippedDomainCount++;
                    return false;
                }
                return true;
            })->take($limit)->values();

            if ($skippedDomainCount > 0) {
                $this->info("Відфільтровано {$skippedDomainCount} товар(ів) із заздалегідь відомих захищених доменів (не витрачено на них ліміт перевірок)");
            }
        }

        if ($products->isEmpty()) {
            $this->info('Немає товарів з посиланням для перевірки.');
            return 0;
        }

        $this->info('Перевіряю ' . $products->count() . ' товар(ів)');

        foreach ($products as $i => $product) {
            $position = '[' . ($i + 1) . '/' . $products->count() . "] ID={$product->id}";
            $sources = $this->sourcesOf($product);

            if (isset($sources['own'])) {
                $this->info("{$position}, магазин: {$sources['own']}");
                if ($this->isSkippedDomain($sources['own'])) {
                    $this->info('  пропущено (захищений від ботів домен)');
                } else {
                    try {
                        if (!$this->checkOwnSource($product, $sources['own'], !isset($sources['competitor']))) {
                            continue; // оголошення видалено (404 у магазині)
                        }
                    } catch (\Throwable $e) {
                        $this->logCheck($product, null, null, null, false, 'fetch_error', $e->getMessage());
                        $this->error('  Помилка: ' . $e->getMessage());
                        $this->maybeAutoSuspend($product);
                    }
                }
                usleep(300000);
            }

            if (isset($sources['competitor'])) {
                $this->info("{$position}, конкурент: {$sources['competitor']}");
                if ($this->isSkippedDomain($sources['competitor'])) {
                    $this->info('  пропущено (захищений від ботів домен)');
                } else {
                    try {
                        $this->checkCompetitor($product, $sources['competitor']);
                    } catch (\Throwable $e) {
                        $this->logCheck($product, null, null, null, false, 'competitor_fetch_error', $e->getMessage());
                        $this->error('  Помилка: ' . $e->getMessage());
                    }
                }
                usleep(300000);
            }
        }

        return 0;
    }

    /**
     * Посилання на товар: ['own' => url магазину, 'competitor' => url конкурента]
     * (ВАЖЛИВО: $product->url — аксесор "/ads/slug", тому сире значення через getOriginal)
     */
    protected function sourcesOf(Ad $product): array
    {
        return array_filter([
            'own' => (string) $product->getOriginal('url'),
            'competitor' => (string) $product->competitor_url,
        ]);
    }

    protected function fetch(string $url)
    {
        // http_errors=false — щоб самим вирішувати, що робити з 404,
        // а не отримувати виключення й губити конкретний код статусу.
        return $this->http->get($url, [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (compatible; AddnewPriceMonitor/1.0; +https://addnew.biz)',
            ],
            'timeout' => 20,
            'verify' => false,
            'http_errors' => false,
        ]);
    }

    /**
     * Сторінка товару на сайті самого магазину: наявність, 404 → видалення,
     * ціна — лише якщо немає посилання на конкурента ($applyPrice).
     *
     * @return bool false — оголошення видалено
     */
    protected function checkOwnSource(Ad $product, string $sourceUrl, bool $applyPrice): bool
    {
        $response = $this->fetch($sourceUrl);

        if ($response->getStatusCode() === 404) {
            $this->handleProductGone($product, $sourceUrl);
            return false;
        }

        $found = $this->extractProductData((string) $response->getBody());

        if (!$found['price'] && !$found['availability']) {
            $this->logCheck($product, $product->price, null, null, false, 'not_found', 'Структуровані дані не знайдено на сторінці (можливо, тихий редирект на іншу сторінку)');
            $this->warn('  Ані ціни, ані наявності не знайдено на сторінці');
            $this->maybeAutoSuspend($product);
            return true;
        }

        $notes = [];
        $oldPrice = (float) $product->price;

        if ($found['availability']) {
            if ($found['availability'] !== $product->stock) {
                $notes[] = "Наявність: {$product->stock} -> {$found['availability']}";
                $product->stock = $found['availability'];
            } else {
                $notes[] = 'Наявність без змін (' . $found['availability'] . ')';
            }
        }

        [$priceApplied, $foundCurrency] = $applyPrice
            ? $this->applyFoundPrice($product, $found, $notes)
            : [false, $found['currency'] ? strtoupper($found['currency']) : null];

        $product->save();

        $note = implode('; ', $notes);
        $this->logCheck($product, $oldPrice, $found['price'], $foundCurrency, $priceApplied, 'success', $note);
        $this->info('  ' . $note);

        return true;
    }

    /**
     * Сторінка конкурента: ЛИШЕ ціна. Наявність, 404 і недоступність конкурента
     * на наш товар не впливають — тільки фіксуються в лозі.
     */
    protected function checkCompetitor(Ad $product, string $url): void
    {
        $response = $this->fetch($url);
        $oldPrice = (float) $product->price;

        if ($response->getStatusCode() >= 400) {
            $status = $response->getStatusCode() === 404 ? 'competitor_404' : 'competitor_fetch_error';
            $this->logCheck($product, $oldPrice, null, null, false, $status, 'Сторінка конкурента відповіла HTTP ' . $response->getStatusCode() . ' — ціну не змінено');
            $this->warn('  Сторінка конкурента відповіла HTTP ' . $response->getStatusCode() . ' — ціну не змінено');
            return;
        }

        $found = $this->extractProductData((string) $response->getBody());

        if (!$found['price']) {
            $this->logCheck($product, $oldPrice, null, null, false, 'competitor_not_found', 'Ціну на сторінці конкурента не знайдено — ціну не змінено');
            $this->warn('  Ціну на сторінці конкурента не знайдено');
            return;
        }

        $notes = [];
        [$priceApplied, $foundCurrency, $suspicious] = $this->applyFoundPrice($product, $found, $notes);

        if ($priceApplied) {
            $product->save();
        }

        $note = implode('; ', $notes);
        $this->logCheck($product, $oldPrice, $found['price'], $foundCurrency, $priceApplied, $suspicious ? 'competitor_suspicious' : 'competitor_success', $note);
        $this->info('  ' . $note);
    }

    /**
     * Застосовує знайдену ціну, якщо збігається валюта і зміна не підозріла.
     *
     * @return array [застосовано, валюта джерела, підозріла]
     */
    protected function applyFoundPrice(Ad $product, array $found, array &$notes): array
    {
        if (!$found['price']) {
            return [false, null, false];
        }

        $productCurrency = strtoupper(optional($product->currency)->code ?? '');
        $foundCurrency = strtoupper($found['currency'] ?? '');

        if (!$productCurrency || $foundCurrency !== $productCurrency) {
            $notes[] = "Валюта не збігається (товар={$productCurrency}, джерело={$foundCurrency}) — ціну НЕ оновлено";
            return [false, $foundCurrency, false];
        }

        // Ціна в БД ціла — порівнюємо вже округлене значення, інакше 1299.50
        // щогодини виглядала б як «зміна» відносно збережених 1300.
        $oldPrice = (float) $product->price;
        $newPrice = (int) round($found['price']);

        if ($newPrice === (int) round($oldPrice)) {
            $notes[] = 'Ціна без змін';
            return [false, $foundCurrency, false];
        }

        $maxRatio = (float) env('PRICE_MONITOR_MAX_RATIO', 2);
        if ($oldPrice > 0 && $newPrice > 0 && max($newPrice / $oldPrice, $oldPrice / $newPrice) > $maxRatio) {
            $notes[] = "Знайдена ціна {$newPrice} {$foundCurrency} відрізняється від поточної {$oldPrice} більш ніж у {$maxRatio} рази — НЕ застосовано (перевірте вручну)";
            return [false, $foundCurrency, true];
        }

        if ($newPrice <= 0) {
            $notes[] = "Знайдена ціна {$found['price']} некоректна — НЕ застосовано";
            return [false, $foundCurrency, true];
        }

        $product->price = $newPrice;
        $notes[] = "Ціна: {$oldPrice} -> {$newPrice} {$foundCurrency}";

        return [true, $foundCurrency, false];
    }

    /**
     * Джерело підтвердило 404 — сторінки товару вже точно не існує
     * (на відміну від тимчасового збою чи захисту від ботів, де сайт
     * просто "мовчить" або віддає щось незрозуміле). Це достатньо
     * надійна ознака, щоб видалити оголошення одразу, без очікування
     * кількох невдалих спроб поспіль.
     */
    protected function handleProductGone(Ad $product, string $sourceUrl): void
    {
        $this->warn("Джерело повернуло 404 (сторінку видалено) — видаляю оголошення ID={$product->id} з бази");

        // Логуємо ПЕРЕД видаленням, щоб історія причини лишилась навіть
        // після того, як сам товар зникне з таблиці ads.
        $this->logCheck($product, $product->price, null, null, false, 'deleted_404',
            "Джерело повернуло 404 Not Found ({$sourceUrl}) — оголошення видалено автоматично");

        $this->appendDailyReportStat('products_deleted_404_count', 1);

        $product->delete();
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

        $meta = $this->metaTags($html);

        if (isset($meta['product:price:amount'])) {
            $result['price'] = $this->normalizeNumber($meta['product:price:amount']);
            $result['currency'] = $meta['product:price:currency'] ?? 'USD';
        }
        if (isset($meta['product:availability'])) {
            $result['availability'] = $this->normalizeAvailability($meta['product:availability']);
        }

        if (!$result['price'] && isset($meta['price'])) {
            $result['price'] = $this->normalizeNumber($meta['price']);
            $result['currency'] = $meta['pricecurrency'] ?? 'USD';
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
            // AggregateOffer (маркетплейси): ціни немає, є діапазон lowPrice..highPrice
            if (!isset($offers['price']) && isset($offers['lowPrice'])) {
                $offers['price'] = $offers['lowPrice'];
            }
            if (isset($offers['price']) || isset($offers['availability'])) {
                $price = isset($offers['price']) ? $this->normalizeNumber($offers['price']) : null;
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

    /**
     * Мета-теги сторінки як [property|itemprop|name (lowercase) => content],
     * незалежно від порядку атрибутів у тезі.
     */
    protected function metaTags(string $html): array
    {
        $tags = [];
        if (preg_match_all('/<meta\b[^>]*>/i', $html, $m)) {
            foreach ($m[0] as $tag) {
                if (!preg_match('/\bcontent=["\']([^"\']*)["\']/i', $tag, $c)) {
                    continue;
                }
                if (preg_match('/\b(?:property|itemprop|name)=["\']([^"\']+)["\']/i', $tag, $k)) {
                    $key = strtolower($k[1]);
                    if (!isset($tags[$key])) {
                        $tags[$key] = html_entity_decode($c[1], ENT_QUOTES, 'UTF-8');
                    }
                }
            }
        }
        return $tags;
    }

    /**
     * Число з ціни: 1299, 1299.50, "1 299,00", "1.299,00", "1,299.00", "1 299 грн"
     */
    protected function normalizeNumber($raw): ?float
    {
        if (is_int($raw) || is_float($raw)) {
            return (float) $raw;
        }

        // лише цифри й розділювачі (пробіли, нерозривні пробіли, валюта — геть)
        $raw = preg_replace('/[^\d.,]/u', '', (string) $raw);
        if ($raw === '') {
            return null;
        }

        $lastComma = strrpos($raw, ',');
        $lastDot = strrpos($raw, '.');

        if ($lastComma !== false && $lastDot !== false) {
            // обидва є: десятковий — той, що правіше
            $decimal = $lastComma > $lastDot ? ',' : '.';
            $thousands = $decimal === ',' ? '.' : ',';
            $raw = str_replace([$thousands, $decimal], ['', '.'], $raw);
        } elseif ($lastComma !== false || $lastDot !== false) {
            $sep = $lastComma !== false ? ',' : '.';
            $parts = explode($sep, $raw);
            // "1,299" / "1.299.000" — розділювач тисяч; "1299,5" / "39.99" — десятковий
            $isThousands = count($parts) > 2 || strlen(end($parts)) === 3;
            $raw = $isThousands ? str_replace($sep, '', $raw) : str_replace($sep, '.', $raw);
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
            ->where('status', 'not like', 'competitor\_%')
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

            $source = $product->getOriginal('url');
            $this->logCheck($product, null, null, null, false, 'auto_suspended',
                "Оголошення автоматично призупинено після {$threshold} невдалих спроб підряд отримати корисні дані з джерела ({$source})");
            $this->warn("Оголошення ID={$product->id} автоматично ПРИЗУПИНЕНО (джерело недоступне/порожнє {$threshold} рази підряд)");

            $host = parse_url($source, PHP_URL_HOST);
            if ($host) {
                $this->maybeAutoSkipDomain($host);
            }
        }
    }

    /**
     * Якщо кілька РІЗНИХ товарів з одного домену незалежно один від
     * одного дійшли до auto_suspended — це вже не збіг для конкретного
     * товару, а ознака, що ввесь домен захищений від ботів (403,
     * CAPTCHA, Cloudflare-виклик тощо). Автоматично додаємо домен у
     * "Виключені домени", щоб майбутні прогони більше не витрачали на
     * нього ліміт перевірок — так само, як домени, додані вручну.
     */
    protected function maybeAutoSkipDomain(string $host): void
    {
        if (in_array($host, \App\SkippedDomain::list(), true)) {
            return; // вже в списку
        }

        $threshold = (int) env('DOMAIN_AUTO_SKIP_AFTER_ADS', 2);

        // Тільки вже призупинені товари з посиланням, що містить цей
        // хост — набагато вужча (і дешевша) вибірка, ніж перебирати всі
        // активні товари.
        $suspendedAdIds = Ad::where('is_product', 1)
            ->where('status', 0)
            ->where(function ($q) use ($host) {
                $q->where('url', 'LIKE', "%{$host}%");
            })
            ->pluck('id');

        if ($suspendedAdIds->count() < $threshold) {
            return;
        }

        $autoSuspendedCount = 0;
        foreach ($suspendedAdIds as $adId) {
            $lastCheck = ProductPriceCheck::where('ad_id', $adId)
                ->orderByDesc('checked_at')
                ->first();
            if ($lastCheck && $lastCheck->status === 'auto_suspended') {
                $autoSuspendedCount++;
            }
        }

        if ($autoSuspendedCount >= $threshold) {
            \App\SkippedDomain::firstOrCreate(
                ['domain' => $host],
                ['note' => "Автоматично додано: {$autoSuspendedCount} товар(ів) із цього домену незалежно призупинились через недоступність джерела (products:monitor-prices, " . now()->toDateString() . ')']
            );
            $this->warn("Домен {$host} автоматично додано до \"Виключених доменів\" — {$autoSuspendedCount} товар(ів) підряд не вдалось перевірити.");
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
