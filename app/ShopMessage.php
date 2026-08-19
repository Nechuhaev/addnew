<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShopMessage extends Model
{
    protected $fillable = [
        'shop_user_id',
        'admin_user_id',
        'subject',
        'body',
        'sent_to_email',
        'sent_successfully',
        'error_message',
    ];

    protected $casts = [
        'sent_successfully' => 'boolean',
    ];

    public function shop()
    {
        return $this->belongsTo(User::class, 'shop_user_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }
}