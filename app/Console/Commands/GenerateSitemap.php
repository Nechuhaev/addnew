<?php

namespace App\Console\Commands;

use App\Ad;
use App\AdCategory;
use App\Article;
use App\ArticleCategory;
use Illuminate\Console\Command;
use App\AdCity;
use App\AdCountry;
use App\AdRegion;
use App\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\SitemapIndex;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Создать карту сайта';

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

        ini_set('memory_limit', '-1');

        $sitemap_index = SitemapIndex::create();

        // Users
        $sitemap_users = Sitemap::create();

        DB::table('users')
            ->selectRaw('users.id as user_id')
            ->get()->each(function ($item) use ($sitemap_users) {
                if ($item->user_id) {
                    $sitemap_users->add(route('author', ['id' => $item->user_id]));
                }
            });

        $sitemap_users->writeToFile(public_path('users.xml'));
        unset($sitemap_users);
        $sitemap_index->add('/users.xml');
        $this->line('users.xml создано');


        // pages

        $sitemap_pages = Sitemap::create();
        $this->line('Начинаем создание карты сайта');

        // custom pages
        Page::all()->each(function ($item) use ($sitemap_pages) {
            $sitemap_pages->add($item->url);
        });

        $sitemap_pages->writeToFile(public_path('pages.xml'));

        unset($sitemap_pages);
        $sitemap_index->add('/pages.xml'); // ->setLastModificationDate(Carbon::today())
        $this->line('pages.xml создано');

        //countries
        $sitemap_countries = Sitemap::create();
        AdCountry::all()->each(function ($item) use ($sitemap_countries) {
            $sitemap_countries->add($item->url);
        });

        $sitemap_countries->writeToFile(public_path('countries.xml'));
        unset($sitemap_countries);
        $sitemap_index->add('/countries.xml');
        $this->line('countries.xml создано');

        // ad categories
        $sitemap_ad_categories = Sitemap::create();
        AdCategory::all()->each(function ($item) use ($sitemap_ad_categories) {
            $sitemap_ad_categories->add($item->url);
        });

        $sitemap_ad_categories->writeToFile(public_path('ad_categories.xml'));
        unset($sitemap_ad_categories);
        $sitemap_index->add('/ad_categories.xml');
        $this->line('ad_categories.xml создано');

        // blog categories
        $sitemap_articles = Sitemap::create();
        ArticleCategory::all()->each(function ($item) use ($sitemap_articles) {
            $sitemap_articles->add($item->url);
        });

        $sitemap_articles->writeToFile(public_path('articles.xml'));
        unset($sitemap_articles);
        $sitemap_index->add('/articles.xml');
        $this->line('articles.xml создано');

        // blog post
        $sitemap_blog = Sitemap::create();
        Article::all()->each(function ($item) use ($sitemap_blog) {
            $sitemap_blog->add($item->url);
        });

        $sitemap_blog->writeToFile(public_path('blog.xml'));
        unset($sitemap_blog);
        $sitemap_index->add('/blog.xml');
        $this->line('blog.xml создано');

        // regions
        $sitemap_regions = Sitemap::create();

        DB::table('ad_regions')
            ->selectRaw('ad_countries.slug as country, ad_regions.slug as region')
            ->leftJoin('ad_countries', 'ad_regions.country_id', '=', 'ad_countries.id')
            ->get()->each(function ($item) use ($sitemap_regions) {
                if ($item->country && $item->region) {
                    $sitemap_regions->add(route('region.page', ['country' => $item->country, 'region' => $item->region]));
                }
            });

        $sitemap_regions->writeToFile(public_path('regions.xml'));
        unset($sitemap_regions);
        $sitemap_index->add('/regions.xml');
        $this->line('regions.xml создано');

        // --- Отсюда начинаем немного оптимизировать

        // cities
        $sitemap_cities = Sitemap::create();
        DB::table('ad_cities')
            ->selectRaw('ad_countries.slug as country, ad_regions.slug as region, ad_cities.slug as city')
            ->leftJoin('ad_regions', 'ad_cities.region_id', '=', 'ad_regions.id')
            ->leftJoin('ad_countries', 'ad_regions.country_id', '=', 'ad_countries.id')
            ->get()->each(function ($item) use ($sitemap_cities) {
                if ($item->country && $item->region) {
                    $sitemap_cities->add(route('city.page', ['country' => $item->country, 'region' => $item->region, 'city' => $item->city]));
                }
            });

        $sitemap_cities->writeToFile(public_path('cities.xml'));
        unset($sitemap_cities);
        $sitemap_index->add('/cities.xml');
        $this->line('cities.xml создано');

        // ads
        $sitemap_ads = Sitemap::create();

        DB::table('ads')->select("slug")->get()->each(function ($item) use ($sitemap_ads) {
            $sitemap_ads->add(route('ad.page', ['slug' => $item->slug]));
        });

        $sitemap_ads->writeToFile(public_path('ads.xml'));
        unset($sitemap_ads);
        $sitemap_index->add('/ads.xml');
        $this->line('ads.xml создано');

        $sitemap_index->writeToFile(public_path('sitemap.xml'));

        // tags
        $tags_collection = DB::table('ad_tags')->select("slug", 'id')->get();

        $tags_parts = $tags_collection->chunk(3000);

        foreach ($tags_parts as $tags_part_key => $tags_part) {
            $sitemap_tags = Sitemap::create();

            $tags_part->each(function ($item) use ($sitemap_tags) {
                if ($item->slug) {
                    $sitemap_tags->add(route('tag', ['slug' => $item->slug]));
                }
            });

            $file_name = 'tags' . $tags_part_key . '.xml';
            $sitemap_tags->writeToFile(public_path($file_name));
            unset($sitemap_tags);
            $sitemap_index->add('/'.$file_name);
            $this->line($file_name. ' создано');
        }

        $sitemap_index->writeToFile(public_path('sitemap.xml'));

        $this->line('sitemap.xml завершен.');

    }
}
