<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\CallsLlm;
use App\Console\Commands\Concerns\UsesPromptTemplates;
use App\Page;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class TranslatePagesSeo extends Command
{
    use CallsLlm;
    use UsesPromptTemplates;

    /**
     * php artisan pages:translate-seo
     *
     * Перекладає name, meta_title, meta_description, content статичних
     * сторінок (Page) на українську через Claude API (з фолбеком на
     * OpenRouter/Groq/Cloudflare/Gemini, якщо Claude недоступний —
     * див. Concerns\CallsLlm). Той самий підхід, що й
     * categories:translate-seo — обробляє БУДЬ-ЯКУ сторінку без
     * заповненого content_uk, не тільки конкретні slug'и, тому команда
     * лишається корисною і для майбутніх нових сторінок.
     */
    protected $signature = 'pages:translate-seo {--limit=}';

    protected $description = 'Перекладає SEO-поля статичних сторінок (name, meta_title, meta_description, content) на українську';

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

        $pages = Page::where(function ($q) {
                $q->whereNull('content_uk')->orWhere('content_uk', '');
            })
            ->whereNotNull('content')
            ->orderBy('id')
            ->limit($limit)
            ->get();

        if ($pages->isEmpty()) {
            $this->info('Усі сторінки з заповненим контентом вже перекладені.');
            return 0;
        }

        $this->info('Перекладаю ' . $pages->count() . ' сторінок(и)');

        foreach ($pages as $i => $page) {
            $this->info('[' . ($i + 1) . '/' . $pages->count() . "] ID={$page->id}: {$page->getOriginal('name')}");

            try {
                $result = $this->translateOne($page);

                $page->name_uk = $result['name'] ?? null;
                $page->meta_title_uk = $result['meta_title'] ?? null;
                $page->meta_description_uk = $result['meta_description'] ?? null;
                $page->content_uk = $result['content'] ?? null;
                $page->save();

                $this->info('  -> OK');
            } catch (\Throwable $e) {
                $this->error('  Помилка: ' . $e->getMessage());
            }

            usleep(300000);
        }

        return 0;
    }

    protected function translateOne(Page $page): array
    {
        $name = $page->getOriginal('name') ?? '';
        $metaTitle = $page->getOriginal('meta_title') ?? '';
        $metaDescription = $page->getOriginal('meta_description') ?? '';
        $content = $page->getOriginal('content') ?? '';

        $prompt = $this->prompt(
            'page_translate_seo',
            'Переклад статичних сторінок (Page)',
            "Ти — професійний перекладач і SEO-копірайтер. Переклади наступні поля "
                . "статичної сторінки сайту-дошки оголошень з російської на українську. Зберігай "
                . "структуру й сенс, адаптуй природно для української мови (не дослівний переклад "
                . "слово-в-слово), зберігай HTML-теги в content без змін, якщо вони є.\n\n"
                . "Назва сторінки: \"{{name}}\"\n\n"
                . "Meta title: \"{{meta_title}}\"\n\n"
                . "Meta description: \"{{meta_description}}\"\n\n"
                . "Content:\n{{content}}\n\n"
                . "Відповідь — ТІЛЬКИ валідний JSON без markdown-обрамлення, формату:\n"
                . "{\"name\": \"...\", \"meta_title\": \"...\", \"meta_description\": \"...\", \"content\": \"...\"}",
            [
                'name' => $name,
                'meta_title' => $metaTitle,
                'meta_description' => $metaDescription,
                'content' => $content,
            ],
            'Перекладає статичні сторінки (Умови користування, Про сайт, Заборонені товари тощо) на українську.'
        );

        $raw = $this->callLlm($prompt, 4000, $this->validatesAsJsonObject());
        $json = $this->extractJsonObject($raw);
        $data = json_decode($json, true);

        if (!is_array($data)) {
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
