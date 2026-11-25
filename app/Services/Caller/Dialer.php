<?php

namespace App\Services\Caller;

use App\Models\Caller\Call;
use App\Models\Caller\DoNotCall;
use App\Models\Caller\Lead;
use Carbon\Carbon;

/**
 * Автонабір: бере ліди з черги й ініціює дзвінки через ElevenLabs.
 * Викликається щохвилини з планувальника (caller:dial).
 */
class Dialer
{
    /** Скільки хвилин чекаємо вебхук, перш ніж вважати дзвінок завислим */
    const STUCK_MINUTES = 20;

    /** @var ElevenLabsClient */
    private $client;

    public function __construct(ElevenLabsClient $client = null)
    {
        $this->client = $client ?: new ElevenLabsClient();
    }

    /** Ліди, яким пора дзвонити: спершу передзвони на прохання, потім повтори, потім нові. */
    public static function dueQuery(Carbon $now, int $maxAttempts)
    {
        return Lead::query()
            ->whereNotIn('phone', function ($q) {
                $q->select('phone')->from('caller_do_not_call');
            })
            ->where(function ($q) use ($now, $maxAttempts) {
                $q->where('status', 'new')
                    ->orWhere(function ($q) use ($now, $maxAttempts) {
                        $q->where('status', 'retry')->where('attempts', '<', $maxAttempts)
                            ->where(function ($q) use ($now) {
                                $q->whereNull('next_call_at')->orWhere('next_call_at', '<=', $now);
                            });
                    })
                    ->orWhere(function ($q) use ($now) {
                        $q->where('status', 'callback')->where('next_call_at', '<=', $now);
                    });
            })
            ->orderByRaw("CASE status WHEN 'callback' THEN 0 WHEN 'retry' THEN 1 ELSE 2 END")
            ->orderBy('next_call_at')
            ->orderBy('id');
    }

    public static function callsToday(Carbon $now): int
    {
        return Call::where('call_date', $now->copy()->setTimezone(CallSchedule::tz())->toDateString())->count();
    }

    public static function activeCalls(Carbon $now): int
    {
        return Call::where('status', 'initiated')->where('started_at', '>=', $now->copy()->subMinutes(self::STUCK_MINUTES))->count();
    }

    /** Повертає в чергу ліди, по яких вебхук так і не прийшов. */
    public function recoverStuck(array $settings, Carbon $now): int
    {
        $rules = OutcomeRules::fromSettings($settings);
        $cutoff = $now->copy()->subMinutes(self::STUCK_MINUTES);
        $n = 0;
        foreach (Lead::where('status', 'calling')->where('last_called_at', '<', $cutoff)->get() as $lead) {
            $u = $rules->notReached($lead->attempts, $now, 'Вебхук не надійшов');
            $lead->update(['status' => $u['status'], 'next_call_at' => $u['next_call_at'], 'notes' => $u['notes']]);
            Call::where('lead_id', $lead->id)->where('status', 'initiated')->update(['status' => 'failed', 'last_error' => 'Вебхук не надійшов']);
            $n++;
        }
        return $n;
    }

    /**
     * Один прохід автонабору. Повертає масив рядків для логу.
     * @param bool $force  ігнорувати вимикач «enabled» і робочий час (кнопка «Подзвонити зараз»)
     */
    public function tick(array $settings, Carbon $now = null, bool $force = false, Lead $only = null): array
    {
        $now = $now ?: Carbon::now();
        $log = [];
        $recovered = $this->recoverStuck($settings, $now);
        if ($recovered) {
            $log[] = "Повернуто в чергу завислих: {$recovered}";
        }

        if (!$force && empty($settings['enabled'])) {
            return $log;
        }
        if (!$force && !CallSchedule::fromSettings($settings)->isCallingTime($now)) {
            return $log;
        }

        $leftToday = $settings['max_calls_per_day'] - self::callsToday($now);
        $freeLines = $settings['max_concurrent_calls'] - self::activeCalls($now);
        $n = min($leftToday, $freeLines);
        if ($n <= 0) {
            return $log;
        }

        $dryRun = !empty($settings['dry_run']);
        if (!$dryRun && !ElevenLabsClient::isConfigured()) {
            $log[] = 'ElevenLabs не налаштовано (.env): дзвінки не ініціюються';
            return $log;
        }

        $leads = $only ? collect([$only]) : self::dueQuery($now, $settings['max_attempts'])->limit($n)->get();
        $rules = OutcomeRules::fromSettings($settings);

        foreach ($leads as $lead) {
            if (DoNotCall::has($lead->phone)) {
                continue;
            }
            $label = "{$lead->phone} ({$lead->store_name}), спроба " . ($lead->attempts + 1);
            if ($dryRun) {
                $log[] = "[тестовий режим] Подзвонив би: {$label}";
                continue;
            }

            // Спершу резервуємо лід, щоб паралельний прохід не подзвонив двічі
            $reserved = Lead::where('id', $lead->id)->where('status', '!=', 'calling')
                ->update(['status' => 'calling', 'attempts' => $lead->attempts + 1, 'last_called_at' => $now]);
            if (!$reserved) {
                continue;
            }
            $lead->refresh();

            try {
                $r = $this->client->outboundCall($lead, $settings['ringing_timeout_secs']);
                $convId = $r['conversation_id'] ?? ('no-id-' . $lead->id . '-' . $now->timestamp);
                Call::firstOrCreate(['conversation_id' => $convId], [
                    'lead_id' => $lead->id,
                    'started_at' => $now,
                    'call_date' => $now->copy()->setTimezone(CallSchedule::tz())->toDateString(),
                    'status' => 'initiated',
                ]);
                $lead->update(['conversation_id' => $convId]);
                $log[] = "Дзвінок: {$label} → {$convId}";
            } catch (\Throwable $e) {
                $u = $rules->notReached($lead->attempts, $now, 'Помилка ініціації: ' . mb_substr($e->getMessage(), 0, 300));
                $lead->update(['status' => $u['status'], 'next_call_at' => $u['next_call_at'], 'notes' => $u['notes']]);
                $log[] = "Помилка дзвінка {$label}: " . $e->getMessage();
            }
        }
        return $log;
    }
}
