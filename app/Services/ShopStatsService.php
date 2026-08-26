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
    public function getProductStats(int $userId)
    {
        $products = Ad::where('user_id', $userId)
            ->where('is_product', 1)
            ->orderBy('created_at', 'desc')
            ->get();

        $productIds = $products->pluck('id')->toArray();

        $rawStats = ProductView::whereIn('ad_id', $productIds)
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
                'views' => $views,
                'click_contacts' => $clickContacts,
                'click_shop_link' => $clickShopLink,
                'avg_duration' => $avgDuration,
            ];
        })->sortByDesc('views')->values();
    }

    /**
     * Підсумкові тотали для одного магазину (використовується і в
     * кабінеті магазину, і на детальній сторінці адмінки).
     */
    public function getTotals(int $userId, $productStats = null): array
    {
        $productStats = $productStats ?? $this->getProductStats($userId);

        return [
            'shop_views' => ShopView::where('user_id', $userId)->count(),
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
    public function getAllShopsOverview()
    {
        // Магазини = користувачі, у яких є хоча б один товар (is_product=1) —
        // та сама умова, що й у списку /admin/shops.
        $shopUserIds = Ad::where('is_product', 1)
            ->distinct()
            ->pluck('user_id');

        $productViewsByUser = ProductView::join('ads', 'product_views.ad_id', '=', 'ads.id')
            ->whereIn('ads.user_id', $shopUserIds)
            ->selectRaw('ads.user_id, product_views.event_type, COUNT(*) as cnt')
            ->groupBy('ads.user_id', 'product_views.event_type')
            ->get()
            ->groupBy('user_id');

        $shopViewsByUser = ShopView::whereIn('user_id', $shopUserIds)
            ->selectRaw('user_id, COUNT(*) as cnt')
            ->groupBy('user_id')
            ->pluck('cnt', 'user_id');

        $productsCountByUser = Ad::where('is_product', 1)
            ->whereIn('user_id', $shopUserIds)
            ->selectRaw('user_id, COUNT(*) as cnt')
            ->groupBy('user_id')
            ->pluck('cnt', 'user_id');

        $users = \App\User::whereIn('id', $shopUserIds)->get(['id', 'firstname', 'email']);

        return $users->map(function ($user) use ($productViewsByUser, $shopViewsByUser, $productsCountByUser) {
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
                'products_count' => $productsCountByUser->get($user->id, 0),
                'shop_views' => $shopViewsByUser->get($user->id, 0),
                'product_views' => $productViews,
                'click_contacts' => $clickContacts,
                'click_shop_link' => $clickShopLink,
            ];
        })->sortByDesc('product_views')->values();
    }
}