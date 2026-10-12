<?php

namespace App\Http\Controllers\Admin\Shop;

use App\Http\Controllers\Controller;
use App\Services\ShopStatsService;
use App\Services\StatsPeriod;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ShopStatsController extends Controller
{
    const PER_PAGE = [25, 50, 100];

    // Колонки зведеної таблиці: ключ => назва (числові мають фільтр «від»)
    const OVERVIEW_NUMERIC = [
        'products_count' => 'Товарів',
        'active_products' => 'Активних',
        'shop_views' => 'Перегл. магазину',
        'product_views' => 'Перегл. товарів',
        'click_contacts' => 'Контакти',
        'click_shop_link' => 'Переходи',
        'ctr' => 'CTR, %',
    ];

    // Повні назви — підказка при наведенні на заголовок
    const OVERVIEW_TITLES = [
        'products_count' => 'Усього товарів магазину',
        'active_products' => 'Опубліковані й в наявності',
        'shop_views' => 'Перегляди сторінки магазину за період',
        'product_views' => 'Перегляди сторінок товарів за період',
        'click_contacts' => 'Кліки «Показати контакти» за період',
        'click_shop_link' => 'Переходи на сайт магазину за період',
        'ctr' => 'Переходи на сайт / перегляди товарів, %',
    ];

    const DETAIL_NUMERIC = [
        'views' => 'Переглядів',
        'click_contacts' => 'Показ контактів',
        'click_shop_link' => 'Переходів на сайт',
        'avg_duration' => 'Сер. час',
    ];

    protected $shopStatsService;

    public function __construct(ShopStatsService $shopStatsService)
    {
        $this->shopStatsService = $shopStatsService;
    }

    public function index(Request $request)
    {
        $period = StatsPeriod::fromRequest($request);
        $rows = $this->shopStatsService->getAllShopsOverview($period);

        $q = mb_strtolower(trim((string) $request->get('q')));
        if ($q !== '') {
            $rows = $rows->filter(function ($r) use ($q) {
                return mb_strpos(mb_strtolower($r['name'] . ' ' . $r['email'] . ' ' . $r['site'] . ' #' . $r['id']), $q) !== false;
            });
        }
        if ($request->boolean('active_only')) {
            $rows = $rows->filter(function ($r) {
                return $r['shop_views'] + $r['product_views'] + $r['click_contacts'] + $r['click_shop_link'] > 0;
            });
        }
        $rows = $this->applyMinFilters($rows, $request, array_keys(self::OVERVIEW_NUMERIC));

        $totals = [];
        foreach (array_keys(self::OVERVIEW_NUMERIC) as $key) {
            $totals[$key] = $key === 'ctr' ? null : $rows->sum($key);
        }
        $totals['ctr'] = $totals['product_views'] ? round($totals['click_shop_link'] / $totals['product_views'] * 100, 1) : 0;

        [$sort, $dir] = $this->sort($request, array_merge(['name', 'id'], array_keys(self::OVERVIEW_NUMERIC)), 'product_views');
        $rows = $this->sortRows($rows, $sort, $dir);

        return view('admin.shops.stats-overview', [
            'shops' => $this->paginate($rows, $request),
            'period' => $period,
            'sort' => $sort,
            'dir' => $dir,
            'numeric' => self::OVERVIEW_NUMERIC,
            'titles' => self::OVERVIEW_TITLES,
            'totals' => $totals,
            'shopsCount' => $rows->count(),
            'perPageOptions' => self::PER_PAGE,
        ]);
    }

    public function show(Request $request, $id)
    {
        $shop = User::findOrFail($id);
        $period = StatsPeriod::fromRequest($request);

        $products = $this->shopStatsService->getProductStats($shop->id, $period);
        $totals = $this->shopStatsService->getTotals($shop->id, $products, $period);

        $q = mb_strtolower(trim((string) $request->get('q')));
        if ($q !== '') {
            $products = $products->filter(function ($p) use ($q) {
                return mb_strpos(mb_strtolower($p['name'] . ' #' . $p['id']), $q) !== false;
            });
        }
        if ($request->get('state') === 'active') {
            $products = $products->where('active', true);
        } elseif ($request->get('state') === 'inactive') {
            $products = $products->where('active', false);
        }
        $products = $this->applyMinFilters($products, $request, array_keys(self::DETAIL_NUMERIC));

        [$sort, $dir] = $this->sort($request, array_merge(['name', 'id'], array_keys(self::DETAIL_NUMERIC)), 'views');
        $products = $this->sortRows($products, $sort, $dir);

        return view('admin.shops.stats-detail', [
            'shop' => $shop,
            'products' => $this->paginate($products, $request),
            'totals' => $totals,
            'period' => $period,
            'sort' => $sort,
            'dir' => $dir,
            'numeric' => self::DETAIL_NUMERIC,
            'productsCount' => $products->count(),
            'perPageOptions' => self::PER_PAGE,
        ]);
    }

    /** Фільтри «від» по числових колонках: min[ключ]=N */
    protected function applyMinFilters(Collection $rows, Request $request, array $keys): Collection
    {
        $min = (array) $request->get('min', []);
        foreach ($keys as $key) {
            if (isset($min[$key]) && $min[$key] !== '' && is_numeric($min[$key])) {
                $v = (float) $min[$key];
                $rows = $rows->filter(function ($r) use ($key, $v) {
                    return (float) ($r[$key] ?? 0) >= $v;
                });
            }
        }
        return $rows;
    }

    protected function sort(Request $request, array $allowed, string $default): array
    {
        $sort = in_array($request->get('sort'), $allowed, true) ? $request->get('sort') : $default;
        $dir = $request->get('dir') === 'asc' ? 'asc' : 'desc';
        return [$sort, $dir];
    }

    protected function sortRows(Collection $rows, string $sort, string $dir): Collection
    {
        $rows = $rows->sortBy(function ($r) use ($sort) {
            $v = $r[$sort] ?? null;
            return is_string($v) ? mb_strtolower($v) : (float) $v;
        }, SORT_REGULAR, $dir === 'desc');
        return $rows->values();
    }

    protected function paginate(Collection $rows, Request $request): LengthAwarePaginator
    {
        $perPage = in_array((int) $request->get('per_page'), self::PER_PAGE, true) ? (int) $request->get('per_page') : self::PER_PAGE[0];
        $page = max(1, (int) $request->get('page', 1));
        return (new LengthAwarePaginator($rows->forPage($page, $perPage)->values(), $rows->count(), $perPage, $page, [
            'path' => $request->url(),
        ]))->appends($request->except('page'));
    }
}
