<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\CallsLlm;
use App\User;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class TranslateShopInfo extends Command
{
    use CallsLlm;

    /**
     * php artisan shops:translate-info
     *
     * Перекладає опис магазину (поле info на User) на українську.
     * Одразу з повним фолбеком: Claude → OpenRouter → Groq →
     * Cloudflare Workers AI → Gemini (див. Concerns\CallsLlm) —
     * на відміну від інших Translate*-команд, які поки що працюють
     * тільки через Claude напряму.
     */
    protected $signature = 'shops:translate-info {--limit=}';

    protected $description = 'Перекладає опис магазину (info) на українську, з фолбеком по кількох LLM-провайдерах';

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

        // "Магазин" — той самий критерій, що й в admin/shops: користувач
        // з хоча б одним товаром (is_product=1), незалежно від прапорця
        // is_shop_owner.
        $shops = User::whereHas('ads', function ($q) {
                $q->where('is_product', 1);
            })
            ->where(function ($q) {
                $q->whereNull('info_uk')->orWhere('info_uk', '');
            })
            ->whereNotNull('info')
            ->where('info', '!=', '')
            ->orderBy('id')
            ->limit($limit)
            ->get();

        if ($shops->isEmpty()) {
            $this->info('Усі магазини із заповненим описом вже перекладені.');
            return 0;
        }

        $this->info('Перекладаю опис для ' . $shops->count() . ' магазин(ів)');

        foreach ($shops as $i => $shop) {
            $this->info('[' . ($i + 1) . '/' . $shops->count() . "] ID={$shop->id}: {$shop->getOriginal('firstname')}");

            try {
                $translated = $this->translateOne($shop->getOriginal('info'));
                $shop->info_uk = $translated;
                $shop->save();
                $this->info('  -> OK');
            } catch (\Throwable $e) {
                $this->error('  Помилка: ' . $e->getMessage());
            }

            usleep(300000);
        }

        return 0;
    }

    protected function translateOne(string $info): string
    {
        $prompt = "Ти — професійний перекладач. Переклади опис магазину (дошка оголошень) "
            . "з російської на українську. Зберігай сенс і тон, адаптуй природно для "
            . "української мови (не дослівно слово-в-слово). Поверни ТІЛЬКИ переклад, "
            . "без пояснень і без лапок навколо тексту.\n\nОпис:\n{$info}";

        return trim($this->callLlm($prompt, 2000));
    }
}
