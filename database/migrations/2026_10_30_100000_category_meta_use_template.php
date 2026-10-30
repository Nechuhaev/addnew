<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * У ~90% категорій власний title — старий шаблон на 80–150 символів із повторами
 * («Купити та продати … Безкоштовні оголошення … | Дошка оголошень AddNew.Biz»):
 * Google його обрізає. Задовгі власні title (> 70) і description (> 170) очищаємо —
 * тоді спрацьовує загальний шаблон SeoField 'ad-category'
 * («{категорія} в Україні — оголошення з цінами | Addnew.biz»). Кожна мова окремо.
 * Категорії з ручними текстами (міграція 2026_10_20) не чіпаємо.
 * Старі значення — у seo_text_backups (tag нижче), down() їх повертає.
 */
class CategoryMetaUseTemplate extends Migration
{
    const TAG = '2026_10_30_category_meta_template';
    const KEEP_SLUGS = ['muzyikalnyie-instrumentyi', 'koshki', 'vyazka'];
    const LIMITS = ['meta_title' => 70, 'meta_title_uk' => 70, 'meta_description' => 170, 'meta_description_uk' => 170];

    public function up()
    {
        if (!Schema::hasTable('seo_text_backups')) {
            return; // таблицю створює 2026_10_20_100000_rewrite_seo_texts
        }

        DB::table('ad_categories')->whereNotIn('slug', self::KEEP_SLUGS)->orderBy('id')
            ->select(array_merge(['id', 'slug'], array_keys(self::LIMITS)))
            ->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    $update = [];
                    foreach (self::LIMITS as $field => $max) {
                        $value = trim((string) $row->{$field});
                        if ($value !== '' && mb_strlen($value) > $max) {
                            $update[$field] = '';
                            DB::table('seo_text_backups')->insert([
                                'tag' => self::TAG,
                                'table_name' => 'ad_categories',
                                'row_id' => $row->id,
                                'row_key' => $row->slug,
                                'field' => $field,
                                'value' => $row->{$field},
                                'created_at' => now(),
                            ]);
                        }
                    }
                    if ($update) {
                        DB::table('ad_categories')->where('id', $row->id)->update($update);
                    }
                }
            });
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
}
