<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * php artisan ads:clean-temp-images [--days=7] [--dry-run]
 *
 * Фото нового оголошення спершу кладуться локально в storage/app/public/ads/{час}-{код}/
 * (крок «попередній перегляд»), а при публікації копіюються в S3 — локальна копія більше
 * не потрібна. Видаляє такі папки, старші за N днів. Папку, на яку оголошення посилається
 * локальним шляхом (/storage/ads/...), не чіпає.
 */
class CleanTempAdImages extends Command
{
    protected $signature = 'ads:clean-temp-images {--days=7} {--dry-run}';

    protected $description = 'Видалення тимчасових локальних фото оголошень (storage/app/public/ads)';

    public function handle()
    {
        $disk = Storage::disk('local');
        $before = time() - max(1, (int) $this->option('days')) * 86400;
        $dry = (bool) $this->option('dry-run');

        $used = [];
        DB::table('ads')->where('image', 'like', '%/storage/ads/%')->orWhere('images', 'like', '%storage%ads%')
            ->select('image', 'images')->orderBy('id')->each(function ($r) use (&$used) {
                if (preg_match_all('~storage\\\\?/ads\\\\?/([^/\\\\"]+)~', $r->image . ' ' . $r->images, $m)) {
                    foreach ($m[1] as $dir) {
                        $used[$dir] = true;
                    }
                }
            });

        $deleted = $kept = $bytes = 0;
        foreach ($disk->directories('public/ads') as $dir) {
            $name = basename($dir);
            $created = (int) $name ?: (int) @filemtime($disk->path($dir));
            if ($created >= $before || isset($used[$name])) {
                $kept++;
                continue;
            }
            foreach ($disk->allFiles($dir) as $file) {
                $bytes += (int) $disk->size($file);
            }
            if (!$dry) {
                $disk->deleteDirectory($dir);
            }
            $deleted++;
        }

        $this->info(($dry ? '[dry-run] ' : '') . "Видалено папок: {$deleted} ("
            . round($bytes / 1048576) . " МБ), залишено: {$kept}, з посиланням з оголошень: " . count($used));

        return 0;
    }
}
