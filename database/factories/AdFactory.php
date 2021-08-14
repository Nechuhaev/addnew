<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Ad;
use App\AdCountry;
use App\AdRegion;
use App\AdCity;
use App\AdCategory;
use App\AdCurrency;
use App\AdTag;
use \App\User;
use Faker\Generator as Faker;

$factory->define(Ad::class, function (Faker $faker) {
    $city = AdCity::all()->random(1)->first();
    $category = AdCategory::all()->random(1)->first();
    $currency = AdCurrency::all()->random(1)->first();
    $user = User::all()->random(1)->first();

    $images = [
        $faker->imageUrl(),
        $faker->imageUrl(),
        $faker->imageUrl(),
        $faker->imageUrl()
    ];

    return [
        'category_id' => $category->id,
        'city_id' => $city->id,
        'user_id' => $user->id,
        'currency_id' => $currency->id,
        'image' => $faker->imageUrl(),
        'images' => $images,
        'name' => $faker->realText(rand(10, 20)),
        'slug' => $faker->slug,
        'content' => $faker->text(999),
        'price' => rand(1, 100),
        'telephone' => $faker->phoneNumber,
        'email' => $faker->unique()->safeEmail,
        'meta_title' => $faker->optional()->realText(100),
        'meta_description' => $faker->optional()->realText(100),
        'status' => rand(0, 1),
        'total_views' => rand(100, 500),
        'today_views' => rand(10, 40),
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


// Теги
$factory->define(AdTag::class, function (Faker $faker) {
    return [
        'name' => $faker->realText(rand(10, 11)),
        'slug' => $faker->slug,
        'content' => $faker->realText(rand(200, 700)),
        'meta_title' => $faker->optional()->realText(100),
        'meta_description' => $faker->optional()->realText(100),
    ];
});


