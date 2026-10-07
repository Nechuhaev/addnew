<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Шаблон «Запрошення для кандидатів» у «Шаблони листів» — раніше текст
 * був зашитий у код (ShopLead), тепер його можна редагувати в адмінці.
 */
class AddLeadInvitationMessageTemplate extends Migration
{
    const NAME = 'Запрошення для кандидатів';

    public function up()
    {
        if (DB::table('shop_message_templates')->where('name', self::NAME)->exists()) {
            return;
        }

        DB::table('shop_message_templates')->insert([
            'name' => self::NAME,
            'subject' => 'Безкоштовне розміщення товарів {{shop_name}} на addnew.biz',
            'body' => '<p>Добрий день!</p>'
                . '<p>Мене звати {{sender_name}}, я представляю дошку оголошень addnew.biz. '
                . 'Бачимо, що {{shop_name}} продає {{goods}} онлайн, і хочемо запросити вас безкоштовно розмістити їх у нас.</p>'
                . '<p>Що це дає:</p>'
                . '<ul>'
                . '<li>додаткових покупців: кожна картка товару веде прямо на ваш сайт;</li>'
                . '<li>жодної оплати ні за розміщення, ні за переходи;</li>'
                . '<li>мінімум роботи: підходить той самий YML/XML-файл, який ви готуєте для прайс-агрегаторів, — завантажуєте його, і товари з цінами й фото з\'являються на сайті.</li>'
                . '</ul>'
                . '<p>Реєстрація магазину займає кілька хвилин: <a href="{{register_url}}">{{register_url}}</a></p>'
                . '<p>Якщо цікаво — просто дайте відповідь на цей лист, допоможу з підключенням. '
                . 'Якщо ні — вибачте за турботу, більше не писатимемо.</p>'
                . '<p>З повагою,<br>{{sender_name}}<br>addnew.biz</p>',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        DB::table('shop_message_templates')->where('name', self::NAME)->delete();
    }
}
