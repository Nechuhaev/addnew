<?php

namespace App\Services\Seo;

use GuzzleHttp\Client;

/**
 * Bing Webmaster Tools API (JSON) через API-ключ користувача: BING_WEBMASTER_API_KEY.
 * Ключ передається лише параметром apikey в адресі запиту (так вимагає Bing), тому в лог адресу не пишемо.
 * Сайт — BING_SITE_URL (за замовчуванням APP_URL зі слешем у кінці, як він доданий у Bing Webmaster).
 */
class BingClient
{
    const ENDPOINT = 'https://ssl.bing.com/webmaster/api.svc/json/';

    /** @var Client */
    protected $http;
    protected $key;
    protected $siteUrl;

    public function __construct(?Client $http = null)
    {
        $this->http = $http ?: new Client(['timeout' => 60]);
        $this->key = (string) env('BING_WEBMASTER_API_KEY', '');
        $this->siteUrl = rtrim((string) env('BING_SITE_URL', config('app.url')), '/') . '/';
    }

    public function configError(): ?string
    {
        return $this->key === '' ? 'BING_WEBMASTER_API_KEY не задано в .env' : null;
    }

    public function siteUrl(): string
    {
        return $this->siteUrl;
    }

    /** Покази й кліки по днях: [Y-m-d => ['clicks' => n, 'impressions' => n]] */
    public function trafficByDay(): array
    {
        $out = [];
        foreach ($this->call('GetRankAndTrafficStats') as $row) {
            $date = self::date($row['Date'] ?? null);
            if ($date) {
                $out[$date] = ['clicks' => (int) ($row['Clicks'] ?? 0), 'impressions' => (int) ($row['Impressions'] ?? 0)];
            }
        }

        return $out;
    }

    /** Сканування й індекс по днях: [Y-m-d => ['in_index' => n, 'crawled_pages' => n, 'crawl_errors' => n]] */
    public function crawlByDay(): array
    {
        $out = [];
        foreach ($this->call('GetCrawlStats') as $row) {
            $date = self::date($row['Date'] ?? null);
            if ($date) {
                $out[$date] = [
                    'in_index' => (int) ($row['InIndex'] ?? 0),
                    'crawled_pages' => (int) ($row['CrawledPages'] ?? 0),
                    'crawl_errors' => (int) ($row['CrawlErrors'] ?? 0),
                ];
            }
        }

        return $out;
    }

    protected function call(string $method): array
    {
        try {
            $response = $this->http->get(self::ENDPOINT . $method, [
                'query' => ['siteUrl' => $this->siteUrl, 'apikey' => $this->key],
                'headers' => ['Accept' => 'application/json'],
            ]);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            // Повідомлення Guzzle містить адресу з ключем — віддаємо лише код і тіло відповіді
            $code = $e->getResponse() ? $e->getResponse()->getStatusCode() : 0;
            $body = $e->getResponse() ? mb_substr((string) $e->getResponse()->getBody(), 0, 300) : 'немає відповіді';
            throw new \RuntimeException("Bing API {$method}: HTTP {$code} {$body}");
        }
        $data = json_decode((string) $response->getBody(), true);

        return is_array($data['d'] ?? null) ? $data['d'] : [];
    }

    /** «/Date(1699920000000-0800)/» → Y-m-d */
    public static function date($value): ?string
    {
        if (is_string($value) && preg_match('~/Date\((-?\d+)~', $value, $m)) {
            return gmdate('Y-m-d', intdiv((int) $m[1], 1000));
        }

        return null;
    }
}
