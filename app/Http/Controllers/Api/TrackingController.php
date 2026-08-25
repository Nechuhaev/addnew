<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\ProductView;
use App\ShopView;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    protected const ALLOWED_EVENTS = ['click_contacts', 'click_shop_link'];

    /**
     * POST /api/track/product/{id}
     * body: { "event": "click_contacts" | "click_shop_link" }
     */
    public function trackProductEvent(Request $request, $adId)
    {
        $event = $request->input('event');

        if (!in_array($event, self::ALLOWED_EVENTS, true)) {
            return response()->json(['ok' => false], 422);
        }

        ProductView::record((int) $adId, $request->ip(), $event);

        return response()->json(['ok' => true]);
    }

    /**
     * POST /api/track/product/{id}/duration
     * body: { "seconds": 42 }
     * Викликається через navigator.sendBeacon при виході зі сторінки —
     * тому без CSRF-перевірки (beacon не може її пройти) і без дедуплікації
     * (кожен візит має власну тривалість).
     */
    public function trackProductDuration(Request $request, $adId)
    {
        $seconds = (int) $request->input('seconds', 0);
        ProductView::recordDuration((int) $adId, $request->ip(), $seconds);

        return response()->json(['ok' => true]);
    }

    /**
     * POST /api/track/shop/{userId}
     */
    public function trackShopView(Request $request, $userId)
    {
        ShopView::record((int) $userId, $request->ip());

        return response()->json(['ok' => true]);
    }
}