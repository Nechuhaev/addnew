<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\SearchQuery;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Адмінка: «Пошукові запити» — що шукають відвідувачі і чого не знаходять.
 */
class SearchQueryController extends Controller
{
    const PERIODS = [7, 30, 90, 365];

    public function index(Request $request)
    {
        $days = in_array((int) $request->get('days'), self::PERIODS, true) ? (int) $request->get('days') : 30;
        $source = array_key_exists($request->get('source'), SearchQuery::SOURCES) ? $request->get('source') : null;
        $filter = trim((string) $request->get('q'));
        $from = Carbon::today()->subDays($days - 1);

        $base = function () use ($from, $source, $filter) {
            $q = SearchQuery::where('created_at', '>=', $from);
            if ($source) {
                $q->where('source', $source);
            }
            if ($filter !== '') {
                $q->where('query', 'LIKE', '%' . addcslashes(SearchQuery::normalize($filter), '%_\\') . '%');
            }
            return $q;
        };

        $totals = $base()->selectRaw('COUNT(*) AS searches, COUNT(DISTINCT query) AS unique_queries, SUM(results = 0) AS zero')->first();

        $dailyRows = $base()->selectRaw('DATE(created_at) AS d, COUNT(*) AS n, SUM(results = 0) AS zero')
            ->groupBy('d')->pluck('n', 'd');
        $dailyZero = $base()->selectRaw('DATE(created_at) AS d, SUM(results = 0) AS zero')
            ->groupBy('d')->pluck('zero', 'd');
        $daily = [];
        for ($d = $from->copy(); $d->lte(Carbon::today()); $d->addDay()) {
            $key = $d->toDateString();
            $daily[] = ['date' => $d->copy(), 'n' => (int) ($dailyRows[$key] ?? 0), 'zero' => (int) ($dailyZero[$key] ?? 0)];
        }

        $top = $base()->selectRaw('query, COUNT(*) AS n, ROUND(AVG(results)) AS avg_results, SUM(results = 0) AS zero, MAX(created_at) AS last_at')
            ->groupBy('query')->orderByDesc('n')->orderBy('query')->limit(50)->get();

        $zero = $base()->where('results', 0)
            ->selectRaw('query, COUNT(*) AS n, MAX(created_at) AS last_at')
            ->groupBy('query')->orderByDesc('n')->orderByDesc('last_at')->limit(50)->get();

        $bySource = $base()->selectRaw('source, COUNT(*) AS n')->groupBy('source')->pluck('n', 'source');
        $byLocale = $base()->selectRaw('locale, COUNT(*) AS n')->groupBy('locale')->pluck('n', 'locale');

        $recent = $base()->orderByDesc('id')->limit(50)->get();
        $shopNames = User::whereIn('id', $recent->pluck('shop_id')->filter()->unique())->get()->keyBy('id');

        return view('admin.search.index', compact(
            'days', 'source', 'filter', 'totals', 'daily', 'top', 'zero', 'bySource', 'byLocale', 'recent', 'shopNames'
        ) + ['periods' => self::PERIODS, 'sources' => SearchQuery::SOURCES]);
    }
}
