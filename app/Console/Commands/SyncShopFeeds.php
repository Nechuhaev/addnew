<?php

namespace App\Console\Commands;

use App\ShopFeed;
use App\Services\Import\ShopFeedSync;
use Illuminate\Console\Command;

class SyncShopFeeds extends Command
{
    /**
     * php artisan feeds:sync [--feed=ID] [--force]
     * Оновлює фіди, яким настав час (next_run_at), по одному.
     */
    protected $signature = 'feeds:sync {--feed=} {--force} {--limit=10}';

    protected $description = 'Автооновлення товарів магазинів з фідів за URL';

    public function handle(ShopFeedSync $sync)
    {
        $query = ShopFeed::with('user')->where('enabled', 1);
        if ($this->option('feed')) {
            $query->where('id', (int) $this->option('feed'));
        } elseif (!$this->option('force')) {
            $query->where(function ($q) {
                $q->whereNull('next_run_at')->orWhere('next_run_at', '<=', now());
            });
        }

        $feeds = $query->orderBy('next_run_at')->limit((int) $this->option('limit') ?: 10)->get();
        foreach ($feeds as $feed) {
            if ($feed->isRunning()) {
                $this->line("feed #{$feed->id}: вже виконується, пропускаю");
                continue;
            }
            $started = microtime(true);
            $import = $sync->run($feed);
            $this->line(sprintf(
                'feed #%d user #%d: %s — всього %d, нових %d, оновлено %d, помилок %d, зникло %d (%.1f c)%s',
                $feed->id, $feed->user_id, $import->status, $import->total, $import->new_count,
                $import->update_count, $import->error_count, $import->missing_count,
                microtime(true) - $started, $import->error_message ? ' — ' . $import->error_message : ''
            ));
        }
        if ($feeds->isEmpty()) {
            $this->line('Немає фідів до оновлення.');
        }
    }
}
