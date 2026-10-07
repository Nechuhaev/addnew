<?php

namespace App\Http\Controllers\Admin\Shop;

use App\Ad;
use App\Http\Controllers\Controller;
use App\ProductPriceCheck;
use Illuminate\Http\Request;

/**
 * Моніторинг цін конкурентів (products:monitor-prices, перевірки competitor_*):
 * усі товари з посиланням на конкурента, остання перевірка й лічильники за добу.
 */
class CompetitorMonitorController extends Controller
{
    const PROBLEM_STATUSES = ['competitor_suspicious', 'competitor_404', 'competitor_not_found', 'competitor_fetch_error'];

    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all');

        $lastChecks = ProductPriceCheck::selectRaw('ad_id, MAX(id) AS last_check_id')
            ->where('status', 'like', 'competitor\\_%')
            ->groupBy('ad_id');

        $query = Ad::with(['user', 'currency'])
            ->select('ads.*')
            ->where('is_product', 1)
            ->whereNotNull('competitor_url')
            ->where('competitor_url', '!=', '')
            ->leftJoinSub($lastChecks, 'lc', 'lc.ad_id', '=', 'ads.id')
            ->leftJoin('product_price_checks AS pc', 'pc.id', '=', 'lc.last_check_id');

        if ($filter === 'problems') {
            // помилки, підозрілі ціни й «інша валюта» (ціна так і не оновлюється)
            $query->where(function ($q) {
                $q->whereIn('pc.status', self::PROBLEM_STATUSES)
                  ->orWhere('pc.note', 'like', ProductPriceCheck::CURRENCY_MISMATCH_NOTE . '%');
            });
        } elseif ($filter === 'changed') {
            $query->where('pc.status', 'competitor_success')->where('pc.price_applied', 1);
        } elseif ($filter === 'never') {
            $query->whereNull('pc.id');
        }

        $products = $query->orderByRaw('pc.checked_at IS NULL')
            ->orderByDesc('pc.checked_at')
            ->paginate(50)
            ->appends(['filter' => $filter]);

        $checks = ProductPriceCheck::lastCompetitorChecks($products->pluck('id')->all());

        // Скільки перевірок конкурентів за останню добу — за результатом
        $day = ProductPriceCheck::where('status', 'like', 'competitor\\_%')
            ->where('checked_at', '>=', now()->subDay())
            ->get(['status', 'price_applied', 'note']);

        $summary = [
            'total' => $day->count(),
            'changed' => $day->where('status', 'competitor_success')->where('price_applied', true)->count(),
            'unchanged' => $day->filter(function ($c) {
                return $c->status === 'competitor_success' && !$c->price_applied && !$c->isCurrencyMismatch();
            })->count(),
            'currency' => $day->filter(function ($c) {
                return $c->isCurrencyMismatch();
            })->count(),
            'suspicious' => $day->where('status', 'competitor_suspicious')->count(),
            'errors' => $day->whereIn('status', ['competitor_404', 'competitor_not_found', 'competitor_fetch_error'])->count(),
        ];

        return view('admin.shops.competitor-monitor', [
            'products' => $products,
            'checks' => $checks,
            'summary' => $summary,
            'filter' => $filter,
            'totalWithCompetitor' => Ad::where('is_product', 1)->whereNotNull('competitor_url')->where('competitor_url', '!=', '')->count(),
        ]);
    }
}
