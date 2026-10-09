<?php

use App\Translation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Текст картки «Імпорт товарів» (/profile/shop/import-export) — тепер
 * згадує всі підтримувані формати, зокрема YML (Prom.ua, Rozetka).
 */
class UpdateShopImportCardTextTranslation extends Migration
{
    const TEXTS = [
        'uk' => 'Завантажте файл товарів у форматі YML (Prom.ua, Rozetka), XML Google Merchant або CSV — товари з цінами й фото з\'являться на сайті. Повторне завантаження оновить ціни та наявність.',
        'ru' => 'Загрузите файл товаров в формате YML (Prom.ua, Rozetka), XML Google Merchant или CSV — товары с ценами и фото появятся на сайте. Повторная загрузка обновит цены и наличие.',
    ];

    public function up()
    {
        foreach (self::TEXTS as $locale => $value) {
            $where = ['group' => 'shop', 'key' => 'import_card_text', 'locale' => $locale];
            if (DB::table('translations')->where($where)->exists()) {
                DB::table('translations')->where($where)->update(['value' => $value, 'updated_at' => now()]);
            } else {
                DB::table('translations')->insert($where + ['value' => $value, 'created_at' => now(), 'updated_at' => now()]);
            }
            Translation::clearCache('shop', $locale);
        }
    }

    public function down()
    {
        // Попередній текст не зберігався — відкат не змінює переклад.
    }
}
