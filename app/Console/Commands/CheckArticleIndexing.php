<?php

namespace App\Console\Commands;

use App\Article;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CheckArticleIndexing extends Command
{
    /**
     * php artisan content:check-index
     *
     * Перевіряє статус індексації статей у Google через URL Inspection API
     * (Search Console). Використовує сервісний акаунт Google Cloud —
     * авторизація через JWT, підписаний приватним ключем (без SDK,
     * тільки Guzzle + вбудований openssl_sign, у стилі решти проєкту).
     *
     * Ліміти Google: 2000 запитів/день, 600/хв на властивість —
     * запускаємо з розумним лімітом за раз (CHECK_INDEX_PER_RUN).
     */
    protected $signature = 'content:check-index {--limit=}';

    protected $description = 'Перевіряє статус індексації статей у Google Search Console';

    /** @var Client */
    protected $http;

    const TOKEN_CACHE_KEY = 'google_search_console_access_token';
    const SCOPE = 'https://www.googleapis.com/auth/webmasters.readonly';

    public function __construct()
    {
        parent::__construct();
        $this->http = new Client();
    }

    public function handle()
    {
        $keyPath = env('GOOGLE_SERVICE_ACCOUNT_JSON_PATH', storage_path('app/google-service-account.json'));
        $siteUrl = env('GOOGLE_SEARCH_CONSOLE_SITE_URL');

        if (!file_exists($keyPath)) {
            $this->error("Файл ключа сервісного акаунту не знайдено: {$keyPath}");
            return 1;
        }
        if (empty($siteUrl)) {
            $this->error('GOOGLE_SEARCH_CONSOLE_SITE_URL не задано в .env (напр. https://addnew.biz/)');
            return 1;
        }

        try {
            $accessToken = $this->getAccessToken($keyPath);
        } catch (\Throwable $e) {
            $this->error('Не вдалося отримати access token: ' . $e->getMessage());
            return 1;
        }

        $limit = (int) ($this->option('limit') ?: env('CHECK_INDEX_PER_RUN', 50));

        // Пріоритет: спершу статті, яких ще ЖОДНОГО разу не перевіряли,
        // потім ті, що перевірялись найдавніше.
        $articleIds = DB::table('articles')
            ->leftJoin('article_index_status', 'article_index_status.article_id', '=', 'articles.id')
            ->orderByRaw('article_index_status.checked_at IS NOT NULL, article_index_status.checked_at ASC')
            ->limit($limit)
            ->pluck('articles.id');

        if ($articleIds->isEmpty()) {
            $this->info('Немає статей для перевірки.');
            return 0;
        }

        $this->info('Перевіряю індексацію для ' . $articleIds->count() . ' статей(тю)');

        foreach ($articleIds as $i => $articleId) {
            $article = Article::find($articleId);
            if (!$article) {
                continue;
            }

            $url = route('blog.article', $article->slug);

            try {
                $result = $this->inspectUrl($accessToken, $url, $siteUrl);
                $this->storeResult($articleId, $result);
                $verdict = $result['inspectionResult']['indexStatusResult']['verdict'] ?? 'UNKNOWN';
                $this->info('[' . ($i + 1) . '/' . $articleIds->count() . "] {$url} → {$verdict}");
            } catch (\Throwable $e) {
                $this->error("Помилка перевірки {$url}: " . $e->getMessage());
            }

            // 600 запитів/хв ліміт — невелика пауза про всяк випадок,
            // якщо колись піднімемо $limit значно вище.
            usleep(150000); // 0.15 сек
        }

        return 0;
    }

    // -----------------------------------------------------------------
    // Google OAuth2 — service account JWT flow (без SDK)
    // -----------------------------------------------------------------

    protected function getAccessToken(string $keyPath): string
    {
        $cached = Cache::get(self::TOKEN_CACHE_KEY);
        if ($cached) {
            return $cached;
        }

        $key = json_decode(file_get_contents($keyPath), true);
        if (empty($key['client_email']) || empty($key['private_key'])) {
            throw new \RuntimeException('Некоректний файл ключа сервісного акаунту (немає client_email/private_key)');
        }

        $tokenUri = $key['token_uri'] ?? 'https://oauth2.googleapis.com/token';
        $now = time();

        $header = $this->base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $claims = $this->base64UrlEncode(json_encode([
            'iss' => $key['client_email'],
            'scope' => self::SCOPE,
            'aud' => $tokenUri,
            'iat' => $now,
            'exp' => $now + 3600,
        ]));

        $signingInput = "{$header}.{$claims}";
        $signature = '';
        $signed = openssl_sign($signingInput, $signature, $key['private_key'], OPENSSL_ALGO_SHA256);
        if (!$signed) {
            throw new \RuntimeException('Не вдалося підписати JWT приватним ключем сервісного акаунту');
        }

        $jwt = $signingInput . '.' . $this->base64UrlEncode($signature);

        $response = $this->http->post($tokenUri, [
            'form_params' => [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ],
            'timeout' => 30,
        ]);

        $data = json_decode((string) $response->getBody(), true);
        if (empty($data['access_token'])) {
            throw new \RuntimeException('Google не повернув access_token: ' . json_encode($data));
        }

        // Кешуємо трохи менше за реальний час дії токена (зазвичай 3600 сек).
        $ttl = max(60, (int) ($data['expires_in'] ?? 3600) - 120);
        Cache::put(self::TOKEN_CACHE_KEY, $data['access_token'], $ttl);

        return $data['access_token'];
    }

    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    // -----------------------------------------------------------------
    // URL Inspection API
    // -----------------------------------------------------------------

    protected function inspectUrl(string $accessToken, string $inspectionUrl, string $siteUrl): array
    {
        $response = $this->http->post('https://searchconsole.googleapis.com/v1/urlInspection/index:inspect', [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'inspectionUrl' => $inspectionUrl,
                'siteUrl' => $siteUrl,
            ],
            'timeout' => 30,
        ]);

        return json_decode((string) $response->getBody(), true) ?? [];
    }

    protected function storeResult(int $articleId, array $result): void
    {
        $status = $result['inspectionResult']['indexStatusResult'] ?? [];

        $lastCrawlTime = null;
        if (!empty($status['lastCrawlTime'])) {
            try {
                $lastCrawlTime = Carbon::parse($status['lastCrawlTime']);
            } catch (\Throwable $e) {
                $lastCrawlTime = null;
            }
        }

        $values = [
            'verdict' => $status['verdict'] ?? null,
            'coverage_state' => $status['coverageState'] ?? null,
            'indexing_state' => $status['indexingState'] ?? null,
            'robots_txt_state' => $status['robotsTxtState'] ?? null,
            'last_crawl_time' => $lastCrawlTime,
            'checked_at' => now(),
            'raw_response' => json_encode($result),
            'updated_at' => now(),
        ];

        $exists = DB::table('article_index_status')->where('article_id', $articleId)->exists();
        if ($exists) {
            DB::table('article_index_status')->where('article_id', $articleId)->update($values);
        } else {
            DB::table('article_index_status')->insert(array_merge($values, [
                'article_id' => $articleId,
                'created_at' => now(),
            ]));
        }
    }
}