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
                'meta_title' => strtr($seo_field->meta_title, $entity_values),
                'meta_description' => strtr($seo_field->meta_description, $entity_values),
                'description' => strtr($seo_field->description, $entity_values)
            ];
        } else {
            $text = app()->getLocale() === 'ru'
                ? "Объявления пользователя $user->username на доске объявлений addnew.biz"
                : "Оголошення користувача $user->username на дошці оголошень addnew.biz";
            $meta = ['meta_title' => $text, 'meta_description' => $text, 'description' => $text];
        }

        if (request()->get('page')) {
            $meta['description'] = false;
        }

        // Пошук і фільтри в межах магазину/автора
        $term = trim((string) request()->get('s'));
        $search = new \App\Services\AdSearch($term);
        $results = Ad::getAds()->where('ads.user_id', $user->id);
        // Серед оголошень автора є чутливі — сторінка без реклами AdSense (правила AdSense)
        if (Ad::where('user_id', $user->id)->where('status', 1)->where('is_sensitive', 1)->exists()) {
            \App\Http\AdSense::disable();
        }
        $search->apply($results);
        $listingFilters = \App\Services\ListingFilters::fromRequest(request(), !$search->isEmpty());
        $listingFilters->apply($results);
        if (!$search->isEmpty() && $listingFilters->wantsRelevance()) {
            $search->orderByRelevance($results);
        }
        $results = $results->paginate(15)
            ->appends(array_merge(request()->only(['s']), $listingFilters->query()));

        if ($term !== '') {
            \App\SearchQuery::record($term, $results->total(), 'shop', $user->id);
        }

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

        // Магазин: власні title/description замість шаблону «Оголошення користувача…»
        // (на сторінки магазинів шукають саме за назвою магазину, а такий заголовок майже не клікають)
        if ($is_shop) {
            $isRu = app()->getLocale() === 'ru';
            $name = $user->username;
            $activeCount = $user->ads()->where('status', 1)->count();
            $meta['meta_title'] = $isRu
                ? "{$name} — товары и цены, отзывы | Addnew.biz"
                : "{$name} — товари й ціни, відгуки | Addnew.biz";
            if ((int) request()->get('page') > 1) {
                $meta['meta_title'] = ($isRu ? "{$name} — товары, страница " : "{$name} — товари, сторінка ") . (int) request()->get('page') . ' | Addnew.biz';
            }
            $countText = number_format($activeCount, 0, ',', ' ') . ' ' . ($isRu
                ? $this->plural($activeCount, ['товар', 'товара', 'товаров'])
                : $this->plural($activeCount, ['товар', 'товари', 'товарів']));
            $ratingText = '';
            if ($avgRating) {
                $n = $reviews->count();
                $ratingText = $isRu
                    ? ', рейтинг ' . number_format($avgRating, 1, ',', '') . ' из 5 (' . $n . ' ' . $this->plural($n, ['отзыв', 'отзыва', 'отзывов']) . ')'
                    : ', рейтинг ' . number_format($avgRating, 1, ',', '') . ' з 5 (' . $n . ' ' . $this->plural($n, ['відгук', 'відгуки', 'відгуків']) . ')';
            }
            $meta['meta_description'] = $isRu
                ? "{$name} на Addnew.biz: {$countText} с ценами{$ratingText}. Сравните цены с другими продавцами и перейдите в магазин."
                : "{$name} на Addnew.biz: {$countText} з цінами{$ratingText}. Порівняйте ціни з іншими продавцями й перейдіть у магазин.";
        }

        return view('front.ad.user')->with([
            'entity' => $user,
            'is_shop' => $is_shop,
            'emailVerified' => $emailVerified,
            'reviews' => $reviews ?? collect(),
            'avgRating' => $avgRating ?? null,
            'myReview' => $myReview ?? null,
            'ads' => $ads,
            'term' => $term,
            'total' => $results->total(),
            'listingFilters' => $listingFilters,
            'links' => $results->onEachSide(1)->links('front.widgets.paginate'),
            'tags' => AdTag::getAdsTags($ads),
            'microdata' => $microdata_info,
            'breadcrumbs' => 'ad_user',
            'meta' => $meta
        ]);
    }

    /** «1 товар», «3 товари», «25 товарів» */
    protected function plural(int $n, array $forms): string
    {
        $m10 = $n % 10;
        $m100 = $n % 100;
        if ($m10 === 1 && $m100 !== 11) {
            return $forms[0];
        }
        if ($m10 >= 2 && $m10 <= 4 && ($m100 < 12 || $m100 > 14)) {
            return $forms[1];
        }

        return $forms[2];
    }
}
