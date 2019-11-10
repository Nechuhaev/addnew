<?php

use Illuminate\Database\Seeder;

class AdTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\AdCountry::class, 40)->create();

        factory(App\AdRegion::class, 300)->create();

        factory(App\AdCity::class, 900)->create();

        //Родительские категории
        factory(App\AdCategory::class, 14)->create();
        //Дочерние категории
        factory(App\AdCategory::class, 250)->create();


    }
}
