<?php

namespace App\Http\Controllers\Admin\Ad;

use App\Ad as AdModel;
use App\AdTag;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Ad extends Controller
{
    public function showList() {
        return view('admin.ad.ads');
    }

    public function show($id = null) {

        if ($id) {
            $action = route('admin.ad.update');
        } else {
            $action = route('admin.ad.create');
        }

        return view('admin.ad.ad')->with([
            'action' => $action
        ]);
    }

    /**
     * Добавить новое объявление
     * @param Request $request
     */
    public function create(Request $request)
    {






        $errors = [
            'category_id.required' => "Категория не выбрана",
            'city_id.required' => "Город не выбран",
            'user_id.required' => "Выберите пользователя!",
            'image.required' => "Выберите главное изображение для объявления",
            'name.required' => "Введите название объявления",
            'slug.required' => "Введите слаг к объявлению",
            'content.required' => "Введите описание!",
            'content.min' => "Описание слишком короткое(минимально: :min символов)!",
            'telephone.required' => "Введите номер телефона",
            'email.required' => "Введите контактный email",
            'email.email' => "email не валиден",
            'meta_title.min' => "Вы превысили максимальную длину поля мета заголовка: :max",
            'meta_description.min' => "Вы превысили максимальную длину поля мета описания: :max",
        ];
        $request->validate([
            'category_id' => 'required|integer',
            'city_id' => 'required|integer',
            'user_id' => 'required|integer',
            'image' => 'required',
            'name' => 'required|string',
            'slug' => 'unique:ads,slug',
            'content' => 'required|min:30',
            'telephone' => 'required|min:7',
            'email' => 'required|email',
            'meta_title' => 'max:255',
            'meta_description' => 'max:255',
        ], $errors);

        // Сохранить теги
        $all_tags = array_unique(array_map('trim', explode(',', $request->tags)));
        $not_existing_tags = $all_tags;
        $existing_tags = AdTag::whereIn('name', $all_tags)->get();

        foreach ($existing_tags as $existing_tag) {
            if (($key = array_search($existing_tag->name, $not_existing_tags)) !== false) {
                unset($not_existing_tags[$key]);
            }
        }

        foreach ($not_existing_tags as $not_existing_tag) {
            AdTag::create(['name' => $not_existing_tag, 'slug' => null]);
        }

        $tags_to_attach = AdTag::whereIn('name', $all_tags)->pluck('id')->toArray();

        // Сохранить объявление
        $request = $request->all();

        $images_line = "";

        $images = array_unique($request['images']);

        foreach ($images as $key => $image) {
            if (!$image) unset($images[$key]);
        }

        $request['images'] = implode($images);

        $ad = AdModel::create($request);

        if ($tags_to_attach) {
            $ad->tags()->attach($tags_to_attach);
        }

        //Связать объявление с тегами
        return redirect(route('admin.ads'))
            ->with('success', 'Объявление добавлено');
    }

}
