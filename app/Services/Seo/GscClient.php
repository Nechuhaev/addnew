<?php

namespace App\Services\Seo;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

/**
 * Google Search Console API через сервісний акаунт (JWT без SDK, як у content:check-index):
 * URL Inspection (статус індексації однієї адреси, ліміт 2000/добу на властивість)
 * і Search Analytics (сторінки з показами — одним запитом тисячі адрес).
 * Ключ: GOOGLE_SERVICE_ACCOUNT_JSON_PATH, властивість: GOOGLE_SEARCH_CONSOLE_SITE_URL.
 */
class GscClient
{
    const TOKEN_CACHE_KEY = 'google_search_console_access_token';
    const SCOPE = 'https://www.googleapis.com/auth/webmasters.readonly';
    const INDEXING_SCOPE = 'https://www.googleapis.com/auth/indexing';

    /** @var Client */
    protected $http;
    protected $keyPath;
    protected $siteUrl;

    public function __construct(?Client $http = null)
    {
        $this->http = $http ?: new Client(['timeout' => 30]);
        $this->keyPath = env('GOOGLE_SERVICE_ACCOUNT_JSON_PATH', storage_path('app/google-service-account.json'));
        $this->siteUrl = (string) env('GOOGLE_SEARCH_CONSOLE_SITE_URL', '');
    }

    /** null — усе гаразд, інакше текст помилки конфігурації */
    public function configError(): ?string
    {
        if (!is_file($this->keyPath)) {
            return "Файл ключа сервісного акаунту не знайдено: {$this->keyPath}";
        }
        if ($this->siteUrl === '') {
            return 'GOOGLE_SEARCH_CONSOLE_SITE_URL не задано в .env (напр. https://addnew.biz/)';
        }

        return null;
    }

    /** indexStatusResult з URL Inspection API */
    public function inspect(string $url): array
    {
        $response = $this->http->post('https://searchconsole.googleapis.com/v1/urlInspection/index:inspect', [
            'headers' => ['Authorization' => 'Bearer ' . $this->token()],
            'json' => ['inspectionUrl' => $url, 'siteUrl' => $this->siteUrl],
        ]);
        $data = json_decode((string) $response->getBody(), true) ?: [];

        return $data['inspectionResult']['indexStatusResult'] ?? [];
    }

    /**
     * Google Indexing API: повідомити про нову/оновлену адресу (URL_UPDATED). Ліміт — 200/добу на проєкт.
     * Потрібно: увімкнений Indexing API у Google Cloud і сервісний акаунт — власник (Owner) властивості в Search Console.
     */
    public function publishUrl(string $url): array
    {
        $response = $this->http->post('https://indexing.googleapis.com/v3/urlNotifications:publish', [
            'headers' => ['Authorization' => 'Bearer ' . $this->token(self::INDEXING_SCOPE)],
            'json' => ['url' => $url, 'type' => 'URL_UPDATED'],
        ]);

        return json_decode((string) $response->getBody(), true) ?: [];
    }

    /**
     * Усі сторінки з показами за період: [url => impressions]
     */
    public function pagesWithImpressions(string $startDate, string $endDate): array
    {
        $out = [];
        $endpoint = 'https://www.googleapis.com/webmasters/v3/sites/' . rawurlencode($this->siteUrl) . '/searchAnalytics/query';
        for ($start = 0; $start < 250000; $start += 25000) {
            $response = $this->http->post($endpoint, [
                'headers' => ['Authorization' => 'Bearer ' . $this->token()],
                'json' => [
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                    'dimensions' => ['page'],
                    'rowLimit' => 25000,
                    'startRow' => $start,
                ],
                'timeout' => 120,
            ]);
            $rows = (json_decode((string) $response->getBody(), true) ?: [])['rows'] ?? [];
            foreach ($rows as $row) {
                $out[$row['keys'][0]] = (int) $row['impressions'];
            }
            if (count($rows) < 25000) {
                break;
            }
        }

        return $out;
    }

    protected function token(string $scope = self::SCOPE): string
    {
        $cacheKey = $scope === self::SCOPE ? self::TOKEN_CACHE_KEY : self::TOKEN_CACHE_KEY . ':' . md5($scope);
        $cached = Cache::get($cacheKey);
        if ($cached) {
            return $cached;
        }

        $key = json_decode((string) file_get_contents($this->keyPath), true);
        if (empty($key['client_email']) || empty($key['private_key'])) {
            throw new \RuntimeException('Некоректний файл ключа сервісного акаунту (немає client_email/private_key)');
        }
        $tokenUri = $key['token_uri'] ?? 'https://oauth2.googleapis.com/token';
        $now = time();
        $input = self::b64(json_encode(['alg' => 'RS256', 'typ' => 'JWT'])) . '.' . self::b64(json_encode([
            'iss' => $key['client_email'],
            'scope' => $scope,
            'aud' => $tokenUri,
            'iat' => $now,
            'exp' => $now + 3600,
        ]));
        $signature = '';
        if (!openssl_sign($input, $signature, $key['private_key'], OPENSSL_ALGO_SHA256)) {
            throw new \RuntimeException('Не вдалося підписати JWT приватним ключем сервісного акаунту');
        }

        $response = $this->http->post($tokenUri, ['form_params' => [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $input . '.' . self::b64($signature),
        ]]);
        $data = json_decode((string) $response->getBody(), true);
        if (empty($data['access_token'])) {
            throw new \RuntimeException('Google не повернув access_token: ' . json_encode($data));
        }
        Cache::put($cacheKey, $data['access_token'], max(60, (int) ($data['expires_in'] ?? 3600) - 120));

        return $data['access_token'];
    }

    protected static function b64(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
