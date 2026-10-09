<?php

namespace App\Http\Controllers\Admin\Shop;

use App\Http\Controllers\Controller;
use App\Import;
use App\ShopFeed;
use Illuminate\Http\Request;

/**
 * Адмінка: фіди магазинів (автооновлення товарів за URL).
 */
class ShopFeedController extends Controller
{
    public function index(Request $request)
    {
        $query = ShopFeed::with('user')->orderByRaw("status = 'failed' DESC")->orderByDesc('fail_count')->orderByDesc('last_run_at');
        if ($request->get('status') === 'failed') {
            $query->where('status', 'failed');
        }
        $feeds = $query->paginate(50)->appends($request->only('status'));
        $lastRuns = Import::whereIn('id', function ($q) use ($feeds) {
            $q->selectRaw('MAX(id)')->from('imports')->whereIn('feed_id', $feeds->pluck('id'))->groupBy('feed_id');
        })->get()->keyBy('feed_id');

        return view('admin.shops.feeds', [
            'feeds' => $feeds,
            'lastRuns' => $lastRuns,
            'failedCount' => ShopFeed::where('status', 'failed')->count(),
        ]);
    }

    public function run($id)
    {
        $feed = ShopFeed::findOrFail($id);
        if (!$feed->isRunning()) {
            $feed->forceFill(['next_run_at' => now(), 'enabled' => true])->save();
        }
        return back()->with('success', 'Фід буде оновлено протягом 5 хвилин.');
    }
}
