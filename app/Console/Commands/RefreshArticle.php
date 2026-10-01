<?php

namespace App\Console\Commands;

use App\Article;
use App\Console\Commands\Concerns\CallsLlm;
use App\Console\Commands\Concerns\TranslatesViaDeepL;
use App\Console\Commands\Concerns\UpdatesDailyReport;
use App\Console\Commands\Concerns\UsesPromptTemplates;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RefreshArticle extends Command
{
    use CallsLlm;
    use TranslatesViaDeepL;
    use UpdatesDailyReport;
    use UsesPromptTemplates;

    /**
     * php artisan content:refresh
     *
     * Знаходить старі й малопереглядові статті, переписує текст через
     * Claude API, зберігаючи slug, картинки й ID (щоб не ламати URL/SEO-вагу).
     */
    protected $signature = 'content:refresh {--limit=}';

    protected $description = 'Оновлює старі малопереглядові статті свіжим текстом через Claude API';

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

        $ageMonths = (int) env('ARTICLE_REFRESH_AGE_MONTHS', 6);
        $maxViews = (int) env('ARTICLE_REFRESH_MAX_VIEWS', 20);
        $limit = (int) ($this->option('limit') ?: env('REFRESH_PER_RUN', 1));

        $cutoff = now()->subMonths($ageMonths)->toDateTimeString();

        $candidates = DB::select(DB::raw("
            SELECT * FROM (
                SELECT a.id, a.name, a.updated_at,
                    COALESCE(v.total, 0) AS views
                FROM articles a
                LEFT JOIN (
                    SELECT article_id, COUNT(*) as total FROM article_views
                    WHERE event_type = 'view'
                    GROUP BY article_id
                ) v ON v.article_id = a.id
                WHERE a.updated_at < '{$cutoff}'
            ) t
            WHERE t.views < {$maxViews}
            ORDER BY views ASC, t.updated_at ASC
            LIMIT {$limit}
        "));

        if (empty($candidates)) {
            $this->info("Немає статей, що підходять під критерії (старіші {$ageMonths} міс., менше {$maxViews} переглядів).");
            return 0;
        }

        $this->info('Знайдено ' . count($candidates) . ' статей(тю) для оновлення');

        foreach ($candidates as $i => $row) {
            $this->info('[' . ($i + 1) . '/' . count($candidates) . "] ID={$row->id}, \"{$row->name}\" (переглядів: {$row->views})");
            try {
                $this->refreshOne($row->id);
            } catch (\Throwable $e) {
                $this->error("Помилка оновлення статті ID={$row->id}: " . $e->getMessage());
            }
        }

        // lastmod у sitemap має відображати реальну дату оновлення статті.
        $this->call('content:sitemap');

        return 0;
    }

    protected function refreshOne(int $articleId): void
    {
        $article = Article::with('categories')->findOrFail($articleId);
        $recentArticles = Article::with('categories')->latest()->take(30)->get();

        $oldContent = $this->stripRelatedArticlesBlock($article->content);

        $refreshed = $this->generateRefreshedContent($article, $oldContent, $recentArticles);

        $newContentHtml = $this->sanitizeInternalLinks($refreshed['content_html'], $recentArticles);
        $newContentHtml = $this->preserveMissingImages($oldContent, $newContentHtml);

        $categoryId = optional($article->categories->first())->id;
        if ($categoryId) {
            $relatedBlock = $this->buildRelatedArticlesBlock($categoryId, $article->id);
            if ($relatedBlock) {
                $newContentHtml .= $relatedBlock;
            }
        }

        $ukExcerpt = $refreshed['excerpt'] ?? $article->getOriginal('excerpt_uk') ?? $article->getOriginal('excerpt');
        $ukMetaTitle = $refreshed['meta_title'] ?? $article->getOriginal('meta_title_uk') ?? $article->getOriginal('meta_title');
        $ukMetaDescription = $refreshed['meta_description'] ?? $article->getOriginal('meta_description_uk') ?? $article->getOriginal('meta_description');

        $ukName = $article->getOriginal('name_uk') ?: $article->getOriginal('name');

        $ru = null;
        try {
            [$ruName, $ruExcerpt, $ruContent, $ruMetaTitle, $ruMetaDescription] = $this->translateViaDeepL(
                [$ukName, $ukExcerpt, $newContentHtml, $ukMetaTitle, $ukMetaDescription]
            );
            $ru = [
                'name' => $ruName,
                'excerpt' => $ruExcerpt,
                'content' => $ruContent,
                'meta_title' => $ruMetaTitle,
                'meta_description' => $ruMetaDescription,
            ];
        } catch (\Throwable $e) {
            $this->warn('DeepL недоступний (' . $e->getMessage() . '), пробую переклад через LLM...');
            try {
                $ru = $this->translateToRussian($ukName, $ukExcerpt, $newContentHtml, $ukMetaTitle, $ukMetaDescription);
            } catch (\Throwable $e2) {
                $this->warn('Не вдалося перекласти оновлений текст на російську жодним способом: ' . $e2->getMessage() . ' — сирі колонки лишаться з попереднім текстом.');
                $ru = null;
            }
        }

        $article->update([
            // name/slug свідомо НЕ чіпаємо — щоб не зламати існуючий URL і SEO-вагу.
            'excerpt' => $ru['excerpt'] ?? $article->getOriginal('excerpt'),
            'excerpt_uk' => $ukExcerpt,
            'content' => $ru['content'] ?? $article->getOriginal('content'),
            'content_uk' => $newContentHtml,
            'meta_title' => $ru['meta_title'] ?? $article->getOriginal('meta_title'),
            'meta_title_uk' => $ukMetaTitle,
            'meta_description' => $ru['meta_description'] ?? $article->getOriginal('meta_description'),
            'meta_description_uk' => $ukMetaDescription,
            'focus_keyword' => $refreshed['focus_keyword'] ?? $article->focus_keyword,
            'tags' => $refreshed['tags'] ?? $article->tags,
        ]);

        $this->info("Стаття ID={$articleId} оновлена. URL={$article->url}");

        $this->appendDailyReportStat(
            'articles_refreshed_count',
            1,
            'articles_refreshed_summary',
            $article->getOriginal('name')
        );
    }

    // -----------------------------------------------------------------
    // Anthropic API — сам метод callClaude тепер у трейті CallsLlm
    // -----------------------------------------------------------------

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

    /**
     * Перекладає оновлений українською текст на російську — для сирих
     * колонок (name/content/excerpt/meta_*), які за мовною архітектурою
     * сайту мають бути саме російською. Ідентично PublishArticle::translateToRussian().
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

    /**
     * Захисне "ремонтування" JSON — ідентично PublishArticle::sanitizeJsonControlChars().
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

    protected function generateRefreshedContent(Article $article, string $oldContentHtml, $recentArticles): array
    {
        $siteTopic = env('SITE_TOPIC', 'дошка оголошень');
        $language = env('ARTICLE_LANGUAGE', 'українська');
        $today = now()->format('Y-m-d');

        $prevListStr = '(поки що немає жодної статті)';
        if ($recentArticles->isNotEmpty()) {
            $lines = [];
            foreach ($recentArticles as $a) {
                if ($a->id === $article->id) {
                    continue;
                }
                $catNames = $a->categories->pluck('name')->implode(', ');
                $lines[] = "- \"{$a->name}\" — {$a->url} (категорія: {$catNames})";
            }
            $prevListStr = implode("\n", $lines) ?: $prevListStr;
        }

        $prompt = $this->prompt(
            'article_refresh',
            'Оновлення старих малопереглядових статей',
            "Ти — редактор блогу і SEO-спеціаліст сайту-дошки оголошень \"{{site_topic}}\". "
                . "Сьогодні {{today}}. Стаття нижче була опублікована давно й майже не набирає переглядів — "
                . "потрібно її ОНОВИТИ: освіжити факти й приклади, покращити структуру і SEO, зробити текст "
                . "актуальнішим і кориснішим. Це не нова стаття — переписуй на основі наявної, зберігаючи тему.\n\n"
                . "Заголовок статті (НЕ змінювати сенс, можна легко відшліфувати): \"{{article_name}}\"\n\n"
                . "Поточний текст статті (HTML):\n---\n{{old_content}}\n---\n\n"
                . "Пиши мовою: {{language}}.\n\n"
                . "ВАЖЛИВО: якщо в поточному тексті є теги <figure> з <img> — збережи їх ДОСЛІВНО на приблизно "
                . "тому ж місці в тексті. Не видаляй і не змінюй їхні атрибути (src, alt тощо) — тільки текст "
                . "навколо них можна переписувати.\n\n"
                . "Твоя відповідь має бути ТІЛЬКИ валідним JSON-об'єктом і нічим більше — без вступних фраз, "
                . "без markdown-обрамлення. Структура:\n\n"
                . "{\n"
                . "  \"excerpt\": \"короткий опис статті для картки в блозі, 1-2 речення\",\n"
                . "  \"content_html\": \"оновлений HTML-текст статті\",\n"
                . "  \"focus_keyword\": \"основний SEO-ключ, 2-4 слова\",\n"
                . "  \"meta_title\": \"SEO-заголовок, до 60 символів\",\n"
                . "  \"meta_description\": \"SEO-опис, 150-160 символів\",\n"
                . "  \"tags\": [\"3-5 коротких міток\"]\n"
                . "}\n\n"
                . "Вимоги до content_html:\n"
                . "- Живий, корисний текст, МІНІМУМ 650 слів.\n"
                . "- HTML-теги: <h2>, <h3>, <p>, <ul>/<li> тощо, де доречно.\n"
                . "- БЕЗ <html>/<body> обгортки і БЕЗ h1.\n"
                . "- Лапки всередині тексту екрануй як належить для JSON-рядка (\\\").\n"
                . "- focus_keyword має природно зустрічатися в тексті.\n\n"
                . "ВНУТРІШНЯ ПЕРЕЛІНКОВКА:\nОсь список інших опублікованих статей на сайті:\n{{recent_articles}}\n\n"
                . "Якщо серед них є статті, ДІЙСНО релевантні темі — природно встав у content_html 1-3 текстових "
                . "посилання на них у форматі <a href=\"URL\">текст анкора</a>. НЕ вигадуй URL, використовуй "
                . "ТІЛЬКИ ті, що є у списку вище. Якщо жодна не підходить — не став жодного посилання.\n\n"
                . "Пам'ятай: вся твоя відповідь — це один JSON-об'єкт, що починається з { і закінчується }.",
            [
                'site_topic' => $siteTopic,
                'today' => $today,
                'article_name' => $article->name,
                'old_content' => $oldContentHtml,
                'language' => $language,
                'recent_articles' => $prevListStr,
            ],
            'Переписує старі малопереглядові статті: освіжує факти, покращує SEO, зберігаючи URL і вбудовані картинки.'
        );

        $raw = $this->callLlm($prompt, 4000, $this->validatesAsJsonObject());
        $json = $this->extractJsonObject($raw);
        $data = json_decode($json, true);
        if (!is_array($data)) {
            throw new \RuntimeException('Не вдалося розпарсити JSON відповіді моделі');
        }
        return $data;
    }

    // -----------------------------------------------------------------
    // Захист вбудованих елементів при переписуванні
    // -----------------------------------------------------------------

    /**
     * Прибирає посилання на /blog/..., яких немає серед реально існуючих
     * статей. Ідентично PublishArticle::sanitizeInternalLinks().
     */
    protected function sanitizeInternalLinks(string $html, $recentArticles): string
    {
        $whitelist = [];
        foreach ($recentArticles as $a) {
            $whitelist[$a->url] = true;
        }

        return preg_replace_callback(
            '/<a\s+href="([^"]*)"[^>]*>(.*?)<\/a>/is',
            function ($m) use ($whitelist) {
                $href = $m[1];
                $anchorText = $m[2];
                if (Str::contains($href, '/blog/') && !isset($whitelist[$href])) {
                    return $anchorText;
                }
                return $m[0];
            },
            $html
        );
    }

    /**
     * Якщо модель, попри інструкцію, "загубила" якийсь <figure> з картинкою
     * зі старого тексту — повертаємо його в кінець статті. Без цього
     * запобіжника оновлення могло б непомітно прибрати вставлену раніше
     * другу картинку статті.
     */
    protected function preserveMissingImages(string $oldHtml, string $newHtml): string
    {
        preg_match_all('/<figure[^>]*>.*?<\/figure>/is', $oldHtml, $matches);
        $missing = '';

        foreach ($matches[0] as $figure) {
            if (Str::contains($newHtml, $figure)) {
                continue;
            }
            if (preg_match('/<img[^>]+src="([^"]+)"/i', $figure, $srcMatch) && Str::contains($newHtml, $srcMatch[1])) {
                continue;
            }
            $missing .= "\n" . $figure;
        }

        return $newHtml . $missing;
    }

    /**
     * Прибирає старий блок "Схожі статті" перед тим, як передати текст
     * моделі — цей блок генерується заново нижче, з актуальним списком.
     */
    protected function stripRelatedArticlesBlock(string $html): string
    {
        return preg_replace('/\n?<h3>Схожі статті<\/h3>\s*<ul>.*?<\/ul>\s*/is', '', $html);
    }

    /**
     * Ідентично PublishArticle::buildRelatedArticlesBlock(), з додатковим
     * виключенням самої статті, що оновлюється, зі списку "схожих".
     */
    protected function buildRelatedArticlesBlock(int $categoryId, ?int $excludeArticleId = null, int $limit = 3): string
    {
        $query = Article::whereHas('categories', function ($q) use ($categoryId) {
            $q->where('article_categories.id', $categoryId);
        });

        if ($excludeArticleId) {
            $query->where('id', '<>', $excludeArticleId);
        }

        $related = $query->latest()->take($limit)->get();

        if ($related->isEmpty()) {
            return '';
        }

        $items = $related->map(function ($a) {
            return '  <li><a href="' . $a->url . '">' . e($a->name) . '</a></li>';
        })->implode("\n");

        return "\n<h3>Схожі статті</h3>\n<ul>\n{$items}\n</ul>\n";
    }
}
