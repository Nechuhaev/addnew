<?php

namespace App\Console\Commands;

use App\AdCity;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class TranslateCities extends Command
{
    /**
     * php artisan cities:translate
     *
     * Перекладає назви міст на українську через Claude API — пакетами,
     * щоб зекономити виклики. Пріоритет: спершу міста, де реально є
     * оголошення (найважливіші для сайту), потім — решта.
     * Просимо саме ОФІЦІЙНУ українську назву населеного пункту,
     * не буквальний переклад слова.
     */
    protected $signature = 'cities:translate {--limit=} {--batch=25} {--only-used}';

    protected $description = 'Перекладає назви міст на українську через Claude API';

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

        $query = AdCity::whereNull('name_uk')
            ->orWhere('name_uk', '');

        if ($onlyUsed) {
            $query = AdCity::where(function ($q) {
                    $q->whereNull('name_uk')->orWhere('name_uk', '');
                })
                ->whereHas('ads');
        }

        $cities = $query->orderBy('id')->limit($limit)->get(['id', 'name']);

        if ($cities->isEmpty()) {
            $this->info('Усі міста вже перекладені (в межах вибраного фільтра).');
            return 0;
        }

        $this->info('Перекладаю ' . $cities->count() . ' міст(а), пакетами по ' . $batchSize);

        $batches = $cities->chunk($batchSize);
        $translated = 0;

        foreach ($batches as $i => $batch) {
            $this->info('Пакет ' . ($i + 1) . '/' . $batches->count());

            try {
                $result = $this->translateBatch($batch);
                foreach ($result as $id => $nameUk) {
                    AdCity::where('id', $id)->update(['name_uk' => $nameUk]);
                    $translated++;
                }
            } catch (\Throwable $e) {
                $this->error('Помилка пакету: ' . $e->getMessage());
            }

            usleep(300000);
        }

        $this->info("Готово. Перекладено міст: {$translated}");
        return 0;
    }

    /**
     * @param \Illuminate\Support\Collection $batch
     * @return array [id => 'Українська назва']
     */
    protected function translateBatch($batch): array
    {
        $list = $batch->map(function ($city) {
            return $city->id . ': ' . $city->name;
        })->implode("\n");

        $prompt = "Ти — довідник з української топоніміки. Нижче список населених пунктів "
            . "України (та інших країн, можливо) у форматі \"ID: Назва російською/оригіналом\".\n\n"
            . "Для кожного дай ОФІЦІЙНУ українську назву цього населеного пункту "
            . "(не буквальний переклад слова, а справжню, статутну українську назву міста/села/селища, "
            . "як вона використовується в українських документах і на дорожніх знаках). "
            . "Якщо населений пункт не в Україні (наприклад, у Росії, Польщі тощо) — дай "
            . "усталену українську назву цього міста (напр. Москва, Варшава), а не транслітерацію.\n\n"
            . "Список:\n{$list}\n\n"
            . "Відповідь — ТІЛЬКИ валідний JSON-об'єкт формату {\"ID\": \"Назва\", ...}, "
            . "без жодного додаткового тексту чи пояснень, без markdown-обрамлення.";

        $raw = $this->callClaude($prompt, 2000);
        $json = $this->extractJsonObject($raw);
        $data = json_decode($json, true);

        if (!is_array($data)) {
            throw new \RuntimeException('Не вдалося розпарсити JSON відповіді моделі');
        }

        $result = [];
        foreach ($data as $id => $name) {
            $result[(int) $id] = trim($name);
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