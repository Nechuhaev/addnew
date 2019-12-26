<?php

namespace App\Http\Controllers;

use App\AdCity;
use App\AdCountry;
use App\AdRegion;
use App\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\SitemapIndex;

class SitemapController extends Controller
{
    public function create() {


        $sitemap_index = SitemapIndex::create();

        // pages

        $sitemap_pages = Sitemap::create();

        // custom pages
        Page::all()->each(function ($item) use ($sitemap_pages) {
            $sitemap_pages->add($item->url);
        });
        $sitemap_pages->writeToFile(public_path('pages.xml'));

        $sitemap_index->add('/pages.xml'); // ->setLastModificationDate(Carbon::today())



        //countries
        $sitemap_countries = Sitemap::create();
        AdCountry::all()->each(function ($item) use ($sitemap_countries) {
            $sitemap_countries->add($item->url);
        });

        $sitemap_countries->writeToFile(public_path('countries.xml'));
        $sitemap_index->add('/countries.xml');

        // regions
        $regions = Sitemap::create();
        AdRegion::all()->each(function ($item) use ($regions) {
            $regions->add($item->url);
        });

        $regions->writeToFile(public_path('regions.xml'));
        $sitemap_index->add('/regions.xml');

        // cities
        $cities = Sitemap::create();
        AdCity::all()->each(function ($item) use ($cities) {
            $cities->add($item->url);
        });

        $cities->writeToFile(public_path('cities.xml'));
        $sitemap_index->add('/cities.xml');

        // ad categories
        $ad_categories = Sitemap::create();
        AdCity::all()->each(function ($item) use ($ad_categories) {
            $ad_categories->add($item->url);
        });

        $ad_categories->writeToFile(public_path('ad_categories.xml'));
        $sitemap_index->add('/ad_categories.xml');

        // ads

        // blog categories

        // blog post

        $sitemap_index->writeToFile(public_path('sitemap.xml'));



    }
}
