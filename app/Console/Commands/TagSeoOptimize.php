<?php

namespace App\Console\Commands;

use App\AdTag;
use App\Console\Commands\Concerns\CallsLlm;
use App\Console\Commands\Concerns\UsesPromptTemplates;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class TagSeoOptimize extends Command
{
    use CallsLlm;
    use UsesPromptTemplates;

    /**
     * php artisan tags:seo-optimize
     *
     * Генерує УНІКАЛЬНИЙ meta_title/meta_description/опис для тегів
     * оголошень через LLM (замість старого шаблонного тексту з простою
     * підстановкою назви тега). Пакетами (за замовчуванням 10/день —
     * тегів дуже багато), пріоритет — найпопулярніші (найбільше
     * оголошень) спершу.
     *
     * --only=ID — обробити ОДИН конкретний тег одразу (використовується
     * AdTagObserver при створенні нового тега відвідувачем).
     */
    protected $signature = 'tags:seo-optimize {--limit=} {--only=}';

    protected $description = 'Генерує унікальний SEO-текст (meta_title, meta_description, опис) для тегів оголошень через LLM';

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

        $onlyId = $this->option('only');

        if ($onlyId) {
            $tag = AdTag::withCount('ads')->find($onlyId);
            if (!$tag) {
                $this->error("Тег ID={$onlyId} не знайдено");
                return 1;
            }
            $tags = collect([$tag]);
        } else {
            $limit = (int) ($this->option('limit') ?: env('TAG_SEO_PER_RUN', 10));

            $tags = AdTag::withCount('ads')
                ->where('seo_optimized', false)
                ->orderByDesc('ads_count') // найпопулярніші (найбільше оголошень) — спершу
                ->limit($limit)
                ->get();
        }

        if ($tags->isEmpty()) {
            $this->info('Немає тегів, що потребують SEO-генерації.');
            return 0;
        }

        $this->info('Генерую SEO для ' . $tags->count() . ' тег(ів)');

        foreach ($tags as $i => $tag) {
            $this->info('[' . ($i + 1) . '/' . $tags->count() . "] ID={$tag->id}: {$tag->getOriginal('name')} (оголошень: {$tag->ads_count})");

            try {
                $this->optimizeOne($tag);
                $this->info('  -> OK');
            } catch (\Throwable $e) {
                $this->error('  Помилка: ' . $e->getMessage());
                // seo_optimized лишається false — спробуємо наступного разу.
            }

            usleep(300000);
        }

        return 0;
    }

    protected function optimizeOne(AdTag $tag): void
    {
        $result = $this->generateSeoContent($tag);

        $tag->meta_title = $result['meta_title'] ?? $tag->getOriginal('meta_title');
        $tag->meta_description = $result['meta_description'] ?? $tag->getOriginal('meta_description');
        $tag->content = $result['content'] ?? $tag->getOriginal('content');
        $tag->seo_optimized = true;
        $tag->seo_optimized_at = now();
        $tag->save();
    }

    protected function generateSeoContent(AdTag $tag): array
    {
        $siteTopic = env('SITE_TOPIC', 'дошка оголошень');
        $language = env('ARTICLE_LANGUAGE', 'українська');
        $tagName = $tag->getOriginal('name');
        $adsCount = $tag->ads_count ?? 0;

        $prompt = $this->prompt(
            'tag_seo_generate',
            'SEO-текст для сторінки тега (мітки) оголошень',
            "Ти — SEO-копірайтер сайту-дошки оголошень \"{{site_topic}}\". "
                . "Потрібно написати УНІКАЛЬНИЙ SEO-текст для сторінки з оголошеннями за міткою "
                . "\"{{tag_name}}\" (на сторінці зараз {{ads_count}} оголошень із цією міткою).\n\n"
                . "Це НЕ шаблонний текст із простою підстановкою назви тега — потрібен справді "
                . "змістовний, корисний текст саме про цю конкретну тему/товар/категорію, який "
                . "допоможе відвідувачу зрозуміти, що він тут знайде, і буде УНІКАЛЬНИМ за структурою "
                . "речень відносно текстів на сторінках інших тегів (не шаблон з одним замінним словом).\n\n"
                . "Пиши мовою: {{language}}.\n\n"
                . "Відповідь — ТІЛЬКИ валідний JSON, без вступних фраз і markdown-обрамлення:\n"
                . "{\n"
                . "  \"meta_title\": \"SEO-заголовок сторінки, до 60 символів, з міткою\",\n"
                . "  \"meta_description\": \"SEO-опис сторінки, 150-160 символів\",\n"
                . "  \"content\": \"SEO-текст на 100-200 слів для розміщення внизу сторінки зі списком оголошень за цією міткою\"\n"
                . "}",
            [
                'site_topic' => $siteTopic,
                'tag_name' => $tagName,
                'ads_count' => $adsCount,
                'language' => $language,
            ],
            'Генерує унікальний meta_title/meta_description/SEO-текст для сторінки конкретного тега (мітки) оголошень.'
        );

        $raw = $this->callLlm($prompt, 1000, $this->validatesAsJsonObject());
        $json = $this->extractJsonObject($raw);
        $data = json_decode($json, true);

        if (!is_array($data) || empty($data['content'])) {
            throw new \RuntimeException('Не вдалося розпарсити JSON відповіді моделі');
        }

        return $data;
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
