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
        $default['meta_title_uk'] = old('meta_title_uk');
        $default['meta_description'] = old('meta_description');
        $default['meta_description_uk'] = old('meta_description_uk');
        $default['sort_order'] = old('sort_order');
        $default['name'] = old('name');
        $default['name_uk'] = old('name_uk');
        $default['slug'] = old('slug');
        $default['content'] = old('content');
        $default['content_uk'] = old('content_uk');


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
     * ВАЖЛИВО: тут навмисно getOriginal(), а НЕ звичайний аксесор
     * ($category->name) — аксесор локале-залежний (поверне _uk версію,
     * якщо поточна локаль адмінки 'uk'), і форма редагування показала б
     * (і при збереженні переписала б) українським текстом замість
     * російського сирого поля.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(Request $request) {

        $category = ArticleCategory::where('id', (int)$request->id)->first();

        $page_title = $category->getOriginal('name');
        $default['meta_title'] = $category->getOriginal('meta_title');
        $default['meta_title_uk'] = $category->getOriginal('meta_title_uk');
        $default['meta_description'] = $category->getOriginal('meta_description');
        $default['meta_description_uk'] = $category->getOriginal('meta_description_uk');
        $default['sort_order'] = $category->sort_order;
        $default['name'] = $category->getOriginal('name');
        $default['name_uk'] = $category->getOriginal('name_uk');
        $default['slug'] = $category->slug;
        $default['content'] = $category->getOriginal('content');
        $default['content_uk'] = $category->getOriginal('content_uk');

        $action = route('admin.article.category.update', $category->id);

        return view('admin.article.category.single', [
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
            $category->meta_title_uk = $request->get('meta_title_uk');
            $category->meta_description = $request->get('meta_description');
            $category->meta_description_uk = $request->get('meta_description_uk');
            $category->sort_order = $request->get('sort_order');
            $category->name = $request->get('name');
            $category->name_uk = $request->get('name_uk');
            $category->slug = $request->get('slug');
            $category->content = $request->get('content');
            $category->content_uk = $request->get('content_uk');

            $category->save();

            return redirect(route('admin.article.category.index'))->with('success', 'Категория обновлена');
        }
    }
}
