<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * php artisan logs:trim [--max=5] [--keep=1] [--dry-run]
 *
 * Логи cron-задач (appendOutputTo → storage/logs/*.log) ростуть без обмежень.
 * Файл, більший за --max МБ, обрізається до останніх --keep МБ (з цілого рядка).
 * laravel-*.log не чіпає: їх ротує сам Laravel (14 днів).
 */
class TrimLogs extends Command
{
    protected $signature = 'logs:trim {--max=5} {--keep=1} {--dry-run}';

    protected $description = 'Обрізання великих логів cron-задач у storage/logs';

    public function handle()
    {
        $max = max(1, (float) $this->option('max')) * 1048576;
        $keep = (int) (max(0.1, (float) $this->option('keep')) * 1048576);
        $dry = (bool) $this->option('dry-run');
        $freed = 0;

        foreach (glob(storage_path('logs/*.log')) ?: [] as $file) {
            if (strpos(basename($file), 'laravel') === 0 || filesize($file) <= $max) {
                continue;
            }
            $size = filesize($file);
            if (!$dry) {
                $fh = fopen($file, 'r');
                fseek($fh, -$keep, SEEK_END);
                $tail = (string) stream_get_contents($fh);
                fclose($fh);
                $nl = strpos($tail, "\n");
                $tail = $nl === false ? $tail : substr($tail, $nl + 1);
                file_put_contents($file, '[' . date('Y-m-d H:i:s') . "] logs:trim — старіші записи видалено\n" . $tail, LOCK_EX);
                clearstatcache(true, $file);
            }
            $after = $dry ? min($size, $keep) : filesize($file);
            $freed += $size - $after;
            $this->line(basename($file) . ': ' . round($size / 1048576, 1) . ' → ' . round($after / 1048576, 1) . ' МБ');
        }

        $this->info(($dry ? '[dry-run] ' : '') . 'Звільнено ' . round($freed / 1048576, 1) . ' МБ');

        return 0;
    }
}
