<?php

use Illuminate\Database\Seeder;
use App\AdCurrency;


class CurrencyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $currency = new AdCurrency();

        $currency->name = 'Гривна';
        $currency->code = 'UAH';
        $currency->rate = '1';
        $currency->symbol = 'грн.';

        $currency->save();

        //
        $currency = new AdCurrency();

        $currency->name = 'Dollar';
        $currency->code = 'USD';
        $currency->rate = '25';
        $currency->symbol = '$';

        $currency->save();

        //
        $currency = new AdCurrency();

        $currency->name = 'Euro';
        $currency->code = 'EUR';
        $currency->rate = '28';
        $currency->symbol = 'E';

        $currency->save();
    }
}
