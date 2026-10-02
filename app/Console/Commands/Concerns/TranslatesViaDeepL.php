<?php

namespace App\Console\Commands\Concerns;

use GuzzleHttp\Client;

/**
 * Переклад через DeepL API — для пари українська/російська це значно
 * дешевше й надійніше за LLM: немає ризику битого JSON, обрізання
 * токенів чи вигаданого тексту. tag_handling=html зберігає HTML-теги
 * недоторканими, перекладаючи лише текст усередині них.
 *
 * Безкоштовний рівень DeepL: 500 000 символів/місяць (рахуються з
 * урахуванням HTML-тегів при tag_handling=html).
 */
trait TranslatesViaDeepL
{
    /**
     * Перекладає кілька текстів ОДНИМ запитом (економніше й швидше, ніж
     * окремий виклик на кожне поле). Повертає масив перекладів у ТІЙ
     * САМІЙ послідовності, що й вхідний масив $texts.
     *
     * @param string[] $texts
     * @return string[]
     */
    protected function translateViaDeepL(array $texts, string $sourceLang = 'UK', string $targetLang = 'RU'): array
    {
        $apiKey = env('DEEPL_API_KEY');
        if (empty($apiKey)) {
            throw new \RuntimeException('DEEPL_API_KEY не задано в .env');
        }

        // DeepL Free-акаунти ОБОВ'ЯЗКОВО використовують окремий піддомен
        // api-free.deepl.com — звичайний api.deepl.com поверне 403 для
        // безкоштовного ключа.
        $isFree = filter_var(env('DEEPL_API_FREE', true), FILTER_VALIDATE_BOOLEAN);
        $baseUrl = env('DEEPL_BASE_URL', $isFree ? 'https://api-free.deepl.com' : 'https://api.deepl.com');

        // ВАЖЛИВО: DeepL НЕ приймає multipart/form-data для перекладу
        // (повертає оманливе "text field is required" замість чіткої
        // помилки формату). Потрібен саме application/x-www-form-urlencoded,
        // причому для КІЛЬКОХ текстів — ПОВТОРЮВАНИЙ параметр text=...&text=...,
        // а не text[0]=...&text[1]=... (так Guzzle серіалізував би звичайний
        // асоціативний масив через 'form_params') — тому формуємо body вручну.
        $bodyParts = [];
        foreach ($texts as $text) {
            $bodyParts[] = 'text=' . urlencode($text);
        }
        $bodyParts[] = 'source_lang=' . urlencode($sourceLang);
        $bodyParts[] = 'target_lang=' . urlencode($targetLang);
        $bodyParts[] = 'tag_handling=html';
        $body = implode('&', $bodyParts);

        $client = property_exists($this, 'http') && $this->http instanceof Client
            ? $this->http
            : new Client();

        $response = $client->post(rtrim($baseUrl, '/') . '/v2/translate', [
            'headers' => [
                'Authorization' => 'DeepL-Auth-Key ' . $apiKey,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body' => $body,
            'timeout' => 30,
        ]);

        $data = json_decode((string) $response->getBody(), true);
        $translations = array_map(function ($t) {
            return $t['text'] ?? '';
        }, $data['translations'] ?? []);

        if (count($translations) !== count($texts)) {
            throw new \RuntimeException('DeepL повернув несподівану кількість перекладів (' . count($translations) . ' замість ' . count($texts) . ')');
        }

        return $translations;
    }
}
