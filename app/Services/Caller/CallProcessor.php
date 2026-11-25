<?php

namespace App\Services\Caller;

use App\Models\Caller\Call;
use App\Models\Caller\DoNotCall;
use App\Models\Caller\Lead;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Обробка post-call вебхуків ElevenLabs.
 * Вебхук лише зберігає подію (швидка відповідь 200), а аналіз через Claude
 * робить caller:process щохвилини — на shared-хостингу немає воркера черг.
 */
class CallProcessor
{
    const MAX_PROCESS_ATTEMPTS = 3;

    /** @var CallAnalyzer */
    private $analyzer;
    /** @var InstructionMailer */
    private $mailer;

    public function __construct(CallAnalyzer $analyzer = null, InstructionMailer $mailer = null)
    {
        $this->analyzer = $analyzer ?: new CallAnalyzer();
        $this->mailer = $mailer ?: new InstructionMailer();
    }

    /**
     * Приймає подію з вебхука. Повертає короткий опис для логу.
     * call_initiation_failure обробляється одразу (без Claude), транскрипти — ставляться в чергу.
     */
    public function receive(array $ev, array $settings, Carbon $now = null): string
    {
        $now = $now ?: Carbon::now();
        $type = $ev['type'] ?? '';
        $data = $ev['data'] ?? [];
        $convId = (string) ($data['conversation_id'] ?? '');
        if ($convId === '') {
            return 'подія без conversation_id';
        }

        $call = Call::where('conversation_id', $convId)->first();
        $lead = $call && $call->lead_id ? Lead::find($call->lead_id) : null;
        if (!$lead && !empty($data['user_id'])) {
            $lead = Lead::where('phone', $data['user_id'])->first();
        }
        if (!$lead) {
            return "невідомий дзвінок {$convId}";
        }
        if ($call && !in_array($call->status, ['initiated'], true)) {
            return "дубль {$convId}";
        }
        $call = $call ?: new Call([
            'conversation_id' => $convId,
            'lead_id' => $lead->id,
            'started_at' => $now,
            'call_date' => $now->copy()->setTimezone(CallSchedule::tz())->toDateString(),
        ]);

        if ($type === 'call_initiation_failure') {
            $reason = $data['failure_reason'] ?? 'unknown';
            $u = OutcomeRules::fromSettings($settings)->notReached($lead->attempts, $now, "Не додзвонилися: {$reason}");
            $lead->update(['status' => $u['status'], 'next_call_at' => $u['next_call_at'], 'notes' => $u['notes']]);
            $call->fill(['status' => 'failed', 'outcome' => mb_substr($reason, 0, 32)])->save();
            return "{$lead->phone}: недодзвін ({$reason}) → {$u['status']}";
        }

        if ($type !== 'post_call_transcription') {
            return "пропущено {$type}";
        }

        $meta = $data['metadata'] ?? [];
        if (!empty($meta['start_time_unix_secs'])) {
            $call->started_at = Carbon::createFromTimestamp((int) $meta['start_time_unix_secs']);
        }
        $call->fill([
            'status' => 'received',
            'duration_secs' => isset($meta['call_duration_secs']) ? (int) $meta['call_duration_secs'] : null,
            'transcript_json' => json_encode($data['transcript'] ?? [], JSON_UNESCAPED_UNICODE),
            'raw_event' => json_encode(['metadata' => $meta, 'analysis' => $data['analysis'] ?? null], JSON_UNESCAPED_UNICODE),
        ])->save();
        return "{$lead->phone}: транскрипт прийнято, чекає аналізу";
    }

    /** Аналізує прийняті транскрипти. Повертає рядки для логу. */
    public function processReceived(array $settings, int $limit = 10, Carbon $now = null): array
    {
        $now = $now ?: Carbon::now();
        $log = [];
        $calls = Call::where('status', 'received')->where('process_attempts', '<', self::MAX_PROCESS_ATTEMPTS)
            ->orderBy('id')->limit($limit)->get();
        foreach ($calls as $call) {
            try {
                $log[] = $this->processOne($call, $settings, $now);
            } catch (\Throwable $e) {
                $attempts = $call->process_attempts + 1;
                $call->update([
                    'process_attempts' => $attempts,
                    'last_error' => mb_substr($e->getMessage(), 0, 1000),
                    'status' => $attempts >= self::MAX_PROCESS_ATTEMPTS ? 'failed' : 'received',
                ]);
                Log::error('[caller] аналіз дзвінка ' . $call->conversation_id . ': ' . $e->getMessage());
                $log[] = "Помилка аналізу {$call->conversation_id}: " . $e->getMessage();
            }
        }
        return $log;
    }

    public function processOne(Call $call, array $settings, Carbon $now): string
    {
        $lead = Lead::findOrFail($call->lead_id);
        $a = $this->analyzer->analyze($call->transcript(), (string) $lead->store_name, $call->started_at ?: $now);
        $u = OutcomeRules::fromSettings($settings)->apply($lead->attempts, $a, $now);

        if ($u['dnc']) {
            DoNotCall::add($lead->phone, $a['summary']);
        }
        $patch = [
            'status' => $u['status'],
            'next_call_at' => $u['next_call_at'],
            'outcome' => $a['outcome'],
            'notes' => $u['notes'],
        ];
        if ($a['email'] !== '') {
            $patch['email'] = $a['email'];
        }
        if ($a['contact_name'] !== '' && !$lead->contact_name) {
            $patch['contact_name'] = $a['contact_name'];
        }
        if ($a['has_merchant_feed'] !== 'unknown') {
            $patch['has_merchant_feed'] = $a['has_merchant_feed'];
        }
        $lead->update($patch);

        $call->update([
            'status' => 'completed',
            'outcome' => $a['outcome'],
            'analysis_json' => json_encode($a, JSON_UNESCAPED_UNICODE),
            'process_attempts' => $call->process_attempts + 1,
            'last_error' => null,
        ]);

        $msg = "{$lead->phone}: {$a['outcome']} → {$u['status']}" . ($a['email'] ? " ({$a['email']})" : '');
        if (!empty($settings['email_enabled']) && !$u['dnc']) {
            $st = $this->mailer->enqueue($lead->fresh(), $a, $settings);
            if ($st) {
                $msg .= ", лист: {$st}";
            }
        }
        if (!empty($a['quality_flags'])) {
            $msg .= ' ⚠ якість: ' . implode('; ', $a['quality_flags']);
        }
        return $msg;
    }
}
