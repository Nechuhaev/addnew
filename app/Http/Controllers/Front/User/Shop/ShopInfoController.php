<?php

namespace App\Http\Controllers\Front\User\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\UpdateShopInfoRequest;
use App\Services\ShopInfoService;
use Illuminate\Support\Facades\Auth;

class ShopInfoController extends Controller
{
    protected $shopInfoService;

    public function __construct(ShopInfoService $shopInfoService)
    {
        $this->shopInfoService = $shopInfoService;
    }

    public function index()
    {
        $user = Auth::user();

        if (!$user->is_shop_owner) {
            abort(403, 'Доступ разрешен только владельцам магазинов.');
        }

        return view('front.user.profile.shop.info', compact('user'));
    }

    public function update(UpdateShopInfoRequest $request)
    {
        $user = Auth::user();

        $this->shopInfoService->update(
            $user,
            $request->validated(),
            $request->file('logo')
        );

        return redirect()->route('profile.shop.info')->with('success', 'Информация о магазине обновлена!');
    }
}
