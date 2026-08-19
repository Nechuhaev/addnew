<?php

namespace App\Console\Commands;

use App\Ad;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckAdImages extends Command
{
    /**
     * php artisan ads:check-images
     *
     * Перевіряє, чи реально доступні зображення оголошень (HEAD-запит).
     * Якщо картинка недоступна (403/404 на S3 тощо) — замінює поле
     * напряму в БД на локальний placeholder. Це обходить проблему
     * mod_pagespeed: сервер підміняє <img src> ще ДО браузера, тому
     * клієнтський onerror на биту S3-адресу спрацювати не встигає —
     * потрібно, щоб у БД одразу лежав робочий URL.
     */
    protected $signature = 'ads:check-images {--limit=}';

    protected $description = 'Перевіряє доступність зображень оголошень і замінює биті на локальний placeholder';

    const PLACEHOLDER = '/assets/front/img/placeholder.png';

    /** @var Client */
    protected $http;

    public function __construct()
    {
        parent::__construct();
        $this->http = new Client();
    }

    public function handle()
    {
        $limit = (int) ($this->option('limit') ?: env('IMAGE_CHECK_PER_RUN', 100));

        $ads = Ad::query()
            ->select(['id', 'image', 'images'])
            ->where('image', 'LIKE', 'http%') // тільки зовнішні URL; локальні шляхи не перевіряємо
            ->orderByRaw('(SELECT MAX(checked_at) FROM ad_image_checks WHERE ad_image_checks.ad_id = ads.id) IS NOT NULL')
            ->orderByRaw('(SELECT MAX(checked_at) FROM ad_image_checks WHERE ad_image_checks.ad_id = ads.id) ASC')
            ->limit($limit)
            ->get();

        if ($ads->isEmpty()) {
            $this->info('Немає оголошень із зовнішніми URL зображень для перевірки.');
            return 0;
        }

        $this->info('Перевіряю зображення для ' . $ads->count() . ' оголошень(ня)');

        $fixedCount = 0;

        foreach ($ads as $i => $ad) {
            $mainOk = $this->isReachable($ad->image);

            $secondaryUrls = array_filter($ad->images ?: []);
            $secondaryResults = [];
            foreach ($secondaryUrls as $url) {
                if (Str::startsWith($url, 'http')) {
                    $secondaryResults[$url] = $this->isReachable($url);
                }
            }

            $changed = false;

            if (!$mainOk) {
                $ad->image = self::PLACEHOLDER;
                $changed = true;
            }

            $validSecondary = array_keys(array_filter($secondaryResults));
            if (count($validSecondary) !== count($secondaryUrls)) {
                $ad->images = $validSecondary;
                $changed = true;
            }

            if ($changed) {
                $ad->save();
                $fixedCount++;
            }

            $this->logCheck($ad->id, $mainOk ? 'ok' : 'broken');

            $status = $mainOk ? 'OK' : 'ВИПРАВЛЕНО (плейсхолдер)';
            $this->info('[' . ($i + 1) . '/' . $ads->count() . "] ID={$ad->id}: {$status}");

            usleep(150000); // 0.15с пауза — не бомбити S3 запитами занадто швидко
        }

        $this->info("Готово. Виправлено оголошень: {$fixedCount} з {$ads->count()} перевірених.");

        return 0;
    }

    protected function isReachable(?string $url): bool
    {
        if (empty($url) || !Str::startsWith($url, 'http')) {
            return true; // локальний шлях чи порожньо — не наша турбота тут
        }

        try {
            $response = $this->http->head($url, [
                'timeout' => 10,
                'http_errors' => false,
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (compatible; AddnewImageChecker/1.0; +https://addnew.biz)',
                ],
            ]);

            return $response->getStatusCode() >= 200 && $response->getStatusCode() < 300;
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function logCheck(int $adId, string $status): void
    {
        DB::table('ad_image_checks')->insert([
            'ad_id' => $adId,
            'status' => $status,
            'checked_at' => now(),
        ]);
    }
}