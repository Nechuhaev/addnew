<?php

namespace App\Http\Controllers\Admin\Shop;

use App\Ad;
use App\Http\Controllers\Controller;
use App\Mail\ShopAdminMessage;
use App\ShopMessage;
use App\Services\ShopInfoService;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ShopController extends Controller
{
    protected $shopInfoService;

    public function __construct(ShopInfoService $shopInfoService)
    {
        $this->shopInfoService = $shopInfoService;
    }

    /**
     * Список власників магазинів. Показуємо будь-кого з товарами
     * (is_product=1), незалежно від прапорця is_shop_owner. Підтримує
     * пошук по назві, email, телефону, країні й ID.
     */
    public function index(Request $request)
    {
        $order = $request->get('order', 'created_at');
        $direction = $request->get('direction', 'desc');
        $search = $request->get('search');

        $query = User::whereHas('ads', function ($q) {
                $q->where('is_product', 1);
            })
            ->withCount(['ads as products_count' => function ($q) {
                $q->where('is_product', 1);
            }]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'LIKE', "%{$search}%")
                    ->orWhere('firstname', 'LIKE', "%{$search}%")
                    ->orWhere('lastname', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('telephone', 'LIKE', "%{$search}%")
                    ->orWhere('country', 'LIKE', "%{$search}%")
                    // Пошук по РЕАЛЬНІЙ країні товарів магазину (та сама
                    // логіка, що визначає колонку "Страна" в списку) —
                    // не тільки по ручному полю country, яке зазвичай
                    // порожнє.
                    ->orWhereHas('ads', function ($q2) use ($search) {
                        $q2->where('is_product', 1)
                            ->whereHas('city', function ($q3) use ($search) {
                                $q3->whereHas('region', function ($q4) use ($search) {
                                    $q4->whereHas('country', function ($q5) use ($search) {
                                        $q5->where('name', 'LIKE', "%{$search}%");
                                    });
                                });
                            });
                    });
            });
        }

        $shops = $query->orderBy($order, $direction)->paginate(15)->appends($request->query());

        // Країна визначається не вручну, а так само, як на публічній
        // сторінці /stores — через реальні товари магазину: товар має
        // city_id -> AdCity -> region -> country. Один магазин може мати
        // товари в кількох країнах одразу — показуємо всі через кому.
        $shopIds = $shops->pluck('id');

        $countriesByShop = DB::table('ads')
            ->join('ad_cities', 'ads.city_id', '=', 'ad_cities.id')
            ->join('ad_regions', 'ad_cities.region_id', '=', 'ad_regions.id')
            ->join('ad_countries', 'ad_regions.country_id', '=', 'ad_countries.id')
            ->where('ads.is_product', 1)
            ->whereIn('ads.user_id', $shopIds)
            ->select('ads.user_id', 'ad_countries.name')
            ->distinct()
            ->get()
            ->groupBy('user_id')
            ->map(function ($rows) {
                return $rows->pluck('name')->unique()->implode(', ');
            });

        foreach ($shops as $shop) {
            $shop->countries_display = $countriesByShop->get($shop->id, '—');
        }

        // Статус останньої перевірки email — для зеленого підсвічування
        // (збігається) чи жовтої іконки-попередження (щойно автоматично
        // виправлено на актуальний з сайту магазину).
        $emailCheckStatus = DB::table('shop_email_checks as t1')
            ->whereIn('user_id', $shopIds)
            ->whereRaw('t1.checked_at = (SELECT MAX(t2.checked_at) FROM shop_email_checks t2 WHERE t2.user_id = t1.user_id)')
            ->pluck('matched', 'user_id');

        foreach ($shops as $shop) {
            $shop->email_matched = $emailCheckStatus->has($shop->id)
                ? (bool) $emailCheckStatus->get($shop->id)
                : null; // null = ще не перевірялось
        }

        return view('admin.shops.list', [
            'shops' => $shops,
            'order' => $order,
            'direction' => $direction,
            'search' => $search,
        ]);
    }

    /**
     * Форма редагування опису магазину.
     */
    public function edit($id)
    {
        $shop = User::findOrFail($id);

        $messages = ShopMessage::where('shop_user_id', $shop->id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('admin.shops.edit', ['shop' => $shop, 'messages' => $messages]);
    }

    /**
     * Надіслати лист власнику магазину, зберегти в історії незалежно
     * від того, вдалось відправити чи ні (щоб бачити спроби й помилки).
     */
    public function sendMessage($id, Request $request)
    {
        $shop = User::findOrFail($id);

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string|max:10000',
        ], [
            'subject.required' => 'Введіть тему листа',
            'body.required' => 'Введіть текст листа',
        ]);

        $sentOk = true;
        $errorMessage = null;

        try {
            Mail::to($shop->email)->send(new ShopAdminMessage($validated['subject'], $validated['body']));
        } catch (\Throwable $e) {
            $sentOk = false;
            $errorMessage = $e->getMessage();
        }

        ShopMessage::create([
            'shop_user_id' => $shop->id,
            'admin_user_id' => Auth::id(),
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'sent_to_email' => $shop->email,
            'sent_successfully' => $sentOk,
            'error_message' => $errorMessage,
        ]);

        if ($sentOk) {
            return redirect(route('admin.shops.edit', $shop->id))->with('success', 'Лист надіслано магазину.');
        }

        return redirect(route('admin.shops.edit', $shop->id))->with('error', 'Не вдалося надіслати лист: ' . $errorMessage);
    }

    /**
     * Зберегти опис магазину.
     */
    public function update($id, Request $request)
    {
        $shop = User::findOrFail($id);

        $validated = $request->validate([
            'firstname' => 'required|string|max:255',
            'telephone' => 'nullable|string|max:100',
            'email' => 'required|email|max:255',
            'info' => 'nullable|string|max:5000',
            'site_url' => 'nullable|url|max:255',
            'country' => 'nullable|string|max:100',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:3072',
        ], [
            'logo.image' => 'Недопустимий формат файлу.',
            'logo.mimes' => 'Недопустимий формат файлу.',
            'logo.max' => 'Максимальний розмір файлу: 2 МБ.',
            'banner.image' => 'Недопустимий формат файлу.',
            'banner.mimes' => 'Недопустимий формат файлу.',
            'banner.max' => 'Максимальний розмір файлу: 3 МБ.',
            'site_url.url' => 'Введіть коректне посилання (https://...)',
        ]);

        $this->shopInfoService->update(
            $shop,
            $validated,
            $request->file('logo'),
            $request->file('banner'),
            $request->boolean('delete_banner')
        );

        return redirect(route('admin.shops.edit', $id))->with('success', 'Інформацію про магазин оновлено!');
    }

    /**
     * Товари конкретного магазину.
     */
    public function products($id)
    {
        $shop = User::findOrFail($id);

        $products = Ad::where('user_id', $shop->id)
            ->where('is_product', 1)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $inactiveCount = Ad::where('user_id', $shop->id)
            ->where('is_product', 1)
            ->where('stock', '!=', 'in_stock')
            ->count();

        return view('admin.shops.products', [
            'shop' => $shop,
            'products' => $products,
            'inactiveCount' => $inactiveCount,
        ]);
    }

    /**
     * Масове видалення ВСІХ неактивних (немає в наявності) товарів
     * цього магазину одним кліком. Та сама умова, що визначає
     * позначку "НЕАКТИВНЕ" у списку — stock != in_stock.
     */
    public function bulkDeleteInactive($id)
    {
        $shop = User::findOrFail($id);

        $deleted = Ad::where('user_id', $shop->id)
            ->where('is_product', 1)
            ->where('stock', '!=', 'in_stock')
            ->delete();

        return redirect(route('admin.shops.products', $shop->id))
            ->with('success', "Видалено неактивних товарів: {$deleted}");
    }

    /**
     * Видалити товар (з адмінського списку товарів магазину).
     */
    public function deleteProduct($id)
    {
        $product = Ad::where('id', $id)->where('is_product', 1)->firstOrFail();
        $shopId = $product->user_id;
        $product->delete();

        return redirect(route('admin.shops.products', $shopId))->with('success', 'Товар видалено.');
    }

    /**
     * Видалити магазин повністю — і сам акаунт, і всі його оголошення/товари.
     * Незворотна дія, підтвердження — на рівні UI (confirm перед сабмітом).
     */
    public function destroy($id)
    {
        $shop = User::findOrFail($id);

        Ad::where('user_id', $shop->id)->delete();
        $shop->delete();

        return redirect(route('admin.shops'))->with('success', 'Магазин і всі його товари видалено.');
    }

    /**
     * Увійти в акаунт магазину (impersonation).
     * Тільки адмін може ініціювати; ID адміна зберігається в сесії,
     * щоб потім можна було повернутись назад.
     *
     * Заодно виставляємо is_shop_owner=1, якщо ще не виставлено —
     * інакше частина функцій магазину (напр. імпорт товарів) буде
     * недоступна через middleware('shop_owner') на тих роутах, хоча
     * товари в користувача фактично вже є.
     */
    public function impersonate($id)
    {
        $shop = User::findOrFail($id);

        if (!$shop->is_shop_owner) {
            $shop->setShopOwner(true);
        }

        session(['impersonator_id' => Auth::id()]);
        Auth::login($shop);

        return redirect(route('profile.shop.dashboard'))
            ->with('success', 'Ви увійшли як магазин: ' . $shop->username);
    }
}