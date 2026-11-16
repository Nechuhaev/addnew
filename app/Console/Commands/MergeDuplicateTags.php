<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * php artisan tags:merge-duplicates [--apply] [--exclude=12,345] [--limit-show=40]
 *
 * Знаходить теги-дублікати — однакова назва без урахування регістру, пробілів, дефісів і розділових знаків
 * («Айфон 13», «айфон13», «айфон-13») — і об'єднує кожну групу в один основний тег:
 *  - основний — тег із найбільшою кількістю активних оголошень (далі — з власним текстом, далі — найстаріший);
 *  - зв'язки з оголошеннями переносяться на основний, текст копіюється, якщо в основного його немає;
 *  - старі адреси дублікатів ведуть 301 на основний тег (таблиця tag_redirects);
 *  - дублікати видаляються.
 * Без --apply лише показує групи й пише повний список у storage/app/tag-merge-preview-*.csv.
 * З --apply спершу зберігає резервну копію (storage/app/tag-merge-backup-*.json).
 */
class MergeDuplicateTags extends Command
{
    protected $signature = 'tags:merge-duplicates {--apply} {--exclude=} {--limit-show=40}';

    protected $description = "Об'єднання тегів-дублікатів (різний регістр, пробіли, дефіси) з 301 на основний тег";

    const CONTENT_FIELDS = ['content', 'content_uk', 'meta_title', 'meta_title_uk', 'meta_description', 'meta_description_uk', 'name_uk'];

    public function handle()
    {
        $exclude = array_filter(array_map('intval', explode(',', (string) $this->option('exclude'))));
        $groups = $this->groups($exclude);
        $dupCount = array_sum(array_map(function ($g) {
            return count($g) - 1;
        }, $groups));
        $this->info('Тегів у базі: ' . DB::table('ad_tags')->count() . '; груп дублікатів: ' . count($groups) . "; тегів буде об'єднано: {$dupCount}");

        $csv = storage_path('app/tag-merge-preview-' . date('Ymd-His') . '.csv');
        $fh = fopen($csv, 'w');
        fputcsv($fh, ['group', 'role', 'tag_id', 'name', 'slug', 'active_ads', 'has_text']);
        foreach (array_values($groups) as $i => $g) {
            foreach ($g as $j => $t) {
                fputcsv($fh, [$i + 1, $j === 0 ? 'основний' : 'дублікат', $t->id, $t->name, $t->slug, $t->active, $t->has_text ? 1 : 0]);
            }
        }
        fclose($fh);

        $shown = array_slice($groups, 0, (int) $this->option('limit-show'));
        foreach ($shown as $g) {
            $main = $g[0];
            $dups = array_map(function ($t) {
                return "«{$t->name}» ({$t->active})";
            }, array_slice($g, 1));
            $clean = self::cleanName($main->name);
            $this->line("  «{$clean}» ({$main->active} огол.) ← " . implode(', ', $dups) . ($clean !== $main->name ? " [назву «{$main->name}» буде очищено]" : ''));
        }
        $this->line('Повний список: ' . $csv);

        if (!$this->option('apply')) {
            $this->warn('Це попередній перегляд. Щоб об\'єднати, запустіть з --apply (теги, які не треба чіпати, — --exclude=id,id).');
            return 0;
        }
        if (!Schema::hasTable('tag_redirects')) {
            $this->error('Немає таблиці tag_redirects — спершу php artisan migrate');
            return 1;
        }

        $backup = ['tags' => [], 'links' => []];
        foreach ($groups as $g) {
            foreach (array_slice($g, 1) as $t) {
                $backup['tags'][] = (array) DB::table('ad_tags')->where('id', $t->id)->first();
                $backup['links'][$t->id] = DB::table('ad_tag')->where('tag_id', $t->id)->pluck('ad_id')->all();
            }
        }
        $backupFile = storage_path('app/tag-merge-backup-' . date('Ymd-His') . '.json');
        file_put_contents($backupFile, json_encode($backup, JSON_UNESCAPED_UNICODE));
        $this->line('Резервна копія: ' . $backupFile);

        $merged = 0;
        foreach ($groups as $g) {
            $main = $g[0];
            DB::transaction(function () use ($g, $main, &$merged) {
                $mainRow = DB::table('ad_tags')->where('id', $main->id)->first();
                foreach (array_slice($g, 1) as $dup) {
                    $dupRow = DB::table('ad_tags')->where('id', $dup->id)->first();
                    if (!$dupRow) {
                        continue;
                    }
                    // зв'язки з оголошеннями → основний тег (без повторів)
                    DB::table('ad_tag')->where('tag_id', $dup->id)
                        ->whereNotIn('ad_id', function ($q) use ($main) {
                            $q->select('ad_id')->from('ad_tag')->where('tag_id', $main->id);
                        })->update(['tag_id' => $main->id]);
                    DB::table('ad_tag')->where('tag_id', $dup->id)->delete();
                    // текст і мета — якщо в основного порожньо
                    $fill = [];
                    foreach (self::CONTENT_FIELDS as $f) {
                        if (property_exists($dupRow, $f) && trim((string) ($mainRow->{$f} ?? '')) === '' && trim((string) $dupRow->{$f}) !== '') {
                            $fill[$f] = $dupRow->{$f};
                            $mainRow->{$f} = $dupRow->{$f};
                        }
                    }
                    if ($fill) {
                        DB::table('ad_tags')->where('id', $main->id)->update($fill);
                    }
                    // 301: старий slug і всі переадресації, що вели на дублікат
                    if ($dupRow->slug) {
                        DB::table('tag_redirects')->updateOrInsert(['from_slug' => $dupRow->slug], ['to_tag_id' => $main->id, 'updated_at' => now(), 'created_at' => now()]);
                    }
                    DB::table('tag_redirects')->where('to_tag_id', $dup->id)->update(['to_tag_id' => $main->id]);
                    foreach (['seo_index_status', 'seo_index_submissions'] as $table) {
                        if (Schema::hasTable($table)) {
                            DB::table($table)->where('entity_type', 'tag')->where('entity_id', $dup->id)->delete();
                        }
                    }
                    DB::table('ad_tags')->where('id', $dup->id)->delete();
                    $merged++;
                }
                // Назва основного тегу без розділових знаків на краях («, холодильник» → «холодильник»); slug не змінюється
                $clean = self::cleanName((string) $mainRow->name);
                if ($clean !== '' && $clean !== $mainRow->name) {
                    DB::table('ad_tags')->where('id', $main->id)->update(['name' => $clean]);
                }
            });
        }
        foreach (['seo_index_summary_v2:tag', 'seo_our_noindex_ids:tag'] as $key) {
            \Illuminate\Support\Facades\Cache::forget($key);
        }
        $this->info("Об'єднано тегів: {$merged}. Після цього: php artisan sitemap:rebuild");

        return 0;
    }

    /** Групи дублікатів: [ [основний, дублікат, ...], ... ], від найбільших за оголошеннями */
    protected function groups(array $exclude): array
    {
        $active = DB::table('ad_tag')->join('ads', 'ads.id', '=', 'ad_tag.ad_id')->where('ads.status', 1)
            ->groupBy('ad_tag.tag_id')->selectRaw('ad_tag.tag_id, COUNT(*) n')->pluck('n', 'ad_tag.tag_id')->all();

        $byKey = [];
        DB::table('ad_tags')->select('id', 'name', 'slug', 'content', 'content_uk')->orderBy('id')
            ->chunk(5000, function ($rows) use (&$byKey, $active, $exclude) {
                foreach ($rows as $r) {
                    if (in_array((int) $r->id, $exclude, true)) {
                        continue;
                    }
                    $key = self::key((string) $r->name);
                    if ($key === '') {
                        continue;
                    }
                    $byKey[$key][] = (object) [
                        'id' => (int) $r->id, 'name' => (string) $r->name, 'slug' => (string) $r->slug,
                        'active' => (int) ($active[$r->id] ?? 0),
                        'has_text' => trim((string) $r->content . (string) $r->content_uk) !== '',
                    ];
                }
            });

        $groups = [];
        foreach ($byKey as $tags) {
            if (count($tags) < 2) {
                continue;
            }
            usort($tags, function ($a, $b) {
                return [$b->active, (int) $b->has_text, -$b->id] <=> [$a->active, (int) $a->has_text, -$a->id];
            });
            $groups[] = $tags;
        }
        usort($groups, function ($a, $b) {
            return array_sum(array_column($b, 'active')) <=> array_sum(array_column($a, 'active'));
        });

        return $groups;
    }

    /** Назва без пробілів і розділових знаків на початку й у кінці (закрита дужка в кінці лишається) */
    public static function cleanName(string $name): string
    {
        return trim((string) preg_replace('/^[^\p{L}\p{N}]+|[^\p{L}\p{N})]+$/u', '', trim($name)));
    }

    /** Ключ порівняння: нижній регістр, ё→е, без пробілів, дефісів і розділових знаків */
    public static function key(string $name): string
    {
        $s = mb_strtolower(trim($name));
        $s = str_replace(['ё', '’', "'", 'ʼ', '`'], ['е', '', '', '', ''], $s);

        return (string) preg_replace('/[^\p{L}\p{N}]+/u', '', $s);
    }
}
