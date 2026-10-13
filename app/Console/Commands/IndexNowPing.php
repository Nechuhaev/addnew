<?php

namespace App\Console\Commands;

use App\Ad;
use App\Services\SeoRules;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * IndexNow (Bing, Yandex, Seznam…; на індекс Bing спирається пошук ChatGPT і Copilot):
 * надсилає адреси нових оголошень (uk і ru) і знятих із публікації — щоб пошуковики
 * оновили їх одразу, а не чекали сканування. Чутливі оголошення (SeoRules) не надсилаються.
 *
 * Потрібно в .env: INDEXNOW_KEY=<8–128 символів a-z, 0-9, ->; файл ключа віддає /indexnow-key.txt.
 * За розкладом — кожні 15 хв; між запусками пам'ятає час останньої відправки.
 */
class IndexNowPing extends Command
{
    protected $signature = 'seo:indexnow {--since= : З якого часу брати оголошення (напр. "2 hours ago")} {--dry-run : Лише показати адреси}';

    protected $description = 'IndexNow: надсилає нові й зняті з публікації оголошення в Bing та інші пошуковики';

    const ENDPOINT = 'https://api.indexnow.org/indexnow';
    const LAST_RUN_KEY = 'indexnow_last_run';
    const MAX_URLS = 10000;

    public function handle()
    {
        $key = (string) config('seo.indexnow_key', '');
        if (!preg_match('/^[a-zA-Z0-9\-]{8,128}$/', $key)) {
            $this->warn('INDEXNOW_KEY не задано або має неправильний формат (8–128 символів a-z, 0-9, -). Нічого не надіслано.');

            return 0;
        }

        $now = now();
        $since = $this->option('since')
            ? \Carbon\Carbon::parse($this->option('since'))
            : \Carbon\Carbon::parse(Cache::get(self::LAST_RUN_KEY, $now->copy()->subHour()->toDateTimeString()));

        $urls = [];
        // Нові активні оголошення
        Ad::where('status', 1)->where('created_at', '>=', $since)
            ->select('id', 'slug', 'name', 'content', 'category_id')
            ->orderBy('id')->chunk(500, function ($ads) use (&$urls) {
                foreach ($ads as $ad) {
                    if ($ad->slug && !SeoRules::isSensitiveAd($ad)) {
                        $urls[] = route('ad.page', ['slug' => $ad->slug]);
                        $urls[] = route('ru.ad.page', ['slug' => $ad->slug]);
                    }
                }
            });
        // Зняті з публікації — щоб пошуковик швидше прибрав їх із видачі
        Ad::where('status', '!=', 1)->where('updated_at', '>=', $since)
            ->select('id', 'slug')->orderBy('id')->chunk(500, function ($ads) use (&$urls) {
                foreach ($ads as $ad) {
                    if ($ad->slug) {
                        $urls[] = route('ad.page', ['slug' => $ad->slug]);
                        $urls[] = route('ru.ad.page', ['slug' => $ad->slug]);
                    }
                }
            });

        $urls = array_values(array_unique($urls));
        $this->info('Адрес для IndexNow: ' . count($urls) . ' (з ' . $since->toDateTimeString() . ')');

        if ($this->option('dry-run')) {
            foreach (array_slice($urls, 0, 50) as $u) {
                $this->line('  ' . $u);
            }

            return 0;
        }

        if ($urls) {
            $host = parse_url(url('/'), PHP_URL_HOST);
            $client = new Client(['timeout' => 20]);
            foreach (array_chunk($urls, self::MAX_URLS) as $batch) {
                try {
                    $res = $client->post(self::ENDPOINT, [
                        'json' => [
                            'host' => $host,
                            'key' => $key,
                            'keyLocation' => url('/indexnow-key.txt'),
                            'urlList' => $batch,
                        ],
                        'http_errors' => false,
                    ]);
                    $code = $res->getStatusCode();
                    // 200/202 — прийнято; 403 — ключ не підтверджено; 422 — адреси не з цього хоста; 429 — забагато запитів
                    $this->line("IndexNow: HTTP {$code}, адрес: " . count($batch));
                    if (!in_array($code, [200, 202], true)) {
                        $this->error('IndexNow відхилив запит: ' . mb_substr((string) $res->getBody(), 0, 300));

                        return 1;   // час не зсуваємо — наступний запуск надішле ці адреси ще раз
                    }
                } catch (\Throwable $e) {
                    $this->error('IndexNow: ' . $e->getMessage());

                    return 1;
                }
            }
        }

        Cache::forever(self::LAST_RUN_KEY, $now->toDateTimeString());

        return 0;
    }
}
