<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShopLeadMessage extends Model
{
    protected $fillable = [
        'shop_lead_id', 'admin_user_id', 'subject', 'body',
        'sent_to_email', 'sent_successfully', 'error_message',
    ];

    protected $casts = [
        'sent_successfully' => 'boolean',
    ];
}
