<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Період статистики з GET-параметрів: period=today|7|30|90|all або from/to (Y-m-d).
 */
class StatsPeriod
{
    const PRESETS = ['today' => 'Сьогодні', '7' => '7 днів', '30' => '30 днів', '90' => '90 днів', 'all' => 'Увесь час'];
    const DEFAULT = '30';

    /** @var Carbon|null */
    public $from;
    /** @var Carbon|null */
    public $to;
    /** @var string preset або 'custom' */
    public $key;

    public static function fromRequest(Request $request): self
    {
        $p = new self();
        $from = static::date($request->get('from'));
        $to = static::date($request->get('to'));

        if ($from || $to) {
            if ($from && $to && $from->gt($to)) {
                [$from, $to] = [$to, $from];
            }
            $p->key = 'custom';
            $p->from = $from ? $from->startOfDay() : null;
            $p->to = $to ? $to->endOfDay() : null;
            return $p;
        }

        $key = (string) $request->get('period', self::DEFAULT);
        $p->key = array_key_exists($key, self::PRESETS) ? $key : self::DEFAULT;
        if ($p->key === 'today') {
            $p->from = Carbon::today();
        } elseif ($p->key !== 'all') {
            $p->from = Carbon::today()->subDays((int) $p->key - 1);
        }
        return $p;
    }

    protected static function date($v): ?Carbon
    {
        if (!$v || !preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $v)) {
            return null;
        }
        try {
            return Carbon::createFromFormat('Y-m-d', $v);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** Обмежити запит по колонці часу */
    public function apply($query, string $column)
    {
        if ($this->from) {
            $query->where($column, '>=', $this->from);
        }
        if ($this->to) {
            $query->where($column, '<=', $this->to);
        }
        return $query;
    }

    /** Параметри для посилань (зберегти період) */
    public function query(): array
    {
        if ($this->key === 'custom') {
            return array_filter(['from' => optional($this->from)->toDateString(), 'to' => optional($this->to)->toDateString()]);
        }
        return $this->key === self::DEFAULT ? [] : ['period' => $this->key];
    }

    public function label(): string
    {
        if ($this->key !== 'custom') {
            return self::PRESETS[$this->key];
        }
        return ($this->from ? $this->from->format('d.m.Y') : '…') . ' — ' . ($this->to ? $this->to->format('d.m.Y') : 'сьогодні');
    }
}
