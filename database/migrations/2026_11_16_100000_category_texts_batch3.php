<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Ручні SEO-тексти (uk + ru), пакет 3 — наступні 10 категорій за показами в Search Console
 * (серпень–листопад 2026): Кішки, Музичні інструменти, Насоси, В'язка, Дитячі коляски, Сільгосптехніка,
 * Оренда будинків, Охоронні послуги, Мото, Інші тварини. Формат як у пакетах 2026_10_30/31:
 * таблиця, вибір і перевірка, як подати оголошення, безпека, FAQ (<div class="article-faq"> → FAQPage).
 * Тексти — у database/seo-texts/2026_11_16/{slug}.{uk|ru}.html. Старі content/content_uk —
 * у seo_text_backups (TAG), down() їх повертає.
 */
class CategoryTextsBatch3 extends Migration
{
    const TAG = '2026_11_16_category_texts_batch3';
    const DIR = 'seo-texts/2026_11_16';

    /** slug => slug батьківської категорії (slug підкатегорій не унікальний між розділами) */
    const CATEGORIES = [
        'koshki' => 'zhivotnyie',
        'muzyikalnyie-instrumentyi' => 'hobbi-otdyih-i-sport',
        'nasosyi' => 'stroitelstvo-i-remont',
        'vyazka' => 'zhivotnyie',
        'detskie-kolyaski' => 'detskiy-mir',
        'selhoztehnika' => 'transport',
        'arenda-domov' => 'nedvizhimost-2',
        'ohrannyie-uslugi' => 'biznes-i-uslugi',
        'moto' => 'transport',
        'drugie-zhivotnyie' => 'zhivotnyie',
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
