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

        DB::enableQueryLog(); // Enable query log

        //$articles = Article::with('categories')->get()->toArray();
        $articles_array = [];
        $articles = Article::with('categories')->paginate(10);

        foreach ($articles as $article) {
            $articles_array[$article->id] = [
                'url' => route('blog.article', ['slug' => $article->slug]),
                'name' => $article->name,
                'excerpt' => $article->excerpt,
                'image' => $article->image,
            ];

            foreach ($article->categories as $category) {

                $articles_array[$article->id]['categories'][] = [
                    'url' => $category->slug,
                    'name' => $category->name
                ];
            }
        }


        //dd($articles_array);
        return view('front.article.category')->with([
            'articles' => $articles_array
        ]);
    }

    public function showArticle($slug) {

        $article = Article::where('slug', $slug)->first();

        $article_data = [
            'name' => $article->name,
            'content' => $article->content,
            'meta_title' => $article->meta_title,
            'meta_description' => $article->meta_description,
            'categories' => $article->categories(),
            'created_at' => $article->created_at,
        ];

        return view('front.article.article', ['article' => $article]);
    }

    public function showCategory() {
        return view('front.article.category');
    }

    private function sidebar() {
        $categories = ArticleCategory::all();

    }
}
