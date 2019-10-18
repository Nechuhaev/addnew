<?php

namespace App\Http\Controllers\Admin\Article;

use App\Article;
use App\ArticleCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Article\ArticleStoreRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ArticleController extends Controller
{

    public function showArticles() {

        $articles = Article::orderBy('created_at', 'DESC')->paginate(15);

        return view('admin.article.list')->with(['articles' => $articles]);
    }

    /**
     * Show article's add form
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showArticleAddForm() {

        $default['image'] = old('image');
        $default['meta_title'] = old('meta_title');
        $default['meta_description'] = old('meta_description');
        $default['sort_order'] = old('sort_order');
        $default['name'] = old('name');
        $default['slug'] = old('slug');
        $default['content'] = old('content');
        $default['excerpt'] = old('excerpt');
        $default['categories'] = old('categories') ?? [];

        $action = route('admin.article.add');

        $categories = ArticleCategory::all();

        $categories_to_view = [];
        foreach ($categories as $category) {
            $categories_to_view[] = [
                'id' => $category->id,
                'name' => $category->name
            ];
        }

        return view('admin.article.single', [
            'action' => $action,
            'default' => $default,
            'categories' => $categories_to_view
        ]);
    }

    public function showArticleEditForm($article_id) {
        $article = Article::find($article_id);

        if ($article) {
            $default['image'] = $article->image;
            $default['meta_title'] = $article->meta_title;
            $default['meta_description'] = $article->meta_description;
            $default['sort_order'] = $article->sort_order;
            $default['name'] = $article->name;
            $default['slug'] = $article->slug;
            $default['excerpt'] = $article->excerpt;
            $default['content'] = $article->content;
            $default['categories'] = $article->categories()->allRelatedIds()->toArray();


            $action = route('admin.article.update');

            $categories = ArticleCategory::all();

            $categories_to_view = [];
            foreach ($categories as $category) {
                $categories_to_view[] = [
                    'id' => $category->id,
                    'name' => $category->name
                ];
            }

            return view('admin.articles.article', [
                'action' => $action,
                'default' => $default,
                'categories' => $categories_to_view,
                'article_id' => $article_id,
            ]);
        } else {
            //redirect()
        }
    }


    public function add(ArticleStoreRequest $request) {

        if ($request->validated()) {
            $article = Article::create($request->all());

            $categories = ArticleCategory::find($request->categories);

            if ($categories) {
                $article->categories()->attach($categories);
            }
        }

        return redirect(route('admin.articles'))->with('success', 'Статья добавлена');

    }

    public function update(ArticleStoreRequest $request) {

        if ($request->validated()) {
            $article = Article::find($request->article_id);

            if ($article) {
                $article->fill($request->all())->save();
            }

            $categories = ArticleCategory::find($request->categories);

            if ($categories) {
                $article->categories()->sync($categories);
            }
        }

        return redirect(route('admin.articles'))->with('success', 'Статья обновлена');
    }

    public function delete(Request $request) {
        if ($request->article_id) {
            Article::find($request->article_id)->delete();
        }
        return redirect(route('admin.articles'))->with('success', 'Статья удалена :(');
    }
}
