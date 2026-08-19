<?php
namespace App\Http\Controllers\Front\Article;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Article;
use App\ArticleView;
use App\Ad;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ArticleController extends Controller
{
    /**
     * Страница статьи блога
     *
     * @param Request $request
     * @param $slug
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function page(Request $request, $slug)
    {
        $article = Article::where('slug', $slug)->first();
        if ($article) {
            ArticleView::record(
                $article->id,
                $request->ip(),
                'view',
                $request->header('referer')
            );

            $data['article'] = $article;
            $data['randomProducts'] = $this->randomAds(true, 6);
            $data['randomListings'] = $this->randomAds(false, 6);

            return view('front.article.article')->with($data);
        }
        abort(404);
    }

    /**
     * Випадкова підбірка активних оголошень/товарів для блоків на
     * сторінці статті. ORDER BY RAND() на великій таблиці ads (сотні
     * тисяч рядків) — повільно, тому пул ID кешується на 10 хв,
     * а сама вибірка йде по індексованому id.
     */
    protected function randomAds(bool $isProduct, int $count)
    {
        $cacheKey = 'random_ad_ids_' . ($isProduct ? 'products' : 'listings');

        $ids = Cache::remember($cacheKey, 600, function () use ($isProduct) {
            return DB::table('ads')
                ->where('is_product', $isProduct ? 1 : 0)
                ->where('status', 1)
                ->pluck('id')
                ->all();
        });

        if (empty($ids)) {
            return collect();
        }

        $randomIds = (array) array_rand(array_flip($ids), min($count, count($ids)));

        return Ad::with('currency')->whereIn('id', $randomIds)->get();
    }
}