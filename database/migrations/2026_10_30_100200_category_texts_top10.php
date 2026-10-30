<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Ручні SEO-тексти (uk + ru) для 10 категорій з найбільшими показами в GSC:
 * визначення, підрозділи, таблиця порівняння, кроки перевірки, ціни, безпека
 * і блок FAQ (<div class="article-faq">) — його Category бере у FAQPage (GeoFaq::extractFromHtml).
 * Тексти — у database/seo-texts/2026_10_30/{slug}.{uk|ru}.html. Старі content/content_uk —
 * у seo_text_backups (tag нижче), down() їх повертає.
 */
class CategoryTextsTop10 extends Migration
{
    const TAG = '2026_10_30_category_texts_top10';
    const DIR = 'seo-texts/2026_10_30';

    /** slug => slug батьківської категорії (slug підкатегорій не унікальний між розділами) */
    const CATEGORIES = [
        'tehnika-dlya-doma' => 'elektronika',
        'spetstehnika' => 'transport',
        'sobaki' => 'zhivotnyie',
        'arenda-komnat' => 'nedvizhimost-2',
        'santehnika' => 'stroitelstvo-i-remont',
        'elektrika' => 'stroitelstvo-i-remont',
        'elektronika' => null,
        'telefonyi' => 'elektronika',
        'shini-diski' => 'transport',
        'posuda-kuhonnaya-utvar' => 'dom-i-sad',
    ];

    public function up()
    {
        if (!Schema::hasTable('seo_text_backups')) {
            return; // таблицю створює 2026_10_20_100000_rewrite_seo_texts
        }

        foreach (self::CATEGORIES as $slug => $parentSlug) {
            $row = $this->find($slug, $parentSlug);
            $uk = $this->text($slug, 'uk');
            $ru = $this->text($slug, 'ru');
            if (!$row || $uk === null || $ru === null) {
                continue;
            }
            foreach (['content_uk' => $uk, 'content' => $ru] as $field => $value) {
                DB::table('seo_text_backups')->insert([
                    'tag' => self::TAG,
                    'table_name' => 'ad_categories',
                    'row_id' => $row->id,
                    'row_key' => $slug,
                    'field' => $field,
                    'value' => $row->{$field},
                    'created_at' => now(),
                ]);
            }
            DB::table('ad_categories')->where('id', $row->id)->update(['content_uk' => $uk, 'content' => $ru]);
        }
    }

    public function down()
    {
        if (!Schema::hasTable('seo_text_backups')) {
            return;
        }
        foreach (DB::table('seo_text_backups')->where('tag', self::TAG)->get() as $b) {
            DB::table($b->table_name)->where('id', $b->row_id)->update([$b->field => $b->value]);
        }
        DB::table('seo_text_backups')->where('tag', self::TAG)->delete();
    }

    protected function find(string $slug, ?string $parentSlug)
    {
        $q = DB::table('ad_categories as c')->where('c.slug', $slug)->select('c.id', 'c.content', 'c.content_uk');
        if ($parentSlug) {
            $q->join('ad_categories as p', 'p.id', '=', 'c.parent_id')->where('p.slug', $parentSlug);
        } else {
            $q->where(function ($w) {
                $w->whereNull('c.parent_id')->orWhere('c.parent_id', 0);
            });
        }

        return $q->orderBy('c.id')->first();
    }

    protected function text(string $slug, string $lang): ?string
    {
        $path = database_path(self::DIR . "/{$slug}.{$lang}.html");

        return is_file($path) ? trim((string) file_get_contents($path)) : null;
    }
}
