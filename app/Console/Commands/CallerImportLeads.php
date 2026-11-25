<?php

namespace App\Console\Commands;

use App\Services\Caller\LeadImporter;
use Illuminate\Console\Command;

class CallerImportLeads extends Command
{
    protected $signature = 'caller:import {file : шлях до CSV (phone, store_name, store_site, contact_name, category)}';

    protected $description = 'AI-дзвінки: імпорт лідів із CSV';

    public function handle(LeadImporter $importer)
    {
        $file = $this->argument('file');
        if (!is_readable($file)) {
            $this->error("Файл не знайдено: {$file}");
            return 1;
        }
        $s = $importer->importFile($file, 'cli');
        foreach ($s['errors'] as $e) {
            $this->warn($e);
        }
        $this->info("Додано: {$s['inserted']}, уже були: {$s['exists']}, «не дзвонити»: {$s['dnc']}, "
            . "уже зареєстровані магазини: {$s['registered']}, невалідні: {$s['invalid']}");
        return 0;
    }
}
