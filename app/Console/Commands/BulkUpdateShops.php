<?php

namespace App\Console\Commands;

use App\Ad;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * php artisan shops:bulk-update storage/app/shop-emails.csv [--apply]
 *
 * Масові дії з магазинами за списком (CSV, роздільник «;», рядки «ID;дія;email»):
 *   email  — внести email (знайдений на сайті магазину); позначається як підтверджений
 *            звіркою, тож пізніший shops:apply-email-check --clear-not-found його не зітре;
 *   delete — видалити магазин разом з усіма товарами.
 * Рядки, що починаються з «#», і заголовок пропускаються.
 * Без --apply — лише показує план. З --apply — пише резервну копію в storage/app/shops-bulk-ДАТА.csv.
 */
class BulkUpdateShops extends Command
{
    protected $signature = 'shops:bulk-update {file} {--apply}';

    protected $description = 'Масово внести email магазинам або видалити магазини за CSV-списком';

    public function handle()
    {
        $path = $this->argument('file');
        if (!is_file($path)) {
            $path = base_path($path);
        }
        if (!is_file($path) || !($fh = fopen($path, 'r'))) {
            $this->error('Файл не знайдено: ' . $this->argument('file'));
            return 1;
        }

        $plan = [];
        $planned = []; // email => ID, щоб один email не дістався двом магазинам
        while (($row = fgetcsv($fh, 0, ';')) !== false) {
            $id = trim((string) ($row[0] ?? ''));
            if ($id === '' || $id[0] === '#' || !ctype_digit($id)) {
                continue;
            }
            $action = mb_strtolower(trim((string) ($row[1] ?? '')));
            $email = mb_strtolower(trim((string) ($row[2] ?? '')));
            $shop = User::find((int) $id);
            if (!$shop) {
                $this->warn("ID={$id}: магазину немає — пропущено");
                continue;
            }
            if ($action === 'email') {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $this->warn("ID={$id}: некоректний email «{$email}» — пропущено");
                    continue;
                }
                if (User::where('email', $email)->where('id', '!=', $shop->id)->exists()) {
                    $this->warn("ID={$id}: {$email} уже належить іншому акаунту — пропущено");
                    continue;
                }
                if (isset($planned[$email]) && $planned[$email] !== $shop->id) {
                    $this->warn("ID={$id}: {$email} у списку вже призначено ID={$planned[$email]} — пропущено");
                    continue;
                }
                $planned[$email] = $shop->id;
            } elseif ($action !== 'delete') {
                $this->warn("ID={$id}: невідома дія «{$action}» — пропущено (email або delete)");
                continue;
            }
            $plan[] = ['shop' => $shop, 'action' => $action, 'email' => $email, 'products' => Ad::where('user_id', $shop->id)->count()];
        }
        fclose($fh);

        if (!$plan) {
            $this->info('Немає що робити.');
            return 0;
        }

        foreach ($plan as $p) {
            $s = $p['shop'];
            $what = $p['action'] === 'delete'
                ? "ВИДАЛИТИ магазин і {$p['products']} товарів"
                : 'email: ' . ($s->email ?: '—') . ' → ' . $p['email'];
            $this->line("ID={$s->id} {$s->username} ({$s->site_url}) | {$what}");
        }
        $counts = array_count_values(array_column($plan, 'action'));
        $this->info('Внести email: ' . ($counts['email'] ?? 0) . '; видалити магазинів: ' . ($counts['delete'] ?? 0));

        if (!$this->option('apply')) {
            $this->info('Це перегляд — нічого не змінено. Щоб виконати, додайте --apply.');
            return 0;
        }

        $backup = storage_path('app/shops-bulk-' . date('Y-m-d-His') . '.csv');
        $bh = fopen($backup, 'w');
        fwrite($bh, "\xEF\xBB\xBF");
        fputcsv($bh, ['ID', 'Магазин', 'Email', 'Сайт', 'Телефон', 'Товарів', 'Дія', 'Новий email'], ';');
        foreach ($plan as $p) {
            $s = $p['shop'];
            fputcsv($bh, [$s->id, $s->username, $s->email, $s->site_url, $s->telephone, $p['products'], $p['action'], $p['email']], ';');
        }
        fclose($bh);
        $this->info("Резервна копія: {$backup}");

        $done = ['email' => 0, 'delete' => 0];
        foreach ($plan as $p) {
            $s = $p['shop'];
            try {
                if ($p['action'] === 'email') {
                    $old = $s->email;
                    $s->email = $p['email'];
                    $s->save();
                    DB::table('shop_email_checks')->insert([
                        'user_id' => $s->id, 'old_email' => $old, 'found_email' => $p['email'],
                        'matched' => true, 'status' => 'ok', 'checked_at' => now(),
                    ]);
                } else {
                    DB::transaction(function () use ($s) {
                        Ad::where('user_id', $s->id)->delete();
                        $s->delete();
                    });
                }
                $done[$p['action']]++;
            } catch (\Throwable $e) {
                $this->error("ID={$s->id}: помилка — {$e->getMessage()}");
            }
        }

        $this->info("Готово: внесено email — {$done['email']}, видалено магазинів — {$done['delete']}");

        return 0;
    }
}
