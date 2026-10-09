<?php

namespace App\Services;

use App\User;

/**
 * Способи доставки й оплати магазину: довідник (uk/ru) і підписи для показу.
 */
class ShopDelivery
{
    /** key => [uk, ru] */
    const DELIVERY = [
        'nova_poshta' => ['Нова Пошта', 'Новая Почта'],
        'ukrposhta'   => ['Укрпошта', 'Укрпочта'],
        'meest'       => ['Meest', 'Meest'],
        'courier'     => ["Кур'єр", 'Курьер'],
        'pickup'      => ['Самовивіз', 'Самовывоз'],
    ];

    const PAYMENT = [
        'cod'     => ['Накладений платіж', 'Наложенный платёж'],
        'card'    => ['На картку', 'На карту'],
        'online'  => ['Онлайн-оплата на сайті', 'Онлайн-оплата на сайте'],
        'invoice' => ['Безготівковий рахунок', 'Безналичный расчёт'],
        'cash'    => ['Готівка при самовивозі', 'Наличные при самовывозе'],
    ];

    public static function label(array $map, string $key): string
    {
        $pair = $map[$key] ?? [$key, $key];
        return app()->getLocale() === 'ru' ? $pair[1] : $pair[0];
    }

    /** Підписи вибраних способів у порядку довідника */
    public static function labels(array $map, $selected): array
    {
        $selected = is_array($selected) ? $selected : [];
        $out = [];
        foreach ($map as $key => $pair) {
            if (in_array($key, $selected, true)) {
                $out[] = static::label($map, $key);
            }
        }
        return $out;
    }

    /**
     * Дані для показу покупцю або null, якщо магазин нічого не заповнив.
     */
    public static function forShop(?User $shop): ?array
    {
        if (!$shop) {
            return null;
        }
        $data = [
            'delivery' => static::labels(self::DELIVERY, $shop->delivery_methods),
            'payment' => static::labels(self::PAYMENT, $shop->payment_methods),
            'free_from' => $shop->free_delivery_from ? (int) $shop->free_delivery_from : null,
            'note' => trim((string) $shop->delivery_note),
        ];
        if (!$data['delivery'] && !$data['payment'] && !$data['free_from'] && $data['note'] === '') {
            return null;
        }
        return $data;
    }
}
