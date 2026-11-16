<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * php artisan tags:mark-noindex [--apply] [--unmark=12,34]
 *
 * Позначає теги, сторінки яких не мають сенсу в пошуку (ad_tags.is_noindex):
 *  1 — технічні теги магазинів (назва-домен чи slug фіда: дефіс, крапка, xn--, shop, market…, і ≥90% оголошень
 *      від одного магазину — вони дублюють сторінку магазину) та загальні слова («Привет», «Человека», «Товары»);
 *  2 — підозрілі теги ескорт-спаму під виглядом вакансій («Девушек», «Досуг», «Сопровождение»…): noindex і без AdSense.
 * Такі теги закриваються noindex, зникають із sitemap і з блоків тегів на сайті. Без --apply — лише перегляд.
 */
class MarkNoindexTags extends Command
{
    protected $signature = 'tags:mark-noindex {--apply} {--unmark=}';

    protected $description = 'Noindex для технічних тегів магазинів, загальних слів і підозрілих тегів';

    /** Загальні слова, не назви товарів (порівняння за ключем MergeDuplicateTags::key) */
    const GENERIC = [
        'привет', 'человека', 'человек', 'товары', 'сотрудников', 'общение', 'объявление', 'скидки', 'марки',
        'работк', 'жилье', 'житло', 'график', 'website', 'online', 'ua', 'old', 'drop', 'mailcom', 'красное',
        'купить', 'продам', 'продаю', 'недорого', 'дешево', 'новый', 'новая', 'бу', 'срочно',
    ];

    /** Ескорт-спам під виглядом вакансій — noindex + без AdSense */
    const SENSITIVE = [
        'девушек', 'девушка', 'девушки', 'женщина', 'женщин', 'досуг', 'сопровождение', 'длявзрослых', 'материнство',
    ];

    const SHOP_PATTERN = '~(^xn--|shop|market|store|magaz|optom|-opt$)~u';

    /** Справжні бренди з дефісом — не технічні теги */
    const BRANDS = ['dnipro-m', 'victor-reinz'];

    /**
     * Технічна назва: службові слова магазину; латинський слаг без пробілів з дефісом/крапкою;
     * або речення замість тегу (довше 40 символів). Кириличні слова з дефісом («мини-рынки») — не технічні.
     */
    public static function isTechnicalName(string $name): bool
    {
        $s = mb_strtolower(trim($name));
        if (in_array($s, self::BRANDS, true)) {
            return false;
        }
        if (preg_match(self::SHOP_PATTERN, $s) || mb_strlen($s) > 40) {
            return true;
        }

        return (bool) preg_match('~^[a-z0-9]+([._-][a-z0-9]+)+$~', $s);
    }

    public function handle()
    {
        if (!Schema::hasColumn('ad_tags', 'is_noindex')) {
            $this->error('Немає колонки ad_tags.is_noindex — спершу php artisan migrate');
            return 1;
        }
        if ($unmark = array_filter(array_map('intval', explode(',', (string) $this->option('unmark'))))) {
            DB::table('ad_tags')->whereIn('id', $unmark)->update(['is_noindex' => 0]);
            $this->forgetCaches();
            $this->info('Знято позначку з тегів: ' . implode(', ', $unmark));
            return 0;
        }

        $marks = $this->candidates();
        $byType = [1 => [], 2 => []];
        foreach ($marks as $id => [$level, $name, $ads, $reason]) {
            $byType[$level][] = "«{$name}» ({$ads} огол., {$reason})";
        }
        $this->info('Технічні й загальні теги (noindex): ' . count($byType[1]));
        foreach (array_slice($byType[1], 0, 80) as $line) {
            $this->line('  ' . $line);
        }
        $this->info('Підозрілі теги (noindex + без AdSense): ' . count($byType[2]));
        foreach ($byType[2] as $line) {
            $this->line('  ' . $line);
        }

        if (!$this->option('apply')) {
            $this->warn('Це попередній перегляд. Щоб позначити — з --apply. Зняти позначку пізніше: --unmark=id,id');
            return 0;
        }
        foreach ([1, 2] as $level) {
            $ids = array_keys(array_filter($marks, function ($m) use ($level) {
                return $m[0] === $level;
            }));
            foreach (array_chunk($ids, 1000) as $chunk) {
                DB::table('ad_tags')->whereIn('id', $chunk)->where('is_noindex', '<', $level)->update(['is_noindex' => $level]);
            }
        }
        $this->forgetCaches();
        $this->info('Позначено тегів: ' . count($marks) . '. Після цього: php artisan sitemap:rebuild');

        return 0;
    }

    /** [tag_id => [рівень, назва, активних оголошень, причина]] */
    protected function candidates(): array
    {
        $generic = array_flip(self::GENERIC);
        $sensitive = array_flip(self::SENSITIVE);
        $active = DB::table('ad_tag')->join('ads', 'ads.id', '=', 'ad_tag.ad_id')->where('ads.status', 1)
            ->groupBy('ad_tag.tag_id')->selectRaw('ad_tag.tag_id, COUNT(*) n, COUNT(DISTINCT ads.user_id) users')
            ->get()->keyBy('tag_id');

        $out = [];
        DB::table('ad_tags')->where('is_noindex', 0)->select('id', 'name')->orderBy('id')
            ->chunk(5000, function ($rows) use (&$out, $generic, $sensitive, $active) {
                foreach ($rows as $r) {
                    $key = MergeDuplicateTags::key((string) $r->name);
                    $n = (int) optional($active->get($r->id))->n;
                    if (isset($sensitive[$key])) {
                        $out[$r->id] = [2, $r->name, $n, 'підозрілий'];
                    } elseif (isset($generic[$key])) {
                        $out[$r->id] = [1, $r->name, $n, 'загальне слово'];
                    } elseif ($n >= 5 && self::isTechnicalName((string) $r->name)) {
                        $out[$r->id] = [1, $r->name, $n, 'технічний тег магазину'];
                    }
                }
            });
        // технічні теги — лише якщо ≥90% оголошень від одного магазину
        foreach ($out as $id => $m) {
            if ($m[3] !== 'технічний тег магазину') {
                continue;
            }
            $top = (int) DB::table('ad_tag')->join('ads', 'ads.id', '=', 'ad_tag.ad_id')->where('ads.status', 1)
                ->where('ad_tag.tag_id', $id)->groupBy('ads.user_id')->selectRaw('COUNT(*) n')->orderByDesc('n')->value('n');
            if ($m[2] === 0 || $top / $m[2] < 0.9) {
                unset($out[$id]);
            }
        }
        uasort($out, function ($a, $b) {
            return $b[2] <=> $a[2];
        });

        return $out;
    }

    protected function forgetCaches(): void
    {
        foreach (['seo_index_summary_v2:tag', 'seo_our_noindex_ids:tag'] as $key) {
            Cache::forget($key);
        }
    }
}
