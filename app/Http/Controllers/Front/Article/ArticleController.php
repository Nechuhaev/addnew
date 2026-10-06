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
            $data['randomProducts'] = $this->relevantAds($article, true, 6);
            $data['randomListings'] = $this->relevantAds($article, false, 6);

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

    /**
     * Слова в назві, за якими оголошення не потрапляють у блоки на редакційних сторінках.
     */
    protected $widgetStopWords = ['донор', 'эскорт', 'интим', 'рецептурн', 'суррогат', 'сурогат'];

    /**
     * Категорії (разом із вкладеними), яких не показуємо в блоках статей.
     */
    protected $widgetBlockedSlugs = ['znakomstva-i-kontaktyi', 'medikamenty-i-meditsinskie-tovary', 'eroticheskaya-odezhda', 'tovary-dlya-vzroslykh'];

    /**
     * Рубрика блогу → категорія верхнього рівня на дошці.
     */
    protected $widgetCategoryMap = [
        'blog-transport' => 'transport',
        'elektronika' => 'elektronika',
        'rabota' => 'rabota',
        'nedvizhimost' => 'nedvizhimost-2',
        'stroitelstvo' => 'stroitelstvo-i-remont',
        'oborudovanie' => 'oborudovanie-2',
        'zhivotnye' => 'zhivotnyie',
        'biznes-i-uslugi' => 'biznes-i-uslugi',
        'dom-i-sad' => 'dom-i-sad',
        'detskiy-mir' => 'detskiy-mir',
        'moda-i-stil' => 'moda-i-stil',
    ];

    /**
     * Блоки на сторінці статті: оголошення/товари тієї ж тематики, що й рубрика
     * статті (а не випадкові з усього сайту). Чутливі категорії й слова виключені.
     * Немає підходящих: порожня колекція (шаблон тоді ховає блок).
     */
    protected function relevantAds($article, bool $isProduct, int $count)
    {
        $catIds = $this->widgetCategoryIds($article);
        if (empty($catIds)) {
            return collect();
        }

        $stop = $this->widgetStopWords;
        $key = 'article_widget_ids_' . ($isProduct ? 'p' : 'l') . '_' . md5(implode(',', $catIds));
        $ids = Cache::remember($key, 1800, function () use ($isProduct, $catIds, $stop) {
            $q = DB::table('ads')
                ->whereIn('category_id', $catIds)
                ->where('is_product', $isProduct ? 1 : 0)
                ->where('status', 1)
                ->whereNotNull('image')
                ->where('image', '!=', '')
                ->orderBy('id', 'desc')
                ->limit(1500);
            foreach ($stop as $w) {
                $q->where('name', 'not like', '%' . $w . '%');
            }

            return $q->pluck('id')->all();
        });

        if (empty($ids)) {
            return collect();
        }
        $pick = (array) array_rand(array_flip($ids), min($count, count($ids)));

        return Ad::with('currency')->whereIn('id', $pick)->get();
    }

    protected function widgetCategoryIds($article)
    {
        $slugs = [];
        foreach ($article->categories as $c) {
            if (isset($this->widgetCategoryMap[$c->slug])) {
                $slugs[] = $this->widgetCategoryMap[$c->slug];
            }
        }
        if (empty($slugs)) {
            return [];
        }
        sort($slugs);
        $blocked = $this->widgetBlockedSlugs;

        return Cache::remember('article_widget_cats_' . md5(implode(',', $slugs)), 3600, function () use ($slugs, $blocked) {
            $top = DB::table('ad_categories')->whereIn('slug', $slugs)->pluck('id')->all();
            $bad = DB::table('ad_categories')->whereIn('slug', $blocked)->pluck('id')->all();

            return array_values(array_diff($this->categoryTree($top), $this->categoryTree($bad)));
        });
    }

    protected function categoryTree(array $roots)
    {
        $all = $roots;
        $frontier = $roots;
        while (!empty($frontier)) {
            $frontier = array_values(array_diff(
                DB::table('ad_categories')->whereIn('parent_id', $frontier)->pluck('id')->all(),
                $all
            ));
            $all = array_merge($all, $frontier);
        }

        return $all;
    }
}
