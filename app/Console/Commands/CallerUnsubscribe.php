<?php

namespace App\Console\Commands;

use App\Services\Caller\InstructionMailer;
use Illuminate\Console\Command;

class CallerUnsubscribe extends Command
{
    protected $signature = 'caller:unsubscribe {key : email або телефон}';

    protected $description = 'AI-дзвінки: відписати магазин (більше ні листів, ні дзвінків)';

    public function handle(InstructionMailer $mailer)
    {
        $n = $mailer->unsubscribe($this->argument('key'));
        $n ? $this->info("Відписано лідів: {$n}") : $this->warn('Не знайдено');
        return 0;
    }
}
