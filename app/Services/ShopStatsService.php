<?php

namespace App\Services;

use App\Ad;
use App\ProductView;
use App\ShopView;

class ShopStatsService
{
    /**
     * Статистика по кожному товару конкретного магазину.
     *
     * @param int $userId
     * @return \Illuminate\Support\Collection
     */
    public function getProductStats(int $userId, ?StatsPeriod $period = null)
    {
        $products = Ad::where('user_id', $userId)
            ->where('is_product', 1)
            ->orderBy('created_at', 'desc')
            ->get();

        $productIds = $products->pluck('id')->toArray();

        $rawQuery = ProductView::whereIn('ad_id', $productIds);
        if ($period) {
            $period->apply($rawQuery, 'created_at');
        }
        $rawStats = $rawQuery
            ->selectRaw('ad_id, event_type, COUNT(*) as cnt, AVG(duration_seconds) as avg_duration')
            ->groupBy('ad_id', 'event_type')
            ->get()
            ->groupBy('ad_id');

        return $products->map(function ($product) use ($rawStats) {
            $rows = $rawStats->get($product->id, collect());

            $views = 0;
            $clickContacts = 0;
            $clickShopLink = 0;
            $avgDuration = null;

            foreach ($rows as $row) {
                if ($row->event_type === 'view') {
                    $views = (int) $row->cnt;
                } elseif ($row->event_type === 'click_contacts') {
                    $clickContacts = (int) $row->cnt;
                } elseif ($row->event_type === 'click_shop_link') {
                    $clickShopLink = (int) $row->cnt;
                } elseif ($row->event_type === 'time_on_page') {
                    $avgDuration = $row->avg_duration ? round($row->avg_duration) : null;
                }
            }

            return [
                'id' => $product->id,
                'name' => $product->name,
                'image' => $product->image,
                'url' => $product->url,
                'edit_url' => route('profile.shop.product.edit', ['id' => $product->id]),
                'views' => $views,
                'click_contacts' => $clickContacts,
                'click_shop_link' => $clickShopLink,
                'avg_duration' => $avgDuration,
                'active' => (int) $product->getOriginal('status') === 1 && $product->stock !== 'out_of_stock',
            ];
        })->sortByDesc('views')->values();
    }

    /**
     * Підсумкові тотали для одного магазину (використовується і в
     * кабінеті магазину, і на детальній сторінці адмінки).
     */
    public function getTotals(int $userId, $productStats = null, ?StatsPeriod $period = null): array
    {
        $productStats = $productStats ?? $this->getProductStats($userId, $period);
        $shopViews = ShopView::where('user_id', $userId);
        if ($period) {
            $period->apply($shopViews, 'created_at');
        }

        return [
            'shop_views' => $shopViews->count(),
            'product_views' => $productStats->sum('views'),
            'click_contacts' => $productStats->sum('click_contacts'),
            'click_shop_link' => $productStats->sum('click_shop_link'),
        ];
    }

    /**
     * Огляд по ВСІХ магазинах одразу (для адмінського списку) — один
     * рядок на магазин з підсумковими цифрами, без деталізації по товарах.
     * Ефективно, без N+1: усі агрегати одним запитом по всіх магазинах.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllShopsOverview(?StatsPeriod $period = null)
    {
        // Магазини = користувачі, у яких є хоча б один товар (is_product=1) —
        // та сама умова, що й у списку /admin/shops.
        $shopUserIds = Ad::where('is_product', 1)
            ->distinct()
            ->pluck('user_id');

        $productViewsQuery = ProductView::join('ads', 'product_views.ad_id', '=', 'ads.id')
            ->whereIn('ads.user_id', $shopUserIds);
        if ($period) {
            $period->apply($productViewsQuery, 'product_views.created_at');
        }
        $productViewsByUser = $productViewsQuery
            ->selectRaw('ads.user_id, product_views.event_type, COUNT(*) as cnt')
            ->groupBy('ads.user_id', 'product_views.event_type')
            ->get()
            ->groupBy('user_id');

        $shopViewsQuery = ShopView::whereIn('user_id', $shopUserIds);
        if ($period) {
            $period->apply($shopViewsQuery, 'created_at');
        }
        $shopViewsByUser = $shopViewsQuery
            ->selectRaw('user_id, COUNT(*) as cnt')
            ->groupBy('user_id')
            ->pluck('cnt', 'user_id');

        $productsCountByUser = Ad::where('is_product', 1)
            ->whereIn('user_id', $shopUserIds)
            ->selectRaw('user_id, COUNT(*) as cnt')
            ->groupBy('user_id')
            ->pluck('cnt', 'user_id');

        $activeCountByUser = Ad::where('is_product', 1)
            ->whereIn('user_id', $shopUserIds)
            ->where('status', 1)
            ->where(function ($q) {
                $q->whereNull('stock')->orWhere('stock', '!=', 'out_of_stock');
            })
            ->selectRaw('user_id, COUNT(*) as cnt')
            ->groupBy('user_id')
            ->pluck('cnt', 'user_id');

        $users = \App\User::whereIn('id', $shopUserIds)->get(['id', 'firstname', 'lastname', 'email', 'site_url']);

        return $users->map(function ($user) use ($productViewsByUser, $shopViewsByUser, $productsCountByUser, $activeCountByUser) {
            $rows = $productViewsByUser->get($user->id, collect());

            $productViews = 0;
            $clickContacts = 0;
            $clickShopLink = 0;

            foreach ($rows as $row) {
                if ($row->event_type === 'view') {
                    $productViews = (int) $row->cnt;
                } elseif ($row->event_type === 'click_contacts') {
                    $clickContacts = (int) $row->cnt;
                } elseif ($row->event_type === 'click_shop_link') {
                    $clickShopLink = (int) $row->cnt;
                }
            }

            return [
                'id' => $user->id,
                'name' => $user->firstname ?: $user->email,
                'email' => $user->email,
                'site' => $user->site_url ? (parse_url($user->site_url, PHP_URL_HOST) ?: $user->site_url) : '',
                'products_count' => (int) $productsCountByUser->get($user->id, 0),
                'active_products' => (int) $activeCountByUser->get($user->id, 0),
                'shop_views' => (int) $shopViewsByUser->get($user->id, 0),
                'product_views' => $productViews,
                'click_contacts' => $clickContacts,
                'click_shop_link' => $clickShopLink,
                // Частка переходів на сайт від переглядів товарів, %
                'ctr' => $productViews ? round($clickShopLink / $productViews * 100, 1) : 0,
            ];
        })->sortByDesc('product_views')->values();
    }
}
