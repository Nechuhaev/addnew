<?php

namespace App\Http\Controllers\Front\Article;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Article;

class ArticleController extends Controller
{

    /**
     * Страница статьи блога
     *
     * @param $slug
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function page($slug)
    {
        $article = Article::where('slug', $slug)->first();

        if ($article) {

            $data['article'] = $article;

            return view('front.article.article')->with($data);
        }

        abort(404);
    }

}
