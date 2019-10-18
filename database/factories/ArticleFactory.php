<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Article;
use App\ArticleCategory;
use Faker\Generator as Faker;

$factory->define(Article::class, function (Faker $faker) {
    return [
        'name' => $faker->realText(rand(20, 50)),
        'slug' => $faker->slug,
        'content' => $faker->realText(rand(800, 2000)),
        'excerpt' => $faker->realText(rand(300, 700)),
        'meta_title' => $faker->realText(rand(100, 255)),
        'meta_description' => $faker->realText(rand(100, 255)),
        'sort_order' => rand(0, 100),
        'image' => $faker->imageUrl()
    ];
});

$factory->define(ArticleCategory::class, function (Faker $faker) {
    return [
        'name' => $faker->realText(rand(10, 20)),
        'slug' => $faker->slug,
        'content' => $faker->realText(rand(800, 2000)),
        'meta_title' => $faker->optional()->realText(100),
        'meta_description' => $faker->optional()->realText(100),
        'sort_order' => rand(0, 100)
    ];
});

