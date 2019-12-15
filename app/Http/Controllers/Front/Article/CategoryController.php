<?php

namespace App\Http\Controllers\Front\Article;

use App\ArticleCategory;
use App\Article;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Страница категории блога
     *
     * @param null $category_slug
     * @return $this
     */
    public function page($category_slug = null) {
        if ($category_slug) {

            $category = ArticleCategory::where('slug', $category_slug)->first();

            $data['articles'] = $category->articles()->paginate(15);
            $data['category'] = $category;
        } else {
            $data['articles'] = Article::with('categories')->paginate(15);
            $data['category'] = null;
        }

        return view('front.article.category')->with($data);

    }
}
