<?php

namespace App\Services\Caller;

use App\BlockedEmail;
use App\Mail\CallerInstruction;
use App\Models\Caller\DoNotCall;
use App\Models\Caller\InstructionEmail;
use App\Models\Caller\Lead;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Листи з інструкцією тим, хто погодився під час дзвінка.
 * Відправка — через звичайну пошту сайту (config/mail.php, MAIL_* у .env).
 */
class InstructionMailer
{
    const MAX_SEND_ATTEMPTS = 3;

    /**
     * Ставить лист у чергу після дзвінка. Повертає статус або null, якщо лист не потрібен.
     * На перевірку (review) ідуть сумнівні адреси, щоб інструкція не пішла на чужу пошту.
     */
    public function enqueue(Lead $lead, array $a, array $settings): ?string
    {
        if (!in_array($a['outcome'], ['agreed', 'send_info'], true)) {
            return null;
        }
        $email = mb_strtolower(trim((string) $a['email']));
        if ($email === '') {
            return null;
        }
        if (InstructionEmail::where('lead_id', $lead->id)->where('template', 'instruction')->exists()) {
            return null;
        }

        list($status, $reason) = $this->classify($email, !empty($a['email_uncertain']), $settings);

        InstructionEmail::create([
            'lead_id' => $lead->id,
            'to_email' => $email,
            'template' => 'instruction',
            'status' => $status,
            'has_merchant_feed' => $a['has_merchant_feed'] ?? 'unknown',
            'contact_name' => ($a['contact_name'] ?? '') ?: (string) $lead->contact_name,
            'reason' => $reason,
        ]);
        return $status;
    }

    /** @return array [status, reason] */
    public function classify(string $email, bool $uncertain, array $settings): array
    {
        if (!Util::isValidEmail($email)) {
            return ['review', 'адреса невалідна'];
        }
        if ($this->isBlockedDomain($email)) {
            return ['review', 'поштовий сервіс у списку заблокованих на сайті'];
        }
        if (User::where('email', $email)->exists()) {
            return ['skipped', 'користувач з цим email уже зареєстрований'];
        }
        if ($uncertain) {
            return ['review', 'адресу розпізнано з сумнівом'];
        }
        if (!empty($settings['email_require_review'])) {
            return ['review', 'увімкнено ручне схвалення'];
        }
        return ['pending', null];
    }

    public function isBlockedDomain(string $email): bool
    {
        $domain = mb_strtolower(substr(strrchr($email, '@') ?: '', 1));
        if ($domain === '') {
            return false;
        }
        // У blocked_emails.mailbox зберігається домен поштового сервісу (з «@» або без)
        return BlockedEmail::whereRaw('LOWER(TRIM(mailbox)) IN (?, ?)', [$domain, '@' . $domain])->exists();
    }

    public function sentLastHour(Carbon $now): int
    {
        return InstructionEmail::where('status', 'sent')->where('sent_at', '>=', $now->copy()->subHour())->count();
    }

    /** Відправляє листи з черги з урахуванням ліміту на годину. Повертає кількість відправлених. */
    public function sendPending(array $settings, Carbon $now = null, callable $log = null): int
    {
        $now = $now ?: Carbon::now();
        $log = $log ?: function ($m) { Log::info('[caller-mail] ' . $m); };

        $budget = $settings['email_max_per_hour'] - $this->sentLastHour($now);
        if ($budget <= 0) {
            return 0;
        }

        $sent = 0;
        $rows = InstructionEmail::with('lead')->where('status', 'pending')->orderBy('id')->limit($budget)->get();
        foreach ($rows as $row) {
            $lead = $row->lead;
            if (!$lead || DoNotCall::has($lead->phone)) {
                $row->update(['status' => 'skipped', 'last_error' => $lead ? 'у списку «не дзвонити»' : 'лід видалено']);
                continue;
            }
            try {
                Mail::to($row->to_email)->send($this->mailable($row, $lead, $settings));
                $row->update(['status' => 'sent', 'sent_at' => $now, 'attempts' => $row->attempts + 1, 'last_error' => null]);
                $sent++;
                $log("#{$row->id} → {$row->to_email} ({$lead->store_name})");
            } catch (\Throwable $e) {
                $attempts = $row->attempts + 1;
                $row->update([
                    'status' => $attempts >= self::MAX_SEND_ATTEMPTS ? 'failed' : 'pending',
                    'attempts' => $attempts,
                    'last_error' => mb_substr($e->getMessage(), 0, 1000),
                ]);
                $log("Помилка #{$row->id} → {$row->to_email}: " . $e->getMessage());
            }
        }
        return $sent;
    }

    public function mailable(InstructionEmail $row, Lead $lead, array $settings): CallerInstruction
    {
        return new CallerInstruction(
            (string) $row->contact_name,
            (string) $lead->store_name,
            (string) $row->has_merchant_feed,
            $settings
        );
    }

    /**
     * Відповіли «відписатися»: за email або телефоном → у «не дзвонити»,
     * невідправлені листи скасовуються. Повертає кількість лідів.
     */
    public function unsubscribe(string $emailOrPhone): int
    {
        $key = mb_strtolower(trim($emailOrPhone));
        $phones = [];
        $phone = Util::normalizeUaPhone($key);
        if ($phone && Lead::where('phone', $phone)->exists()) {
            $phones[] = $phone;
        }
        if (strpos($key, '@') !== false) {
            $leadIds = InstructionEmail::where('to_email', $key)->pluck('lead_id');
            $byEmail = Lead::where('email', $key)->orWhereIn('id', $leadIds)->pluck('phone')->all();
            $phones = array_merge($phones, $byEmail);
        }
        $phones = array_unique($phones);
        foreach ($phones as $p) {
            DoNotCall::add($p, 'відписався від листів');
        }
        return count($phones);
    }
}
