<?php

namespace App\Console\Commands;

use App\AdTag;
use App\Console\Commands\Concerns\CallsLlm;
use App\Console\Commands\Concerns\UsesPromptTemplates;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class TranslateTags extends Command
{
    use CallsLlm;
    use UsesPromptTemplates;

    /**
     * php artisan tags:translate
     *
     * Перекладає назви тегів оголошень на українську через Claude API.
     * Теги — короткі фрази (не географічні назви), тому пакети більші
     * за cities/regions. Пріоритет за замовчуванням — найпопулярніші
     * теги (найбільше оголошень), бо їх 19000+ і перекласти все одразу
     * нереалістично.
     */
    protected $signature = 'tags:translate {--limit=} {--batch=50} {--only-used}';

    protected $description = 'Перекладає назви тегів оголошень на українську через Claude API';

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

        $limit = (int) ($this->option('limit') ?: 200);
        $batchSize = (int) $this->option('batch');
        $onlyUsed = $this->option('only-used');

        $query = AdTag::where(function ($q) {
            $q->whereNull('name_uk')->orWhere('name_uk', '');
        });

        if ($onlyUsed) {
            $query->withCount('ads')
                ->has('ads')
                ->orderByDesc('ads_count'); // найпопулярніші спершу
        } else {
            $query->orderBy('id');
        }

        $tags = $query->limit($limit)->get(['id', 'name']);

        if ($tags->isEmpty()) {
            $this->info('Усі теги вже перекладені (в межах вибраного фільтра).');
            return 0;
        }

        $this->info('Перекладаю ' . $tags->count() . ' тег(ів), пакетами по ' . $batchSize);

        $batches = $tags->chunk($batchSize);
        $translated = 0;

        foreach ($batches as $i => $batch) {
            $this->info('Пакет ' . ($i + 1) . '/' . $batches->count());

            try {
                $result = $this->translateBatch($batch);
                foreach ($result as $id => $nameUk) {
                    AdTag::where('id', $id)->update(['name_uk' => $nameUk]);
                    $translated++;
                }
            } catch (\Throwable $e) {
                $this->error('Помилка пакету: ' . $e->getMessage());
            }

            usleep(300000);
        }

        $this->info("Готово. Перекладено тегів: {$translated}");
        return 0;
    }

    protected function translateBatch($batch): array
    {
        $list = $batch->map(function ($tag) {
            return $tag->id . ': ' . $tag->name;
        })->implode("\n");

        $prompt = $this->prompt(
            'tags_translate',
            'Переклад тегів оголошень',
            "Ти — професійний перекладач для дошки оголошень. Нижче список тегів "
                . "(ключових слів/фраз, якими позначені оголошення) у форматі "
                . "\"ID: Слово/фраза російською\".\n\n"
                . "Для кожного дай природний український відповідник. Це короткі теги "
                . "(назви товарів, брендів, категорій), не географічні назви — перекладай "
                . "як звичайне слово чи фразу українською, природно, як казав би нею носій "
                . "мови. Якщо це власна назва бренду (напр. iPhone, Samsung) — залиш як є, "
                . "без перекладу.\n\n"
                . "Список:\n{{list}}\n\n"
                . "Відповідь — ТІЛЬКИ валідний JSON-об'єкт формату {\"ID\": \"Слово\", ...}, "
                . "без жодного додаткового тексту чи пояснень, без markdown-обрамлення.",
            ['list' => $list],
            'Пакетний переклад тегів оголошень (ключових слів/фраз) на природну українську.'
        );

        $raw = $this->callLlm($prompt, 3000, $this->validatesAsJsonObject());
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
