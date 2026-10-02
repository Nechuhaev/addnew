<?php

namespace App\Http\Controllers\Front\Ad;

use App\Http\Controllers\Controller;
use App\ShopReview;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShopReviewController extends Controller
{
    /**
     * Залишити (або оновити наявний) відгук магазину. Будь-який
     * зареєстрований користувач, по одному відгуку на магазин —
     * повторна подача оновлює попередній, не створює дублікат.
     */
    public function store(Request $request, $shopId)
    {
        $shop = User::findOrFail($shopId);

        if (Auth::id() == $shop->id) {
            return redirect()->back()->with('error', 'Не можна залишити відгук власному магазину.');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
        ], [
            'rating.required' => 'Оберіть оцінку від 1 до 5.',
            'rating.min' => 'Оцінка має бути від 1 до 5.',
            'rating.max' => 'Оцінка має бути від 1 до 5.',
        ]);

        ShopReview::updateOrCreate(
            ['shop_user_id' => $shop->id, 'reviewer_user_id' => Auth::id()],
            ['rating' => $validated['rating'], 'comment' => $validated['comment'] ?? null]
        );

        return redirect(route('author', $shop->id))->with('success', 'Дякуємо за відгук!');
    }
}
