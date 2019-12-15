<?php

namespace App\Http\Controllers\Front\Article;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Article extends Controller
{
    /**
     * Страница статьи блога
     *
     * @param $slug
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function article($slug)
    {
        $data['article'] = \App\Article::where('slug', $slug)->first();

        return view('front.article.article')->with($data);
    }

}
