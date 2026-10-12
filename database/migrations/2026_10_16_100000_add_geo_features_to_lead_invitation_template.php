<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * «Запрошення для кандидатів»: ще три переваги — сторінки областей і міст із
 * розділами й кількістю пропозицій, блок «Магазини» для нових магазинів,
 * повна українська версія сайту. Пункти вставляються в кінець першого списку
 * <ul> — правки адміна в решті тексту зберігаються.
 */
class AddGeoFeaturesToLeadInvitationTemplate extends Migration
{
    const NAME = 'Запрошення для кандидатів';

    // За цією фразою впізнаємо, що пункти вже додано (повторно не вставляємо)
    const MARKER = 'Покупці з вашого міста';

    public static function items(): string
    {
        return '<li><strong>Покупці з вашого міста</strong> — у кожної області й міста є своя сторінка з розділами каталогу '
            . 'та кількістю пропозицій у кожному, тож покупці поруч швидко знаходять ваші товари.</li>'
            . '<li><strong>Новий магазин на виду</strong> — нові магазини показуємо в блоці «Магазини» на головній сторінці '
            . 'та на сторінках областей і міст.</li>'
            . '<li><strong>Українською та російською</strong> — сайт повністю двомовний: інтерфейс, розділи, назви міст і областей, '
            . 'тож вашу пропозицію зручно переглядати обома мовами.</li>';
    }

    public function up()
    {
        $template = DB::table('shop_message_templates')->where('name', self::NAME)->first();
        if (!$template) {
            return;
        }
        if (strpos($template->body, self::MARKER) !== false) {
            return;
        }

        $pos = stripos($template->body, '</ul>');
        if ($pos === false) {
            echo "  У шаблоні «" . self::NAME . "» немає списку переваг — нові пункти не додано (можна вставити вручну в «Шаблони листів»).\n";
            return;
        }

        $body = substr($template->body, 0, $pos) . self::items() . substr($template->body, $pos);

        DB::table('shop_message_templates')->where('id', $template->id)->update([
            'body' => $body,
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        $template = DB::table('shop_message_templates')->where('name', self::NAME)->first();
        if ($template && strpos($template->body, self::items()) !== false) {
            DB::table('shop_message_templates')->where('id', $template->id)->update([
                'body' => str_replace(self::items(), '', $template->body),
                'updated_at' => now(),
            ]);
        }
    }
}
