<?php

namespace App\Services\Caller;

use App\Models\Caller\Lead;
use App\User;

/**
 * Конверсія дзвінків: магазин, з яким говорили, зареєструвався на сайті.
 * Звіряємо email з розмови і домен сайту з користувачами-магазинами.
 */
class ConversionTracker
{
    public function run(): int
    {
        $found = 0;
        $leads = Lead::whereNull('registered_user_id')->whereNotNull('last_called_at')->get();
        if ($leads->isEmpty()) {
            return 0;
        }

        $byDomain = [];
        $byEmail = [];
        foreach (User::select('id', 'email', 'site_url', 'created_at')->get() as $u) {
            if ($u->email) {
                $byEmail[mb_strtolower($u->email)] = $u;
            }
            $d = Util::domain($u->site_url);
            if ($d !== '') {
                $byDomain[$d] = $u;
            }
        }

        foreach ($leads as $lead) {
            $user = null;
            if ($lead->email && isset($byEmail[mb_strtolower($lead->email)])) {
                $user = $byEmail[mb_strtolower($lead->email)];
            } elseif ($lead->store_site && isset($byDomain[Util::domain($lead->store_site)])) {
                $user = $byDomain[Util::domain($lead->store_site)];
            }
            // Рахуємо лише реєстрації ПІСЛЯ першого дзвінка
            if ($user && $user->created_at && $lead->last_called_at && $user->created_at->greaterThan($lead->created_at)) {
                $lead->update(['registered_user_id' => $user->id, 'registered_at' => $user->created_at]);
                $found++;
            }
        }
        return $found;
    }
}
