<?php

namespace App\Http\Controllers\Admin\Ad;

use App\AdCategory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Category extends Controller
{
    public function showForm(Request $request, $category_id = null) {
        if ($request->has('order')) {
            $order = $request->get('order');
        } else {
            $order = 'name';
        }

        $direction = $request->get('direction') ?? 'asc';

        $parent_list = AdCategory::where('parent_id', 0)->withCount('ads')->orderBy($order, $direction)->get(['id', 'name'])->toArray();

        $categories = AdCategory::withCount('ads')->orderBy($order, $direction)->get()->toArray();

        // Строим дерево
        $categories_tree = [];
        foreach ($categories as $key => $category) {
            if ($category['parent_id'] == 0) {
                $categories_tree[$category['id']]['id'] = $category['id'];
                $categories_tree[$category['id']]['name'] = $category['name'];
                $categories_tree[$category['id']]['children'] = [];

                unset($categories[$key]);
            }
        }


        foreach ($categories as $key => $category) {
            $categories_tree[$category['parent_id']]['children'][] = $category;
            unset($categories[$key]);
        }


        $category = null;

        if ($category_id) {
            $action = route('admin.adCategories.update');
            $category = AdCategory::find( (int) $category_id);
        } else {
            $action = route('admin.adCategories.create');
        }


        return view('admin.ad.category')->with([
            'action' => $action,
            'category' => $category,
            'direction' => $direction,
            'order' => $order,
            'parent_list' => $parent_list,
            'tree' => $categories_tree
        ]);
    }

    /**
     * Создать новую категорию через форму добавления
     */
    public function create(Request $request) {

        $errors = [
            'name.required' => 'Введите название',
            'slug.unique' => 'Такой slug уже существует',
            'meta_title.max' => 'Максимальная длина поля Meta-тег title: :max символов',
            'meta_description.max' => 'Максимальная длина поля Meta-тег description: :max символов'
        ];

        $request->validate([
            'name' => 'required|string',
            'slug' => 'unique:ad_categories,slug',
            'meta_title' => 'max:255',
            'meta_description' => 'max:255',
        ], $errors);

        AdCategory::create($request->all());

        return redirect(route('admin.adCategories'))->with('success', 'Категория добавлена');
    }

    /**
     * Изменить существующую категорию
     */
    public function update(Request $request) {
        $errors = [
            'name.required' => 'Введите название',
            'slug.unique' => 'Такой slug уже используется',
            'meta_title.max' => 'Максимальная длина поля Meta-тег title: :max символов',
            'meta_description.max' => 'Максимальная длина поля Meta-тег description: :max символов',
            'parent_id.not_in' => 'Грубейшая ошибка, сэр! Категория не может быть родительской сама для себя!'
        ];


        $request->validate([
            'name' => 'required|string',
            'slug' => 'unique:ad_categories,slug,' . $request->category_id,
            'meta_title' => 'max:255',
            'meta_description' => 'max:255',
            'parent_id' => 'not_in:'.$request->category_id
        ], $errors);

        $category = AdCategory::find($request->category_id);

        if ($category) {
            $category->fill($request->all())->save();
        }

        return redirect(route('admin.adCategories'))->with('success', 'Категория обновлена');

    }

    /**
     * Удалить категорию.
     * В случае, если в категории есть дочерние категории
     * или объявления - уведомляем что удаление невозможно
     */
    public function delete($category_id) {
        #TODO: Проверить наличие объявлений в категории перед удалением
        if ($category_id) {
            AdCategory::find($category_id)->delete();
        }
        return redirect(route('admin.adCategories'))->with('success', 'Категория удалена');
    }
}
