<?php

namespace App\Console\Commands\Concerns;

/**
 * Дає artisan-командам метод callLlm() — перебирає провайдерів по черзі,
 * поки один з них не поверне текст:
 *
 *   1. Claude (Anthropic)
 *   2. OpenRouter
 *   3. Groq
 *   4. Cloudflare Workers AI
 *   5. Gemini (сам всередині ще й перебирає кілька моделей за пріоритетом)
 *
 * Кожен провайдер, для якого не задано потрібний ключ у .env, сам кидає
 * зрозумілу помилку "не задано" — вона обробляється так само, як і будь-яка
 * інша помилка провайдера (просто переходимо до наступного), тому не
 * налаштовані провайдери не ламають ланцюжок, а тихо пропускаються.
 *
 * Команда, що використовує цей trait, повинна мати властивість
 * protected $http (екземпляр GuzzleHttp\Client).
 */
trait CallsLlm
{
    /**
     * Головна точка входу — саме її треба викликати замість callClaude()
     * напряму, щоб автоматично отримати повний ланцюжок фолбеків.
     *
     * $validator (необов'язково) — функція виду fn(string $text): bool.
     * Якщо задана і повертає false для відповіді провайдера — ця відповідь
     * НЕ приймається як успіх: замість того щоб повернути "сирий" текст,
     * який викликач потім не зможе розпарсити (наприклад, JSON-парсер не
     * знайде дужок), ми одразу переходимо до наступного провайдера. Без
     * цього провайдер, що "успішно" відповів прозою замість JSON, псував
     * би результат мовчки, хоча наступний провайдер міг би впоратись.
     */
    protected function callLlm(string $prompt, int $maxTokens = 4000, ?callable $validator = null): string
    {
        $providers = [
            ['name' => 'Claude', 'method' => 'callClaude'],
            ['name' => 'OpenRouter', 'method' => 'callOpenRouter'],
            ['name' => 'Groq', 'method' => 'callGroq'],
            ['name' => 'Cloudflare Workers AI', 'method' => 'callCloudflareAi'],
            ['name' => 'Gemini', 'method' => 'callGemini'],
        ];

        $errors = [];
        foreach ($providers as $i => $provider) {
            try {
                $text = $this->{$provider['method']}($prompt, $maxTokens);

                if ($validator !== null && !$validator($text)) {
                    throw new \RuntimeException('Відповідь не пройшла валідацію очікуваного формату');
                }

                if ($i > 0 && method_exists($this, 'info')) {
                    $this->info("Використано резервного провайдера: {$provider['name']}.");
                }
                return $text;
            } catch (\Throwable $e) {
                $errors[] = "{$provider['name']}: " . $e->getMessage();
                $hasMore = $i < count($providers) - 1;
                if (method_exists($this, 'warn') && $hasMore) {
                    $this->warn("{$provider['name']} недоступний (" . $e->getMessage() . '), пробую наступного провайдера...');
                }
            }
        }

        throw new \RuntimeException('Усі LLM-провайдери недоступні: ' . implode(' | ', $errors));
    }

    /**
     * Готовий валідатор для callLlm(): відповідь має містити хоча б одну
     * пару { ... } (JSON-об'єкт). Використовуй, коли очікуєш від моделі
     * JSON-об'єкт (наприклад, дані статті).
     */
    protected function validatesAsJsonObject(): callable
    {
        return function (string $text): bool {
            return strpos($text, '{') !== false && strpos($text, '}') !== false;
        };
    }

    /**
     * Готовий валідатор для callLlm(): відповідь має містити хоча б одну
     * пару [ ... ] (JSON-масив). Використовуй, коли очікуєш від моделі
     * JSON-масив (наприклад, список тем чи ключів).
     */
    protected function validatesAsJsonArray(): callable
    {
        return function (string $text): bool {
            return strpos($text, '[') !== false && strpos($text, ']') !== false;
        };
    }

    // -----------------------------------------------------------------
    // 1. Claude (Anthropic)
    // -----------------------------------------------------------------

    protected function callClaude(string $prompt, int $maxTokens = 4000): string
    {
        $apiKey = env('ANTHROPIC_API_KEY');
        if (empty($apiKey)) {
            throw new \RuntimeException('ANTHROPIC_API_KEY не задано в .env');
        }

        $response = $this->http->post('https://api.anthropic.com/v1/messages', [
            'headers' => [
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ],
            'json' => [
                'model' => env('ANTHROPIC_MODEL', 'claude-sonnet-4-6'),
                'max_tokens' => $maxTokens,
                'messages' => [['role' => 'user', 'content' => $prompt]],
            ],
            'timeout' => 180,
        ]);

        $data = json_decode((string) $response->getBody(), true);
        $textBlocks = array_filter($data['content'] ?? [], function ($b) {
            return ($b['type'] ?? '') === 'text';
        });
        $text = trim(implode("\n", array_map(function ($b) {
            return $b['text'];
        }, $textBlocks)));

        if ($text === '') {
            throw new \RuntimeException('Claude повернув порожню відповідь: ' . json_encode($data));
        }

        return $text;
    }

    // -----------------------------------------------------------------
    // 2. OpenRouter (openrouter.ai) — OpenAI-сумісний формат
    // -----------------------------------------------------------------

    protected function callOpenRouter(string $prompt, int $maxTokens = 4000): string
    {
        $apiKey = env('OPENROUTER_API_KEY');
        if (empty($apiKey)) {
            throw new \RuntimeException('OPENROUTER_API_KEY не задано в .env');
        }

        $model = env('OPENROUTER_MODEL', 'google/gemini-2.5-flash');

        $response = $this->http->post('https://openrouter.ai/api/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => $model,
                'messages' => [['role' => 'user', 'content' => $prompt]],
                'max_tokens' => $maxTokens,
            ],
            'timeout' => 180,
        ]);

        $data = json_decode((string) $response->getBody(), true);
        $text = $data['choices'][0]['message']['content'] ?? null;

        if ($text === null || trim($text) === '') {
            throw new \RuntimeException('OpenRouter не повернув текст: ' . json_encode($data));
        }

        return trim($text);
    }

    // -----------------------------------------------------------------
    // 3. Groq (console.groq.com) — OpenAI-сумісний формат
    // -----------------------------------------------------------------

    protected function callGroq(string $prompt, int $maxTokens = 4000): string
    {
        $apiKey = env('GROQ_API_KEY');
        if (empty($apiKey)) {
            throw new \RuntimeException('GROQ_API_KEY не задано в .env');
        }

        // Groq деприкейтив старі чат-моделі Llama (llama-3.3-70b-versatile,
        // llama-3.1-8b-instant) — актуальна робоча модель загального
        // призначення: openai/gpt-oss-120b (менша — openai/gpt-oss-20b).
        $model = env('GROQ_MODEL', 'openai/gpt-oss-120b');

        $response = $this->http->post('https://api.groq.com/openai/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => $model,
                'messages' => [['role' => 'user', 'content' => $prompt]],
                'max_tokens' => $maxTokens,
            ],
            'timeout' => 180,
        ]);

        $data = json_decode((string) $response->getBody(), true);
        $text = $data['choices'][0]['message']['content'] ?? null;

        if ($text === null || trim($text) === '') {
            throw new \RuntimeException('Groq не повернув текст: ' . json_encode($data));
        }

        return trim($text);
    }

    // -----------------------------------------------------------------
    // 4. Cloudflare Workers AI
    // -----------------------------------------------------------------

    protected function callCloudflareAi(string $prompt, int $maxTokens = 4000): string
    {
        $apiToken = env('CLOUDFLARE_API_TOKEN');
        $accountId = env('CLOUDFLARE_ACCOUNT_ID');

        if (empty($apiToken) || empty($accountId)) {
            throw new \RuntimeException('CLOUDFLARE_API_TOKEN або CLOUDFLARE_ACCOUNT_ID не задано в .env');
        }

        $model = env('CLOUDFLARE_AI_MODEL', '@cf/meta/llama-3.1-8b-instruct');
        $url = "https://api.cloudflare.com/client/v4/accounts/{$accountId}/ai/run/{$model}";

        $response = $this->http->post($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $apiToken,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'messages' => [['role' => 'user', 'content' => $prompt]],
                'max_tokens' => $maxTokens,
            ],
            'timeout' => 180,
        ]);

        $data = json_decode((string) $response->getBody(), true);
        $text = $data['result']['response'] ?? null;

        if ($text === null || trim($text) === '') {
            throw new \RuntimeException('Cloudflare Workers AI не повернув текст: ' . json_encode($data));
        }

        return trim($text);
    }

    // -----------------------------------------------------------------
    // 5. Gemini — останній рубіж, сам перебирає кілька моделей за пріоритетом
    // -----------------------------------------------------------------

    protected function callGemini(string $prompt, int $maxTokens = 4000): string
    {
        $apiKey = env('GEMINI_API_KEY');
        if (empty($apiKey)) {
            throw new \RuntimeException('GEMINI_API_KEY не задано в .env');
        }

        $modelsConfig = env(
            'GEMINI_MODEL',
            'gemini-3.7-flash,gemini-3.6-flash,gemini-3.5-flash,gemini-2.5-flash,gemini-flash-latest'
        );
        $models = array_values(array_filter(array_map('trim', explode(',', $modelsConfig))));

        if (empty($models)) {
            throw new \RuntimeException('GEMINI_MODEL порожній після розбору списку моделей');
        }

        $lastError = null;
        foreach ($models as $i => $model) {
            try {
                return $this->callGeminiModel($prompt, $maxTokens, $model, $apiKey);
            } catch (\Throwable $e) {
                $lastError = $e;
                $hasMore = $i < count($models) - 1;
                if (method_exists($this, 'warn') && $hasMore) {
                    $this->warn("Gemini [{$model}] не спрацював (" . $e->getMessage() . '), пробую наступну модель...');
                }
                continue;
            }
        }

        throw $lastError ?: new \RuntimeException('Жодна модель Gemini не спрацювала');
    }

    protected function callGeminiModel(string $prompt, int $maxTokens, string $model, string $apiKey): string
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

        $payload = [
            'headers' => [
                'x-goog-api-key' => $apiKey,
                'content-type' => 'application/json',
            ],
            'json' => [
                'contents' => [
                    ['parts' => [['text' => $prompt]]],
                ],
                'generationConfig' => [
                    'maxOutputTokens' => $maxTokens,
                ],
            ],
            'timeout' => 180,
        ];

        $attempts = 0;
        $lastError = null;
        while ($attempts < 2) {
            $attempts++;
            try {
                $response = $this->http->post($url, $payload);
                $data = json_decode((string) $response->getBody(), true);
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

                if ($text === null || trim($text) === '') {
                    throw new \RuntimeException('Gemini не повернув текст: ' . json_encode($data));
                }

                return trim($text);
            } catch (\Throwable $e) {
                $lastError = $e;
                $isTransient = strpos($e->getMessage(), '503') !== false
                    || strpos($e->getMessage(), '429') !== false
                    || strpos($e->getMessage(), 'high demand') !== false;
                if ($isTransient && $attempts < 2) {
                    if (method_exists($this, 'warn')) {
                        $this->warn("Gemini [{$model}] тимчасово перевантажений, чекаю 3 сек і пробую ще раз...");
                    }
                    sleep(3);
                    continue;
                }
                throw $lastError;
            }
        }

        throw $lastError;
    }
}
