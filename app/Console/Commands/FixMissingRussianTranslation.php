<?php

namespace App\Console\Commands;

use App\Article;
use App\Console\Commands\Concerns\CallsLlm;
use App\Console\Commands\Concerns\TranslatesViaDeepL;
use App\Console\Commands\Concerns\UsesPromptTemplates;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class FixMissingRussianTranslation extends Command
{
    use CallsLlm;
    use TranslatesViaDeepL;
    use UsesPromptTemplates;

    /**
     * php artisan articles:fix-missing-translation
     *
     * ОДНОРАЗОВА команда виправлення статей, опублікованих/оновлених
     * через content:publish чи content:refresh ДО виправлення цього
     * бага: текст генерувався УКРАЇНСЬКОЮ (ARTICLE_LANGUAGE), але
     * зберігався в СИРИХ колонках — тоді як за мовною архітектурою
     * сайту сирі колонки мають бути РОСІЙСЬКОЮ, а _uk — українською.
     * Наслідок: RU-відвідувачі бачили український текст замість
     * перекладу (той самий клас бага, що вже раз лагодили точково для
     * ID 21-31 через FixArticlesBilingual, але першопричину в
     * content:publish тоді не виправили).
     *
     * Для кожної постраждалої статті:
     * 1. Поточний (насправді український) текст переноситься в _uk.
     * 2. Генерується СВІЖИЙ переклад на російську для сирих колонок.
     * 3. slug/URL НЕ чіпається — лишається як є.
     */
    protected $signature = 'articles:fix-missing-translation {--dry-run} {--only=}';

    protected $description = 'Одноразово виправляє статті без російського перекладу (укр. текст у сирих колонках)';

    /** @var Client */
    protected $http;

    public function __construct()
    {
        parent::__construct();
        $this->http = new Client();
    }

    public function handle()
    {
        $query = Article::where('ai_generated', true)
            ->where(function ($q) {
                $q->whereNull('name_uk')->orWhere('name_uk', '');
            });

        if ($only = $this->option('only')) {
            $query->where('id', (int) $only);
        }

        $articles = $query->orderBy('id')->get();

        if ($articles->isEmpty()) {
            $this->info('Немає статей, що потребують виправлення.');
            return 0;
        }

        $dryRun = $this->option('dry-run');

        $this->info('Знайдено ' . $articles->count() . ' статей(тю) для виправлення' . ($dryRun ? ' (DRY RUN — нічого не змінюю)' : ''));

        foreach ($articles as $i => $article) {
            $this->info('[' . ($i + 1) . '/' . $articles->count() . "] ID={$article->id}: {$article->getOriginal('name')}");

            if ($dryRun) {
                continue;
            }

            try {
                $this->fixOne($article);
                $this->info('  -> OK');
            } catch (\Throwable $e) {
                $this->error('  Помилка: ' . $e->getMessage());
            }

            usleep(300000);
        }

        return 0;
    }

    protected function fixOne(Article $article): void
    {
        $ukName = $article->getOriginal('name');
        $ukExcerpt = $article->getOriginal('excerpt') ?? '';
        $ukContent = $article->getOriginal('content');
        $ukMetaTitle = $article->getOriginal('meta_title') ?? $ukName;
        $ukMetaDescription = $article->getOriginal('meta_description') ?? '';

        $ru = null;
        try {
            [$ruName, $ruExcerpt, $ruContent, $ruMetaTitle, $ruMetaDescription] = $this->translateViaDeepL(
                [$ukName, $ukExcerpt, $ukContent, $ukMetaTitle, $ukMetaDescription]
            );
            $ru = [
                'name' => $ruName,
                'excerpt' => $ruExcerpt,
                'content' => $ruContent,
                'meta_title' => $ruMetaTitle,
                'meta_description' => $ruMetaDescription,
            ];
        } catch (\Throwable $e) {
            $this->warn('  DeepL недоступний (' . $e->getMessage() . '), пробую переклад через LLM...');
            $ru = $this->translateToRussian($ukName, $ukExcerpt, $ukContent, $ukMetaTitle, $ukMetaDescription);
        }

        $article->update([
            // slug/URL свідомо НЕ чіпаємо.
            'name_uk' => $ukName,
            'excerpt_uk' => $ukExcerpt,
            'content_uk' => $ukContent,
            'meta_title_uk' => $ukMetaTitle,
            'meta_description_uk' => $ukMetaDescription,
            'name' => $ru['name'] ?? $ukName,
            'excerpt' => $ru['excerpt'] ?? $ukExcerpt,
            'content' => $ru['content'] ?? $ukContent,
            'meta_title' => $ru['meta_title'] ?? $ukMetaTitle,
            'meta_description' => $ru['meta_description'] ?? $ukMetaDescription,
        ]);
    }

    /**
     * Ідентично PublishArticle::translateToRussian() — той самий
     * ключ промпту 'article_translate_to_russian' в адмінці "Промпти".
     */
    protected function translateToRussian(string $name, string $excerpt, string $contentHtml, string $metaTitle, string $metaDescription): array
    {
        $prompt = $this->prompt(
            'article_translate_to_russian',
            'Переклад щойно опублікованої статті на російську',
            "Ти — професійний перекладач і редактор блогу. Переклади наступну статтю "
                . "на російську мову. Це має бути якісний, природний текст рідною мовою — "
                . "не дослівний переклад слово-в-слово, а гарний редакторський переклад, що зберігає "
                . "сенс, тон і структуру оригіналу. Зберігай усі HTML-теги (<h2>, <p>, <ul>, <li>, "
                . "<a href=\"...\">, <figure>, <img> тощо) БЕЗ ЗМІН, перекладай тільки текстовий "
                . "вміст усередині тегів. Посилання (href) НЕ чіпай.\n\n"
                . "КРИТИЧНО ВАЖЛИВО ДЛЯ ФОРМАТУ: у полі content використовуй ОДИНАРНІ лапки для "
                . "HTML-атрибутів (напр. <a href='...' class='...'>, НЕ <a href=\"...\">). Це "
                . "обов'язково, бо подвійні лапки в HTML конфліктують із подвійними лапками, якими "
                . "обрамлений сам JSON-рядок, і ламають структуру відповіді.\n\n"
                . "ЩЕ ОДНЕ КРИТИЧНО ВАЖЛИВЕ ПРАВИЛО: НІКОЛИ не використовуй символ \" (подвійні лапки) "
                . "усередині тексту статті для ЖОДНОЇ мети — ні для позначення дюймів, ні для цитат "
                . "(використовуй лапки-ялинки « » замість прямих \"). Кожен буквальний символ \" "
                . "усередині JSON-рядка ламає всю відповідь, тому НАДІЙНІШЕ просто ніколи його не "
                . "використовувати в тексті статті, ніж покладатись на екранування.\n\n"
                . "Назва: \"{{name}}\"\n\n"
                . "Короткий опис: \"{{excerpt}}\"\n\n"
                . "Meta title: \"{{meta_title}}\"\n\n"
                . "Meta description: \"{{meta_description}}\"\n\n"
                . "Контент:\n{{content}}\n\n"
                . "Відповідь — ТІЛЬКИ валідний JSON-об'єкт без markdown-обрамлення, формату:\n"
                . "{\"name\": \"...\", \"excerpt\": \"...\", \"meta_title\": \"...\", "
                . "\"meta_description\": \"...\", \"content\": \"...\"}",
            [
                'name' => $name,
                'excerpt' => $excerpt,
                'meta_title' => $metaTitle,
                'meta_description' => $metaDescription,
                'content' => $contentHtml,
            ],
            'Перекладає щойно згенеровану українську статтю на російську одразу після публікації (для сирих колонок).'
        );

        $raw = $this->callLlm($prompt, 8000, $this->validatesAsJsonObject());
        $raw = $this->sanitizeJsonControlChars($raw);
        $json = $this->extractJsonObject($raw);
        $data = json_decode($json, true);

        if (!is_array($data)) {
            throw new \RuntimeException('Не вдалося розпарсити JSON перекладу на російську');
        }

        return $data;
    }

    protected function sanitizeJsonControlChars(string $text): string
    {
        $result = '';
        $inString = false;
        $escaped = false;
        $len = strlen($text);

        for ($i = 0; $i < $len; $i++) {
            $ch = $text[$i];
            $ord = ord($ch);

            if ($inString) {
                if ($escaped) {
                    $result .= $ch;
                    $escaped = false;
                    continue;
                }
                if ($ch === '\\') {
                    $result .= $ch;
                    $escaped = true;
                    continue;
                }
                if ($ch === '"') {
                    $inString = false;
                    $result .= $ch;
                    continue;
                }
                if ($ord < 0x20) {
                    switch ($ch) {
                        case "\n": $result .= '\\n'; break;
                        case "\r": $result .= '\\r'; break;
                        case "\t": $result .= '\\t'; break;
                        default:   $result .= sprintf('\\u%04x', $ord);
                    }
                    continue;
                }
                $result .= $ch;
            } else {
                if ($ch === '"') {
                    $inString = true;
                }
                $result .= $ch;
            }
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
