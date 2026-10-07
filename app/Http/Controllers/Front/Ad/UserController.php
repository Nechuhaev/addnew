<?php

namespace App\Http\Controllers\Front\Ad;

use App\AdTag;
use App\Http\Controllers\Controller;
use App\SeoField;
use App\User;
use App\Ad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class UserController extends Controller
{
    public function page($user_id) {
        $user = User::find($user_id);

        if (!$user) abort(404);

        \App\ShopView::record($user->id, request()->ip());

        $seo_field = SeoField::where('index', 'ad-user')->first();

        if ($seo_field) {
            $entity_values = [
                '---user_name---'  => $user->username,
                '---ads_count---'  => $user->ads()->count(),
            ];
            $meta = [
                'meta_title' => $entity->meta_title ?? strtr($seo_field->meta_title, $entity_values),
                'meta_description' => $entity->meta_description ?? strtr($seo_field->meta_description, $entity_values),
                'description' => $entity->content ?? strtr($seo_field->description, $entity_values)
            ];
        } else {
            $meta = [
                'meta_title' => "Объявления пользователя $user->username на доске объявлений addnew.biz",
                'meta_description' => "Объявления пользователя $user->username на доске объявлений addnew.biz",
                'description' => "Объявления пользователя $user->username на доске объявлений addnew.biz",
            ];
        }

        if (request()->get('page')) {
            $meta['description'] = false;
        }

        $results = Ad::getAds()
            ->where('user_id', $user->id)
            ->paginate(15);

        $ads = Ad::getLoopArray($results);

        $microdata_info = DB::table('ads')
            ->selectRaw('min(ads.price) as min, max(ads.price) as max, count(ads.id) as ads_count')
            ->where('user_id', $user->id)
            ->where('ads.price', '>', 0)
            ->first();

        $is_shop = $user->ads()->where('is_product', 1)->count();

        // Бейдж "Email підтверджено" — за останнім результатом
        // shops:check-email (яка звіряє email магазину з тим, що
        // реально вказано на його власному сайті).
        $emailVerified = false;
        if ($is_shop) {
            $lastCheck = DB::table('shop_email_checks')
                ->where('user_id', $user->id)
                ->orderByDesc('checked_at')
                ->first();
            $emailVerified = $lastCheck ? (bool) $lastCheck->matched : false;
        }

        // Рейтинг і відгуки магазину.
        $reviews = \App\ShopReview::where('shop_user_id', $user->id)
            ->with('reviewer')
            ->orderByDesc('created_at')
            ->get();
        $avgRating = $reviews->isNotEmpty() ? round($reviews->avg('rating'), 1) : null;
        $myReview = auth()->check()
            ? \App\ShopReview::where('shop_user_id', $user->id)->where('reviewer_user_id', auth()->id())->first()
            : null;

        // SEO-текст під списком: для магазинів збирається з реальних даних
        // (ShopSeoText), для звичайних користувачів блоку немає взагалі.
        if (request()->get('page') || !$is_shop) {
            $meta['description'] = false;
        } else {
            $meta['description'] = app(\App\Services\Seo\ShopSeoText::class)->build($user);
        }

        return view('front.ad.user')->with([
            'entity' => $user,
            'is_shop' => $is_shop,
            'emailVerified' => $emailVerified,
            'reviews' => $reviews ?? collect(),
            'avgRating' => $avgRating ?? null,
            'myReview' => $myReview ?? null,
            'ads' => $ads,
            'links' => $results->onEachSide(1)->links('front.widgets.paginate'),
            'tags' => AdTag::getAdsTags($ads),
            'microdata' => $microdata_info,
            'breadcrumbs' => 'ad_user',
            'meta' => $meta
        ]);
    }
}
