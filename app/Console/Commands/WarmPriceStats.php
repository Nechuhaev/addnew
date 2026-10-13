<?php

namespace App\Console\Commands;

use App\Services\PriceStats;
use Illuminate\Console\Command;

/**
 * Перераховує статистику цін для сторінок /ceny (щодня, див. Kernel).
 */
class WarmPriceStats extends Command
{
    protected $signature = 'prices:warm';

    protected $description = 'Перерахувати статистику цін за розділами (сторінки /ceny)';

    public function handle()
    {
        $n = PriceStats::warm();
        $this->info("Сторінок цін: {$n}");

        return 0;
    }
}
