<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * php artisan tags:prune-mismatched [--apply] [--limit-show=50]
 *
 * Відв'язує тег від оголошення, якщо слів тегу немає ні в назві, ні в тексті оголошення
 * (колись автотеги проставлялися з помилками: «Голуб» на статуетках, «Холодильник» на оренді квартир).
 * Звіряються російська й українська назви тегу (name, name_uk). Кириличне слово шукається за основою (без 2 останніх літер для слів довших за 5, без 1 — для 4–5: «рацию» знайде «рация»), латинське — цілим;
 * тег, що не згаданий у ≥90% з ≥20 своїх оголошень, не чіпається (споріднена тема від автора) — лише показується;
 * для тегу з кількох значущих слів достатньо половини. Теги, у яких ≥90% оголошень від одного магазину,
 * не чіпаються (це теги з фіда — для них tags:mark-noindex).
 * Без --apply — лише перегляд; з --apply спершу резервна копія зв'язків (storage/app/tag-prune-backup-*.json).
 */
class PruneMismatchedTags extends Command
{
    protected $signature = 'tags:prune-mismatched {--apply} {--limit-show=50}';

    protected $description = "Відв'язати теги від оголошень, де слова тегу немає в назві чи тексті";

    public function handle()
    {
        $tags = [];
        DB::table('ad_tags')->select('id', 'name', 'name_uk')->orderBy('id')->chunk(5000, function ($rows) use (&$tags) {
            foreach ($rows as $r) {
                // теги переважно російською, оголошення — часто українською: звіряємо обидві назви
                $variants = array_values(array_filter([self::stems((string) $r->name), self::stems((string) $r->name_uk)]));
                if ($variants) {
                    $tags[(int) $r->id] = ['name' => (string) $r->name, 'variants' => $variants];
                }
            }
        });
        $shopTags = $this->shopTagIds();

        $remove = [];   // [[tag_id, ad_id], ...]
        $perTag = [];   // tag_id => [усього, прибрати]
        DB::table('ads')->select('id', 'name', 'content')->orderBy('id')->chunk(2000, function ($ads) use (&$remove, &$perTag, $tags, $shopTags) {
            $links = DB::table('ad_tag')->whereIn('ad_id', $ads->pluck('id')->all())->get(['ad_id', 'tag_id'])->groupBy('ad_id');
            foreach ($ads as $ad) {
                if (!isset($links[$ad->id])) {
                    continue;
                }
                $text = self::normalize($ad->name . ' ' . strip_tags((string) $ad->content));
                foreach ($links[$ad->id] as $link) {
                    $tagId = (int) $link->tag_id;
                    if (!isset($tags[$tagId]) || isset($shopTags[$tagId])) {
                        continue;
                    }
                    $perTag[$tagId][0] = ($perTag[$tagId][0] ?? 0) + 1;
                    if (!self::matchesAny($tags[$tagId]['variants'], $text)) {
                        $remove[] = [$tagId, (int) $ad->id];
                        $perTag[$tagId][1] = ($perTag[$tagId][1] ?? 0) + 1;
                    }
                }
            }
        });

        // Тег, що не збігається майже ніде (≥90% з ≥20), — швидше споріднена тема від автора
        // («логоневроз» на оголошеннях логопеда), а не помилка автотегів: не чіпаємо, показуємо для ручної перевірки
        $systemic = array_filter($perTag, function ($p) {
            return ($p[0] ?? 0) >= 20 && ($p[1] ?? 0) / $p[0] >= 0.9;
        });
        if ($systemic) {
            $remove = array_values(array_filter($remove, function ($pair) use ($systemic) {
                return !isset($systemic[$pair[0]]);
            }));
            $this->warn('Не чіпаю (тег майже ніде не згаданий — перевірте вручну): ' . count($systemic));
            foreach (array_slice($systemic, 0, 30, true) as $tagId => $p) {
                $this->line(sprintf('  #%d «%s»: %d з %d', $tagId, $tags[$tagId]['name'], $p[1], $p[0]));
            }
            $perTag = array_diff_key($perTag, $systemic);
        }

        $this->info("Зв'язків тег–оголошення до відв'язки: " . count($remove) . ' (тегів зачеплено: ' . count(array_filter($perTag, function ($p) {
            return ($p[1] ?? 0) > 0;
        })) . ')');
        uasort($perTag, function ($a, $b) {
            return ($b[1] ?? 0) <=> ($a[1] ?? 0);
        });
        foreach (array_slice($perTag, 0, (int) $this->option('limit-show'), true) as $tagId => $p) {
            if (($p[1] ?? 0) === 0) {
                break;
            }
            $this->line(sprintf('  «%s»: прибрати %d з %d', $tags[$tagId]['name'], $p[1], $p[0]));
        }

        if (!$this->option('apply')) {
            $this->warn("Це попередній перегляд. Щоб відв'язати — з --apply.");
            return 0;
        }

        $backup = storage_path('app/tag-prune-backup-' . date('Ymd-His') . '.json');
        file_put_contents($backup, json_encode($remove));
        $this->line('Резервна копія зв\'язків: ' . $backup);
        foreach (array_chunk($remove, 500) as $chunk) {
            DB::transaction(function () use ($chunk) {
                foreach ($chunk as [$tagId, $adId]) {
                    DB::table('ad_tag')->where('tag_id', $tagId)->where('ad_id', $adId)->delete();
                }
            });
        }
        foreach (['seo_index_summary_v2:tag', 'seo_our_noindex_ids:tag'] as $key) {
            Cache::forget($key);
        }
        $this->info("Відв'язано: " . count($remove) . '. Після цього: php artisan sitemap:rebuild');

        return 0;
    }

    /** Теги, у яких ≥90% активних оголошень від одного магазину (теги з фідів) */
    protected function shopTagIds(): array
    {
        $rows = DB::table('ad_tag')->join('ads', 'ads.id', '=', 'ad_tag.ad_id')->where('ads.status', 1)
            ->groupBy('ad_tag.tag_id', 'ads.user_id')->selectRaw('ad_tag.tag_id, COUNT(*) n')->get();
        $total = $max = [];
        foreach ($rows as $r) {
            $total[$r->tag_id] = ($total[$r->tag_id] ?? 0) + $r->n;
            $max[$r->tag_id] = max($max[$r->tag_id] ?? 0, $r->n);
        }
        $out = [];
        foreach ($total as $id => $n) {
            if ($n >= 5 && $max[$id] / $n >= 0.9) {
                $out[(int) $id] = true;
            }
        }

        return $out;
    }

    /** Нижній регістр, ё→е, лише літери й цифри через пробіл */
    public static function normalize(string $text): string
    {
        $s = mb_strtolower(html_entity_decode($text, ENT_QUOTES, 'UTF-8'));
        $s = str_replace(['ё', 'ї', 'є', 'ґ'], ['е', 'і', 'е', 'г'], $s);

        return ' ' . trim((string) preg_replace('/[^\p{L}\p{N}]+/u', ' ', $s)) . ' ';
    }

    /** Основи значущих слів тегу (від 3 символів; для слів довших за 5 — без 2 останніх літер) */
    public static function stems(string $name): array
    {
        $out = [];
        foreach (explode(' ', trim(self::normalize($name))) as $w) {
            $len = mb_strlen($w);
            if ($len < 3 && !preg_match('/\d/u', $w)) {
                continue;
            }
            if (!preg_match('/\p{Cyrillic}/u', $w)) {
                $out[] = $w; // латиниця (бренди, моделі) — ціле слово
            } elseif ($len > 5) {
                $out[] = mb_substr($w, 0, $len - 2);
            } elseif ($len >= 4) {
                $out[] = mb_substr($w, 0, $len - 1); // «рацию» → «раци» знайде «рация»
            } else {
                $out[] = $w;
            }
        }

        return array_values(array_unique($out));
    }

    public static function matchesAny(array $variants, string $text): bool
    {
        foreach ($variants as $stems) {
            if (self::matches($stems, $text)) {
                return true;
            }
        }

        return false;
    }

    /** Достатньо половини основ (округлення вгору); кожна шукається як початок слова */
    public static function matches(array $stems, string $text): bool
    {
        $need = (int) ceil(count($stems) / 2);
        $found = 0;
        foreach ($stems as $stem) {
            if (mb_strpos($text, ' ' . $stem) !== false) {
                $found++;
            }
        }

        return $found >= $need;
    }
}
