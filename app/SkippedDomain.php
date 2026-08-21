<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SkippedDomain extends Model
{
    protected $table = 'monitoring_skipped_domains';

    protected $fillable = ['domain', 'note'];

    protected static function booted()
    {
        static::saved(function () {
            Cache::forget('monitoring_skipped_domains_list');
        });
        static::deleted(function () {
            Cache::forget('monitoring_skipped_domains_list');
        });
    }

    /**
     * Список доменів для швидкої перевірки — закешовано на годину,
     * щоб не бити в БД щоразу під час моніторингу сотень товарів.
     */
    public static function list(): array
    {
        return Cache::remember('monitoring_skipped_domains_list', 3600, function () {
            return self::pluck('domain')->toArray();
        });
    }
}