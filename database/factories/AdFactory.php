<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Ad;
use App\AdCountry;
use App\AdRegion;
use App\AdCity;
use App\AdCategory;
use App\AdCurrency;
use Faker\Generator as Faker;

$factory->define(Ad::class, function (Faker $faker) {
    return [
        //
    ];
});


// Страна
$factory->define(AdCountry::class, function (Faker $faker) {
    return [
        'name' => $faker->realText(rand(10, 20)),
        'image' => $faker->imageUrl(),
        'slug' => $faker->slug,
        'content' => $faker->realText(rand(200, 700)),
        'meta_title' => $faker->optional()->realText(100),
        'meta_description' => $faker->optional()->realText(100),
        'sort_order' => rand(0, 100)
    ];
});

// Регион
$factory->define(AdRegion::class, function (Faker $faker) {
    $country = AdCountry::all()->random(1)->first();

    return [
        'name' => $faker->realText(rand(10, 20)),
        'country_id' => $country->id,
        'slug' => $faker->slug,
        'content' => $faker->realText(rand(200, 700)),
        'meta_title' => $faker->optional()->realText(100),
        'meta_description' => $faker->optional()->realText(100),
        'sort_order' => rand(0, 100)
    ];
});

// Город
$factory->define(AdCity::class, function (Faker $faker) {
    $region = AdRegion::all()->random(1)->first();
    return [
        'name' => $faker->realText(rand(10, 20)),
        'region_id' => $region->id,
        'slug' => $faker->slug,
        'content' => $faker->realText(rand(200, 700)),
        'meta_title' => $faker->optional()->realText(100),
        'meta_description' => $faker->optional()->realText(100),
        'sort_order' => rand(0, 100)
    ];
});

// Категории
$factory->define(AdCategory::class, function (Faker $faker) {
    if (AdCategory::all()->count() > 13) {
        $category = AdCategory::where('id', '<', 13)->get()->random(1)->first();
        $category_id = $category->id;
    } else {
        $category_id = 0;
    }
    return [
        'name' => $faker->realText(rand(10, 20)),
        'image' => $faker->imageUrl(),
        'parent_id' => $category_id,
        'slug' => $faker->slug,
        'content' => $faker->realText(rand(200, 700)),
        'meta_title' => $faker->optional()->realText(100),
        'meta_description' => $faker->optional()->realText(100),
        'sort_order' => rand(0, 100)
    ];
});

