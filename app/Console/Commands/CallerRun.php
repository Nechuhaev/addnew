<?php

namespace App\Console\Commands;

use App\Services\Caller\CallerSettings;
use App\Services\Caller\CallProcessor;
use App\Services\Caller\Dialer;
use Illuminate\Console\Command;

/**
 * Щохвилини: аналізує прийняті розмови, потім ініціює нові дзвінки.
 * Аналіз першим — щоб результат (передзвін, «не дзвонити») врахувався в цьому ж проході.
 */
class CallerRun extends Command
{
    protected $signature = 'caller:run {--no-dial : лише аналіз розмов, без нових дзвінків}';

    protected $description = 'AI-дзвінки: аналіз розмов і автонабір (щохвилини)';

    public function handle(CallProcessor $processor, Dialer $dialer)
    {
        $settings = CallerSettings::all();

        foreach ($processor->processReceived($settings) as $line) {
            $this->line($line);
        }
        if (!$this->option('no-dial')) {
            foreach ($dialer->tick($settings) as $line) {
                $this->line($line);
            }
        }
        return 0;
    }
}
