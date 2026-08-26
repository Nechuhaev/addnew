<?php

namespace App\Http\Controllers\Admin\Shop;

use App\Http\Controllers\Controller;
use App\Services\ShopStatsService;
use App\User;

class ShopStatsController extends Controller
{
    protected $shopStatsService;

    public function __construct(ShopStatsService $shopStatsService)
    {
        $this->shopStatsService = $shopStatsService;
    }

    public function index()
    {
        $shops = $this->shopStatsService->getAllShopsOverview();

        return view('admin.shops.stats-overview', ['shops' => $shops]);
    }

    public function show($id)
    {
        $shop = User::findOrFail($id);

        $products = $this->shopStatsService->getProductStats($shop->id);
        $totals = $this->shopStatsService->getTotals($shop->id, $products);

        return view('admin.shops.stats-detail', [
            'shop' => $shop,
            'products' => $products,
            'totals' => $totals,
        ]);
    }
}