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

    /**
     * Ціна для карток у списках: у гривнях — «N грн.», в іншій валюті —
     * з її символом (раніше будь-яка валюта підписувалась «грн.»).
     */
    public static function convert($amount, $currencyId = null) {
        static $currencies = null;
        if ($currencyId) {
            if ($currencies === null) {
                $currencies = self::all()->keyBy('id');
            }
            $currency = $currencies->get((int) $currencyId);
            if ($currency && strtoupper($currency->code) !== 'UAH' && (float) $currency->rate != 1.0) {
                return $amount . ' ' . ($currency->symbol ?: $currency->code);
            }
        }
        return $amount .' грн.';
    }
}
