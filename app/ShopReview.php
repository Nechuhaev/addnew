<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShopReview extends Model
{
    protected $fillable = [
        'shop_user_id',
        'reviewer_user_id',
        'rating',
        'comment',
    ];

    public function shop()
    {
        return $this->belongsTo(User::class, 'shop_user_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_user_id');
    }
}
