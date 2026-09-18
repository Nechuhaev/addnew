<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShopMessageTemplate extends Model
{
    protected $fillable = [
        'name',
        'subject',
        'body',
    ];
}
