<?php

namespace App\Models\Caller;

use Illuminate\Database\Eloquent\Model;

class DoNotCall extends Model
{
    protected $table = 'caller_do_not_call';

    protected $fillable = ['phone', 'reason'];

    public static function has(string $phone): bool
    {
        return static::where('phone', $phone)->exists();
    }

    /** Додає номер у список і зупиняє всі дзвінки та листи цьому ліду. */
    public static function add(string $phone, string $reason = null): void
    {
        static::firstOrCreate(['phone' => $phone], ['reason' => $reason ? mb_substr($reason, 0, 250) : null]);

        $lead = Lead::where('phone', $phone)->first();
        if ($lead) {
            $lead->update(['status' => 'dnc', 'next_call_at' => null]);
            InstructionEmail::where('lead_id', $lead->id)
                ->whereIn('status', ['pending', 'review', 'failed'])
                ->update(['status' => 'skipped', 'last_error' => 'у списку «не дзвонити»']);
        }
    }
}
