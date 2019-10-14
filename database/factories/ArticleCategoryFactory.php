<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\ArticleCategory;
use Faker\Generator as Faker;

$factory->define(ArticleCategory::class, function (Faker $faker) {
    $faker->locale('ru_RU');
    return [
        'name' => $faker->realText(70),
        'slug' => $faker->slug,
        'content' => $faker->realText(10),
        'meta_title' => $faker->optional()->realText(100),
        'meta_description' => $faker->optional()->realText(100),
        'sort_order' => $faker->numberBetween(0, 100)
    ];
});
