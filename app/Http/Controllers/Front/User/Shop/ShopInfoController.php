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
        $data = $request->validated();
        // Незняті чекбокси не приходять у запиті — без цього їх не можна було б зняти
        $data['delivery_methods'] = array_values(array_unique($data['delivery_methods'] ?? []));
        $data['payment_methods'] = array_values(array_unique($data['payment_methods'] ?? []));
        $data['free_delivery_from'] = $data['free_delivery_from'] ?? null;
        $data['delivery_note'] = $data['delivery_note'] ?? null;

        $this->shopInfoService->update(
            $user,
            $data,
            $request->file('logo'),
            $request->file('banner')
        );
        return redirect()->route('profile.shop.info')->with('success', 'Информация о магазине обновлена!');
    }
}