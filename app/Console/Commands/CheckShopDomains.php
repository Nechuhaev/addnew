<?php

namespace App\Console\Commands;

use App\User;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class CheckShopDomains extends Command
{
    /**
     * php artisan shops:check-domains
     *
     * ОДНОРАЗОВА команда: перевіряє доступність домену (site_url) для
     * ВСІХ магазинів одразу (на відміну від shops:check-email, яка йде
     * чергою "давно не перевірені" й лише шукає email, не займаючись
     * самим полем site_url).
     *
     * Без --apply — тільки показує список недоступних доменів, нічого
     * не змінюючи (для перегляду перед реальним запуском).
     * З --apply — додатково ОЧИЩУЄ поле site_url для магазинів, чий
     * домен виявився недоступний. Сам акаунт магазину й усі його
     * товари на дошці НЕ чіпаються — лише посилання на зовнішній сайт.
     */
    protected $signature = 'shops:check-domains {--apply} {--limit=}';

    protected $description = 'Перевіряє доступність доменів усіх магазинів; без --apply лише показує список недоступних';

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

        $this->info('Перевіряю ' . $shops->count() . ' магазин(ів)' . ($apply ? ' (режим --apply: недоступні одразу очищуються)' : ' (лише перегляд, без --apply)'));

        $unreachable = [];

        foreach ($shops as $i => $shop) {
            $reachable = $this->isReachable($shop->site_url);
            $status = $reachable ? 'OK' : 'НЕДОСТУПНИЙ';

            $this->info('[' . ($i + 1) . '/' . $shops->count() . "] ID={$shop->id} {$shop->username} ({$shop->site_url}): {$status}");

            if (!$reachable) {
                $unreachable[] = $shop;
            }

            usleep(200000);
        }

        $this->info('');
        $this->info('=== Підсумок: ' . count($unreachable) . ' недоступних доменів із ' . $shops->count() . ' перевірених ===');
        foreach ($unreachable as $shop) {
            $this->line("  ID={$shop->id}: {$shop->username} — {$shop->site_url}");
        }

        if (empty($unreachable)) {
            return 0;
        }

        if ($apply) {
            $this->info('');
            $this->info('Очищую site_url для ' . count($unreachable) . ' магазин(ів)...');
            foreach ($unreachable as $shop) {
                $shop->site_url = null;
                $shop->save();
            }
            $this->info('Готово. Акаунти й товари цих магазинів на дошці НЕ зачеплено.');
        } else {
            $this->info('');
            $this->info('Це був лише перегляд — жодних змін не внесено. Щоб реально очистити site_url для перелічених вище магазинів, запустіть:');
            $this->info('  php artisan shops:check-domains --apply');
        }

        return 0;
    }

    /**
     * Будь-яка HTTP-відповідь (навіть 404/500) означає, що домен
     * резолвиться і сервер відповідає — це вже "доступний" для наших
     * цілей. Недоступним вважаємо лише справжній мережевий збій (DNS,
     * timeout, відмова з'єднання, SSL). Той самий підхід, що й у
     * shops:check-email.
     */
    protected function isReachable(string $url): bool
    {
        try {
            $this->http->get($url, [
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (compatible; AddnewShopChecker/1.0; +https://addnew.biz)',
                ],
                'timeout' => 10,
                'verify' => false,
                'http_errors' => false,
            ]);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
