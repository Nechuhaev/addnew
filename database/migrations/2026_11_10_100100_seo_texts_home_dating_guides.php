<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * SEO-контент за звітом GSC (10.11.2026), тексти — у database/seo-texts/2026_11_10:
 *  1) головна (seo_fields index): у ПОТОЧНИЙ текст додаються живі цифри, «Популярні розділи» й FAQ
 *     (ручні правки в адмінці зберігаються; FAQ → «Часті запитання» + FAQPage у HomeController);
 *  2) «Знакомства и контакты» — текст uk/ru (розділ відкрито для індексації, config/seo.php);
 *  3) 10 гайдів (uk/ru) у рубриці «Гайди».
 * Старі значення — у seo_text_backups (TAG), down() їх повертає й видаляє створені гайди.
 */
class SeoTextsHomeDatingGuides extends Migration
{
    const TAG = '2026_11_10_home_dating_guides';
    const DIR = 'seo-texts/2026_11_10';

    public function up()
    {
        if (Schema::hasTable('seo_text_backups')) {
            $this->home();
            $this->dating();
        }
        $this->guides();
    }

    public function down()
    {
        if (Schema::hasTable('seo_text_backups')) {
            foreach (DB::table('seo_text_backups')->where('tag', self::TAG)->get() as $b) {
                DB::table($b->table_name)->where('id', $b->row_id)->update([$b->field => $b->value]);
            }
            DB::table('seo_text_backups')->where('tag', self::TAG)->delete();
        }
        $slugs = array_keys(require database_path(self::DIR . '/guides/meta.php'));
        $ids = DB::table('articles')->whereIn('slug', $slugs)->where('ai_generated', 0)->pluck('id');
        DB::table('article_article_category')->whereIn('article_id', $ids)->delete();
        DB::table('articles')->whereIn('id', $ids)->delete();
    }

    protected function home(): void
    {
        $row = DB::table('seo_fields')->where('index', 'index')->first();
        if (!$row) {
            return;
        }
        $add = require database_path(self::DIR . '/home-additions.php');
        foreach (['description_uk' => 'uk', 'description' => 'ru'] as $field => $lang) {
            $html = (string) $row->{$field};
            if ($html === '' || strpos($html, '{ads_count}') !== false) {
                continue; // порожньо або вже доповнено
            }
            $new = $html;
            // 1) цифри — після першого абзацу
            $pos = strpos($new, '</p>');
            $new = $pos !== false ? substr_replace($new, "</p>\n" . $add[$lang]['stats'], $pos, 4) : $add[$lang]['stats'] . "\n" . $new;
            // 2) популярні розділи — перед другим H2 (після вступу), інакше в кінці
            $h2 = preg_match_all('~<h2~u', $new, $m, PREG_OFFSET_CAPTURE) && count($m[0]) > 1 ? $m[0][1][1] : null;
            $new = $h2 !== null ? substr_replace($new, $add[$lang]['popular'] . "\n", $h2, 0) : $new . "\n" . $add[$lang]['popular'];
            // 3) FAQ — у кінці, якщо його ще немає
            if (strpos($new, 'article-faq') === false) {
                $new .= "\n" . $add[$lang]['faq'];
            }
            $this->backup('seo_fields', $row->id, 'index', $field, $html);
            DB::table('seo_fields')->where('id', $row->id)->update([$field => $new]);
        }
    }

    protected function dating(): void
    {
        $row = DB::table('ad_categories as c')
            ->join('ad_categories as p', 'p.id', '=', 'c.parent_id')
            ->where('c.slug', 'znakomstva-i-kontaktyi')->where('p.slug', 'hobbi-otdyih-i-sport')
            ->select('c.id', 'c.content', 'c.content_uk')->first()
            ?: DB::table('ad_categories')->where('slug', 'znakomstva-i-kontaktyi')->select('id', 'content', 'content_uk')->first();
        $uk = $this->text('znakomstva-i-kontaktyi.uk.html');
        $ru = $this->text('znakomstva-i-kontaktyi.ru.html');
        if (!$row || $uk === null || $ru === null) {
            return;
        }
        foreach (['content_uk' => $uk, 'content' => $ru] as $field => $value) {
            $this->backup('ad_categories', $row->id, 'znakomstva-i-kontaktyi', $field, $row->{$field});
        }
        DB::table('ad_categories')->where('id', $row->id)->update(['content_uk' => $uk, 'content' => $ru]);
    }

    protected function guides(): void
    {
        $categoryId = DB::table('article_categories')->where('slug', 'gaidy')->value('id');
        $meta = require database_path(self::DIR . '/guides/meta.php');
        $i = 0;
        foreach ($meta as $slug => $g) {
            $uk = $this->text("guides/{$slug}.uk.html");
            $ru = $this->text("guides/{$slug}.ru.html");
            if ($uk === null || $ru === null || DB::table('articles')->where('slug', $slug)->exists()) {
                continue;
            }
            $time = now()->subMinutes(7 * $i++);
            $id = DB::table('articles')->insertGetId([
                'slug' => $slug,
                'name' => $g['ru']['name'],
                'name_uk' => $g['uk']['name'],
                'excerpt' => $g['ru']['excerpt'],
                'excerpt_uk' => $g['uk']['excerpt'],
                'content' => $ru,
                'content_uk' => $uk,
                'meta_title' => $g['ru']['meta_title'],
                'meta_title_uk' => $g['uk']['meta_title'],
                'meta_description' => $g['ru']['meta_description'],
                'meta_description_uk' => $g['uk']['meta_description'],
                'focus_keyword' => $g['focus'],
                'tags' => json_encode($g['tags'], JSON_UNESCAPED_UNICODE),
                'ai_generated' => 0,
                'sort_order' => 0,
                'image' => null,
                'created_at' => $time,
                'updated_at' => $time,
            ]);
            if ($categoryId) {
                DB::table('article_article_category')->insert(['article_id' => $id, 'article_category_id' => $categoryId]);
            }
        }
    }

    protected function backup(string $table, int $id, string $key, string $field, $value): void
    {
        DB::table('seo_text_backups')->insert([
            'tag' => self::TAG, 'table_name' => $table, 'row_id' => $id, 'row_key' => $key,
            'field' => $field, 'value' => $value, 'created_at' => now(),
        ]);
    }

    protected function text(string $file): ?string
    {
        $path = database_path(self::DIR . '/' . $file);

        return is_file($path) ? trim((string) file_get_contents($path)) : null;
    }
}
