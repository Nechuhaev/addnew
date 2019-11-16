<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdCurrency extends Model
{
    protected $fillable = [
        'name',
        'rate',
        'code',
        'symbol'
    ];

    public $timestamps = false;

    public function getIsDefaultAttribute() {
        return ($this->rate == 1) ? 1 : 0;
    }

    public function ad() {
        return $this->hasOne(Ad::class, 'currency_id');
    }

    public function getPrice() {
        $amount = (int)($this->ad->price * $this->rate);
        return (int)$amount;
    }

    public function getFormattedPrices() {
        $currencies = AdCurrency::all();

        $prices = [];
        foreach ($currencies as $currency) {
            $prices[] = [
                'is_default' => $currency->is_default,
                'code' => $currency->code,
                'value' => $currency->getPrice(),
                'symbol' => $currency->symbol
            ];
        }

        return $prices;
    }

    public function isFree() {
        return ($this->ad->price == 0);
    }

    public static function convert($amount, $to = false, $format = true) {
        return $amount .' грн.';
    }
}
