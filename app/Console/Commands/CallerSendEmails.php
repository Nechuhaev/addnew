<?php

namespace App\Console\Commands;

use App\Services\Caller\CallerSettings;
use App\Services\Caller\InstructionMailer;
use Illuminate\Console\Command;

class CallerSendEmails extends Command
{
    protected $signature = 'caller:send-emails';

    protected $description = 'AI-дзвінки: відправка листів з інструкцією з черги';

    public function handle(InstructionMailer $mailer)
    {
        $settings = CallerSettings::all();
        if (empty($settings['email_enabled'])) {
            $this->line('Розсилку вимкнено в налаштуваннях.');
            return 0;
        }
        $n = $mailer->sendPending($settings, null, function ($m) { $this->line($m); });
        $this->line("Надіслано: {$n}");
        return 0;
    }
}
