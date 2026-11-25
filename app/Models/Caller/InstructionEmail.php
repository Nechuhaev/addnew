<?php

namespace App\Models\Caller;

use Illuminate\Database\Eloquent\Model;

class InstructionEmail extends Model
{
    protected $table = 'caller_emails';

    protected $guarded = ['id'];

    protected $casts = [
        'sent_at' => 'datetime',
        'attempts' => 'integer',
    ];

    const STATUSES = [
        'review' => 'На перевірці',
        'pending' => 'У черзі',
        'sent' => 'Надіслано',
        'failed' => 'Помилка',
        'skipped' => 'Скасовано',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function statusBadge(): string
    {
        $map = ['review' => 'warning', 'pending' => 'info', 'sent' => 'success', 'failed' => 'danger', 'skipped' => 'secondary'];
        return $map[$this->status] ?? 'light';
    }
}
