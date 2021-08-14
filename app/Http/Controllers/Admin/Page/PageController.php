<?php

namespace App\Http\Controllers\Admin\Page;

use App\Http\Controllers\Controller;
use App\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function showPages() {
        $data['pages'] = Page::orderBy('created_at', 'desc')->paginate(15);

        return view('admin.page.list')->with($data);
    }

    public function showForm($id = null) {
        if ($id) {
            $page = Page::find($id);

            $default['meta_title'] = $page->meta_title ?? old('meta_title');
            $default['meta_description'] = $page->meta_description ?? old('meta_description');
            $default['sort_order'] = $page->sort_order ?? old('sort_order');
            $default['name'] = $page->name ?? old('name');
            $default['slug'] = $page->slug ?? old('slug');
            $default['content'] = $page->content ?? old('content');
            $data['page_id'] = $page->id;


            $data['action'] = route('admin.page.update');
        } else {
            $default['meta_title'] = old('meta_title');
            $default['meta_description'] = old('meta_description');
            $default['sort_order'] = old('sort_order');
            $default['name'] = old('name');
            $default['slug'] = old('slug');
            $default['content'] = old('content');
            $data['page_id'] = null;

            $data['action'] = route('admin.page.create');
        }

        $data['default'] = $default;


        return view('admin.page.form')->with($data);
    }

    public function create(Request $request) {
        $request->validate([
            'name' => 'required|string',
            'slug' => 'unique:pages,slug',
            'content' => 'required|min:30',
            'meta_title' => 'max:255',
            'meta_description' => 'max:255',
        ]);

        Page::create($request->all());

        return redirect(route('admin.pages'))->with('success', 'Страница добавлена');
    }

    public function update(Request $request) {
        if ($request->has('page_id'));

        $request->validate([
            'name' => 'required|string',
            'slug' => 'unique:pages,slug,'. $request->get('page_id'),
            'content' => 'required|min:30',
            'meta_title' => 'max:255',
            'meta_description' => 'max:255',
        ]);

        $page = Page::find($request->get('page_id'));

        if ($page) {
            $page->fill($request->all());
            $page->save();
        }

        return redirect(route('admin.pages'))->with('success', 'Страница обновлена');
    }

    public function delete(Request $request) {
        if ($request->has('page_id')) {
            Page::find($request->get('page_id'))->delete();
        }
        return redirect(route('admin.pages'))->with('success', 'Страница удалена');
    }
}
