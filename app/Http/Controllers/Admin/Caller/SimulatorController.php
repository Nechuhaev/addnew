<?php

namespace App\Http\Controllers\Admin\Caller;

use App\Http\Controllers\Controller;
use App\Services\Caller\CallAnalyzer;
use App\Services\Caller\ClaudeClient;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Текстовий тренажер: адмін грає власника магазину, Claude — асистентку Марію з тим самим
 * системним промптом, що й у голосовому агенті. Дозволяє відшліфувати сценарій без телефонії.
 */
class SimulatorController extends Controller
{
    const SESSION = 'caller_simulator';

    public function index(Request $request)
    {
        $state = $request->session()->get(self::SESSION);
        return view('admin.caller.simulator', [
            'state' => $state,
            'configured' => ClaudeClient::isConfigured(),
        ]);
    }

    public function reset(Request $request)
    {
        $vars = $request->validate([
            'store_name' => 'required|string|max:120',
            'store_site' => 'nullable|string|max:120',
            'contact_name' => 'nullable|string|max:80',
            'category' => 'nullable|string|max:120',
        ]);
        $vars = array_map(function ($v) { return (string) $v; }, $vars + ['store_site' => '', 'contact_name' => '', 'category' => '']);
        $first = self::fill(trim(@file_get_contents(resource_path('caller/first_message.txt')) ?: ''), $vars);

        // Дзвінок з'єднано: співрозмовник каже «Алло», асистентка відповідає першою фразою
        $request->session()->put(self::SESSION, [
            'vars' => $vars,
            'messages' => [
                ['role' => 'user', 'content' => 'Алло?'],
                ['role' => 'assistant', 'content' => $first],
            ],
            'transcript' => [
                ['role' => 'user', 'message' => 'Алло?'],
                ['role' => 'agent', 'message' => $first],
            ],
        ]);
        return redirect()->route('admin.caller.simulator');
    }

    public function message(Request $request, ClaudeClient $claude)
    {
        $request->validate(['text' => 'required|string|max:1000']);
        $state = $request->session()->get(self::SESSION);
        if (!$state) {
            return response()->json(['error' => 'Спершу почніть розмову'], 422);
        }
        $text = trim($request->get('text'));
        $state['messages'][] = ['role' => 'user', 'content' => $text];

        try {
            $system = self::fill(@file_get_contents(resource_path('caller/system_prompt.md')) ?: '', $state['vars']);
            // У живому дзвінку важлива швидкість відповіді → effort low
            $resp = $claude->create($system, $state['messages'], null, 'low');
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 502);
        }

        // Повертаємо весь content (разом із thinking-блоками) без змін
        $state['messages'][] = ['role' => 'assistant', 'content' => $resp['content']];
        $reply = ClaudeClient::text($resp);
        $state['transcript'][] = ['role' => 'user', 'message' => $text];
        $state['transcript'][] = ['role' => 'agent', 'message' => $reply];
        $request->session()->put(self::SESSION, $state);

        return response()->json(['reply' => $reply]);
    }

    public function analyze(Request $request, CallAnalyzer $analyzer)
    {
        $state = $request->session()->get(self::SESSION);
        if (!$state) {
            return response()->json(['error' => 'Розмови немає'], 422);
        }
        try {
            $a = $analyzer->analyze($state['transcript'], $state['vars']['store_name'], Carbon::now());
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 502);
        }
        return response()->json(['analysis' => $a]);
    }

    public static function fill(string $tpl, array $vars): string
    {
        return preg_replace_callback('/\{\{\s*(\w+)\s*\}\}/', function ($m) use ($vars) {
            return $vars[$m[1]] ?? '';
        }, $tpl);
    }
}
