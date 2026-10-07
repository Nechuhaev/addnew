<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductPriceCheck extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'ad_id',
        'old_price',
        'found_price',
        'found_currency',
        'price_applied',
        'status',
        'note',
        'checked_at',
    ];

    protected $casts = [
        'price_applied' => 'boolean',
        'checked_at' => 'datetime',
    ];

    public function ad()
    {
        return $this->belongsTo(Ad::class);
    }

    /**
     * Остання перевірка конкурента для кожного з товарів [ad_id => ProductPriceCheck]
     */
    public static function lastCompetitorChecks(array $adIds)
    {
        if (!$adIds) {
            return collect();
        }

        $lastIds = static::selectRaw('MAX(id)')
            ->whereIn('ad_id', $adIds)
            ->where('status', 'like', 'competitor\\_%')
            ->groupBy('ad_id');

        return static::whereIn('id', $lastIds)->get()->keyBy('ad_id');
    }

    const CURRENCY_MISMATCH_NOTE = 'Валюта не збігається';

    public function isCurrencyMismatch(): bool
    {
        return strpos((string) $this->note, self::CURRENCY_MISMATCH_NOTE) === 0;
    }

    /**
     * Підпис і колір бейджа для результату перевірки конкурента (адмінка)
     *
     * @return array [текст, клас bootstrap-бейджа]
     */
    public function competitorLabel(): array
    {
        switch ($this->status) {
            case 'competitor_success':
                if ($this->price_applied) {
                    return ['Ціну змінено', 'success'];
                }
                return $this->isCurrencyMismatch() ? ['Інша валюта', 'warning'] : ['Без змін', 'secondary'];
            case 'competitor_suspicious':
                return ['Підозріла ціна', 'warning'];
            case 'competitor_404':
                return ['404 у конкурента', 'danger'];
            case 'competitor_not_found':
                return ['Ціну не знайдено', 'danger'];
            case 'competitor_fetch_error':
                return ['Сайт недоступний', 'danger'];
        }

        return [$this->status, 'light'];
    }
}