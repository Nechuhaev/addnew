<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Фід товарів магазину за URL, який оновлюється автоматично (feeds:sync).
 */
class ShopFeed extends Model
{
    protected $guarded = [];

    protected $casts = [
        'enabled' => 'boolean',
        'started_at' => 'datetime',
        'last_run_at' => 'datetime',
        'last_success_at' => 'datetime',
        'next_run_at' => 'datetime',
    ];

    const FREQUENCIES = [6, 12, 24];

    /** Після скількох невдач поспіль надіслати магазину лист */
    const FAIL_NOTIFY_AFTER = 3;

    /** Вважаємо «running» завислим після стількох хвилин */
    const STALE_MINUTES = 120;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function runs()
    {
        return $this->hasMany(Import::class, 'feed_id')->latest();
    }

    public function isRunning(): bool
    {
        return $this->status === 'running' && $this->started_at && $this->started_at->gt(now()->subMinutes(self::STALE_MINUTES));
    }

    public function scheduleNext(): void
    {
        $this->next_run_at = now()->addHours($this->frequency_hours ?: 24);
    }
}
