<?php

namespace App\Console\Commands\Concerns;

use Illuminate\Support\Str;

/**
 * Дає artisan-командам метод fetchAndSaveImage() — перебирає фотостоки
 * по черзі, поки один з них не поверне картинку:
 *
 *   1. Unsplash
 *   2. Pexels
 *   3. Pixabay
 *
 * Кожен провайдер, для якого не задано ключ у .env, сам кидає зрозумілу
 * помилку — вона обробляється так само, як і будь-яка інша помилка
 * провайдера (переходимо до наступного), тому не налаштовані стоки
 * просто тихо пропускаються.
 *
 * Команда, що використовує цей trait, повинна мати властивість
 * protected $http (екземпляр GuzzleHttp\Client).
 */
trait FetchesImages
{
    /**
     * Головна точка входу. Повертає відносний шлях виду
     * "/images/shares/Posts/файл.jpg" (той самий формат, що вже
     * використовується на сайті) або null, якщо жоден стік не спрацював.
     */
    protected function fetchAndSaveImage(string $query, string $baseName): ?string
    {
        $providers = [
            ['name' => 'Unsplash', 'method' => 'fetchBytesFromUnsplash'],
            ['name' => 'Pexels', 'method' => 'fetchBytesFromPexels'],
            ['name' => 'Pixabay', 'method' => 'fetchBytesFromPixabay'],
        ];

        foreach ($providers as $i => $provider) {
            try {
                $bytes = $this->{$provider['method']}($query);
                if ($bytes !== null) {
                    if ($i > 0 && method_exists($this, 'info')) {
                        $this->info("Картинку знайдено через резервний фотосток: {$provider['name']}.");
                    }
                    return $this->saveImageBytes($bytes, $baseName);
                }
            } catch (\Throwable $e) {
                $hasMore = $i < count($providers) - 1;
                if (method_exists($this, 'warn') && $hasMore) {
                    $this->warn("{$provider['name']} не дав фото для '{$query}' (" . $e->getMessage() . '), пробую наступний стік...');
                } elseif (method_exists($this, 'warn')) {
                    $this->warn("{$provider['name']} не дав фото для '{$query}' (" . $e->getMessage() . ')');
                }
            }
        }

        return null;
    }

    protected function saveImageBytes(string $bytes, string $baseName): string
    {
        $filename = Str::limit($baseName, 60, '') . '-' . substr(md5(uniqid('', true)), 0, 8) . '.jpg';
        $filename = preg_replace('/[^a-z0-9\-\.]/i', '', $filename);
        $destination = public_path('images/shares/Posts/' . $filename);

        file_put_contents($destination, $bytes);

        return '/images/shares/Posts/' . $filename;
    }

    // -----------------------------------------------------------------
    // 1. Unsplash
    // -----------------------------------------------------------------

    protected function fetchBytesFromUnsplash(string $query): ?string
    {
        $key = env('UNSPLASH_ACCESS_KEY');
        if (empty($key)) {
            throw new \RuntimeException('UNSPLASH_ACCESS_KEY не задано в .env');
        }

        $response = $this->http->get('https://api.unsplash.com/photos/random', [
            'query' => ['query' => $query, 'orientation' => 'landscape'],
            'headers' => ['Authorization' => 'Client-ID ' . $key],
            'timeout' => 30,
        ]);
        $data = json_decode((string) $response->getBody(), true);
        $imageUrl = $data['urls']['regular'] ?? null;

        if (!$imageUrl) {
            throw new \RuntimeException('Unsplash не повернув URL фото');
        }

        $imgResponse = $this->http->get($imageUrl, ['timeout' => 60]);
        return (string) $imgResponse->getBody();
    }

    // -----------------------------------------------------------------
    // 2. Pexels
    // -----------------------------------------------------------------

    protected function fetchBytesFromPexels(string $query): ?string
    {
        $key = env('PEXELS_API_KEY');
        if (empty($key)) {
            throw new \RuntimeException('PEXELS_API_KEY не задано в .env');
        }

        $response = $this->http->get('https://api.pexels.com/v1/search', [
            'query' => ['query' => $query, 'per_page' => 1, 'orientation' => 'landscape'],
            // Pexels очікує сам ключ у заголовку Authorization, БЕЗ префіксу "Bearer".
            'headers' => ['Authorization' => $key],
            'timeout' => 30,
        ]);
        $data = json_decode((string) $response->getBody(), true);
        $imageUrl = $data['photos'][0]['src']['large'] ?? null;

        if (!$imageUrl) {
            throw new \RuntimeException('Pexels не знайшов фото за запитом');
        }

        $imgResponse = $this->http->get($imageUrl, ['timeout' => 60]);
        return (string) $imgResponse->getBody();
    }

    // -----------------------------------------------------------------
    // 3. Pixabay
    // -----------------------------------------------------------------

    protected function fetchBytesFromPixabay(string $query): ?string
    {
        $key = env('PIXABAY_API_KEY');
        if (empty($key)) {
            throw new \RuntimeException('PIXABAY_API_KEY не задано в .env');
        }

        $response = $this->http->get('https://pixabay.com/api/', [
            'query' => [
                'key' => $key,
                'q' => $query,
                'image_type' => 'photo',
                'orientation' => 'horizontal',
                // У Pixabay мінімальне значення per_page — 3, менше не приймається API.
                'per_page' => 3,
            ],
            'timeout' => 30,
        ]);
        $data = json_decode((string) $response->getBody(), true);
        $imageUrl = $data['hits'][0]['largeImageURL'] ?? null;

        if (!$imageUrl) {
            throw new \RuntimeException('Pixabay не знайшов фото за запитом');
        }

        $imgResponse = $this->http->get($imageUrl, ['timeout' => 60]);
        return (string) $imgResponse->getBody();
    }
}
