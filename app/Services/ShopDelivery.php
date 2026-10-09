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

    /** Правила валідації полів доставки/оплати (кабінет магазину й адмінка) */
    public static function rules(): array
    {
        return [
            'delivery_methods' => 'nullable|array',
            'delivery_methods.*' => 'in:' . implode(',', array_keys(self::DELIVERY)),
            'payment_methods' => 'nullable|array',
            'payment_methods.*' => 'in:' . implode(',', array_keys(self::PAYMENT)),
            'free_delivery_from' => 'nullable|integer|min:1|max:10000000',
            'delivery_note' => 'nullable|string|max:500',
        ];
    }

    /**
     * Незняті чекбокси не приходять у запиті — без цього їх не можна було б зняти.
     */
    public static function normalize(array $data): array
    {
        $data['delivery_methods'] = array_values(array_unique($data['delivery_methods'] ?? []));
        $data['payment_methods'] = array_values(array_unique($data['payment_methods'] ?? []));
        $data['free_delivery_from'] = $data['free_delivery_from'] ?? null;
        $data['delivery_note'] = $data['delivery_note'] ?? null;
        return $data;
    }

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
