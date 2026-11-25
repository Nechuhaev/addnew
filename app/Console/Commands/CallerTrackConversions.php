<?php

namespace App\Console\Commands;

use App\Services\Caller\ConversionTracker;
use Illuminate\Console\Command;

class CallerTrackConversions extends Command
{
    protected $signature = 'caller:track-conversions';

    protected $description = 'AI-дзвінки: позначає ліди, які зареєструвалися на сайті після дзвінка';

    public function handle(ConversionTracker $tracker)
    {
        $this->line('Нових реєстрацій після дзвінка: ' . $tracker->run());
        return 0;
    }
}
