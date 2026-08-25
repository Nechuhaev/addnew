<?php
namespace App\Http\Controllers\Front\User\Shop;
use App\Ad;
use App\Http\Controllers\Controller;
use App\ProductView;
use App\ShopView;
use Illuminate\Support\Facades\Auth;

class ShopStatsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        if (!$user->is_shop_owner) {
            abort(403, 'Доступ разрешен только владельцам магазинов.');
        }

        $products = Ad::where('user_id', $user->id)
            ->where('is_product', 1)
            ->orderBy('created_at', 'desc')
            ->get();

        $productIds = $products->pluck('id')->toArray();

        // Одним запитом дістаємо агреговану статистику по всіх товарах
        // магазину одразу — щоб не робити N+1 запитів у циклі.
        $rawStats = ProductView::whereIn('ad_id', $productIds)
            ->selectRaw('ad_id, event_type, COUNT(*) as cnt, AVG(duration_seconds) as avg_duration')
            ->groupBy('ad_id', 'event_type')
            ->get()
            ->groupBy('ad_id');

        $productsWithStats = $products->map(function ($product) use ($rawStats) {
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

        $shopViewsTotal = ShopView::where('user_id', $user->id)->count();

        $totals = [
            'shop_views' => $shopViewsTotal,
            'product_views' => $productsWithStats->sum('views'),
            'click_contacts' => $productsWithStats->sum('click_contacts'),
            'click_shop_link' => $productsWithStats->sum('click_shop_link'),
        ];

        return view('front.user.profile.shop.stats')->with([
            'products' => $productsWithStats,
            'totals' => $totals,
        ]);
    }
}