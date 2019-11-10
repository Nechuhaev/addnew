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

    public function ads()
    {
        return $this->hasMany(Ad::class, 'currency_id');
    }
}
