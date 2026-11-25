<?php

namespace App\Models\Caller;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $table = 'caller_leads';

    protected $guarded = ['id'];

    protected $casts = [
        'attempts' => 'integer',
        'next_call_at' => 'datetime',
        'last_called_at' => 'datetime',
        'registered_at' => 'datetime',
    ];

    const STATUSES = [
        'new' => 'Новий',
        'calling' => 'Дзвонимо',
        'retry' => 'Повтор',
        'callback' => 'Передзвонити',
        'done' => 'Завершено',
        'dnc' => 'Не дзвонити',
        'failed' => 'Не додзвонилися',
    ];

    const OUTCOMES = [
        'agreed' => 'Погодився',
        'send_info' => 'Надіслати інфо',
        'callback' => 'Передзвонити',
        'referred' => 'Інший контакт',
        'not_interested' => 'Не цікаво',
        'do_not_call' => 'Не дзвонити',
        'voicemail' => 'Автовідповідач',
        'no_conversation' => 'Без розмови',
    ];

    public function calls()
    {
        return $this->hasMany(Call::class, 'lead_id')->orderByDesc('started_at');
    }

    public function emails()
    {
        return $this->hasMany(InstructionEmail::class, 'lead_id')->orderByDesc('id');
    }

    public function registeredUser()
    {
        return $this->belongsTo(User::class, 'registered_user_id');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function outcomeLabel(): string
    {
        return $this->outcome ? (self::OUTCOMES[$this->outcome] ?? $this->outcome) : '—';
    }

    /** Bootstrap-клас бейджа для статусу */
    public function statusBadge(): string
    {
        $map = [
            'new' => 'secondary', 'calling' => 'info', 'retry' => 'warning', 'callback' => 'primary',
            'done' => 'success', 'dnc' => 'dark', 'failed' => 'danger',
        ];
        return $map[$this->status] ?? 'light';
    }
}
