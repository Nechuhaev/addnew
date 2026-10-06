<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateBlogSitemap extends Command
{
    /**
     * php artisan content:sitemap
     *
     * Раніше генерував public/blog.xml власним кодом (без hreflang-альтернатив
     * і без російських версій) і щоранку та після кожної публікації
     * перезаписував blog.xml, який будує sitemap:rebuild. Тепер просто
     * викликає sitemap:rebuild, тож усі файли sitemap узгоджені
     * (~12 секунд, запис атомарний).
     */
    protected $signature = 'content:sitemap';

    protected $description = 'Оновлює sitemap (включно з blog.xml) через sitemap:rebuild';

    public function handle()
    {
        return $this->call('sitemap:rebuild');
    }
}
