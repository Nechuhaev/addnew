<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * php artisan translations:audit [--limit=20]
 *
 * Що на українській версії сайту може показуватись російською:
 *  - переклади інтерфейсу (translations, locale=uk), де текст схожий на російський
 *    або порожній/збігається з ключем;
 *  - шаблони «SEO тексти» без UK-версії;
 *  - категорії, області, міста, теги, країни без name_uk / meta_title_uk / content_uk
 *    (при цьому RU-текст є — отже UK-сторінка покаже RU).
 * Нічого не змінює — лише звіт.
 */
class AuditTranslations extends Command
{
    protected $signature = 'translations:audit {--limit=20 : скільки прикладів показувати в кожному розділі}';

    protected $description = 'Звіт: що на UK-версії сайту ще не перекладено';

    // Літери, яких немає в українській абетці, та типові російські слова
    const RU_PATTERN = '/[ыэъёЫЭЪЁ]|(?<![\\p{L}])(и|или|что|это|объявлени\\p{L}*|поиск\\p{L}*|город\\p{L}*|страна|магазинов|предложени\\p{L}*|перейти к|показать)(?![\\p{L}])/iu';

    public static function looksRussian(?string $v): bool
    {
        return $v !== null && $v !== '' && preg_match(self::RU_PATTERN, strip_tags($v)) === 1;
    }

    public function handle()
    {
        $limit = (int) $this->option('limit');

        $this->section('Переклади інтерфейсу (translations, uk) — схожі на російські або порожні');
        // Перевірка в PHP (/u): REGEXP у MySQL 5.x порівнює побайтово й на кирилиці
        // дає хибні збіги майже для кожного рядка.
        $rows = DB::table('translations')->where('locale', 'uk')
            ->orderBy('group')->orderBy('key')->get(['group', 'key', 'value'])
            ->filter(function ($r) {
                $v = trim((string) $r->value);
                return $v === '' || $v === $r->key || self::looksRussian($v);
            });
        $this->report($rows->map(function ($r) {
            return [$r->group . '.' . $r->key, mb_strimwidth((string) $r->value, 0, 90, '…')];
        })->values()->all(), ['ключ', 'значення (uk)'], $limit);

        $this->section('Ключі, які є для ru, але відсутні для uk');
        $missing = DB::table('translations as r')
            ->leftJoin('translations as u', function ($j) {
                $j->on('u.group', '=', 'r.group')->on('u.key', '=', 'r.key')->where('u.locale', 'uk');
            })
            ->where('r.locale', 'ru')->whereNull('u.id')
            ->orderBy('r.group')->orderBy('r.key')->get(['r.group', 'r.key', 'r.value']);
        $this->report($missing->map(function ($r) {
            return [$r->group . '.' . $r->key, mb_strimwidth((string) $r->value, 0, 90, '…')];
        })->all(), ['ключ', 'значення (ru)'], $limit);

        $this->section('«SEO тексти» без української версії');
        $seo = [];
        foreach (DB::table('seo_fields')->orderBy('index')->get() as $f) {
            $empty = [];
            foreach (['meta_title', 'meta_description', 'description'] as $col) {
                if (trim((string) $f->$col) !== '' && trim((string) ($f->{$col . '_uk'} ?? '')) === '') {
                    $empty[] = $col . '_uk';
                }
            }
            if ($empty) {
                $seo[] = [$f->index, implode(', ', $empty)];
            }
        }
        $this->report($seo, ['шаблон (index)', 'порожні поля'], 100);

        foreach ([
            'ad_categories' => 'Категорії',
            'ad_countries' => 'Країни',
            'ad_regions' => 'Області',
            'ad_cities' => 'Міста',
            'ad_tags' => 'Теги',
        ] as $table => $title) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            $this->section("{$title}: є RU-текст, немає UK");
            $rows = [];
            $byCountry = in_array($table, ['ad_regions', 'ad_cities'], true);
            foreach (['name', 'meta_title', 'meta_description', 'content'] as $col) {
                if (!Schema::hasColumn($table, $col) || !Schema::hasColumn($table, $col . '_uk')) {
                    continue;
                }
                $q = DB::table($table . ' as t')->whereRaw("TRIM(COALESCE(t.`{$col}`, '')) <> ''")
                    ->whereRaw("TRIM(COALESCE(t.`{$col}_uk`, '')) = ''");

                if ($table === 'ad_tags' && $col === 'name') {
                    // Назви тегів — переважно бренди/моделі; рахуємо лише ті, що схожі на російські
                    $names = [];
                    $q->select('t.id', 't.name')->orderBy('t.id')->chunk(2000, function ($chunk) use (&$names) {
                        foreach ($chunk as $r) {
                            if (self::looksRussian($r->name)) {
                                $names[] = $r->name;
                            }
                        }
                    });
                    if ($names) {
                        $rows[] = [$col . '_uk (схожі на RU)', count($names), mb_strimwidth(implode(', ', array_slice($names, 0, 5)), 0, 80, '…')];
                    }
                    continue;
                }

                if ($byCountry) {
                    // Окремо по країнах: українські — пріоритет, закордонні — другорядні
                    if ($table === 'ad_cities') {
                        $q->join('ad_regions as r', 'r.id', '=', 't.region_id')->join('ad_countries as c', 'c.id', '=', 'r.country_id');
                    } else {
                        $q->join('ad_countries as c', 'c.id', '=', 't.country_id');
                    }
                    foreach ((clone $q)->selectRaw('c.id, COALESCE(NULLIF(c.name_uk, ""), c.name) as country, COUNT(*) as cnt')
                                 ->groupBy('c.id', 'c.name', 'c.name_uk')->orderByDesc('cnt')->get() as $g) {
                        $examples = (clone $q)->where('c.id', $g->id)->limit(5)->pluck('t.name')->implode(', ');
                        $rows[] = [$col . '_uk', $g->country, $g->cnt, mb_strimwidth($examples, 0, 70, '…')];
                    }
                    continue;
                }

                $count = (clone $q)->count();
                if ($count) {
                    $examples = $q->limit(5)->pluck(Schema::hasColumn($table, 'name') ? 't.name' : 't.id')->implode(', ');
                    $rows[] = [$col . '_uk', $count, mb_strimwidth($examples, 0, 80, '…')];
                }
            }
            if ($byCountry) {
                $this->report($rows, ['поле', 'країна', 'скільки', 'приклади'], 100);
                continue;
            }
            $this->report($rows, ['поле', 'скільки', 'приклади'], 100);
        }

        $this->line('');
        $this->info('Готово. Переклади інтерфейсу — адмінка «Переклади»; SEO-шаблони — «SEO тексти»; назви й тексти — у формах категорій/областей/міст/тегів.');
        $this->line('Автопереклад через DeepL: php artisan geo:translate-deepl (області, міста, країни, категорії, тексти тегів); назви тегів — php artisan tags:translate-deepl. Обидві без --apply лише показують звіт.');
    }

    protected function section(string $title): void
    {
        $this->line('');
        $this->line('<comment>== ' . $title . '</comment>');
    }

    protected function report(array $rows, array $headers, int $limit): void
    {
        if (!$rows) {
            $this->line('  OK — нічого не знайдено.');
            return;
        }
        $this->line('  Знайдено: ' . count($rows) . (count($rows) > $limit ? " (показано {$limit})" : ''));
        $this->table($headers, array_slice($rows, 0, $limit));
    }
}
