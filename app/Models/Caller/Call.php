<?php

namespace App\Models\Caller;

use Illuminate\Database\Eloquent\Model;

class Call extends Model
{
    protected $table = 'caller_calls';

    protected $guarded = ['id'];

    protected $casts = [
        'started_at' => 'datetime',
        'duration_secs' => 'integer',
        'process_attempts' => 'integer',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function analysis(): array
    {
        return $this->analysis_json ? (json_decode($this->analysis_json, true) ?: []) : [];
    }

    public function transcript(): array
    {
        return $this->transcript_json ? (json_decode($this->transcript_json, true) ?: []) : [];
    }
}
