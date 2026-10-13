<?php

namespace App\Console\Commands;

use App\Ad;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * php artisan shops:apply-email-check --clear-not-found --delete-unreachable [--update-mismatch] [--apply]
 *
 * Застосовує результати ОСТАННЬОЇ звірки shops:check-email для кожного магазину:
 *  --clear-not-found     email на сайті не знайдено → email магазину очищується (адмін додасть вручну);
 *  --delete-unreachable  сайт недоступний → магазин видаляється разом з усіма товарами;
 *  --update-mismatch     на сайті інший email → email замінюється адресою з сайту.
 * Без --apply — лише показує, що буде зроблено. З --apply перед змінами пише резервну копію
 * (id, назва, email, сайт, кількість товарів) у storage/app/shop-email-apply-ДАТА.csv.
 */
class ApplyShopEmailCheck extends Command
{
    protected $signature = 'shops:apply-email-check {--clear-not-found} {--delete-unreachable} {--update-mismatch} {--apply}';

    protected $description = 'Застосовує результати звірки email магазинів (очистити / видалити / замінити)';

    public function handle()
    {
        $actions = [
            'not_found' => $this->option('clear-not-found') ? 'clear' : null,
            'domain_unreachable' => $this->option('delete-unreachable') ? 'delete' : null,
            'mismatch' => $this->option('update-mismatch') ? 'update' : null,
        ];
        $actions = array_filter($actions);
        if (!$actions) {
            $this->error('Вкажіть хоча б одну дію: --clear-not-found, --delete-unreachable, --update-mismatch');
            return 1;
        }

        // Остання перевірка кожного магазину
        $latest = DB::table('shop_email_checks as t1')
            ->whereRaw('t1.id = (SELECT MAX(t2.id) FROM shop_email_checks t2 WHERE t2.user_id = t1.user_id)')
            ->whereIn('t1.status', array_keys($actions))
            ->get()
            ->keyBy('user_id');

        if ($latest->isEmpty()) {
            $this->info('Немає магазинів із такими результатами останньої перевірки.');
            return 0;
        }

        $shops = User::whereIn('id', $latest->keys())->orderBy('id')->get();
        $apply = (bool) $this->option('apply');

        $plan = [];
        foreach ($shops as $shop) {
            $check = $latest[$shop->id];
            $action = $actions[$check->status];
            $products = Ad::where('user_id', $shop->id)->count();
            $plan[] = compact('shop', 'check', 'action', 'products');
        }

        if (!$plan) {
            $this->info('Магазинів із такими результатами не знайдено.');
            return 0;
        }

        $labels = ['clear' => 'очистити email', 'delete' => 'ВИДАЛИТИ магазин і товари', 'update' => 'замінити email'];
        foreach ($plan as $p) {
            $extra = $p['action'] === 'update' ? " → {$p['check']->found_email}" : '';
            $extra .= $p['action'] === 'delete' ? " ({$p['products']} товарів)" : '';
            $this->line(sprintf('ID=%d %s | %s | %s | %s%s', $p['shop']->id, $p['shop']->username, $p['shop']->email ?: '—', $p['shop']->site_url, $labels[$p['action']], $extra));
        }

        $counts = array_count_values(array_column($plan, 'action'));
        $this->info('');
        foreach ($counts as $action => $n) {
            $this->info("  {$labels[$action]}: {$n}");
        }

        if (!$apply) {
            $this->info('');
            $this->info('Це лише перегляд — нічого не змінено. Щоб виконати, додайте --apply.');
            return 0;
        }

        // Резервна копія перед змінами
        $backup = storage_path('app/shop-email-apply-' . date('Y-m-d-His') . '.csv');
        $fh = fopen($backup, 'w');
        fwrite($fh, "\xEF\xBB\xBF");
        fputcsv($fh, ['ID', 'Магазин', 'Email', 'Сайт', 'Телефон', 'Товарів', 'Дія', 'Новий email'], ';');
        foreach ($plan as $p) {
            fputcsv($fh, [$p['shop']->id, $p['shop']->username, $p['shop']->email, $p['shop']->site_url, $p['shop']->telephone, $p['products'], $labels[$p['action']], $p['action'] === 'update' ? $p['check']->found_email : ''], ';');
        }
        fclose($fh);
        $this->info("Резервна копія: {$backup}");

        $done = ['clear' => 0, 'delete' => 0, 'update' => 0, 'skipped' => 0];
        foreach ($plan as $p) {
            $shop = $p['shop'];
            if ($p['action'] === 'clear') {
                $shop->email = null;
                $shop->save();
                $done['clear']++;
            } elseif ($p['action'] === 'delete') {
                try {
                    DB::transaction(function () use ($shop) {
                        Ad::where('user_id', $shop->id)->delete();
                        $shop->delete();
                    });
                    $done['delete']++;
                } catch (\Throwable $e) {
                    $this->error("ID={$shop->id}: не вдалося видалити — {$e->getMessage()}");
                    $done['skipped']++;
                }
            } elseif ($p['action'] === 'update') {
                $new = strtolower(trim((string) $p['check']->found_email));
                if ($new === '' || User::where('email', $new)->where('id', '!=', $shop->id)->exists()) {
                    $this->warn("ID={$shop->id}: {$new} уже належить іншому акаунту — пропущено");
                    $done['skipped']++;
                    continue;
                }
                $old = $shop->email;
                $shop->email = $new;
                $shop->save();
                DB::table('shop_email_checks')->insert([
                    'user_id' => $shop->id, 'old_email' => $old, 'found_email' => $new,
                    'matched' => false, 'status' => 'updated', 'checked_at' => now(),
                ]);
                $done['update']++;
            }
        }

        $this->info("Готово: очищено email — {$done['clear']}, видалено магазинів — {$done['delete']}, замінено email — {$done['update']}" . ($done['skipped'] ? ", пропущено — {$done['skipped']}" : ''));

        return 0;
    }
}
