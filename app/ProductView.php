<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ProductView extends Model
{
    // Таблиця лог-подій, тільки created_at, без updated_at.
    public $timestamps = false;

    protected $fillable = [
        'ad_id',
        'event_type',
        'duration_seconds',
        'ip_hash',
    ];

    /**
     * Записати подію (перегляд/клік) з дедуплікацією.
     * Одна IP+товар+тип події не рахується частіше ніж раз на
     * $dedupMinutes хвилин (захист від накрутки при рефреші сторінки).
     *
     * @param  int    $adId
     * @param  string $ip
     * @param  string $eventType    'view', 'click_contacts', 'click_shop_link'
     * @param  int    $dedupMinutes
     * @return bool   true, якщо подію реально записано (не дубль)
     */
    public static function record(int $adId, string $ip, string $eventType, int $dedupMinutes = 30): bool
    {
        $ipHash = hash('sha256', $ip);
        $cacheKey = "product_view:{$adId}:{$eventType}:{$ipHash}";

        if (Cache::has($cacheKey)) {
            return false;
        }

        Cache::put($cacheKey, true, now()->addMinutes($dedupMinutes));

        static::create([
            'ad_id' => $adId,
            'event_type' => $eventType,
            'ip_hash' => $ipHash,
        ]);

        return true;
    }

    /**
     * Записати орієнтовний час перебування на сторінці. НЕ дедуплікується
     * (кожен реальний візит — окремий запис часу), бо кожне відвідування
     * має власну тривалість.
     */
    public static function recordDuration(int $adId, string $ip, int $durationSeconds): void
    {
        // Відсікаємо аномальні значення (вкладка залишена відкритою на
        // ніч тощо) — більше 30 хвилин на сторінці товару малоймовірно
        // й спотворює середнє.
        if ($durationSeconds <= 0 || $durationSeconds > 1800) {
            return;
        }

        static::create([
            'ad_id' => $adId,
            'event_type' => 'time_on_page',
            'duration_seconds' => $durationSeconds,
            'ip_hash' => hash('sha256', $ip),
        ]);
    }

    public function ad()
    {
        return $this->belongsTo(Ad::class);
    }
}