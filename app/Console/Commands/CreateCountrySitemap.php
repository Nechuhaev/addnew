<?php

namespace App\Console\Commands;

use App\Ad;
use App\AdCategory;
use App\AdCountry;
use App\AdTag;
use App\Article;
use App\ArticleCategory;
use App\Http\Resources\Ad\Country;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\SitemapIndex;
use Illuminate\Support\Facades\DB;

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
        ini_set('memory_limit', '512M');
        // Идентификаторы стран для которых позволена генерация сайтмапа
        $allowedCountryIds = [
            62 // Украина
        ];

        foreach ($allowedCountryIds as $allowedCountryId) {
            $country = AdCountry::find($allowedCountryId);

            $sitemap_index = SitemapIndex::create();
            if ($country) {



                //объявления
                Ad::query()
                    ->whereIn('city_id', $country->cities->pluck('id'))
                    ->chunk(5000, function ($ads, $iteration) use ($sitemap_index) {
                        $sitemap_ads = Sitemap::create();

                        $ads->each(function ($item) use ($sitemap_ads) {
                            $sitemap_ads->add(route('ad.page', ['slug' => $item->slug]));
                        });

                        $filename = "ads-{$iteration}.xml";
                        $sitemap_ads->writeToFile(public_path($filename));
                        $sitemap_index->add($filename);
                        $this->line("{$filename} создано");
                        unset($sitemap_ads);
                    });



                // Категории блога
                $sitemap_articles = Sitemap::create();
                ArticleCategory::all()->each(function ($item) use ($sitemap_articles) {
                    $sitemap_articles->add($item->url);
                });

                $sitemap_articles->writeToFile(public_path('articles.xml'));
                unset($sitemap_articles);
                $sitemap_index->add('/articles.xml');
                $this->line('articles.xml создано');



                // Блог
                $sitemap_blog = Sitemap::create();
                Article::all()->each(function ($item) use ($sitemap_blog) {
                    $sitemap_blog->add($item->url);
                });

                $sitemap_blog->writeToFile(public_path('blog.xml'));
                unset($sitemap_blog);
                $sitemap_index->add('/blog.xml');
                $this->line('blog.xml создано');


                // категории
                $sitemap_ad_categories = Sitemap::create();
                AdCategory::all()->each(function ($item) use ($sitemap_ad_categories) {
                    $sitemap_ad_categories->add($item->url);
                });

                $sitemap_ad_categories->writeToFile(public_path('ad_categories.xml'));
                unset($sitemap_ad_categories);
                $sitemap_index->add('/ad_categories.xml');
                $this->line('ad_categories.xml создано');



                // области
                $country->regions()->chunk(5000, function ($regions, $iteration) use ($sitemap_index) {
                    $sitemap_regions = Sitemap::create();

                    DB::table('ad_regions')
                        ->selectRaw('ad_countries.slug as country, ad_regions.slug as region')
                        ->leftJoin('ad_countries', 'ad_regions.country_id', '=', 'ad_countries.id')
                        ->get()->each(function ($item) use ($sitemap_regions) {
                            if ($item->country == 'ukraina' && $item->region) {
                                $sitemap_regions->add(route('region.page', ['country' => $item->country, 'region' => $item->region]));
                            }
                        });

                    $filename = "regions-{$iteration}.xml";
                    $sitemap_regions->writeToFile(public_path($filename));
                    $sitemap_index->add($filename);
                    $this->line("{$filename} создано");
                    unset($sitemap_regions);
                });



                // категории по областям
                // Сейчас в городах мало обхявлений, по этому категории пустые зачастую, сайтмап не генерируем



                // города
                $country->cities()->chunk(5000, function ($cities, $iteration) use ($sitemap_index) {
                    $sitemap_cities = Sitemap::create();

                    DB::table('ad_cities')
                        ->selectRaw('ad_countries.slug as country, ad_regions.slug as region, ad_cities.slug as city')
                        ->leftJoin('ad_regions', 'ad_cities.region_id', '=', 'ad_regions.id')
                        ->leftJoin('ad_countries', 'ad_regions.country_id', '=', 'ad_countries.id')
                        ->get()->each(function ($item) use ($sitemap_cities) {
                            if ($item->country == 'ukraina' && $item->region) {
                                $sitemap_cities->add(route('city.page', ['country' => $item->country, 'region' => $item->region, 'city' => $item->city]));
                            }
                        });

                    $filename = "cities-{$iteration}.xml";
                    $sitemap_cities->writeToFile(public_path($filename));
                    $sitemap_index->add($filename);
                    $this->line("{$filename} создано");
                    unset($sitemap_cities);
                });




                // категории по городам
                // Сейчас в городах мало обхявлений, по этому категории пустые зачастую, сайтмап не генерируем


                // теги
                AdTag::query()->whereHas('ads', function ($query) use ($country) {
                    return $query->whereIn('city_id', $country->cities->pluck('id'));
                })->chunk(5000, function ($tags, $iteration) use ($sitemap_index) {
                    $sitemap_tags = Sitemap::create();

                    $tags->each(function ($item) use ($sitemap_tags) {
                        if ($item->slug) {
                            $sitemap_tags->add(route('tag', ['slug' => $item->slug]));
                        }
                    });

                    $filename = "tags-{$iteration}.xml";
                    $sitemap_tags->writeToFile(public_path($filename));
                    $sitemap_index->add($filename);
                    $this->line("{$filename} создано");
                    unset($sitemap_tags);
                });

            }

            $sitemap_index->writeToFile(public_path('sitemap.xml'));
        }
    }

    private function refreshAdsSitemaps($country)
    {

    }
}
