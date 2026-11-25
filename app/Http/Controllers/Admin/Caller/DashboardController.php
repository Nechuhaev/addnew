<?php

namespace App\Http\Controllers\Admin\Caller;

use App\Http\Controllers\Controller;
use App\Models\Caller\Call;
use App\Models\Caller\InstructionEmail;
use App\Models\Caller\Lead;
use App\Services\Caller\CallerSettings;
use App\Services\Caller\CallProcessor;
use App\Services\Caller\CallSchedule;
use App\Services\Caller\Dialer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $settings = CallerSettings::all();
        $now = Carbon::now();
        $since = $now->copy()->subDays(13)->startOfDay();

        $byStatus = Lead::select('status', DB::raw('COUNT(*) n'))->groupBy('status')->pluck('n', 'status')->toArray();

        $daily = Call::select(
            'call_date',
            DB::raw('COUNT(*) total'),
            DB::raw("SUM(status = 'completed' AND outcome NOT IN ('voicemail','no_conversation')) talked"),
            DB::raw("SUM(outcome = 'agreed') agreed"),
            DB::raw("SUM(outcome = 'send_info') send_info"),
            DB::raw('ROUND(COALESCE(SUM(duration_secs), 0) / 60, 1) minutes')
        )->where('call_date', '>=', $since->toDateString())
            ->groupBy('call_date')->orderByDesc('call_date')->get();

        $outcomes = Call::where('status', 'completed')->where('started_at', '>=', $now->copy()->subDays(30))
            ->select('outcome', DB::raw('COUNT(*) n'))->groupBy('outcome')->orderByDesc('n')->pluck('n', 'outcome')->toArray();

        $totals = [
            'calls_today' => Dialer::callsToday($now),
            'active' => Dialer::activeCalls($now),
            'due' => Dialer::dueQuery($now, $settings['max_attempts'])->count(),
            'talked_30d' => Call::where('status', 'completed')->where('started_at', '>=', $now->copy()->subDays(30))
                ->whereNotIn('outcome', ['voicemail', 'no_conversation'])->count(),
            'agreed_30d' => $outcomes['agreed'] ?? 0,
            'registered' => Lead::whereNotNull('registered_user_id')->count(),
            'emails_review' => InstructionEmail::where('status', 'review')->count(),
            'emails_sent' => InstructionEmail::where('status', 'sent')->count(),
            'analysis_failed' => Call::where('status', 'failed')->whereNotNull('transcript_json')->count(),
        ];

        $qualityIssues = Call::where('status', 'completed')->where('analysis_json', 'like', '%"quality_flags":["%')
            ->with('lead')->orderByDesc('started_at')->limit(5)->get();

        return view('admin.caller.dashboard', [
            'settings' => $settings,
            'health' => CallerSettings::health(),
            'callingNow' => CallSchedule::fromSettings($settings)->isCallingTime($now),
            'byStatus' => $byStatus,
            'daily' => $daily,
            'outcomes' => $outcomes,
            'totals' => $totals,
            'qualityIssues' => $qualityIssues,
            'webhookUrl' => route('webhooks.elevenlabs'),
        ]);
    }

    /** Ручний прохід (аналіз + набір) — зручно для перевірки, не чекаючи cron. */
    public function runNow(CallProcessor $processor, Dialer $dialer)
    {
        $settings = CallerSettings::all();
        $log = array_merge($processor->processReceived($settings), $dialer->tick($settings));
        return redirect()->route('admin.caller')
            ->with('success', $log ? implode("\n", $log) : 'Прохід виконано: нічого робити (поза робочим часом, вимкнено або черга порожня).');
    }
}
