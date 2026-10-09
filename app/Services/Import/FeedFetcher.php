<?php

namespace App\Services\Import;

use GuzzleHttp\Client;

/**
 * Завантаження фіда за URL магазину: лише http/https на публічні адреси
 * (без localhost і внутрішніх мереж), до 25 МБ, тайм-аут 60 с.
 */
class FeedFetcher
{
    const MAX_BYTES = 26214400; // 25 МБ
    const TIMEOUT = 60;

    /**
     * @return string шлях до збереженого файлу
     * @throws \RuntimeException з текстом для користувача (uk)
     */
    public static function download(string $url, string $targetBase): string
    {
        static::assertPublicUrl($url);

        $ext = strtolower(pathinfo((string) parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
        $ext = in_array($ext, ['xml', 'yml', 'csv', 'txt'], true) ? $ext : 'xml';
        $dir = storage_path('app/imports');
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $path = $dir . '/' . $targetBase . '.' . $ext;

        $client = new Client([
            'timeout' => self::TIMEOUT,
            'connect_timeout' => 15,
            'allow_redirects' => [
                'max' => 5,
                'protocols' => ['http', 'https'],
                'on_redirect' => function ($request, $response, $uri) {
                    static::assertPublicUrl((string) $uri);
                },
            ],
            'http_errors' => false,
            'verify' => false,
        ]);

        $received = 0;
        try {
            $response = $client->get($url, [
                'sink' => $path,
                'headers' => ['User-Agent' => 'Mozilla/5.0 (compatible; AddnewFeedBot/1.0; +https://addnew.biz)'],
                'progress' => function ($total, $downloaded) use (&$received) {
                    $received = $downloaded;
                    if ($downloaded > self::MAX_BYTES || $total > self::MAX_BYTES) {
                        throw new \RuntimeException('too_big');
                    }
                },
            ]);
        } catch (\Throwable $e) {
            @unlink($path);
            if (strpos($e->getMessage(), 'too_big') !== false) {
                throw new \RuntimeException('Файл фіда більший за 25 МБ.');
            }
            throw new \RuntimeException('Не вдалося завантажити фід: ' . static::shortError($e->getMessage()));
        }

        $code = $response->getStatusCode();
        if ($code !== 200) {
            @unlink($path);
            throw new \RuntimeException("Сервер магазину відповів кодом {$code}.");
        }
        if (!is_file($path) || filesize($path) === 0) {
            @unlink($path);
            throw new \RuntimeException('Фід порожній.');
        }

        return $path;
    }

    public static function assertPublicUrl(string $url): void
    {
        $allowPrivate = (bool) config('services.feeds.allow_private', false); // лише для локальних тестів
        $parts = parse_url($url);
        $scheme = strtolower($parts['scheme'] ?? '');
        $host = $parts['host'] ?? '';
        if (!in_array($scheme, ['http', 'https'], true) || $host === '') {
            throw new \RuntimeException('Посилання має починатися з http:// або https://');
        }
        $ips = filter_var($host, FILTER_VALIDATE_IP) ? [$host] : (gethostbynamel($host) ?: []);
        if (!$ips) {
            throw new \RuntimeException('Не вдалося знайти сайт за цим посиланням (DNS).');
        }
        foreach ($allowPrivate ? [] : $ips as $ip) {
            if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                throw new \RuntimeException('Посилання веде на внутрішню адресу — такі фіди не підтримуються.');
            }
        }
    }

    protected static function shortError(string $message): string
    {
        if (stripos($message, 'timed out') !== false || stripos($message, 'timeout') !== false) {
            return 'сайт не відповів вчасно.';
        }
        if (stripos($message, 'resolve') !== false) {
            return 'сайт не знайдено.';
        }
        if (stripos($message, 'SSL') !== false) {
            return 'помилка SSL-з\'єднання.';
        }
        return mb_substr(preg_replace('/\s+/', ' ', $message), 0, 200);
    }
}
