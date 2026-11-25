<?php

namespace App\Services\Caller;

use Carbon\Carbon;

/**
 * Аналіз транскрипту дзвінка через Claude: результат, email, час передзвону,
 * заперечення, контроль якості роботи бота.
 */
class CallAnalyzer
{
    const OUTCOMES = ['agreed', 'send_info', 'callback', 'referred', 'not_interested', 'do_not_call', 'voicemail', 'no_conversation'];

    const SYSTEM = <<<'TXT'
Ти аналізуєш транскрипти вихідних дзвінків голосової асистентки Addnew.biz власникам інтернет-магазинів.
Мета дзвінків — отримати згоду й email, щоб надіслати інструкцію з безкоштовного підключення магазину до Addnew.biz.

Правила класифікації outcome:
- agreed: людина погодилася підключитися або спробувати І назвала email.
- send_info: просить надіслати інформацію, але згоди підключатися ще немає (або email не назвала).
- callback: просить передзвонити пізніше / зараз незручно.
- referred: це не та людина; дали інший контакт або сказали, кого шукати.
- not_interested: ввічлива відмова.
- do_not_call: просить не дзвонити, видалити номер, грубить або явно роздратована. Має пріоритет над іншими.
- voicemail: автовідповідач, голосова пошта, IVR-меню.
- no_conversation: живої розмови не відбулося.

Email: якщо продиктовано словами («собачка», «крапка», по буквах), збери в нормальний вигляд у нижньому регістрі.
Якщо не впевнений у написанні — все одно запиши найімовірніший варіант, постав email_uncertain = true і згадай сумнів у next_action.
На email автоматично піде лист, тому сумнів краще позначити, ніж пропустити.
callback_at — розрахуй відносно часу дзвінка («завтра після обіду» → наступного дня 14:00), київський час, ISO 8601 зі зсувом.
Відповідай лише за даними транскрипту, нічого не вигадуй.
TXT;

    /** @var ClaudeClient */
    private $claude;

    public function __construct(ClaudeClient $claude = null)
    {
        $this->claude = $claude ?: new ClaudeClient();
    }

    public static function schema(): array
    {
        $str = function ($d) { return ['type' => 'string', 'description' => $d]; };
        return [
            'type' => 'object',
            'properties' => [
                'outcome' => ['type' => 'string', 'enum' => self::OUTCOMES],
                'email' => $str('Email співрозмовника в нижньому регістрі, або порожній рядок'),
                'email_uncertain' => ['type' => 'boolean', 'description' => 'true, якщо email розпізнано з сумнівом (не підтверджено, плутанина з буквами, обірвано); false, якщо підтверджено або email немає'],
                'contact_name' => $str("Ім'я співрозмовника, якщо прозвучало, або порожній рядок"),
                'callback_at' => $str('Коли передзвонити, ISO 8601 з київським зсувом (напр. 2026-11-26T15:00:00+02:00), або порожній рядок'),
                'referred_contact' => $str("Ім'я/телефон/email іншої відповідальної людини, або порожній рядок"),
                'has_merchant_feed' => ['type' => 'string', 'enum' => ['yes', 'no', 'unknown']],
                'objections' => ['type' => 'array', 'items' => ['type' => 'string'], 'description' => 'Заперечення співрозмовника, коротко українською'],
                'summary' => $str('1–2 речення українською: що сталося в дзвінку'),
                'next_action' => $str('Що має зробити менеджер далі, українською'),
                'quality_flags' => [
                    'type' => 'array', 'items' => ['type' => 'string'],
                    'description' => 'Порушення правил асистенткою: не сказала, що вона AI; вигадала факти; тиснула після відмови; продовжила після «не дзвоніть». Порожній масив, якщо все гаразд.',
                ],
            ],
            'required' => ['outcome', 'email', 'email_uncertain', 'contact_name', 'callback_at', 'referred_contact',
                'has_merchant_feed', 'objections', 'summary', 'next_action', 'quality_flags'],
            'additionalProperties' => false,
        ];
    }

    public static function empty(string $summary = 'Порожній транскрипт.'): array
    {
        return [
            'outcome' => 'no_conversation', 'email' => '', 'email_uncertain' => false, 'contact_name' => '',
            'callback_at' => '', 'referred_contact' => '', 'has_merchant_feed' => 'unknown', 'objections' => [],
            'summary' => $summary, 'next_action' => 'Повторити дзвінок.', 'quality_flags' => [],
        ];
    }

    /** @param array $turns [['role' => 'agent'|'user', 'message' => string], ...] */
    public static function formatTranscript(array $turns): string
    {
        $lines = [];
        foreach ($turns as $t) {
            $msg = trim((string) ($t['message'] ?? ''));
            if ($msg === '') {
                continue;
            }
            $lines[] = (($t['role'] ?? '') === 'agent' ? 'Асистентка' : 'Співрозмовник') . ': ' . $msg;
        }
        return implode("\n", $lines);
    }

    public function analyze(array $turns, string $storeName, Carbon $startedAt): array
    {
        $transcript = self::formatTranscript($turns);
        if ($transcript === '') {
            return self::empty();
        }
        $k = $startedAt->copy()->setTimezone(CallSchedule::tz());
        $days = ['понеділок', 'вівторок', 'середа', 'четвер', 'пʼятниця', 'субота', 'неділя'];
        $when = $k->format('Y-m-d H:i') . ' (' . $days[$k->dayOfWeekIso - 1] . ', Київ, зсув ' . $k->format('P') . ')';

        $resp = $this->claude->create(self::SYSTEM, [[
            'role' => 'user',
            'content' => "Магазин: " . ($storeName ?: 'невідомо') . "\nЧас дзвінка: {$when}\n\n<transcript>\n{$transcript}\n</transcript>",
        ]], self::schema(), 'low');

        $data = json_decode(ClaudeClient::text($resp), true);
        if (!is_array($data) || !in_array($data['outcome'] ?? null, self::OUTCOMES, true)) {
            throw new \RuntimeException('Не вдалося розібрати відповідь аналізу');
        }
        $data = array_merge(self::empty(''), $data);
        $data['email'] = mb_strtolower(trim($data['email']));
        return $data;
    }
}
