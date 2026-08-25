<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ShopView extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'ip_hash',
    ];

    public static function record(int $userId, string $ip, int $dedupMinutes = 30): bool
    {
        $ipHash = hash('sha256', $ip);
        $cacheKey = "shop_view:{$userId}:{$ipHash}";

        if (Cache::has($cacheKey)) {
            return false;
        }

        Cache::put($cacheKey, true, now()->addMinutes($dedupMinutes));

        static::create([
            'user_id' => $userId,
            'ip_hash' => $ipHash,
        ]);

        return true;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}