<?php

namespace App\Http\Controllers\Front\User\Shop;

use App\Http\Controllers\Controller;
use App\Services\ShopDashboardService;
use Illuminate\Support\Facades\Auth;

class ShopDashboardController extends Controller
{
    protected $shopDashboardService;

    public function __construct(ShopDashboardService $shopDashboardService)
    {
        $this->shopDashboardService = $shopDashboardService;
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();

        if (!$user->is_shop_owner) {
            abort(403, 'Доступ разрешен только владельцам магазинов.');
        }

        $shopData = $this->shopDashboardService->getShopData($user);
        $products = $this->shopDashboardService->getProducts($user);

        return view('front.user.profile.shop.dashboard')->with([
            'shop' => $shopData,
            'products' => $products,
        ]);
    }
}
