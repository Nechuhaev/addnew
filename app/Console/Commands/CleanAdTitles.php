<?php

namespace App\Console\Commands;

use App\Services\AdTitleCleaner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * php artisan ads:clean-titles [--apply] [--limit=]
 *
 * Чистить назви приватних оголошень кодом (AdTitleCleaner) — замість AI-переписування.
 * Товари магазинів не чіпає (їхні назви щоразу оновлює фід). URL оголошення не змінюється.
 * Без --apply — лише показує приклади змін. З --apply — пише резервну копію
 * (id, стара назва, нова) у storage/app/ad-titles-ДАТА.csv.
 */
class CleanAdTitles extends Command
{
    protected $signature = 'ads:clean-titles {--apply} {--limit=0 : максимум змін за запуск (0 — без обмеження)}';

    protected $description = 'Чистка назв оголошень кодом: телефони, посилання, емодзі, ВЕЛИКІ ЛІТЕРИ';

    public function handle()
    {
        $apply = (bool) $this->option('apply');
        $limit = (int) $this->option('limit');
        $changes = [];
        $checked = 0;

        DB::table('ads')
            ->where('status', 1)
            ->where('is_product', 0)
            ->select('id', 'name')
            ->orderBy('id')
            ->chunk(2000, function ($rows) use (&$changes, &$checked, $limit) {
                foreach ($rows as $r) {
                    $checked++;
                    $new = AdTitleCleaner::clean((string) $r->name);
                    if ($new !== $r->name) {
                        $changes[$r->id] = [$r->name, $new];
                        if ($limit && count($changes) >= $limit) {
                            return false;
                        }
                    }
                }
            });

        $this->info("Перевірено: {$checked}; назв до зміни: " . count($changes));
        foreach (array_slice($changes, 0, 30, true) as $id => [$old, $new]) {
            $this->line("  #{$id}: {$old}");
            $this->line("        → {$new}");
        }

        if (!$apply || !$changes) {
            if (!$apply && $changes) {
                $this->info('Це перегляд — нічого не змінено. Щоб застосувати: ads:clean-titles --apply');
            }
            return 0;
        }

        $backup = storage_path('app/ad-titles-' . date('Y-m-d-His') . '.csv');
        $fh = fopen($backup, 'w');
        fwrite($fh, "\xEF\xBB\xBF");
        fputcsv($fh, ['id', 'Стара назва', 'Нова назва'], ';');
        foreach ($changes as $id => [$old, $new]) {
            fputcsv($fh, [$id, $old, $new], ';');
            DB::table('ads')->where('id', $id)->update(['name' => $new]);
        }
        fclose($fh);

        $this->info('Оновлено: ' . count($changes) . ". Резервна копія: {$backup}");

        return 0;
    }
}
