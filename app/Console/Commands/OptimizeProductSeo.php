<?php

namespace App\Console\Commands;

use App\Ad;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class OptimizeProductSeo extends Command
{
    /**
     * php artisan products:seo-optimize
     *
     * Переписує назву й опис товарів (тільки тих, що продавець сам
     * імпортував/додав — is_product=1) через Claude API для кращого SEO,
     * НЕ вигадуючи нових характеристик — тільки покращуючи структуру,
     * читабельність і природне входження ключових слів на основі того,
     * що вже вказано (назва, бренд, наявний опис, категорія).
     */
    protected $signature = 'products:seo-optimize {--limit=}';

    protected $description = 'SEO-оптимізація назви й опису товарів через Claude API';

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

        $limit = (int) ($this->option('limit') ?: env('PRODUCT_SEO_PER_RUN', 20));

        $products = Ad::where('is_product', 1)
            ->where('seo_optimized', false)
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();

        if ($products->isEmpty()) {
            $this->info('Немає товарів, що потребують SEO-оптимізації.');
            return 0;
        }

        $this->info('Оптимізую ' . $products->count() . ' товар(ів)');

        foreach ($products as $i => $product) {
            $this->info('[' . ($i + 1) . '/' . $products->count() . "] ID={$product->id}: {$product->name}");

            try {
                $this->optimizeOne($product);
            } catch (\Throwable $e) {
                $this->error('Помилка: ' . $e->getMessage());
                // Товар лишається seo_optimized=false — спробуємо наступного разу.
            }
        }

        return 0;
    }

    protected function optimizeOne(Ad $product): void
    {
        $result = $this->generateSeoContent($product);

        $product->name = strip_tags($result['name']);
        $product->content = strip_tags($result['content']);
        $product->seo_optimized = true;
        $product->seo_optimized_at = now();
        $product->save();

        $this->info('  -> ' . $product->name);
    }

    protected function generateSeoContent(Ad $product): array
    {
        $siteTopic = env('SITE_TOPIC', 'дошка оголошень');
        $language = env('ARTICLE_LANGUAGE', 'українська');
        $category = optional($product->category)->name ?? '';

        $prompt = "Ти — SEO-копірайтер інтернет-магазину на сайті-дошці оголошень \"{$siteTopic}\". "
            . "Потрібно покращити картку товару для пошукової оптимізації.\n\n"
            . "ВАЖЛИВО — ЩО НЕ МОЖНА РОБИТИ:\n"
            . "- НЕ вигадуй характеристики, специфікації, розміри, матеріали чи властивості, "
            . "яких немає в наданих даних нижче. Якщо чогось не вказано — просто не згадуй це.\n"
            . "- НЕ змінюй бренд, модель чи артикул.\n"
            . "- НЕ додавай обіцянок про знижки, доставку, гарантії, яких немає в оригіналі.\n\n"
            . "ЩО МОЖНА РОБИТИ:\n"
            . "- Покращити структуру й читабельність тексту.\n"
            . "- Природно вплести релевантні пошукові ключові слова на основі назви/бренду/категорії.\n"
            . "- Виправити граматику, зробити текст більш продаваним, залишаючись правдивим.\n\n"
            . "Дані товару:\n"
            . "Поточна назва: \"{$product->name}\"\n"
            . "Поточний опис: \"{$product->content}\"\n"
            . "Бренд: \"{$product->brand}\"\n"
            . "Категорія: \"{$category}\"\n\n"
            . "Пиши мовою: {$language}.\n\n"
            . "Відповідь — ТІЛЬКИ валідний JSON, без вступних фраз і markdown-обрамлення:\n"
            . "{\n"
            . "  \"name\": \"оптимізована назва товару, до 150 символів\",\n"
            . "  \"content\": \"оптимізований опис товару, звичайний текст без HTML-тегів\"\n"
            . "}";

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