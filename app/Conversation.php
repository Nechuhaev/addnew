<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = [
        'shop_user_id',
        'buyer_user_id',
        'ad_id',
        'last_message_at',
    ];

    protected $dates = ['last_message_at'];

    public function shop()
    {
        return $this->belongsTo(User::class, 'shop_user_id');
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_user_id');
    }

    public function ad()
    {
        return $this->belongsTo(Ad::class, 'ad_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class)->orderBy('id');
    }

    /**
     * Інша сторона діалогу відносно поточного користувача (для списку
     * "З ким розмова" — щоб не писати цю логіку в кожному контролері).
     */
    public function otherParty(int $currentUserId): ?User
    {
        if ($this->shop_user_id == $currentUserId) {
            return $this->buyer;
        }
        return $this->shop;
    }

    public function unreadCountFor(int $userId): int
    {
        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->count();
    }
}
