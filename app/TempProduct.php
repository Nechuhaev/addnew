<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TempProduct extends Model
{
    protected $guarded = [];

    public function currency() {
        return $this->belongsTo(AdCurrency::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function getImagesAttribute() {
        return json_decode($this->attributes['images']);
    }
}
