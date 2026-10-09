<?php

namespace App\Http\Controllers\Admin\Shop;

use App\Ad;
use App\Http\Controllers\Controller;
use App\Mail\ShopAdminMessage;
use App\ShopMessage;
use App\ShopReview;
use App\Conversation;
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
        // (збігається), жовтої іконки-попередження (автоматично виправлено)
        // чи окремої позначки "домен недоступний" (сайт узагалі не відповідає,
        // на відміну від "сайт живий, email просто не знайдено").
        $emailCheckStatus = DB::table('shop_email_checks as t1')
            ->whereIn('user_id', $shopIds)
            ->whereRaw('t1.checked_at = (SELECT MAX(t2.checked_at) FROM shop_email_checks t2 WHERE t2.user_id = t1.user_id)')
            ->get(['user_id', 'matched', 'status'])
            ->keyBy('user_id');

        foreach ($shops as $shop) {
            $checkRow = $emailCheckStatus->get($shop->id);
            $shop->email_matched = $checkRow ? (bool) $checkRow->matched : null; // null = ще не перевірялось
            $shop->email_check_status = $checkRow->status ?? null;
        }

        // Дата останнього надісланого запрошення на реєстрацію/підключення
        // (лист із темою "Запрошення підключити...") — щоб бачити в списку,
        // кому вже писали, а кому ще ні, і не дублювати розсилку.
        $inviteSentByShop = DB::table('shop_messages')
            ->whereIn('shop_user_id', $shopIds)
            ->where('subject', 'LIKE', '%Запрошення підключити%')
            ->select('shop_user_id', DB::raw('MAX(created_at) as last_sent'))
            ->groupBy('shop_user_id')
            ->pluck('last_sent', 'shop_user_id');

        foreach ($shops as $shop) {
            $shop->invite_sent_at = $inviteSentByShop->get($shop->id);
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

        $messageTemplates = \App\ShopMessageTemplate::orderBy('name')->get();

        $feed = \App\ShopFeed::where('user_id', $shop->id)->first();

        return view('admin.shops.edit', [
            'shop' => $shop,
            'feed' => $feed,
            'feedRuns' => $feed ? \App\Import::where('feed_id', $feed->id)->latest()->limit(5)->get() : collect(),
            'messages' => $messages,
            'messageTemplates' => $messageTemplates,
        ]);
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
            'info_uk' => 'nullable|string|max:5000',
            'site_url' => 'nullable|url|max:255',
            'country' => 'nullable|string|max:100',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:3072',
        ] + \App\Services\ShopDelivery::rules(), [
            'free_delivery_from.*' => 'Сума безкоштовної доставки — ціле число, більше 0.',
            'delivery_note.max' => 'Примітка до доставки — не більше 500 символів.',
            'delivery_methods.*' => 'Вибрано невідомий спосіб доставки.',
            'payment_methods.*' => 'Вибрано невідомий спосіб оплати.',
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
            \App\Services\ShopDelivery::normalize($validated),
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

        // Позначаємо товари, чиє джерело виключено з моніторингу цін
        // (захищені від ботів сайти) — щоб було видно в списку одразу,
        // а не тільки в логах команди products:monitor-prices.
        $skippedDomains = \App\SkippedDomain::list();
        // Позначка — коли ЖОДНЕ з джерел (сайт магазину, конкурент) не перевіряється.
        foreach ($products as $product) {
            $sources = array_filter([$product->getOriginal('url'), $product->competitor_url]);
            $skipped = 0;
            foreach ($sources as $sourceUrl) {
                $host = parse_url($sourceUrl, PHP_URL_HOST);
                foreach ($skippedDomains as $domain) {
                    if ($host && \Illuminate\Support\Str::contains($host, $domain)) {
                        $skipped++;
                        break;
                    }
                }
            }
            $product->monitoring_skipped = $sources && $skipped === count($sources);
        }

        return view('admin.shops.products', [
            'shop' => $shop,
            'products' => $products,
            'competitorChecks' => \App\ProductPriceCheck::lastCompetitorChecks($products->pluck('id')->all()),
            'inactiveCount' => $inactiveCount,
        ]);
    }

    /**
     * Відгуки конкретного магазину — з IP того, хто залишив, щоб
     * помітити накручування (кілька відгуків з однієї адреси тощо).
     */
    public function reviews($id)
    {
        $shop = User::findOrFail($id);

        $reviews = ShopReview::where('shop_user_id', $shop->id)
            ->with('reviewer')
            ->orderByDesc('created_at')
            ->paginate(30);

        // Позначаємо відгуки, чия IP зустрічається БІЛЬШЕ ОДНОГО РАЗУ
        // серед відгуків ЦЬОГО магазину — явна ознака накрутки з одного
        // пристрою/мережі під різними акаунтами.
        $ipCounts = ShopReview::where('shop_user_id', $shop->id)
            ->whereNotNull('ip_address')
            ->selectRaw('ip_address, COUNT(*) as cnt')
            ->groupBy('ip_address')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('cnt', 'ip_address');

        foreach ($reviews as $review) {
            $review->suspicious_ip = $review->ip_address && $ipCounts->has($review->ip_address);
        }

        return view('admin.shops.reviews', [
            'shop' => $shop,
            'reviews' => $reviews,
        ]);
    }

    /**
     * Усі діалоги чату цього магазину (як продавця) — для модерації й
     * перегляду переписки з покупцями. Сортовано за свіжістю, з
     * повним текстом останнього повідомлення для огляду без кліку.
     */
    public function messages($id)
    {
        $shop = User::findOrFail($id);

        $conversations = Conversation::where('shop_user_id', $shop->id)
            ->with(['buyer', 'ad'])
            ->orderByDesc('last_message_at')
            ->paginate(30);

        foreach ($conversations as $c) {
            $c->lastMessage = $c->messages()->orderByDesc('id')->first();
            $c->messagesCount = $c->messages()->count();
        }

        return view('admin.shops.messages', [
            'shop' => $shop,
            'conversations' => $conversations,
        ]);
    }

    /**
     * Повна переписка одного діалогу — для адміністратора, лише
     * перегляд (без можливості відповісти від імені когось).
     */
    public function messagesShow($shopId, $conversationId)
    {
        $shop = User::findOrFail($shopId);
        $conversation = Conversation::where('id', $conversationId)
            ->where('shop_user_id', $shop->id)
            ->with(['buyer', 'ad'])
            ->firstOrFail();

        $messages = $conversation->messages()->with('sender')->get();

        return view('admin.shops.messages-show', [
            'shop' => $shop,
            'conversation' => $conversation,
            'messages' => $messages,
        ]);
    }

    /**
     * Видалити один відгук (накручений/образливий/спам).
     */
    public function deleteReview($shopId, $reviewId)
    {
        $review = ShopReview::where('id', $reviewId)->where('shop_user_id', $shopId)->firstOrFail();
        $review->delete();

        return redirect(route('admin.shops.reviews', $shopId))->with('success', 'Відгук видалено.');
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
