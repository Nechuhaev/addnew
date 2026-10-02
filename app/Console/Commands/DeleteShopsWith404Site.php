<?php

namespace App\Console\Commands;

use App\Ad;
use App\User;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class DeleteShopsWith404Site extends Command
{
    /**
     * php artisan shops:delete-404
     *
     * ОДНОРАЗОВА команда: знаходить магазини, чий сайт (site_url)
     * повертає САМЕ HTTP 404 (на відміну від shops:check-domains, яка
     * шукає взагалі НЕДОСТУПНІ домени — мережевий збій). 404 означає
     * "домен живий, але конкретної сторінки/сайту за цією адресою вже
     * немає" — сильніша ознака, що магазин остаточно закрився.
     *
     * Без --apply — тільки показує список (і кількість товарів, які
     * будуть втрачені), нічого не змінюючи. З --apply — ВИДАЛЯЄ знайдені
     * магазини РАЗОМ З УСІМА ЇХНІМИ ТОВАРАМИ. Незворотна дія.
     */
    protected $signature = 'shops:delete-404 {--apply} {--limit=}';

    protected $description = 'Знаходить (і, з --apply, видаляє) магазини, чий сайт (site_url) повертає HTTP 404';

    /** @var Client */
    protected $http;

    public function __construct()
    {
        parent::__construct();
        $this->http = new Client();
    }

    public function handle()
    {
        $query = User::whereHas('ads', function ($q) {
                $q->where('is_product', 1);
            })
            ->whereNotNull('site_url')
            ->where('site_url', '!=', '')
            ->orderBy('id');

        if ($limit = $this->option('limit')) {
            $query->limit((int) $limit);
        }

        $shops = $query->get();

        if ($shops->isEmpty()) {
            $this->info('Немає магазинів із заповненим site_url.');
            return 0;
        }

        $apply = $this->option('apply');

        $this->info('Перевіряю ' . $shops->count() . ' магазин(ів)' . ($apply ? ' (режим --apply: 404 одразу видаляються)' : ' (лише перегляд, без --apply)'));

        $found404 = [];

        foreach ($shops as $i => $shop) {
            $status = $this->checkStatus($shop->site_url);
            $label = $status === 404 ? '404 — буде видалено' : ($status ?? 'мережева помилка (не чіпаємо)');

            $this->info('[' . ($i + 1) . '/' . $shops->count() . "] ID={$shop->id} {$shop->username} ({$shop->site_url}): {$label}");

            if ($status === 404) {
                $found404[] = $shop;
            }

            usleep(150000);
        }

        $this->info('');

        if (empty($found404)) {
            $this->info('Жоден магазин не повернув 404. Нічого видаляти.');
            return 0;
        }

        $totalProducts = 0;
        foreach ($found404 as $shop) {
            $totalProducts += Ad::where('user_id', $shop->id)->where('is_product', 1)->count();
        }

        $this->info("=== Знайдено {$shops->count()} перевірених, із них " . count($found404) . " із сайтом 404 (разом товарів: {$totalProducts}) ===");
        foreach ($found404 as $shop) {
            $productsCount = Ad::where('user_id', $shop->id)->where('is_product', 1)->count();
            $this->line("  ID={$shop->id}: {$shop->username} — {$shop->site_url} ({$productsCount} товар(ів))");
        }

        if (!$apply) {
            $this->info('');
            $this->info('Це був лише перегляд — жодних змін не внесено. Щоб реально видалити перелічені вище магазини РАЗОМ З УСІМА ЇХНІМИ ТОВАРАМИ, запустіть:');
            $this->info('  php artisan shops:delete-404 --apply');
            return 0;
        }

        $this->info('');
        $this->info('Видаляю ' . count($found404) . ' магазин(ів)...');
        foreach ($found404 as $shop) {
            Ad::where('user_id', $shop->id)->delete();
            $shop->delete();
            $this->info("  Видалено ID={$shop->id}: {$shop->username}");
        }
        $this->info('Готово.');

        return 0;
    }

    /**
     * Повертає реальний HTTP-статус, або null при мережевій помилці
     * (DNS, timeout, відмова з'єднання — це НЕ 404, цим займається
     * окрема команда shops:check-domains).
     */
    protected function checkStatus(string $url): ?int
    {
        try {
            $response = $this->http->get($url, [
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (compatible; AddnewShopChecker/1.0; +https://addnew.biz)',
                ],
                'timeout' => 10,
                'verify' => false,
                'http_errors' => false,
                'allow_redirects' => true,
            ]);
            return $response->getStatusCode();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
