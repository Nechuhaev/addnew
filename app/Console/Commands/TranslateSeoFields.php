<?php

namespace App\Console\Commands;

use App\SeoField;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class TranslateSeoFields extends Command
{
    /**
     * php artisan seo-fields:translate
     *
     * Перекладає 13 SEO-шаблонів (index, ad-city, ad-category тощо) на
     * українську. Ці шаблони містять службові плейсхолдери на кшталт
     * ---filtered_name---, ---city_name--- — їх НЕ можна змінювати,
     * вони підставляються кодом після перекладу.
     */
    protected $signature = 'seo-fields:translate {--limit=}';

    protected $description = 'Перекладає SEO-шаблони (SeoField) на українську, зберігаючи плейсхолдери';

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

        $limit = (int) ($this->option('limit') ?: 20);

        $fields = SeoField::where(function ($q) {
                $q->whereNull('meta_title_uk')->orWhere('meta_title_uk', '');
            })
            ->limit($limit)
            ->get();

        if ($fields->isEmpty()) {
            $this->info('Усі SEO-шаблони вже перекладені.');
            return 0;
        }

        $this->info('Перекладаю ' . $fields->count() . ' SEO-шаблонів');

        foreach ($fields as $field) {
            $this->info("Обробляю: {$field->index}");

            try {
                $result = $this->translateOne($field);

                $field->meta_title_uk = $result['meta_title'] ?? null;
                $field->meta_description_uk = $result['meta_description'] ?? null;
                $field->description_uk = $result['description'] ?? null;
                $field->save();

                $this->info('  -> OK');
            } catch (\Throwable $e) {
                $this->error('  Помилка: ' . $e->getMessage());
            }

            usleep(300000);
        }

        return 0;
    }

    protected function translateOne(SeoField $field): array
    {
        $metaTitle = $field->getOriginal('meta_title') ?? '';
        $metaDescription = $field->getOriginal('meta_description') ?? '';
        $description = $field->getOriginal('description') ?? '';

        $prompt = "Ти — професійний перекладач і SEO-копірайтер. Переклади наступний SEO-шаблон "
            . "з російської на українську.\n\n"
            . "КРИТИЧНО ВАЖЛИВО: текст містить службові плейсхолдери у форматі "
            . "---якесь_слово--- (наприклад ---filtered_name---, ---city_name---, "
            . "---region_name---, ---country_name---, ---full_filtered_name---, "
            . "---user_name---, ---ads_count---, ---shop_count---). Ці плейсхолдери "
            . "ПОТРІБНО залишити ТОЧНО як є, без жодних змін і без перекладу — вони "
            . "автоматично підставляються кодом після твого перекладу. Переклади лише "
            . "звичайний текст навколо них.\n\n"
            . "Meta title: \"{$metaTitle}\"\n\n"
            . "Meta description: \"{$metaDescription}\"\n\n"
            . "Description:\n{$description}\n\n"
            . "Відповідь — ТІЛЬКИ валідний JSON без markdown-обрамлення, формату:\n"
            . "{\"meta_title\": \"...\", \"meta_description\": \"...\", \"description\": \"...\"}";

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
            'timeout' => 180,
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