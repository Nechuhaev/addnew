<?php

/*
|--------------------------------------------------------------------------
| AI-дзвінки інтернет-магазинам
|--------------------------------------------------------------------------
| Секрети (ключі API) — ЛИШЕ в .env. Робочі параметри (ліміти, години,
| розсилка) мають значення за замовчуванням тут і змінюються в адмінці
| (/admin/caller/settings) — значення з адмінки мають пріоритет.
*/

return [

    'elevenlabs' => [
        'api_key' => env('ELEVENLABS_API_KEY'),
        'agent_id' => env('ELEVENLABS_AGENT_ID'),
        'phone_number_id' => env('ELEVENLABS_PHONE_NUMBER_ID'),
        'webhook_secret' => env('ELEVENLABS_WEBHOOK_SECRET'),
        'base_url' => env('ELEVENLABS_BASE_URL', 'https://api.elevenlabs.io'),
    ],

    // Ключ спільний з рештою сайту (ANTHROPIC_API_KEY), модель — окрема
    'claude' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
        'model' => env('CALLER_CLAUDE_MODEL', 'claude-opus-5-5'),
    ],

    // Значення за замовчуванням для налаштувань, що редагуються в адмінці
    'defaults' => [
        'enabled' => env('CALLER_ENABLED', false),
        'dry_run' => env('CALLER_DRY_RUN', true),
        'max_calls_per_day' => (int) env('CALLER_MAX_CALLS_PER_DAY', 100),
        'max_concurrent_calls' => (int) env('CALLER_MAX_CONCURRENT_CALLS', 2),
        'max_attempts' => (int) env('CALLER_MAX_ATTEMPTS', 3),
        'call_hours_start' => (int) env('CALLER_HOURS_START', 10),
        'call_hours_end' => (int) env('CALLER_HOURS_END', 18),
        'call_days' => env('CALLER_DAYS', '1,2,3,4,5'),           // 1 = пн … 7 = нд
        'retry_after_minutes' => (int) env('CALLER_RETRY_AFTER_MINUTES', 180),
        'ringing_timeout_secs' => (int) env('CALLER_RINGING_TIMEOUT', 30),

        'email_enabled' => env('CALLER_EMAIL_ENABLED', false),
        'email_require_review' => env('CALLER_EMAIL_REQUIRE_REVIEW', false),
        'email_max_per_hour' => (int) env('CALLER_EMAIL_MAX_PER_HOUR', 30),
        'email_from_address' => env('CALLER_EMAIL_FROM_ADDRESS', ''),  // порожньо = MAIL_FROM_ADDRESS
        'email_from_name' => env('CALLER_EMAIL_FROM_NAME', 'Addnew.biz'),
        'email_reply_to' => env('CALLER_EMAIL_REPLY_TO', 'addnew.feedback@gmail.com'),

    ],

    'timezone' => 'Europe/Kiev',
];
