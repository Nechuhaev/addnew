<?php

namespace App\Console\Commands;

use App\Services\SeoRules;
use App\StopWord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Оновлює ads.is_sensitive (чутливий вміст — SeoRules::isSensitiveAd).
 * Щогодини: лише оголошення, змінені з минулого запуску (імпорт фідів пише в БД напряму,
 * без подій моделі). Якщо змінився список noindex-стоп-слів або чутливих категорій —
 * автоматично перераховує всі оголошення. --full — перерахувати всі примусово.
 */
class MarkSensitiveAds extends Command
{
    protected $signature = 'ads:mark-sensitive {--full : Перерахувати всі оголошення}';

    protected $description = 'Позначає оголошення з чутливим вмістом (ads.is_sensitive) для фільтра списків';

    const LAST_RUN_KEY = 'ads_mark_sensitive_last_run';
    const RULES_HASH_KEY = 'ads_mark_sensitive_rules_hash';

    public function handle()
    {
        $now = now();
        $hash = md5(json_encode([StopWord::noindexWords(), SeoRules::sensitiveCategoryIds()]));
        $full = $this->option('full') || Cache::get(self::RULES_HASH_KEY) !== $hash || !Cache::has(self::LAST_RUN_KEY);

        $query = DB::table('ads')->select('id', 'name', 'content', 'category_id', 'is_sensitive')->orderBy('id');
        if (!$full) {
            $query->where('updated_at', '>=', Cache::get(self::LAST_RUN_KEY));
        }

        $checked = 0;
        $on = [];
        $off = [];
        $query->chunk(1000, function ($ads) use (&$checked, &$on, &$off) {
            foreach ($ads as $ad) {
                $checked++;
                $sensitive = SeoRules::isSensitiveAd($ad);
                if ($sensitive && !$ad->is_sensitive) {
                    $on[] = $ad->id;
                } elseif (!$sensitive && $ad->is_sensitive) {
                    $off[] = $ad->id;
                }
            }
        });

        // Масове оновлення без зміни updated_at (щоб не чіпати «дату оновлення» оголошень)
        foreach (array_chunk($on, 1000) as $ids) {
            DB::table('ads')->whereIn('id', $ids)->update(['is_sensitive' => 1]);
        }
        foreach (array_chunk($off, 1000) as $ids) {
            DB::table('ads')->whereIn('id', $ids)->update(['is_sensitive' => 0]);
        }

        Cache::forever(self::LAST_RUN_KEY, $now->toDateTimeString());
        Cache::forever(self::RULES_HASH_KEY, $hash);

        $this->info(($full ? 'Повна перевірка' : 'Змінені оголошення') . ": перевірено {$checked}, позначено чутливими " . count($on) . ', знято позначку ' . count($off) . '.');

        return 0;
    }
}
