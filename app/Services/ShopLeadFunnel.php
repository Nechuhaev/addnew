<?php

namespace App\Services;

use App\ShopLead;
use App\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Воронка кандидатів: усього → лист надіслано → зареєструвався → завантажив товари.
 *
 * Кандидат «зареєструвався», якщо на сайті є магазин, що збігається з ним:
 * той самий email, домен сайту магазину = домен кандидата, або email магазину
 * на домені кандидата (info@shop.ua ↔ shop.ua). Ручний статус «Підключились»
 * теж рахується як реєстрація (якщо збіг не знайшовся автоматично).
 * «Завантажив товари» — у знайденого магазину є хоч один товар (is_product).
 */
class ShopLeadFunnel
{
    const STAGES = [
        'sent' => 'Лист надіслано',
        'registered' => 'Зареєструвались',
        'with_products' => 'Завантажили товари',
    ];

    /**
     * @param Collection|ShopLead[] $leads усі кандидати
     * @return array [lead_id => ['sent_at' => ?string, 'shop' => ?User, 'registered' => bool,
     *                'after_letter' => bool, 'products' => int, 'active' => int]]
     */
    public static function build(Collection $leads): array
    {
        if ($leads->isEmpty()) {
            return [];
        }

        // Перший успішно надісланий лист кожному кандидату
        $sentAt = DB::table('shop_lead_messages')
            ->whereIn('shop_lead_id', $leads->pluck('id'))
            ->where('sent_successfully', 1)
            ->groupBy('shop_lead_id')
            ->selectRaw('shop_lead_id, MIN(created_at) AS first_sent')
            ->pluck('first_sent', 'shop_lead_id');

        // Кандидати на збіг: усі магазини + будь-які користувачі з email кандидатів
        $leadEmails = $leads->pluck('email')->filter()->map(function ($e) {
            return mb_strtolower(trim($e));
        })->unique()->values()->all();
        $users = User::where('is_shop_owner', 1)
            ->when($leadEmails, function ($q) use ($leadEmails) {
                $q->orWhereIn('email', $leadEmails);
            })
            ->get(['id', 'email', 'site_url', 'firstname', 'lastname', 'is_shop_owner', 'created_at']);

        $byEmail = [];
        $bySiteDomain = [];
        $byEmailDomain = [];
        foreach ($users as $u) {
            $email = mb_strtolower(trim((string) $u->email));
            if ($email !== '') {
                $byEmail[$email] = $byEmail[$email] ?? $u;
                if ($u->is_shop_owner && ($at = strrpos($email, '@')) !== false) {
                    $d = preg_replace('/^www\./', '', substr($email, $at + 1));
                    $byEmailDomain[$d] = $byEmailDomain[$d] ?? $u;
                }
            }
            if ($u->is_shop_owner && $u->site_url && ($d = ShopLead::domainOf($u->site_url))) {
                $bySiteDomain[$d] = $bySiteDomain[$d] ?? $u;
            }
        }

        $matched = [];
        foreach ($leads as $lead) {
            $email = mb_strtolower(trim((string) $lead->email));
            $matched[$lead->id] = ($email !== '' ? ($byEmail[$email] ?? null) : null)
                ?? $bySiteDomain[$lead->domain] ?? $byEmailDomain[$lead->domain] ?? null;
        }

        // Товари знайдених магазинів: усього й активних
        $shopIds = collect($matched)->filter()->pluck('id')->unique()->values()->all();
        $products = $shopIds
            ? DB::table('ads')->whereIn('user_id', $shopIds)->where('is_product', 1)
                ->groupBy('user_id')
                ->selectRaw('user_id, COUNT(*) AS total, SUM(status = 1) AS active')
                ->get()->keyBy('user_id')
            : collect();

        $out = [];
        foreach ($leads as $lead) {
            $shop = $matched[$lead->id];
            $sent = $sentAt[$lead->id] ?? null;
            $p = $shop ? ($products[$shop->id] ?? null) : null;
            $out[$lead->id] = [
                'sent_at' => $sent,
                'shop' => $shop,
                'registered' => $shop !== null || $lead->status === 'joined',
                // Магазин з'явився після першого листа — реєстрацію можна приписати листу
                'after_letter' => $shop && $sent && $shop->created_at && $shop->created_at->gte($sent),
                'products' => $p ? (int) $p->total : 0,
                'active' => $p ? (int) $p->active : 0,
            ];
        }

        return $out;
    }

    /**
     * Лічильники етапів воронки.
     */
    public static function counts(array $funnel): array
    {
        $c = ['all' => count($funnel), 'sent' => 0, 'registered' => 0, 'sent_registered' => 0, 'registered_after_letter' => 0, 'with_products' => 0];
        foreach ($funnel as $f) {
            $c['sent'] += $f['sent_at'] ? 1 : 0;
            $c['registered'] += $f['registered'] ? 1 : 0;
            $c['sent_registered'] += ($f['sent_at'] && $f['registered']) ? 1 : 0;
            $c['registered_after_letter'] += $f['after_letter'] ? 1 : 0;
            $c['with_products'] += $f['products'] > 0 ? 1 : 0;
        }

        return $c;
    }

    /**
     * Чи кандидат пройшов етап (для фільтра).
     */
    public static function reached(array $f, string $stage): bool
    {
        switch ($stage) {
            case 'sent':
                return (bool) $f['sent_at'];
            case 'registered':
                return $f['registered'];
            case 'with_products':
                return $f['products'] > 0;
            case 'not_sent':
                return !$f['sent_at'];
            case 'sent_not_registered':
                return $f['sent_at'] && !$f['registered'];
            case 'registered_no_products':
                return $f['registered'] && $f['products'] === 0;
        }

        return true;
    }
}
