<?php

namespace App\Console\Commands;

use App\AdCity;
use App\Console\Commands\Concerns\UsesPromptTemplates;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class TranslateCitySeo extends Command
{
    use UsesPromptTemplates;

    /**
     * php artisan cities:translate-seo
     *
     * Перекладає meta_title, meta_description, content міст на
     * українську через Claude API. Пакетами (на відміну від категорій,
     * де перекладали по одній) — міст значно більше (1101), і їхні
     * SEO-тексти зазвичай коротші/шаблонніші за категорійні, тож можна
     * ефективніше об'єднувати кілька міст в один запит.
     */
    protected $signature = 'cities:translate-seo {--limit=} {--batch=5} {--only-used}';

    protected $description = 'Перекладає SEO-поля міст (meta_title, meta_description, content) на українську';

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

        $limit = (int) ($this->option('limit') ?: 500);
        $batchSize = (int) $this->option('batch');
        $onlyUsed = $this->option('only-used');

        $query = AdCity::where(function ($q) {
                $q->whereNull('meta_title_uk')->orWhere('meta_title_uk', '');
            })
            ->whereNotNull('meta_title')
            ->where('meta_title', '!=', '');

        if ($onlyUsed) {
            $query->whereHas('ads');
        }

        $cities = $query->orderBy('id')->limit($limit)->get();

        if ($cities->isEmpty()) {
            $this->info('Усі міста з заповненими SEO-полями вже перекладені (в межах фільтра).');
            return 0;
        }

        $this->info('Перекладаю SEO-поля для ' . $cities->count() . ' міст, пакетами по ' . $batchSize);

        $batches = $cities->chunk($batchSize);

        foreach ($batches as $i => $batch) {
            $this->info('Пакет ' . ($i + 1) . '/' . $batches->count());

            try {
                $result = $this->translateBatch($batch);
                foreach ($result as $id => $fields) {
                    AdCity::where('id', $id)->update([
                        'meta_title_uk' => $fields['meta_title'] ?? null,
                        'meta_description_uk' => $fields['meta_description'] ?? null,
                        'content_uk' => $fields['content'] ?? null,
                    ]);
                }
                $this->info('  -> OK (' . count($result) . ' міст у пакеті)');
            } catch (\Throwable $e) {
                $this->error('  Помилка пакету: ' . $e->getMessage());
            }

            usleep(300000);
        }

        return 0;
    }

    /**
     * @param \Illuminate\Support\Collection $batch
     * @return array [id => ['meta_title'=>..,'meta_description'=>..,'content'=>..]]
     */
    protected function translateBatch($batch): array
    {
        $itemsText = $batch->map(function ($city) {
            return "### ID {$city->id}\n"
                . "Meta title: \"" . ($city->getOriginal('meta_title') ?? '') . "\"\n"
                . "Meta description: \"" . ($city->getOriginal('meta_description') ?? '') . "\"\n"
                . "Content: " . ($city->getOriginal('content') ?? '');
        })->implode("\n\n");

        $prompt = $this->prompt(
            'city_seo_translate',
            'Переклад SEO-полів міст (пакетами)',
            "Ти — професійний перекладач і SEO-копірайтер. Переклади SEO-поля "
                . "(meta title, meta description, content) для кожного з наведених нижче міст "
                . "з російської на українську. Зберігай структуру й сенс, адаптуй природно для "
                . "української мови, зберігай HTML-теги в content без змін, якщо вони є.\n\n"
                . "{{items}}\n\n"
                . "Відповідь — ТІЛЬКИ валідний JSON без markdown-обрамлення, формату:\n"
                . "{\"ID\": {\"meta_title\": \"...\", \"meta_description\": \"...\", \"content\": \"...\"}, ...}",
            ['items' => $itemsText],
            'Пакетний переклад SEO-полів (meta_title, meta_description, content) для міст.'
        );

        $raw = $this->callClaude($prompt, 4000);
        $json = $this->extractJsonObject($raw);
        $data = json_decode($json, true);

        if (!is_array($data)) {
            throw new \RuntimeException('Не вдалося розпарсити JSON відповіді моделі');
        }

        $result = [];
        foreach ($data as $id => $fields) {
            $result[(int) $id] = $fields;
        }

        return $result;
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
            'timeout' => 120,
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
