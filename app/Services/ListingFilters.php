<?php

namespace App\Services;

use Illuminate\Http\Request;

/**
 * Фільтри й сортування списків оголошень (категорія, пошук, тег):
 * ціна від–до (у гривнях, з перерахунком валют), продавець, стан,
 * наявність, сортування. Параметри — у GET, щоб посиланнями можна було ділитися.
 */
class ListingFilters
{
    // Допустимі значення [значення => [uk, ru]]
    const SELLERS = ['shop' => ['Магазини', 'Магазины'], 'private' => ['Приватні', 'Частные']];
    const CONDITIONS = ['new' => ['Нове', 'Новое'], 'used' => ['Б/в', 'Б/у'], 'refurbished' => ['Відновлене', 'Восстановленное']];
    const SORTS = ['new' => ['Спочатку нові', 'Сначала новые'], 'cheap' => ['Спочатку дешевші', 'Сначала дешевле'], 'expensive' => ['Спочатку дорожчі', 'Сначала дороже']];

    /** Підпис значення поточною мовою сайту */
    public static function label(array $map, string $key): string
    {
        return $map[$key][app()->getLocale() === 'ru' ? 1 : 0] ?? $key;
    }

    /** Ціна в гривнях: ціна × курс валюти оголошення */
    const PRICE_UAH = 'ads.price * COALESCE(listing_currency.rate, 1)';

    /** @var array */
    public $values;

    public function __construct(array $values)
    {
        $this->values = $values;
    }

    public static function fromRequest(Request $request): self
    {
        $int = function ($key) use ($request) {
            $v = preg_replace('/\D/', '', (string) $request->get($key));
            return $v === '' ? null : (int) $v;
        };
        $pick = function ($key, array $allowed) use ($request) {
            $v = (string) $request->get($key);
            return isset($allowed[$v]) ? $v : null;
        };

        $from = $int('price_from');
        $to = $int('price_to');
        if ($from !== null && $to !== null && $from > $to) {
            [$from, $to] = [$to, $from];
        }

        return new self([
            'price_from' => $from,
            'price_to' => $to,
            'seller' => $pick('seller', self::SELLERS),
            'condition' => $pick('condition', self::CONDITIONS),
            'in_stock' => $request->get('in_stock') ? 1 : null,
            'sort' => $pick('sort', self::SORTS),
        ]);
    }

    /**
     * @param \Illuminate\Database\Query\Builder $query запит Ad::getAds()
     */
    public function apply($query)
    {
        $v = $this->values;

        $query->leftJoin('ad_currencies AS listing_currency', 'listing_currency.id', '=', 'ads.currency_id');

        if ($v['price_from'] !== null) {
            $query->whereRaw(self::PRICE_UAH . ' >= ?', [$v['price_from']]);
        }
        if ($v['price_to'] !== null) {
            $query->whereRaw(self::PRICE_UAH . ' <= ?', [$v['price_to']]);
        }
        if ($v['seller'] === 'shop') {
            $query->where('ads.is_product', 1);
        } elseif ($v['seller'] === 'private') {
            $query->where(function ($q) {
                $q->where('ads.is_product', 0)->orWhereNull('ads.is_product');
            });
        }
        if ($v['condition']) {
            $query->where('ads.condition', $v['condition']);
        }
        if ($v['in_stock']) {
            $query->where(function ($q) {
                $q->whereNull('ads.stock')->orWhere('ads.stock', '!=', 'out_of_stock');
            });
        }

        if (in_array($v['sort'], ['cheap', 'expensive'], true)) {
            // Без ціни («договірна», 0) — в кінці списку
            $query->orders = null;
            $query->orderByRaw('(' . self::PRICE_UAH . ') <= 0')
                ->orderByRaw('(' . self::PRICE_UAH . ') ' . ($v['sort'] === 'cheap' ? 'ASC' : 'DESC'))
                ->orderBy('ads.date_active', 'desc');
        }

        return $query;
    }

    /** Чи обрано хоч один фільтр або нестандартне сортування (для noindex) */
    public function isActive(): bool
    {
        foreach ($this->values as $key => $value) {
            if ($value !== null && !($key === 'sort' && $value === 'new')) {
                return true;
            }
        }
        return false;
    }

    /** Непорожні параметри — для appends() у пагінації й посилань */
    public function query(): array
    {
        return array_filter($this->values, function ($v) {
            return $v !== null;
        });
    }

    /**
     * Активні фільтри (без сортування): [['key' => ..., 'url' => посилання без цього фільтра]]
     * Підписи формує шаблон — з урахуванням мови сайту.
     */
    public function chips(): array
    {
        $v = $this->values;
        $without = function (...$keys) {
            $q = array_diff_key(request()->except('page'), array_flip($keys));
            return url()->current() . ($q ? '?' . http_build_query($q) : '');
        };

        $chips = [];
        if ($v['price_from'] !== null || $v['price_to'] !== null) {
            $chips[] = ['key' => 'price', 'url' => $without('price_from', 'price_to')];
        }
        foreach (['seller', 'condition', 'in_stock'] as $key) {
            if ($v[$key]) {
                $chips[] = ['key' => $key, 'url' => $without($key)];
            }
        }

        return $chips;
    }

    /** Посилання «Скинути фільтри» — зберігає пошуковий запит і інші параметри сторінки */
    public function resetUrl(): string
    {
        $q = array_diff_key(request()->except('page'), $this->values);
        return url()->current() . ($q ? '?' . http_build_query($q) : '');
    }

    /** Скільки фільтрів обрано (без сортування) — для кнопки «Фільтри (N)» */
    public function count(): int
    {
        return count($this->chips());
    }
}
