<?php

namespace App\Services\Caller;

use Carbon\Carbon;

/**
 * Як результат дзвінка змінює стан ліда. Чисті функції без БД — легко тестувати.
 */
class OutcomeRules
{
    /** Максимум передзвонів «на прохання» понад звичайні спроби — щоб не застрягти в циклі. */
    const EXTRA_CALLBACKS = 2;

    /** @var CallSchedule */
    private $schedule;
    /** @var int */
    private $maxAttempts;
    /** @var int */
    private $retryAfterMinutes;

    public function __construct(CallSchedule $schedule, int $maxAttempts, int $retryAfterMinutes)
    {
        $this->schedule = $schedule;
        $this->maxAttempts = $maxAttempts;
        $this->retryAfterMinutes = $retryAfterMinutes;
    }

    public static function fromSettings(array $s): self
    {
        return new self(CallSchedule::fromSettings($s), $s['max_attempts'], $s['retry_after_minutes']);
    }

    /**
     * @param int   $attempts  скільки спроб уже зроблено (включно з поточною)
     * @param array $a         результат CallAnalyzer::analyze()
     * @return array ['status', 'next_call_at' => ?Carbon, 'notes', 'dnc' => bool]
     */
    public function apply(int $attempts, array $a, Carbon $now): array
    {
        $note = implode(' | ', array_filter([
            $a['summary'] ?? '', $a['next_action'] ?? '',
            !empty($a['referred_contact']) ? 'Контакт: ' . $a['referred_contact'] : '',
        ]));

        switch ($a['outcome']) {
            case 'do_not_call':
                return ['status' => 'dnc', 'next_call_at' => null, 'notes' => $note, 'dnc' => true];

            case 'agreed':
            case 'send_info':
            case 'referred':
            case 'not_interested':
                return ['status' => 'done', 'next_call_at' => null, 'notes' => $note, 'dnc' => false];

            case 'callback':
                if ($attempts >= $this->maxAttempts + self::EXTRA_CALLBACKS) {
                    return ['status' => 'done', 'next_call_at' => null, 'notes' => $note . ' | Ліміт передзвонів вичерпано', 'dnc' => false];
                }
                $at = null;
                if (!empty($a['callback_at'])) {
                    try {
                        $at = Carbon::parse($a['callback_at']);
                    } catch (\Exception $e) {
                        $at = null;
                    }
                }
                if (!$at || $at->lessThanOrEqualTo($now)) {
                    $at = $now->copy()->addDay(); // за замовчуванням — завтра в цей же час
                }
                return ['status' => 'callback', 'next_call_at' => $this->schedule->nextSlot($at), 'notes' => $note, 'dnc' => false];

            default: // voicemail, no_conversation
                return $this->notReached($attempts, $now, $note);
        }
    }

    /** Не дозвонилися: зайнято, не взяли, автовідповідач, помилка ініціації. */
    public function notReached(int $attempts, Carbon $now, string $note = ''): array
    {
        if ($attempts >= $this->maxAttempts) {
            return ['status' => 'failed', 'next_call_at' => null, 'notes' => $note ?: 'Не вдалося додзвонитися', 'dnc' => false];
        }
        // Кожна наступна спроба пізніше (1×, 2×, 3× інтервалу) — щоб влучити в інший час доби
        $delay = $this->retryAfterMinutes * max(1, $attempts);
        return ['status' => 'retry', 'next_call_at' => $this->schedule->retryAt($now, $delay), 'notes' => $note, 'dnc' => false];
    }
}
