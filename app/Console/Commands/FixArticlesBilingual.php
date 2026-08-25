<?php

namespace App\Console\Commands;

use App\Article;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class FixArticlesBilingual extends Command
{
    /**
     * php artisan articles:fix-bilingual
     *
     * ОДНОРАЗОВА команда виправлення 31 наявної статті, які опинились
     * в мовному безладі:
     *  - ID 1-20: написані російською в сирих колонках, UK-версії немає.
     *  - ID 21-31: написані УКРАЇНСЬКОЮ, але в СИРИХ колонках (помилка
     *    старої версії content:publish) — переносимо в _uk, генеруємо
     *    справжній російський переклад у сирі колонки.
     */
    protected $signature = 'articles:fix-bilingual {--only=} {--dry-run}';

    protected $description = 'Одноразово виправляє мовний безлад у 31 наявній статті блогу';

    /** @var Client */
    protected $http;

    // Статті, які насправді написані УКРАЇНСЬКОЮ, але лежать у сирих
    // (мовби "російських") колонках через баг старої версії content:publish.
    const UK_IN_RAW_COLUMNS = [21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31];

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

        $only = $this->option('only');
        $dryRun = $this->option('dry-run');

        // --- Крок 1: перенести UK-текст із сирих колонок у _uk для 21-31 ---
        $swapIds = self::UK_IN_RAW_COLUMNS;
        if ($only) {
            $swapIds = array_intersect($swapIds, [(int) $only]);
        }

        foreach ($swapIds as $id) {
            $article = Article::find($id);
            if (!$article) {
                continue;
            }

            $alreadySwapped = !empty($article->getOriginal('name_uk'));
            if ($alreadySwapped) {
                $this->info("ID={$id}: вже перенесено раніше, пропускаю крок 1");
                continue;
            }

            $this->info("ID={$id}: переношу UK-текст із сирих колонок у _uk");

            if (!$dryRun) {
                $article->name_uk = $article->getOriginal('name');
                $article->excerpt_uk = $article->getOriginal('excerpt');
                $article->content_uk = $article->getOriginal('content');
                $article->meta_title_uk = $article->getOriginal('meta_title');
                $article->meta_description_uk = $article->getOriginal('meta_description');
                $article->save();
            }
        }

        // --- Крок 2: для КОЖНОЇ статті визначити, яка версія відсутня, і
        //     перекласти Claude'ом у потрібному напрямку ---
        $articles = Article::orderBy('id')->get();
        if ($only) {
            $articles = $articles->where('id', (int) $only);
        }

        foreach ($articles as $article) {
            $hasRu = !empty($article->getOriginal('name'));
            $hasUk = !empty($article->getOriginal('name_uk'));

            if (in_array($article->id, self::UK_IN_RAW_COLUMNS)) {
                // Для цих: після кроку 1 _uk вже заповнено (справжній UK-
                // текст), а сирі колонки досі містять СТАРИЙ помилковий
                // український текст — його треба ЗАМІНИТИ на справжній RU.
                $needsTranslation = 'to_ru';
                $sourceName = $article->getOriginal('name_uk');
                $sourceExcerpt = $article->getOriginal('excerpt_uk');
                $sourceContent = $article->getOriginal('content_uk');
                $sourceMetaTitle = $article->getOriginal('meta_title_uk');
                $sourceMetaDesc = $article->getOriginal('meta_description_uk');
            } else {
                // Звичайний випадок (ID 1-20): RU є, UK — немає.
                if (!empty($article->getOriginal('name_uk'))) {
                    $this->info("ID={$article->id}: UK вже перекладено, пропускаю");
                    continue;
                }
                $needsTranslation = 'to_uk';
                $sourceName = $article->getOriginal('name');
                $sourceExcerpt = $article->getOriginal('excerpt');
                $sourceContent = $article->getOriginal('content');
                $sourceMetaTitle = $article->getOriginal('meta_title');
                $sourceMetaDesc = $article->getOriginal('meta_description');
            }

            $this->info("ID={$article->id}: перекладаю ({$needsTranslation})...");

            if ($dryRun) {
                continue;
            }

            try {
                $translated = $this->translateArticle(
                    $sourceName,
                    $sourceExcerpt,
                    $sourceContent,
                    $sourceMetaTitle,
                    $sourceMetaDesc,
                    $needsTranslation === 'to_uk' ? 'українську' : 'російську'
                );

                if ($needsTranslation === 'to_uk') {
                    $article->name_uk = $translated['name'];
                    $article->excerpt_uk = $translated['excerpt'];
                    $article->content_uk = $translated['content'];
                    $article->meta_title_uk = $translated['meta_title'];
                    $article->meta_description_uk = $translated['meta_description'];
                } else {
                    // ЗАМІНЮЄМО старий помилковий укр. текст в сирих
                    // колонках на справжній російський переклад.
                    $article->name = $translated['name'];
                    $article->excerpt = $translated['excerpt'];
                    $article->content = $translated['content'];
                    $article->meta_title = $translated['meta_title'];
                    $article->meta_description = $translated['meta_description'];
                }
                $article->save();
                $this->info("  -> OK");
            } catch (\Throwable $e) {
                $this->error("  Помилка: " . $e->getMessage());
            }

            usleep(300000);
        }

        $this->info('Готово.');
        return 0;
    }

    protected function translateArticle(
        string $name,
        ?string $excerpt,
        string $content,
        ?string $metaTitle,
        ?string $metaDescription,
        string $targetLanguage
    ): array {
        $prompt = "Ти — професійний перекладач і редактор блогу. Переклади наступну статтю "
            . "на {$targetLanguage} мову. Це має бути якісний, природний текст рідною мовою — "
            . "не дослівний переклад слово-в-слово, а гарний редакторський переклад, що зберігає "
            . "сенс, тон і структуру оригіналу. Зберігай усі HTML-теги (<h2>, <p>, <ul>, <li>, "
            . "<a href=\"...\">, <figure>, <img> тощо) БЕЗ ЗМІН, перекладай тільки текстовий "
            . "вміст усередині тегів. Посилання (href) НЕ чіпай.\n\n"
            . "КРИТИЧНО ВАЖЛИВО ДЛЯ ФОРМАТУ: у полі content використовуй ОДИНАРНІ лапки для "
            . "HTML-атрибутів (напр. <a href='...' class='...'>, НЕ <a href=\"...\">). Це "
            . "обов'язково, бо подвійні лапки в HTML конфліктують із подвійними лапками, якими "
            . "обрамлений сам JSON-рядок, і ламають структуру відповіді.\n\n"
            . "ЩЕ ОДНЕ КРИТИЧНО ВАЖЛИВЕ ПРАВИЛО: НІКОЛИ не використовуй символ \" (подвійні лапки) "
            . "усередині тексту статті для ЖОДНОЇ мети — ні для позначення дюймів (пиши \"55-дюймовий\" "
            . "замість \"55\\\"\"), ні для цитат (використовуй лапки-ялинки « » замість прямих \"). "
            . "Кожен буквальний символ \" всередині JSON-рядка ламає всю відповідь, тому НАДІЙНІШЕ "
            . "просто ніколи його не використовувати в тексті статті, ніж покладатись на екранування.\n\n"
            . "Назва: \"{$name}\"\n\n"
            . "Короткий опис: \"" . ($excerpt ?? '') . "\"\n\n"
            . "Meta title: \"" . ($metaTitle ?? '') . "\"\n\n"
            . "Meta description: \"" . ($metaDescription ?? '') . "\"\n\n"
            . "Контент:\n{$content}\n\n"
            . "Відповідь — ТІЛЬКИ валідний JSON-об'єкт без markdown-обрамлення, формату:\n"
            . "{\"name\": \"...\", \"excerpt\": \"...\", \"meta_title\": \"...\", "
            . "\"meta_description\": \"...\", \"content\": \"...\"}";

        $raw = $this->callClaude($prompt, 8000);
        $raw = $this->sanitizeJsonControlChars($raw);

        try {
            $json = $this->extractJsonObject($raw);
            $data = json_decode($json, true);
        } catch (\Throwable $e) {
            $data = null;
        }

        if (!is_array($data)) {
            $debugFile = storage_path('logs/article_translate_debug_' . time() . '.txt');
            file_put_contents($debugFile, $raw);
            $this->error('--- ДІАГНОСТИКА: довжина сирої відповіді = ' . strlen($raw) . ' символів ---');
            $this->error('--- Повна відповідь записана у файл: ' . $debugFile . ' ---');
            $this->error('--- json_last_error: ' . json_last_error_msg() . ' ---');
            throw new \RuntimeException('Не вдалося розпарсити JSON відповіді моделі');
        }

        return $data;
    }

    /**
     * Захисне "ремонтування" JSON: якщо Claude забула заекранувати сирий
     * символ нового рядка/табуляції ВСЕРЕДИНІ JSON-рядка (валідний JSON
     * вимагає \n, а не буквальний перенос рядка) — виправляємо це тут,
     * а не сподіваємось, що промпт завжди спрацює ідеально. Проходимо
     * по байтах і відстежуємо, чи ми зараз "усередині рядка" (між
     * незаекранованими лапками) — керуючі символи поза рядками (просто
     * форматування JSON) не чіпаємо.
     */
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