<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WidenAdCategoriesMetaColumns extends Migration
{
    /**
     * meta_title / meta_description (і _uk) у ad_categories були VARCHAR(255).
     * Українські переклади довші за російські оригінали, і MySQL мовчки
     * обрізав їх на 255 символах, зокрема посередині плейсхолдерів
     * (---full_filt замість ---full_filtered_name---). Розширюємо до TEXT.
     *
     * Сирий ALTER, а не ->change(): не потребує doctrine/dbal.
     */
    public function up()
    {
        foreach (['meta_title', 'meta_title_uk', 'meta_description', 'meta_description_uk'] as $col) {
            if (Schema::hasColumn('ad_categories', $col)) {
                DB::statement("ALTER TABLE `ad_categories` MODIFY `{$col}` TEXT NULL");
            }
        }
    }

    /**
     * Звужувати назад не можна без ризику обрізати дані, тому down() порожній.
     */
    public function down()
    {
    }
}
