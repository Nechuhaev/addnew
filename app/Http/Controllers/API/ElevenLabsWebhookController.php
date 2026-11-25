<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Caller\CallerSettings;
use App\Services\Caller\CallProcessor;
use App\Services\Caller\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Post-call вебхук ElevenLabs Agents. Перевіряє HMAC-підпис, зберігає подію й одразу
 * відповідає 200. Аналіз розмови через Claude робить caller:run (щохвилини).
 */
class ElevenLabsWebhookController extends Controller
{
    public function handle(Request $request, CallProcessor $processor)
    {
        $raw = $request->getContent();
        $ok = Util::verifyElevenLabsSignature(
            $raw,
            $request->header('ElevenLabs-Signature'),
            config('caller.elevenlabs.webhook_secret')
        );
        if (!$ok) {
            return response('bad signature', 401);
        }

        $ev = json_decode($raw, true);
        if (!is_array($ev)) {
            return response('bad json', 400);
        }

        try {
            $msg = $processor->receive($ev, CallerSettings::all());
            Log::info('[caller-webhook] ' . $msg);
        } catch (\Throwable $e) {
            // 500 → ElevenLabs повторить доставку
            Log::error('[caller-webhook] ' . $e->getMessage());
            return response('error', 500);
        }
        return response('ok', 200);
    }
}
