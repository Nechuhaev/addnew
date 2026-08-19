<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductPriceCheck extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'ad_id',
        'old_price',
        'found_price',
        'found_currency',
        'price_applied',
        'status',
        'note',
        'checked_at',
    ];

    protected $casts = [
        'price_applied' => 'boolean',
        'checked_at' => 'datetime',
    ];

    public function ad()
    {
        return $this->belongsTo(Ad::class);
    }
}