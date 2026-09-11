<?php

namespace App\Console\Commands;

use App\Article;
use App\Console\Commands\Concerns\CallsLlm;
use App\Console\Commands\Concerns\FetchesImages;
use App\Console\Commands\Concerns\UsesPromptTemplates;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PublishArticle extends Command
{
    use CallsLlm;
    use UsesPromptTemplates;
    use FetchesImages;

    /**
     * php artisan content:publish
     */
    protected $signature = 'content:publish';

    protected $description = 'Бере тему з контент-плану (або вигадує на льоту), генерує статтю, картинки, публікує в articles';

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
        if (empty(env('UNSPLASH_ACCESS_KEY'))) {
            $this->error('UNSPLASH_ACCESS_KEY не задан у .env');
            return 1;
        }

        $postsPerRun = (int) env('POSTS_PER_RUN', 1);

        $planEntries = DB::table('content_plan_items')
            ->where('status', 'planned')
            ->orderBy('priority', 'asc')
            ->limit($postsPerRun)
            ->get();

        $workItems = [];
        if ($planEntries->isNotEmpty()) {
            $ids = $planEntries->pluck('id')->toArray();
            DB::table('content_plan_items')->whereIn('id', $ids)->update(['status' => 'in_progress']);
            $this->info('Беру ' . count($ids) . ' тем(и) з контент-плану');
            foreach ($planEntries as $entry) {
                $workItems[] = ['topic' => $entry->topic, 'plan_entry' => $entry];
            }
        } else {
            $this->info('Контент-план порожній — генерую тему на льоту');
            try {
                $topic = $this->generateAdHocTopic();
                $workItems[] = ['topic' => $topic, 'plan_entry' => null];
            } catch (\Throwable $e) {
                $this->error('Не вдалося згенерувати тему на льоту: ' . $e->getMessage());
                return 1;
            }
        }

        foreach ($workItems as $i => $item) {
            $topic = $item['topic'];
            $planEntry = $item['plan_entry'];
            $this->info('[' . ($i + 1) . '/' . count($workItems) . "] Тема: {$topic}");

            try {
                $this->processTopic($topic, $planEntry);
            } catch (\Throwable $e) {
                $this->error("Помилка обробки теми '{$topic}': " . $e->getMessage());
                if ($planEntry) {
                    DB::table('content_plan_items')->where('id', $planEntry->id)->update(['status' => 'planned']);
                }
            }
        }

        return 0;
    }

    protected function processTopic(string $topic, $planEntry): void
    {
        $recentArticles = Article::with('categories')->latest()->take(30)->get();

        $topicForModel = $topic;
        if ($planEntry && !empty($planEntry->focus_keyword_hint)) {
            $topicForModel = "{$topic} (орієнтовний фокусний ключ: {$planEntry->focus_keyword_hint})";
        }

        $article = $this->generateArticleContent($topicForModel, $recentArticles);

        // Claude іноді ігнорує інструкцію "не вигадуй URL" і додає правдоподібні,
        // але неіснуючі посилання на /blog/... Гарантовано прибираємо будь-яке
        // посилання на блог, якого немає в реальному списку статей — незалежно
        // від того, наскільки добре сформульований був промпт.
        $article['content_html'] = $this->sanitizeInternalLinks($article['content_html'], $recentArticles);

        // --- Головна картинка (обкладинка) ---
        $mainImagePath = null;
        try {
            $mainImagePath = $this->fetchAndSaveImage($article['image_keywords'] ?? $topic, Str::slug($article['title']));
            $this->info("Головна картинка збережена: {$mainImagePath}");
        } catch (\Throwable $e) {
            $this->warn('Не вдалося отримати/зберегти головну картинку: ' . $e->getMessage());
        }

        // --- Друга картинка (вставляється всередину тексту) ---
        $contentHtml = $article['content_html'];
        try {
            $secondImagePath = $this->fetchAndSaveImage(
                $article['second_image_keywords'] ?? ($article['image_keywords'] ?? $topic),
                Str::slug($article['title']) . '-2'
            );
            if ($secondImagePath) {
                $contentHtml = $this->insertSecondImage($contentHtml, $secondImagePath, $article['focus_keyword'] ?? $article['title']);
                $this->info("Друга картинка вставлена в текст: {$secondImagePath}");
            }
        } catch (\Throwable $e) {
            $this->warn('Не вдалося отримати/вставити другу картинку: ' . $e->getMessage());
        }
        $contentHtml = str_replace('[SECOND_IMAGE]', '', $contentHtml);

        // --- Категорія ---
        $categoryId = $planEntry->category_id ?? null;

        // --- Гарантований блок "Схожі статті" (за категорією) ---
        if ($categoryId) {
            $relatedBlock = $this->buildRelatedArticlesBlock($categoryId);
            if ($relatedBlock) {
                $contentHtml .= $relatedBlock;
            }
        }

        // --- Створення статті ---
        $newArticle = Article::create([
            'name' => $article['title'],
            'slug' => null, // auto-generate через mutator моделі
            'excerpt' => $article['excerpt'],
            'content' => $contentHtml,
            'sort_order' => 0,
            'meta_title' => $article['meta_title'] ?? $article['title'],
            'meta_description' => $article['meta_description'] ?? $article['excerpt'],
            'image' => $mainImagePath,
            'focus_keyword' => $article['focus_keyword'] ?? null,
            'tags' => $article['tags'] ?? [],
            'ai_generated' => true,
        ]);

        if ($categoryId) {
            $newArticle->categories()->sync([$categoryId]);
        }

        $this->info("Стаття створена: ID={$newArticle->id}, URL={$newArticle->url}");

        if ($planEntry) {
            DB::table('content_plan_items')->where('id', $planEntry->id)->update([
                'status' => 'published',
                'article_id' => $newArticle->id,
                'published_at' => now(),
            ]);
        }
    }

    // -----------------------------------------------------------------
    // Anthropic (Claude) API — сам метод тепер у трейті CallsLlm
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

    protected function generateAdHocTopic(): string
    {
        $siteTopic = env('SITE_TOPIC', 'дошка оголошень');
        $language = env('ARTICLE_LANGUAGE', 'українська');
        $prompt = $this->prompt(
            'article_adhoc_topic',
            'Стаття блогу — тема "на льоту" (коли план порожній)',
            "Ти — редактор блогу на тему \"{{site_topic}}\". Придумай ОДНУ конкретну, цікаву тему "
                . "для статті мовою {{language}}. Дай відповідь одним рядком — просто тема, без пояснень і лапок.",
            ['site_topic' => $siteTopic, 'language' => $language],
            'Використовується, лише якщо контент-план порожній — Claude вигадує тему прямо на місці замість запланованої.'
        );
        return trim($this->callLlm($prompt, 200));
    }

    protected function generateArticleContent(string $topic, $recentArticles): array
    {
        $siteTopic = env('SITE_TOPIC', 'дошка оголошень');
        $language = env('ARTICLE_LANGUAGE', 'українська');

        $prevListStr = '(поки що немає жодної статті)';
        if ($recentArticles->isNotEmpty()) {
            $lines = [];
            foreach ($recentArticles as $a) {
                $catNames = $a->categories->pluck('name')->implode(', ');
                $lines[] = "- \"{$a->name}\" — {$a->url} (категорія: {$catNames})";
            }
            $prevListStr = implode("\n", $lines);
        }

        $prompt = $this->prompt(
            'article_generate',
            'Стаття блогу — повна генерація (головний промпт конвеєра)',
            "Ти — редактор блогу і SEO-спеціаліст сайту-дошки оголошень \"{{site_topic}}\". "
                . "Потрібна стаття на тему: \"{{topic}}\".\nПиши статтю мовою: {{language}}.\n\n"
                . "Твоя відповідь має бути ТІЛЬКИ валідним JSON-об'єктом і нічим більше — без вступних фраз, "
                . "без markdown-обрамлення, без тексту до або після JSON. Структура об'єкта:\n\n"
                . "{\n"
                . "  \"title\": \"заголовок статті\",\n"
                . "  \"excerpt\": \"короткий опис статті для картки в блозі, 1-2 речення\",\n"
                . "  \"image_keywords\": \"2-4 англійських слова для пошуку ГОЛОВНОГО фото на Unsplash\",\n"
                . "  \"second_image_keywords\": \"2-4 англійських слова для пошуку ДРУГОГО фото на Unsplash\",\n"
                . "  \"content_html\": \"готовий HTML-текст статті\",\n"
                . "  \"focus_keyword\": \"основний SEO-ключ, 2-4 слова\",\n"
                . "  \"meta_title\": \"SEO-заголовок, до 60 символів\",\n"
                . "  \"meta_description\": \"SEO-опис, 150-160 символів\",\n"
                . "  \"tags\": [\"3-5 коротких міток\"]\n"
                . "}\n\n"
                . "Вимоги до content_html:\n"
                . "- Живий, корисний текст, МІНІМУМ 650 слів.\n"
                . "- HTML-теги: <h2>, <h3>, <p>, <ul>/<li> тощо, де доречно.\n"
                . "- БЕЗ <html>/<body> обгортки і БЕЗ h1 (заголовок вже в полі title).\n"
                . "- Лапки всередині тексту екрануй як належить для JSON-рядка (\\\").\n"
                . "- focus_keyword має природно зустрічатися в тексті (перший абзац і хоча б один підзаголовок).\n"
                . "- Приблизно в середині статті встав окремим рядком маркер [SECOND_IMAGE] — "
                . "на його місце буде підставлена друга картинка.\n\n"
                . "ВНУТРІШНЯ ПЕРЕЛІНКОВКА:\nОсь список вже опублікованих статей на сайті:\n{{recent_articles}}\n\n"
                . "Якщо серед них є статті, ДІЙСНО релевантні темі поточної статті — природно встав у content_html "
                . "1-3 текстових посилання на них у форматі <a href=\"URL\">текст анкора</a>, вплетені в речення. "
                . "НЕ вигадуй URL, використовуй ТІЛЬКИ ті, що є у списку вище. Якщо жодна не підходить — не став "
                . "жодного посилання.\n\n"
                . "Пам'ятай: вся твоя відповідь — це один JSON-об'єкт, що починається з { і закінчується }.",
            [
                'site_topic' => $siteTopic,
                'topic' => $topic,
                'language' => $language,
                'recent_articles' => $prevListStr,
            ],
            'Головний промпт публікації статей: генерує заголовок, повний текст, SEO-поля, теги і контекстні внутрішні посилання.'
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
    // Картинки — сам код тепер у трейті FetchesImages
    // -----------------------------------------------------------------

    /**
     * Прибирає посилання на /blog/..., яких немає серед реально існуючих
     * статей (список $recentArticles). Модель іноді "вигадує" правдоподібні
     * URL попри пряму заборону в промпті — цей метод є гарантією на рівні
     * коду, а не сподіванням, що модель послухається інструкції.
     * Тег <a> видаляється повністю, текст анкора лишається на місці.
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

    protected function insertSecondImage(string $contentHtml, string $imagePath, string $altText): string
    {
        $imgHtml = '<figure class="wp-block-image size-large">'
            . '<img src="' . $imagePath . '" alt="' . e($altText) . '" loading="lazy" '
            . 'style="max-width:100%;height:auto;" /></figure>';

        if (Str::contains($contentHtml, '[SECOND_IMAGE]')) {
            return str_replace('[SECOND_IMAGE]', $imgHtml, $contentHtml);
        }

        // Якщо модель забула плейсхолдер — вставляємо перед другим <h2>
        preg_match_all('/<h2/i', $contentHtml, $matches, PREG_OFFSET_CAPTURE);
        if (count($matches[0]) >= 2) {
            $pos = $matches[0][1][1];
            return substr($contentHtml, 0, $pos) . $imgHtml . "\n" . substr($contentHtml, $pos);
        }

        return $contentHtml . "\n" . $imgHtml;
    }

    // -----------------------------------------------------------------
    // Внутрішня перелінковка: гарантований блок за категорією
    // -----------------------------------------------------------------

    protected function buildRelatedArticlesBlock(int $categoryId, int $limit = 3): string
    {
        $related = Article::whereHas('categories', function ($q) use ($categoryId) {
            $q->where('article_categories.id', $categoryId);
        })->latest()->take($limit)->get();

        if ($related->isEmpty()) {
            return '';
        }

        $items = $related->map(function ($a) {
            return '  <li><a href="' . $a->url . '">' . e($a->name) . '</a></li>';
        })->implode("\n");

        return "\n<h3>Схожі статті</h3>\n<ul>\n{$items}\n</ul>\n";
    }
}
