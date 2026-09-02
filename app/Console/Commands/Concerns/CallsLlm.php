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

    protected function callGemini(string $prompt, int $maxTokens = 4000): string
    {
        $apiKey = env('GEMINI_API_KEY');
        // gemini-2.5-flash недоступна для нових користувачів і вимикається
        // 16-20.10.2026 — тому за замовчуванням беремо актуальну gemini-3.7-flash.
        $model = env('GEMINI_MODEL', 'gemini-3.7-flash');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

        $response = $this->http->post($url, [
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
        ]);

        $data = json_decode((string) $response->getBody(), true);
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if ($text === null || trim($text) === '') {
            throw new \RuntimeException('Gemini не повернув текст: ' . json_encode($data));
        }

        return trim($text);
    }
}
