<?php

namespace App\Services\Caller;

use App\Models\Caller\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Робочі налаштування модуля дзвінків: значення з адмінки (caller_settings)
 * мають пріоритет над config/caller.php (.env). Секрети тут не зберігаються.
 */
class CallerSettings
{
    const CACHE_KEY = 'caller_settings_all';

    /** Опис полів для форми в адмінці: тип, підпис, підказка, межі. */
    public static function schema(): array
    {
        return [
            'Дзвінки' => [
                'enabled' => ['bool', 'Автонабір увімкнено', 'Головний вимикач. Вимкнено — жодних дзвінків.'],
                'dry_run' => ['bool', 'Тестовий режим (без реальних дзвінків)', 'Лише записує в лог, кому подзвонив би.'],
                'max_calls_per_day' => ['int', 'Дзвінків на день, макс.', '', 1, 2000],
                'max_concurrent_calls' => ['int', 'Одночасних дзвінків', 'Не більше ліміту вашого тарифу ElevenLabs.', 1, 20],
                'max_attempts' => ['int', 'Спроб додзвонитися до номера', '', 1, 10],
                'call_hours_start' => ['int', 'Дзвонимо з (година, Київ)', '', 0, 23],
                'call_hours_end' => ['int', 'Дзвонимо до (година, Київ)', 'Не включно: 18 = до 17:59.', 1, 24],
                'call_days' => ['days', 'Дні дзвінків', ''],
                'retry_after_minutes' => ['int', 'Пауза перед повтором, хв', 'Зростає з кожною спробою: 1×, 2×, 3×.', 15, 10080],
                'ringing_timeout_secs' => ['int', 'Скільки чекати відповіді, с', '', 10, 60],
            ],
            'Листи з інструкцією' => [
                'email_enabled' => ['bool', 'Автоматична розсилка інструкцій', 'Тим, хто погодився або попросив інформацію і назвав email.'],
                'email_require_review' => ['bool', 'Кожен лист — лише після ручного схвалення', 'Сумнівні адреси йдуть на перевірку завжди.'],
                'email_max_per_hour' => ['int', 'Листів на годину, макс.', '', 1, 500],
                'email_from_address' => ['email', 'Адреса відправника', 'Порожньо = MAIL_FROM_ADDRESS сайту. Краще адреса на домені addnew.biz.'],
                'email_from_name' => ['string', 'Ім\'я відправника', ''],
                'email_reply_to' => ['email', 'Адреса для відповідей', ''],
            ],
        ];
    }

    public static function all(): array
    {
        $defaults = config('caller.defaults', []);
        $stored = Cache::remember(self::CACHE_KEY, 300, function () {
            if (!Schema::hasTable('caller_settings')) {
                return [];
            }
            return Setting::pluck('value', 'key')->toArray();
        });
        $out = [];
        foreach (self::types() as $key => $type) {
            $raw = array_key_exists($key, $stored) ? $stored[$key] : ($defaults[$key] ?? null);
            $out[$key] = self::cast($type, $raw);
        }
        return $out;
    }

    public static function get(string $key)
    {
        $all = self::all();
        return $all[$key] ?? null;
    }

    public static function save(array $values): void
    {
        $types = self::types();
        foreach ($values as $key => $value) {
            if (!isset($types[$key])) {
                continue;
            }
            $v = self::cast($types[$key], $value);
            if (is_bool($v)) {
                $v = $v ? '1' : '0';
            } elseif (is_array($v)) {
                $v = implode(',', $v);
            }
            Setting::updateOrCreate(['key' => $key], ['value' => (string) $v]);
        }
        Cache::forget(self::CACHE_KEY);
    }

    public static function types(): array
    {
        $types = [];
        foreach (self::schema() as $fields) {
            foreach ($fields as $key => $def) {
                $types[$key] = $def[0];
            }
        }
        return $types;
    }

    public static function cast(string $type, $raw)
    {
        switch ($type) {
            case 'bool':
                return filter_var($raw, FILTER_VALIDATE_BOOLEAN);
            case 'int':
                return (int) $raw;
            case 'days':
                $days = is_array($raw) ? $raw : explode(',', (string) $raw);
                $days = array_values(array_unique(array_filter(array_map('intval', $days), function ($d) {
                    return $d >= 1 && $d <= 7;
                })));
                sort($days);
                return $days;
            default:
                return trim((string) $raw);
        }
    }

    /** Чи заповнені ключі, без яких модуль не працює (для дашборду). */
    public static function health(): array
    {
        $el = config('caller.elevenlabs');
        return [
            'ANTHROPIC_API_KEY' => !empty(config('caller.claude.api_key')),
            'ELEVENLABS_API_KEY' => !empty($el['api_key']),
            'ELEVENLABS_AGENT_ID' => !empty($el['agent_id']),
            'ELEVENLABS_PHONE_NUMBER_ID' => !empty($el['phone_number_id']),
            'ELEVENLABS_WEBHOOK_SECRET' => !empty($el['webhook_secret']),
            'MAIL_HOST' => !empty(config('mail.host')) && config('mail.host') !== 'smtp.mailgun.org',
        ];
    }
}
