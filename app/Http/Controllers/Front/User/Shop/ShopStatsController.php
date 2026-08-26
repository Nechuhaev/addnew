<?php
namespace App\Http\Controllers\Front\User\Shop;
use App\Http\Controllers\Controller;
use App\Services\ShopStatsService;
use Illuminate\Support\Facades\Auth;

class ShopStatsController extends Controller
{
    protected $shopStatsService;

    public function __construct(ShopStatsService $shopStatsService)
    {
        $this->shopStatsService = $shopStatsService;
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        if (!$user->is_shop_owner) {
            abort(403, 'Доступ разрешен только владельцам магазинов.');
        }

        $products = $this->shopStatsService->getProductStats($user->id);
        $totals = $this->shopStatsService->getTotals($user->id, $products);

        return view('front.user.profile.shop.stats')->with([
            'products' => $products,
            'totals' => $totals,
        ]);
    }
}