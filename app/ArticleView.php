<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ArticleView extends Model
{
    // Таблиця лог-подій, тільки created_at, без updated_at.
    public $timestamps = false;

    protected $fillable = [
        'article_id',
        'event_type',
        'ip_hash',
        'referrer',
    ];

    /**
     * Записати подію перегляду/конверсії з дедуплікацією.
     *
     * Одна IP+стаття+тип події не рахується частіше ніж раз на
     * $dedupMinutes хвилин (захист від накрутки при рефреші сторінки).
     *
     * @param  int    $articleId
     * @param  string $ip
     * @param  string $eventType    'view', 'contact_click' тощо
     * @param  string|null $referrer
     * @param  int    $dedupMinutes
     * @return bool   true, якщо подію реально записано (не дубль)
     */
    public static function record(
        int $articleId,
        string $ip,
        string $eventType = 'view',
        ?string $referrer = null,
        int $dedupMinutes = 30
    ): bool {
        $ipHash = hash('sha256', $ip);
        $cacheKey = "article_view:{$articleId}:{$eventType}:{$ipHash}";

        if (Cache::has($cacheKey)) {
            return false;
        }

        Cache::put($cacheKey, true, now()->addMinutes($dedupMinutes));

        static::create([
            'article_id' => $articleId,
            'event_type' => $eventType,
            'ip_hash'    => $ipHash,
            'referrer'   => $referrer,
        ]);

        return true;
    }

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
