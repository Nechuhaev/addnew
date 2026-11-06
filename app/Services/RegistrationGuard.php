<?php

namespace App\Services;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Захист форм реєстрації від ботів:
 *  1) приховане поле-пастка (людина його не бачить і не заповнює);
 *  2) форма відправлена не швидше ніж за MIN_SECONDS після відкриття (підписана мітка часу);
 *  3) з однієї IP — не більше REGISTRATION_IP_LIMIT акаунтів за 24 години (0 — вимкнено).
 */
class RegistrationGuard
{
    const TRAP_FIELD = 'company_website';
    const TS_FIELD = 'form_ts';
    const MIN_SECONDS = 3;

    /** Підписана мітка часу для прихованого поля форми */
    public static function token(): string
    {
        return Crypt::encryptString((string) time());
    }

    /** @throws ValidationException */
    public static function check(Request $request): void
    {
        $ip = (string) $request->ip();

        if (trim((string) $request->input(self::TRAP_FIELD, '')) !== '') {
            self::reject($request, 'trap', __('errors.registration_bot'));
        }

        try {
            $ts = (int) Crypt::decryptString((string) $request->input(self::TS_FIELD, ''));
        } catch (\Throwable $e) {
            $ts = 0;
        }
        if ($ts <= 0 || time() - $ts < self::MIN_SECONDS) {
            self::reject($request, 'too_fast', __('errors.registration_bot'));
        }

        $limit = (int) env('REGISTRATION_IP_LIMIT', 2);
        if ($limit > 0 && self::isPublicIp($ip)
            && User::where('register_ip', $ip)->where('created_at', '>=', now()->subDay())->count() >= $limit) {
            self::reject($request, 'ip_limit', __('errors.registration_ip_limit'));
        }
    }

    /** Приватні/службові адреси (проксі, localhost) не обмежуємо — інакше ліміт зачепив би всіх */
    public static function isPublicIp(string $ip): bool
    {
        return (bool) filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }

    protected static function reject(Request $request, string $reason, string $message): void
    {
        Log::warning('Registration blocked', [
            'reason' => $reason, 'ip' => $request->ip(), 'email' => (string) $request->input('email'), 'path' => $request->path(),
        ]);

        throw ValidationException::withMessages(['email' => $message]);
    }
}
