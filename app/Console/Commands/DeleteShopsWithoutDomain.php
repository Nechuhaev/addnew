<?php

namespace App\Console\Commands;

use App\Ad;
use App\User;
use Illuminate\Console\Command;

class DeleteShopsWithoutDomain extends Command
{
    /**
     * php artisan shops:delete-no-domain
     *
     * ОДНОРАЗОВА команда: видаляє магазини, у яких поле site_url
     * ВЗАГАЛІ порожнє/не заповнене (незалежно від причини — ніколи не
     * заповнювали, чи очистили раніше через shops:check-domains --apply
     * як недоступний домен).
     *
     * Без --apply — тільки показує список (і кількість товарів, які
     * будуть втрачені), нічого не змінюючи. З --apply — ВИДАЛЯЄ знайдені
     * магазини РАЗОМ З УСІМА ЇХНІМИ ТОВАРАМИ. Незворотна дія.
     */
    protected $signature = 'shops:delete-no-domain {--apply} {--limit=}';

    protected $description = 'Знаходить (і, з --apply, видаляє) магазини без заповненого site_url';

    public function handle()
    {
        $query = User::whereHas('ads', function ($q) {
                $q->where('is_product', 1);
            })
            ->where(function ($q) {
                $q->whereNull('site_url')->orWhere('site_url', '');
            })
            ->orderBy('id');

        if ($limit = $this->option('limit')) {
            $query->limit((int) $limit);
        }

        $shops = $query->get();

        if ($shops->isEmpty()) {
            $this->info('Немає магазинів без заповненого site_url.');
            return 0;
        }

        $apply = $this->option('apply');

        $totalProducts = 0;
        foreach ($shops as $shop) {
            $totalProducts += Ad::where('user_id', $shop->id)->where('is_product', 1)->count();
        }

        $this->info("Знайдено {$shops->count()} магазин(ів) без домену (разом товарів: {$totalProducts})" . ($apply ? ' — ВИДАЛЯЮ' : ' (лише перегляд, без --apply)'));

        foreach ($shops as $shop) {
            $productsCount = Ad::where('user_id', $shop->id)->where('is_product', 1)->count();
            $this->line("  ID={$shop->id}: {$shop->username} ({$productsCount} товар(ів))");
        }

        if (!$apply) {
            $this->info('');
            $this->info('Це був лише перегляд — жодних змін не внесено. Щоб реально видалити перелічені вище магазини РАЗОМ З УСІМА ЇХНІМИ ТОВАРАМИ, запустіть:');
            $this->info('  php artisan shops:delete-no-domain --apply');
            return 0;
        }

        $this->info('');
        foreach ($shops as $shop) {
            Ad::where('user_id', $shop->id)->delete();
            $shop->delete();
            $this->info("Видалено ID={$shop->id}: {$shop->username}");
        }
        $this->info('Готово.');

        return 0;
    }
}
