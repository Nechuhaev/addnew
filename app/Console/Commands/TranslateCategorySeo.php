<?php

namespace App\Console\Commands;

use App\AdCategory;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class TranslateCategorySeo extends Command
{
    /**
     * php artisan categories:translate-seo
     *
     * Перекладає meta_title, meta_description, content категорій на
     * українську через Claude API. По одній категорії за раз (не
     * пакетами, як з містами) — тут текст значно довший, тож краще
     * дати моделі більше "простору" на кожну.
     */
    protected $signature = 'categories:translate-seo {--limit=}';

    protected $description = 'Перекладає SEO-поля категорій (meta_title, meta_description, content) на українську';

    /** @var Client */
    protected $http;

    public function __construct()
    {
        parent::__construct();
        $this->http = new Client();
    }

    public function handle()
    {
        $anthropicKey = env('ANTHROPIC_API_KEY');
        if (empty($anthropicKey)) {
            $this->error('ANTHROPIC_API_KEY не задан у .env');
            return 1;
        }

        $limit = (int) ($this->option('limit') ?: 50);

        $categories = AdCategory::where(function ($q) {
                $q->whereNull('meta_title_uk')->orWhere('meta_title_uk', '');
            })
            ->whereNotNull('meta_title')
            ->orderBy('id')
            ->limit($limit)
            ->get();

        if ($categories->isEmpty()) {
            $this->info('Усі категорії з заповненими SEO-полями вже перекладені.');
            return 0;
        }

        $this->info('Перекладаю SEO-поля для ' . $categories->count() . ' категорій');

        foreach ($categories as $i => $category) {
            $this->info('[' . ($i + 1) . '/' . $categories->count() . "] ID={$category->id}: {$category->getOriginal('name')}");

            try {
                $result = $this->translateOne($category);

                $category->meta_title_uk = $result['meta_title'] ?? null;
                $category->meta_description_uk = $result['meta_description'] ?? null;
                $category->content_uk = $result['content'] ?? null;
                $category->save();

                $this->info('  -> OK');
            } catch (\Throwable $e) {
                $this->error('  Помилка: ' . $e->getMessage());
            }

            usleep(300000);
        }

        return 0;
    }

    protected function translateOne(AdCategory $category): array
    {
        $metaTitle = $category->getOriginal('meta_title') ?? '';
        $metaDescription = $category->getOriginal('meta_description') ?? '';
        $content = $category->getOriginal('content') ?? '';

        $prompt = "Ти — професійний перекладач і SEO-копірайтер. Переклади наступні SEO-поля "
            . "категорії дошки оголошень з російської на українську. Зберігай структуру й сенс, "
            . "адаптуй природно для української мови (не дослівний переклад слово-в-слово), "
            . "зберігай HTML-теги в content без змін, якщо вони є.\n\n"
            . "Meta title: \"{$metaTitle}\"\n\n"
            . "Meta description: \"{$metaDescription}\"\n\n"
            . "Content:\n{$content}\n\n"
            . "Відповідь — ТІЛЬКИ валідний JSON без markdown-обрамлення, формату:\n"
            . "{\"meta_title\": \"...\", \"meta_description\": \"...\", \"content\": \"...\"}";

        $raw = $this->callClaude($prompt, 4000);
        $json = $this->extractJsonObject($raw);
        $data = json_decode($json, true);

        if (!is_array($data)) {
            throw new \RuntimeException('Не вдалося розпарсити JSON відповіді моделі');
        }

        return $data;
    }

    protected function callClaude(string $prompt, int $maxTokens): string
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
            'timeout' => 90,
        ]);

        $data = json_decode((string) $response->getBody(), true);
        $textBlocks = array_filter($data['content'] ?? [], function ($b) {
            return ($b['type'] ?? '') === 'text';
        });
        return trim(implode("\n", array_map(function ($b) {
            return $b['text'];
        }, $textBlocks)));
    }

    protected function extractJsonObject(string $text): string
    {
        $text = trim($text);
        $start = strpos($text, '{');
        $end = strrpos($text, '}');
        if ($start === false || $end === false || $end < $start) {
            throw new \RuntimeException('У відповіді моделі не знайдено JSON-об\'єкт');
        }
        return substr($text, $start, $end - $start + 1);
    }
}