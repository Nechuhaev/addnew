<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Видаляє «осиротілі» рядки зведеної таблиці ad_tag: звʼязки, де оголошення
 * (ads) або тегу (ad_tags) вже не існує. Вони лишаються після масового
 * видалення товарів і магазинів (shops:delete-404, shops:delete-no-domain,
 * handleProductGone), бо видалення через query builder не чіпає pivot.
 *
 * За замовчуванням ТІЛЬКИ ЗВІТ. З --apply: спершу резервна копія видалюваних
 * пар у storage/app/orphan_ad_tag_backup_<час>.csv, потім видалення порціями.
 * Операція ідемпотентна: повторний запуск знаходить 0.
 */
class CleanupOrphanTagLinks extends Command
{
    protected $signature = 'adtags:cleanup-orphans {--apply : Реально видалити (без цього лише звіт)} {--batch=1000 : Розмір порції id}';

    protected $description = 'Видаляє осиротілі рядки ad_tag (оголошення чи тегу вже немає); за замовчуванням лише звіт';

    public function handle()
    {
        $apply = (bool) $this->option('apply');
        $batch = max(100, (int) $this->option('batch'));

        $total = DB::table('ad_tag')->count();
        $this->info("Усього звʼязків у ad_tag: {$total}");

        // Унікальні id оголошень і тегів, яких уже немає
        $adIds = DB::table('ad_tag as x')
            ->leftJoin('ads as a', 'a.id', '=', 'x.ad_id')
            ->whereNull('a.id')
            ->whereNotNull('x.ad_id')
            ->distinct()
            ->pluck('x.ad_id')
            ->all();

        $tagIds = DB::table('ad_tag as x')
            ->leftJoin('ad_tags as t', 't.id', '=', 'x.tag_id')
            ->whereNull('t.id')
            ->whereNotNull('x.tag_id')
            ->distinct()
            ->pluck('x.tag_id')
            ->all();

        $adRows = $this->countRows('ad_id', $adIds, $batch);
        $tagRows = $this->countRows('tag_id', $tagIds, $batch);

        $this->line("Видалених оголошень, на які ще є звʼязки: " . count($adIds) . " (рядків ad_tag: {$adRows})");
        $this->line("Неіснуючих тегів, на які ще є звʼязки:     " . count($tagIds) . " (рядків ad_tag: {$tagRows})");
        $this->line('Орієнтовно до видалення (з урахуванням перетину): до ' . ($adRows + $tagRows) . ' рядків, ≈'
            . ($total > 0 ? round(($adRows + $tagRows) / $total * 100, 1) : 0) . '% таблиці');

        if (!$apply) {
            $this->warn('Це лише звіт, нічого не видалено. Щоб видалити: php artisan adtags:cleanup-orphans --apply');

            return 0;
        }

        if (!$adIds && !$tagIds) {
            $this->info('Сиріт немає, видаляти нічого.');

            return 0;
        }

        // Резервна копія пар, які будуть видалені
        $file = storage_path('app/orphan_ad_tag_backup_' . date('Ymd_His') . '.csv');
        $fh = fopen($file, 'w');
        fputcsv($fh, ['ad_id', 'tag_id']);
        foreach (['ad_id' => $adIds, 'tag_id' => $tagIds] as $col => $ids) {
            foreach (array_chunk($ids, $batch) as $chunk) {
                foreach (DB::table('ad_tag')->whereIn($col, $chunk)->get(['ad_id', 'tag_id']) as $r) {
                    fputcsv($fh, [$r->ad_id, $r->tag_id]);
                }
            }
        }
        fclose($fh);
        $this->line("Резервна копія пар: {$file}");

        // Видалення порціями
        $deleted = 0;
        foreach (['ad_id' => $adIds, 'tag_id' => $tagIds] as $col => $ids) {
            foreach (array_chunk($ids, $batch) as $chunk) {
                $deleted += DB::table('ad_tag')->whereIn($col, $chunk)->delete();
            }
        }

        $left = DB::table('ad_tag')->count();
        $this->info("Видалено рядків: {$deleted}. Лишилось у ad_tag: {$left} (було {$total}).");

        return 0;
    }

    protected function countRows($column, array $ids, $batch)
    {
        $n = 0;
        foreach (array_chunk($ids, $batch) as $chunk) {
            $n += DB::table('ad_tag')->whereIn($column, $chunk)->count();
        }

        return $n;
    }
}
