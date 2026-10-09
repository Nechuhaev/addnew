<?php

namespace App\Http\Controllers\Front\User;

use App\Ad;
use App\Favorite;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Обране користувача та налаштування сповіщень про зниження ціни.
 */
class FavoriteController extends Controller
{
    public function index()
    {
        $favorites = Favorite::with(['ad.currency', 'ad.user'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(20);

        return view('front.user.profile.favorites')->with([
            'favorites' => $favorites,
            'alertsOn' => (bool) Auth::user()->price_drop_emails,
        ]);
    }

    /**
     * Додати/прибрати з обраного (AJAX або звичайна форма).
     */
    public function toggle(Request $request, $adId)
    {
        $ad = Ad::with('currency')->where('id', (int) $adId)->where('status', 1)->first();
        $userId = Auth::id();

        $existing = Favorite::where('user_id', $userId)->where('ad_id', (int) $adId)->first();
        if ($existing) {
            $existing->delete();
            $active = false;
        } elseif ($ad) {
            $price = Favorite::priceUah($ad);
            Favorite::create([
                'user_id' => $userId,
                'ad_id' => $ad->id,
                'price_at_add_uah' => $price,
                'base_price_uah' => $price,
            ]);
            $active = true;
        } else {
            abort(404);
        }

        Favorite::forgetIds($userId);
        $count = Favorite::where('user_id', $userId)->count();

        if ($request->expectsJson()) {
            return response()->json(['active' => $active, 'count' => $count]);
        }
        return redirect()->back();
    }

    public function notify(Request $request, $id)
    {
        $favorite = Favorite::where('user_id', Auth::id())->findOrFail((int) $id);
        $favorite->notify = !$favorite->notify;
        if ($favorite->notify && $favorite->ad) {
            // Відлік зниження — від поточної ціни, щоб не прийшов лист про давню зміну
            $favorite->base_price_uah = Favorite::priceUah($favorite->ad);
        }
        $favorite->save();

        return redirect()->back();
    }

    public function destroy($id)
    {
        Favorite::where('user_id', Auth::id())->where('id', (int) $id)->delete();

        return redirect()->back();
    }

    /**
     * Увімкнути/вимкнути всі листи про зниження ціни.
     */
    public function alerts()
    {
        $user = Auth::user();
        $user->price_drop_emails = !$user->price_drop_emails;
        $user->save();

        return redirect()->back();
    }

    /**
     * Відписка за посиланням з листа (без входу).
     */
    public function unsubscribe($userId, $token)
    {
        $user = User::find((int) $userId);
        if (!$user || !hash_equals(Favorite::unsubscribeToken($user->id), (string) $token)) {
            abort(404);
        }
        $user->price_drop_emails = false;
        $user->save();

        return view('front.user.profile.favorites-unsubscribed');
    }
}
