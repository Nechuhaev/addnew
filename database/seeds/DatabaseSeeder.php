<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        $this->call(AdminSeeder::class);
        $this->call(CurrencyTableSeeder::class);
        //$this->call(UsersTableSeeder::class);
        //Создать произвольные статьи
        $this->call(ArticlesTableSeeder::class);
        $this->call(AdTableSeeder::class);


    }
}
