<?php

namespace App\Console\Commands;

use App\SearchQuery;
use Illuminate\Console\Command;

class PruneSearchQueries extends Command
{
    protected $signature = 'search:prune';

    protected $description = 'Видаляє записи журналу пошуку, старші за SearchQuery::KEEP_DAYS днів';

    public function handle()
    {
        $n = SearchQuery::where('created_at', '<', now()->subDays(SearchQuery::KEEP_DAYS))->delete();
        $this->info("Видалено: {$n}");
    }
}
