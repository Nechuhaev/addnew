<?php

namespace App\Services\Caller;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * Мінімальний клієнт Claude Messages API (raw HTTP через Guzzle).
 * Офіційний PHP SDK потребує PHP 8.1+, а сайт працює на PHP 7.4, тож тут той самий
 * підхід, що й в інших командах проєкту.
 *
 * Модель за замовчуванням — Claude Opus 5.5 (CALLER_CLAUDE_MODEL). Увімкнено серверний
 * fallback: якщо модель відмовить, запит автоматично повториться на рекомендованій моделі.
 */
class ClaudeClient
{
    /** @var Client */
    private $http;

    public function __construct(Client $http = null)
    {
        $this->http = $http ?: new Client(['timeout' => 120]);
    }

    public static function isConfigured(): bool
    {
        return !empty(config('caller.claude.api_key'));
    }

    /**
     * @param array       $messages  [['role' => 'user'|'assistant', 'content' => string|array], ...]
     * @param array|null  $schema    JSON Schema для structured outputs (відповідь гарантовано валідний JSON)
     * @return array повна відповідь API (content, stop_reason, usage, ...)
     */
    public function create(string $system, array $messages, array $schema = null, string $effort = 'low', int $maxTokens = 16000): array
    {
        $outputConfig = ['effort' => $effort];
        if ($schema) {
            $outputConfig['format'] = ['type' => 'json_schema', 'schema' => $schema];
        }

        try {
            $res = $this->http->post('https://api.anthropic.com/v1/messages', [
                'headers' => [
                    'x-api-key' => config('caller.claude.api_key'),
                    'anthropic-version' => '2023-06-01',
                    'anthropic-beta' => 'server-side-fallback-2026-07-01',
                    'content-type' => 'application/json',
                ],
                'json' => [
                    'model' => config('caller.claude.model', 'claude-opus-5-5'),
                    'max_tokens' => $maxTokens,
                    'fallbacks' => 'default',
                    'output_config' => $outputConfig,
                    'system' => $system,
                    'messages' => $messages,
                ],
            ]);
        } catch (RequestException $e) {
            $body = $e->hasResponse() ? (string) $e->getResponse()->getBody() : $e->getMessage();
            throw new \RuntimeException('Claude API: ' . mb_substr($body, 0, 500));
        }

        $data = json_decode((string) $res->getBody(), true) ?: [];
        $stop = $data['stop_reason'] ?? null;
        if ($stop === 'refusal') {
            $cat = $data['stop_details']['category'] ?? 'без категорії';
            throw new \RuntimeException("Claude відмовився відповідати ({$cat})");
        }
        if ($stop === 'max_tokens') {
            throw new \RuntimeException('Відповідь Claude обрізана (max_tokens)');
        }
        return $data;
    }

    /** Текст відповіді (усі text-блоки). */
    public static function text(array $response): string
    {
        $out = '';
        foreach ($response['content'] ?? [] as $block) {
            if (($block['type'] ?? '') === 'text') {
                $out .= $block['text'];
            }
        }
        return trim($out);
    }
}
