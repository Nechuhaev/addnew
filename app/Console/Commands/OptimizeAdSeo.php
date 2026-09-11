<?php

namespace App\Console\Commands;

use App\Ad;
use App\Console\Commands\Concerns\UsesPromptTemplates;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class OptimizeAdSeo extends Command
{
    use UsesPromptTemplates;

    /**
     * php artisan ads:seo-optimize
     *
     * Переписує назву й опис ЗВИЧАЙНИХ оголошень (is_product=0) через
     * Claude API для кращого SEO — той самий принцип, що й
     * products:seo-optimize, тільки для оголошень, не товарів магазинів.
     * НЕ вигадує нових фактів — тільки покращує структуру й читабельність
     * на основі того, що користувач сам написав.
     */
    protected $signature = 'ads:seo-optimize {--limit=}';

    protected $description = 'SEO-оптимізація назви й опису звичайних оголошень через Claude API';

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

        $limit = (int) ($this->option('limit') ?: env('AD_SEO_PER_RUN', 20));

        $ads = Ad::where('is_product', 0)
            ->where('seo_optimized', false)
            ->where('status', 1) // тільки активні — немає сенсу оптимізувати призупинені/архівні
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();

        if ($ads->isEmpty()) {
            $this->info('Немає активних оголошень, що потребують SEO-оптимізації.');
            return 0;
        }

        $this->info('Оптимізую ' . $ads->count() . ' оголошень(ня)');

        foreach ($ads as $i => $ad) {
            $this->info('[' . ($i + 1) . '/' . $ads->count() . "] ID={$ad->id}: {$ad->name}");

            try {
                $this->optimizeOne($ad);
            } catch (\Throwable $e) {
                $this->error('Помилка: ' . $e->getMessage());
                // Оголошення лишається seo_optimized=false — спробуємо наступного разу.
            }
        }

        return 0;
    }

    protected function optimizeOne(Ad $ad): void
    {
        $result = $this->generateSeoContent($ad);

        $ad->name = strip_tags($result['name']);
        $ad->content = strip_tags($result['content']);
        $ad->seo_optimized = true;
        $ad->seo_optimized_at = now();
        $ad->save();

        $this->info('  -> ' . $ad->name);
    }

    protected function generateSeoContent(Ad $ad): array
    {
        $siteTopic = env('SITE_TOPIC', 'дошка оголошень');
        $language = env('ARTICLE_LANGUAGE', 'українська');
        $category = optional($ad->category)->name ?? '';
        $city = optional($ad->city)->name ?? '';

        $prompt = $this->prompt(
            'ad_seo_optimize',
            'SEO звичайних оголошень — оптимізація назви й опису',
            "Ти — SEO-копірайтер сайту-дошки оголошень \"{{site_topic}}\". "
                . "Потрібно покращити текст оголошення для пошукової оптимізації.\n\n"
                . "ВАЖЛИВО — ЩО НЕ МОЖНА РОБИТИ:\n"
                . "- НЕ вигадуй характеристики, деталі, стан чи властивості, "
                . "яких немає в наданому тексті нижче. Якщо чогось не вказано — просто не згадуй це.\n"
                . "- НЕ змінюй ціну, категорію чи місто.\n"
                . "- НЕ додавай обіцянок, знижок чи умов, яких немає в оригіналі.\n\n"
                . "ЩО МОЖНА РОБИТИ:\n"
                . "- Покращити структуру й читабельність тексту.\n"
                . "- Природно вплести релевантні пошукові ключові слова на основі назви/категорії/міста.\n"
                . "- Виправити граматику, зробити текст зрозумілішим, залишаючись правдивим.\n\n"
                . "Дані оголошення:\n"
                . "Поточна назва: \"{{ad_name}}\"\n"
                . "Поточний опис: \"{{ad_content}}\"\n"
                . "Категорія: \"{{category}}\"\n"
                . "Місто: \"{{city}}\"\n\n"
                . "Пиши мовою: {{language}}.\n\n"
                . "Відповідь — ТІЛЬКИ валідний JSON, без вступних фраз і markdown-обрамлення:\n"
                . "{\n"
                . "  \"name\": \"оптимізована назва оголошення, до 150 символів\",\n"
                . "  \"content\": \"оптимізований опис оголошення, звичайний текст без HTML-тегів\"\n"
                . "}",
            [
                'site_topic' => $siteTopic,
                'ad_name' => $ad->name,
                'ad_content' => $ad->content,
                'category' => $category,
                'city' => $city,
                'language' => $language,
            ],
            'SEO-переписування назви й опису звичайних оголошень (is_product=0), без вигадування деталей.'
        );

        $raw = $this->callClaude($prompt, 1500);
        $json = $this->extractJsonObject($raw);
        $data = json_decode($json, true);

        if (!is_array($data) || empty($data['name']) || empty($data['content'])) {
            throw new \RuntimeException('Не вдалося розпарсити JSON відповіді моделі');
        }

        return $data;
    }

    protected function callClaude(string $prompt, int $maxTokens = 1500): string
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
            'timeout' => 60,
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
