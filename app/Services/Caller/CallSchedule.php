<?php

namespace App\Services\Caller;

use Carbon\Carbon;

/**
 * Робочий час дзвінків за Києвом (незалежно від налаштувань PHP на хостингу).
 */
class CallSchedule
{
    /** @var int */
    private $start;
    /** @var int */
    private $end;
    /** @var int[] */
    private $days;

    public function __construct(int $start, int $end, array $days)
    {
        $this->start = $start;
        $this->end = $end;
        $this->days = $days ?: [1, 2, 3, 4, 5];
    }

    public static function fromSettings(array $s): self
    {
        return new self($s['call_hours_start'], $s['call_hours_end'], $s['call_days']);
    }

    public static function tz(): string
    {
        return config('caller.timezone', 'Europe/Kiev');
    }

    public function isCallingTime(Carbon $at): bool
    {
        $k = $at->copy()->setTimezone(self::tz());
        return in_array($k->dayOfWeekIso, $this->days, true) && $k->hour >= $this->start && $k->hour < $this->end;
    }

    /** Найближчий дозволений момент >= $at (крок 15 хв: просто й коректно при переході на літній час). */
    public function nextSlot(Carbon $at): Carbon
    {
        $t = $at->copy()->setTimezone(self::tz())->second(0);
        if ($t->lessThan($at)) {
            $t->addMinute();
        }
        for ($i = 0; $i < 4 * 24 * 8; $i++) {
            if ($this->isCallingTime($t)) {
                return $t;
            }
            $t->addMinutes(15);
            // Вирівнюємо до чверті години, щоб слот починався рівно о 10:00, а не о 10:07
            $t->minute((int) (floor($t->minute / 15) * 15));
        }
        return $t;
    }

    public function retryAt(Carbon $now, int $minutes): Carbon
    {
        return $this->nextSlot($now->copy()->addMinutes($minutes));
    }
}
