<?php

namespace App\Http\Controllers\Front\Article;

use App\ArticleCategory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Article;
use Illuminate\Support\Facades\DB;

class ArticleController extends Controller
{
    public function showArticles() {

        $articles = Article::with('categories')->paginate(15);

        return view('front.article.category')->with([
            'articles' => $articles
        ]);
    }

//    public function showArticle($slug) {
//
//        $article = Article::where('slug', $slug)->first();
//
//        return view('front.article.article', ['article' => $article]);
//    }

    public function showCategory($slug) {

        $category = ArticleCategory::where('slug', $slug)->first();

        $articles = $category->articles()->paginate(15);

        return view('front.article.category')->with([
            'articles' => $articles,
            'category_object' => $category
        ]);
    }
}
