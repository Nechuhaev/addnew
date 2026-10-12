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
        'ip_address',
    ];

    protected static function boot()
    {
        parent::boot();

        $forget = function (ShopReview $review) {
            \App\Services\ShopRating::forget((int) $review->shop_user_id);
        };
        static::saved($forget);
        static::deleted($forget);
    }

    public function shop()
    {
        return $this->belongsTo(User::class, 'shop_user_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_user_id');
    }
}
