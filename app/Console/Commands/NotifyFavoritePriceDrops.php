<?php

namespace App\Console\Commands;

use App\Favorite;
use App\Mail\FavoritePriceDrop;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotifyFavoritePriceDrops extends Command
{
    /**
     * php artisan favorites:notify-price-drops [--dry-run] [--user=ID]
     *
     * Раз на день порівнює поточну ціну (у грн) товарів з обраного з базовою
     * ціною (base_price_uah) і надсилає КОЖНОМУ користувачу ОДИН лист з усіма
     * товарами, що подешевшали на Favorite::DROP_THRESHOLD і більше.
     * Після листа база = нова ціна; якщо ціна зросла — база піднімається,
     * щоб наступне зниження теж помітили. Так враховуються всі джерела зміни
     * ціни (моніторинг, імпорт, ручне редагування) без хуків у кожному з них.
     */
    protected $signature = 'favorites:notify-price-drops {--dry-run} {--user=}';

    protected $description = 'Листи користувачам про зниження ціни на товари з обраного';

    public function handle()
    {
        $users = User::where('price_drop_emails', 1)
            ->whereIn('id', Favorite::where('notify', 1)->select('user_id'))
            ->when($this->option('user'), function ($q, $id) {
                $q->where('id', (int) $id);
            });

        $sent = 0;
        $items = 0;

        $users->chunkById(200, function ($chunk) use (&$sent, &$items) {
            foreach ($chunk as $user) {
                $result = $this->processUser($user);
                if ($result > 0) {
                    $sent++;
                    $items += $result;
                }
            }
        });

        $this->info(($this->option('dry-run') ? '[dry-run] ' : '') . "Листів: {$sent}, товарів у них: {$items}");
    }

    protected function processUser(User $user): int
    {
        $favorites = Favorite::with(['ad.currency', 'ad.user'])
            ->where('user_id', $user->id)
            ->where('notify', 1)
            ->get();

        $drops = [];
        foreach ($favorites as $favorite) {
            $ad = $favorite->ad;
            if (!$ad || $ad->getOriginal('status') != 1) {
                continue;
            }
            $current = Favorite::priceUah($ad);
            if ($current <= 0) {
                continue;
            }

            $percent = $favorite->dropPercent($current);
            if ($percent !== null) {
                $drops[] = [
                    'favorite' => $favorite,
                    'name' => $ad->name,
                    'url' => route('ad.page', ['slug' => $ad->slug]),
                    'image' => $ad->image,
                    'seller' => optional($ad->user)->username,
                    'old' => (float) $favorite->base_price_uah,
                    'new' => $current,
                    'percent' => $percent,
                ];
            } elseif ($current > (float) $favorite->base_price_uah && !$this->option('dry-run')) {
                $favorite->base_price_uah = $current;
                $favorite->save();
            }
        }

        if (!$drops || empty($user->email)) {
            return 0;
        }

        usort($drops, function ($a, $b) {
            return $b['percent'] <=> $a['percent'];
        });

        if ($this->option('dry-run')) {
            $this->line("user #{$user->id} {$user->email}: " . count($drops) . ' товар(ів)');
            foreach ($drops as $d) {
                $this->line("  -{$d['percent']}%  {$d['old']} → {$d['new']}  {$d['name']}");
            }
            return count($drops);
        }

        try {
            Mail::to($user->email)->send(new FavoritePriceDrop($drops, Favorite::unsubscribeUrl($user->id)));
        } catch (\Throwable $e) {
            Log::warning('favorites:notify-price-drops: не вдалося надіслати лист', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return 0;
        }

        foreach ($drops as $d) {
            $d['favorite']->base_price_uah = $d['new'];
            $d['favorite']->notified_at = now();
            $d['favorite']->save();
        }

        return count($drops);
    }
}
