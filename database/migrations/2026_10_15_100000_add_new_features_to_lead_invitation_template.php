<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * «Запрошення для кандидатів»: додає до переліку переваг нові можливості
 * (автооновлення з фіда, обране й сповіщення про зниження ціни, історія ціни,
 * доставка й оплата, пошук, «Інші продавці»). Пункти вставляються в кінець
 * першого списку <ul> — правки адміна в решті тексту зберігаються.
 */
class AddNewFeaturesToLeadInvitationTemplate extends Migration
{
    const NAME = 'Запрошення для кандидатів';

    // За цією фразою впізнаємо, що пункти вже додано (повторно не вставляємо)
    const MARKER = 'Автооновлення з фіда';

    public static function items(): string
    {
        return '<li><strong>Автооновлення з фіда</strong> — вкажіть посилання на YML (Prom.ua, Rozetka), Google Merchant XML або CSV, '
            . 'і ми самі оновлюватимемо ціни, наявність, описи й фото кожні 6–24 години. Товари, яких більше немає у фіді, позначимо «немає в наявності».</li>'
            . '<li><strong>Покупці стежать за вашими цінами</strong> — товар можна додати в обране, і ми надішлемо покупцю лист, щойно ціна знизиться. '
            . 'На сторінці товару — графік історії ціни та позначка «Найнижча ціна за 90 днів».</li>'
            . '<li><strong>Доставка й оплата на видноті</strong> — способи доставки (Нова Пошта, Укрпошта, Meest, кур\'єр, самовивіз), оплати '
            . 'та «безкоштовна доставка від…» показуємо на сторінці магазину й кожного товару.</li>'
            . '<li><strong>Вас легко знайти</strong> — швидкий пошук за назвою, брендом, артикулом і штрихкодом, пошук магазинів і пошук у товарах вашого магазину, '
            . 'фільтри за ціною, станом і наявністю.</li>'
            . '<li><strong>Ваша пропозиція поруч із конкурентами</strong> — той самий товар (за штрихкодом або артикулом) показуємо в блоці «Інші продавці», '
            . 'тож покупці бачать вашу ціну й на сторінках інших магазинів.</li>';
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
