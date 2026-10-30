<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * php artisan thumbs:clean [--days=90] [--dry-run]
 *
 * Видаляє зменшені копії фото (public/thumbs), створені понад N днів тому:
 * копії фото видалених/оновлених товарів інакше лишались би назавжди.
 * Потрібні копії створяться знову при першому показі.
 */
class CleanThumbs extends Command
{
    protected $signature = 'thumbs:clean {--days=90} {--dry-run}';

    protected $description = 'Видалення старих зменшених копій фото (public/thumbs)';

    public function handle()
    {
        $dir = public_path('thumbs');
        if (!is_dir($dir)) {
            $this->info('Каталогу public/thumbs немає — нічого чистити.');
            return 0;
        }

        $before = time() - max(1, (int) $this->option('days')) * 86400;
        $dry = (bool) $this->option('dry-run');
        $count = $bytes = $total = 0;

        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS));
        foreach ($it as $file) {
            if (!$file->isFile()) {
                continue;
            }
            $total++;
            if ($file->getMTime() < $before) {
                $count++;
                $bytes += $file->getSize();
                if (!$dry) {
                    @unlink($file->getPathname());
                }
            }
        }

        $this->info(sprintf('%s: %d з %d файлів, %.1f МБ%s', $dry ? 'До видалення' : 'Видалено', $count, $total, $bytes / 1048576, $dry ? ' (перегляд)' : ''));

        return 0;
    }
}
