<?php

namespace App\Http\Controllers\Admin\Article;

use App\ArticleCategory;
use App\Http\Controllers\Controller;

use App\Http\Requests\Article\ArticleCategoryStoreRequest;
//use Illuminate\Http\Request;

use Illuminate\Http\Request;
use Session;

class CategoryController extends Controller
{

    /**
     * Display the list of article categories
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index() {
        $categories = ArticleCategory::orderBy('created_at', 'desc')->paginate(5);

        return view('admin.article.category.list', ['categories' => $categories]);
    }

    /**
     * Display form for creation the new article category
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function add() {

        $category = null;

        $page_title = 'Добавить новую категорию';
        $default['meta_title'] = old('meta_title');
        $default['meta_description'] = old('meta_description');
        $default['sort_order'] = old('sort_order');
        $default['name'] = old('name');
        $default['slug'] = old('slug');
        $default['content'] = old('content');


        $action = route('admin.article.category.create');

        return view('admin.article.category.single', [
            'category' => $category,
            'page_title' => $page_title,
            'action' => $action,
            'default' => $default
        ]);
    }


    /**
     * Display form with article category information
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(Request $request) {

        $category = ArticleCategory::where('id', (int)$request->id)->first();

        $page_title = $category->name;
        $default['meta_title'] = $category->meta_title;
        $default['meta_description'] = $category->meta_description;
        $default['sort_order'] = $category->sort_order;
        $default['name'] = $category->name;
        $default['slug'] = $category->slug;
        $default['content'] = $category->content;

        $action = route('admin.article.category.update', $category->id);

        return view('admin.articles.category', [
            'category' => $category,
            'page_title' => $page_title,
            'action' => $action,
            'default' => $default
        ]);
    }

    /**
     * Create new article via post request
     *
     * @param ArticleCategoryStoreRequest $request
     * @return $this
     */
    public function create(ArticleCategoryStoreRequest $request) {

        if ($request->validated()) {
            ArticleCategory::create($request->all());
        }

        return redirect(route('admin.article.category.index'))->with('success', 'Категория добавлена');

    }


    public function update(ArticleCategoryStoreRequest $request) {

        $category = ArticleCategory::find($request->category_id);

        if ($category) {
            $category->meta_title = $request->get('meta_title');
            $category->meta_description = $request->get('meta_description');
            $category->sort_order = $request->get('sort_order');
            $category->name = $request->get('name');
            $category->slug = $request->get('slug');
            $category->content = $request->get('content');

            $category->save();

            return redirect(route('admin.article.category.index'))->with('success', 'Категория обновлена');
        }
    }
}
