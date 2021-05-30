<?php

namespace App\Console\Commands;

use App\AdCountry;
use App\Http\Resources\Ad\Country;
use Illuminate\Console\Command;
use Spatie\Sitemap\SitemapIndex;

class CreateCountrySitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Создает сайтмап для страны';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Идентификаторы стран для которых позволена генерация сайтмапа
        $allowedCountryIds = [
            62 // Украина
        ];

        foreach ($allowedCountryIds as $allowedCountryId) {
            $country = AdCountry::find($allowedCountryId);

            $sitemap_index = SitemapIndex::create();
            if ($country) {
                // объявления
                // категории
                // области
                // города
                // теги
            }
//            $sitemap_users->writeToFile(public_path('users.xml'));
//            unset($sitemap_users);
//            $sitemap_index->add('/users.xml');
//            $this->line('users.xml создано');
        }
    }
}
