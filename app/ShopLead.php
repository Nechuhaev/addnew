<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Кандидат у магазини (адмінка → Магазини → Кандидати)
 */
class ShopLead extends Model
{
    const STATUSES = [
        'new' => 'Новий',
        'contacted' => 'Написали',
        'replied' => 'Відповіли',
        'joined' => 'Підключились',
        'declined' => 'Відмовились',
    ];

    const STATUS_BADGES = [
        'new' => 'secondary',
        'contacted' => 'info',
        'replied' => 'warning',
        'joined' => 'success',
        'declined' => 'dark',
    ];

    protected $fillable = [
        'name', 'site_url', 'domain', 'category', 'email', 'email_source',
        'email_lookup_status', 'status', 'note', 'contacted_at',
    ];

    protected $casts = [
        'contacted_at' => 'datetime',
    ];

    public function messages()
    {
        return $this->hasMany(ShopLeadMessage::class)->orderByDesc('created_at');
    }

    /**
     * Домен без www — для пошуку дублікатів і магазинів, що вже є на сайті
     */
    public static function domainOf(string $url): ?string
    {
        $host = parse_url(preg_match('#^https?://#i', $url) ? $url : 'https://' . $url, PHP_URL_HOST);

        return $host ? preg_replace('/^www\./i', '', strtolower($host)) : null;
    }

    /**
     * Магазин із цим доменом уже зареєстрований на addnew (щоб не писати своїм)
     */
    public function existingShop(): ?User
    {
        return User::where('is_shop_owner', 1)
            ->where('site_url', 'like', '%' . $this->domain . '%')
            ->first();
    }

    /**
     * Текст листа-запрошення [тема, текст] — підставляється у форму відправки
     * на сторінці кандидатів, адмін може відредагувати його перед надсиланням
     */
    public function invitationLetter(string $senderName): array
    {
        $subject = "Безкоштовне розміщення товарів {$this->name} на addnew.biz";

        $what = $this->category ? "товари в категорії «{$this->category}»" : 'товари';

        $body = "Добрий день!\n\n"
            . "Мене звати {$senderName}, я представляю дошку оголошень addnew.biz. "
            . "Бачимо, що {$this->name} продає {$what} онлайн, і хочемо запросити вас безкоштовно розмістити їх у нас.\n\n"
            . "Що це дає:\n"
            . "• додаткових покупців: кожна картка товару веде прямо на ваш сайт;\n"
            . "• жодної оплати ні за розміщення, ні за переходи;\n"
            . "• мінімум роботи: підходить той самий YML/XML-файл, який ви готуєте для прайс-агрегаторів, — завантажуєте його, і товари з цінами й фото з'являються на сайті.\n\n"
            . "Реєстрація магазину займає кілька хвилин: " . route('business-register') . "\n\n"
            . "Якщо цікаво — просто дайте відповідь на цей лист, допоможу з підключенням. "
            . "Якщо ні — вибачте за турботу, більше не писатимемо.\n\n"
            . "З повагою,\n{$senderName}\naddnew.biz";

        return [$subject, $body];
    }
}
