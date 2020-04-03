<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\SitemapIndex;

class GenerateFilterSitemap extends Command
{
    const SITEMAP_MAX_COUNT = 5000;

    private $sitemapIndex = null;

    private $categories = null;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:filter';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Создать карту сайта для фильтров';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->sitemapIndex = SitemapIndex::create();

        $this->categories = DB::select("SELECT cat.slug as category_slug,
                        cat2.slug as parent_category_slug
                      FROM ad_categories cat
                        LEFT JOIN ad_categories cat2
                        ON (cat2.id = cat.parent_id)");
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->line('Начинаем создавать карту сайта');

        ini_set('memory_limit', '-1');


        $this->createCountriesSitemap();
        $this->createRegionsSitemap();
        $this->createCitiesSitemap();


        $this->sitemapIndex->writeToFile(public_path('sitemap-filter.xml'));

        $this->line('Карта создана');

    }


    public function createCitiesSitemap() {
        $this->line('Создание сайтмапа для городов');


        $locations = DB::select("SELECT ad_cities.slug as location_slug FROM ad_cities");
        // Текущее количество объеявлений в сайтмапе
        $i = 0;

        // Текущий файл
        $file_index = 1;
        $filename = "filtered-city-$file_index.xml";

        $sitemap = Sitemap::create();
        foreach ($locations as $location) {

            foreach ($this->categories as $category) {
                // Контролируем файл, в который добавляется сайтмап
                if ($i == self::SITEMAP_MAX_COUNT) {

                    $this->line("$filename - сохранение...");

                    // Сохраним сайтмап, если его не сохранили раньше
                    if (!file_exists(public_path($filename))) {
                        // Сохраняем сайтмап
                        $sitemap->writeToFile(public_path($filename));

                        unset($sitemap);

                        $sitemap = Sitemap::create();
                    }

                    // Добавим сайтмап в карту сайтмапов
                    $this->sitemapIndex->add('/' . $filename);

                    $i = 0;
                    $file_index++;
                }

                if ($i == 0) {
                    $filename = "filtered-city-$file_index.xml";
                }

                if (!file_exists(public_path($filename))) {
                    if ($category->parent_category_slug) {
                        $url = route('filtered_subcategory.page', [
                            'filter' => $location->location_slug,
                            'category' => $category->parent_category_slug,
                            'subcategory' => $category->category_slug
                        ]);
                    } else {
                        $url = route('filtered_category.page', [
                            'filter' => $location->location_slug,
                            'category' => $category->category_slug
                        ]);
                    }

                    $sitemap->add($url);
                }

                $i++;
            }

        }

        $this->line('Сайтмап для городов создан');

    }

    public function createRegionsSitemap() {
        $this->line('Создание сайтмапа для областей');

        $locations = DB::select("SELECT ad_regions.slug as location_slug FROM ad_regions");
        // Текущее количество объеявлений в сайтмапе
        $i = 0;

        // Текущий файл
        $file_index = 1;
        $filename = "filtered-region-$file_index.xml";

        $sitemap = Sitemap::create();
        foreach ($locations as $location) {

            foreach ($this->categories as $category) {
                // Контролируем файл, в который добавляется сайтмап
                if ($i == self::SITEMAP_MAX_COUNT) {

                    $this->line("$filename - сохранение...");

                    // Сохраним сайтмап, если его не сохранили раньше
                    if (!file_exists(public_path($filename))) {
                        // Сохраняем сайтмап
                        $sitemap->writeToFile(public_path($filename));

                        unset($sitemap);

                        $sitemap = Sitemap::create();
                    }

                    // Добавим сайтмап в карту сайтмапов
                    $this->sitemapIndex->add('/' . $filename);

                    $i = 0;
                    $file_index++;
                }

                if ($i == 0) {
                    $filename = "filtered-region-$file_index.xml";
                }

                if (!file_exists(public_path($filename))) {
                    if ($category->parent_category_slug) {
                        $url = route('filtered_subcategory.page', [
                            'filter' => $location->location_slug,
                            'category' => $category->parent_category_slug,
                            'subcategory' => $category->category_slug
                        ]);
                    } else {
                        $url = route('filtered_category.page', [
                            'filter' => $location->location_slug,
                            'category' => $category->category_slug
                        ]);
                    }

                    $sitemap->add($url);
                }

                $i++;
            }

        }

        $this->line('Сайтмап для областей создан');

    }

    public function createCountriesSitemap() {
        $this->line('Создание сайтмапа для стран');

        $locations = DB::select("SELECT ad_countries.slug as location_slug FROM ad_countries");
        // Текущее количество объеявлений в сайтмапе
        $i = 0;

        // Текущий файл
        $file_index = 1;
        $filename = "filtered-country-$file_index.xml";

        $sitemap = Sitemap::create();
        foreach ($locations as $location) {

            foreach ($this->categories as $category) {
                // Контролируем файл, в который добавляется сайтмап
                if ($i == self::SITEMAP_MAX_COUNT) {

                    $this->line("$filename - сохранение...");

                    // Сохраним сайтмап, если его не сохранили раньше
                    if (!file_exists(public_path($filename))) {
                        // Сохраняем сайтмап
                        $sitemap->writeToFile(public_path($filename));

                        unset($sitemap);

                        $sitemap = Sitemap::create();
                    }

                    // Добавим сайтмап в карту сайтмапов
                    $this->sitemapIndex->add('/' . $filename);

                    $i = 0;
                    $file_index++;
                }

                if ($i == 0) {
                    $filename = "filtered-country-$file_index.xml";
                }

                if (!file_exists(public_path($filename))) {
                    if ($category->parent_category_slug) {
                        $url = route('filtered_subcategory.page', [
                            'filter' => $location->location_slug,
                            'category' => $category->parent_category_slug,
                            'subcategory' => $category->category_slug
                        ]);
                    } else {
                        $url = route('filtered_category.page', [
                            'filter' => $location->location_slug,
                            'category' => $category->category_slug
                        ]);
                    }

                    $sitemap->add($url);
                }

                $i++;
            }

        }

        $this->line('Сайтмап для стран создан');

    }

}
