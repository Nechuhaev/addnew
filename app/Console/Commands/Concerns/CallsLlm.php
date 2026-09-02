<?php

namespace App\Console\Commands\Concerns;

/**
 * Дає artisan-командам метод callLlm() — спершу пробує Claude (Anthropic),
 * і якщо він недоступний (мережева помилка, 5xx, ліміт, таймаут тощо) —
 * автоматично перемикається на Gemini, якщо заданий GEMINI_API_KEY.
 *
 * Команда, що використовує цей trait, повинна мати властивість
 * protected $http (екземпляр GuzzleHttp\Client) — так само, як вже
 * влаштовано в BuildContentPlan і PublishArticle.
 */
trait CallsLlm
{
    /**
     * Головна точка входу — саме її треба викликати замість callClaude()
     * напряму, щоб автоматично отримати фолбек на Gemini.
     */
    protected function callLlm(string $prompt, int $maxTokens = 4000): string
    {
        try {
            return $this->callClaude($prompt, $maxTokens);
        } catch (\Throwable $claudeError) {
            $geminiKey = env('GEMINI_API_KEY');
            if (empty($geminiKey)) {
                // Фолбеку немає — прокидаємо оригінальну помилку Claude як є.
                throw $claudeError;
            }

            if (method_exists($this, 'warn')) {
                $this->warn('Claude API недоступний (' . $claudeError->getMessage() . '), пробую Gemini...');
            }

            try {
                return $this->callGemini($prompt, $maxTokens);
            } catch (\Throwable $geminiError) {
                throw new \RuntimeException(
                    'Обидва LLM-провайдери недоступні. '
                    . 'Claude: ' . $claudeError->getMessage() . ' | '
                    . 'Gemini: ' . $geminiError->getMessage()
                );
            }
        }
    }

    protected function callClaude(string $prompt, int $maxTokens = 4000): string
    {
        $response = $this->http->post('https://api.anthropic.com/v1/messages', [
            'headers' => [
                'x-api-key' => env('ANTHROPIC_API_KEY'),
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

    /**
     * Список моделей Gemini за пріоритетом — env GEMINI_MODEL може містити
     * кілька назв через кому. Якщо перша модель недоступна (404, "no longer
     * available") або постійно перевантажена — пробуємо наступну в списку,
     * а не одразу здаємось. Останній елемент — офіційний аліас "latest",
     * який Google завжди тримає вказівним на щось робоче (хай і
     * експериментальне), тому він — надійний останній рубіж.
     */
    protected function callGemini(string $prompt, int $maxTokens = 4000): string
    {
        $apiKey = env('GEMINI_API_KEY');
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

    /**
     * Виклик конкретної моделі Gemini з однією повторною спробою при
     * тимчасовому перевантаженні (503 "High demand") чи ліміті (429).
     */
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
