<?php

namespace App\Http\Controllers\Admin\Seo;

use App\Http\Controllers\Controller;
use App\SeoField;
use Illuminate\Http\Request;

class Seo extends Controller
{
    public function page() {

        $items = [];

        $seo_field = SeoField::where('index', 'index')->first();
        if ($seo_field) {
            $meta_title = (bool)$seo_field->meta_title;
            $meta_description = (bool)$seo_field->meta_description;
            $description = (bool)$seo_field->description;
        } else {
            $meta_title = false;
            $meta_description = false;
            $description = false;
        }

        $items[] = [
            'name' => 'Главная',
            'description' => $description,
            'meta_title' => $meta_title,
            'meta_description' => $meta_description,
            'action' => route('admin.seo.index')
        ];


        $seo_field = SeoField::where('index', 'countries')->first();
        if ($seo_field) {
            $meta_title = (bool)$seo_field->meta_title;
            $meta_description = (bool)$seo_field->meta_description;
            $description = (bool)$seo_field->description;
        } else {
            $meta_title = false;
            $meta_description = false;
            $description = false;
        }

        $items[] = [
            'name' => 'Список стран',
            'description' => $description,
            'meta_title' => $meta_title,
            'meta_description' => $meta_description,
            'action' => route('admin.seo.countries')
        ];


        $seo_field = SeoField::where('index', 'contacts')->first();
        if ($seo_field) {
            $meta_title = (bool)$seo_field->meta_title;
            $meta_description = (bool)$seo_field->meta_description;
            $description = (bool)$seo_field->description;
        } else {
            $meta_title = false;
            $meta_description = false;
            $description = false;
        }

        $items[] = [
            'name' => 'Контактная информация',
            'description' => $description,
            'meta_title' => $meta_title,
            'meta_description' => $meta_description,
            'action' => route('admin.seo.contacts')
        ];


        $seo_field = SeoField::where('index', 'ad')->first();
        if ($seo_field) {
            $meta_title = (bool)$seo_field->meta_title;
            $meta_description = (bool)$seo_field->meta_description;
            $description = (bool)$seo_field->description;
        } else {
            $meta_title = false;
            $meta_description = false;
            $description = false;
        }

        $items[] = [
            'name' => 'Объявление',
            'description' => $description,
            'meta_title' => $meta_title,
            'meta_description' => $meta_description,
            'action' => route('admin.seo.ad')
        ];


        $seo_field = SeoField::where('index', 'ad-category')->first();
        if ($seo_field) {
            $meta_title = (bool)$seo_field->meta_title;
            $meta_description = (bool)$seo_field->meta_description;
            $description = (bool)$seo_field->description;
        } else {
            $meta_title = false;
            $meta_description = false;
            $description = false;
        }
        $items[] = [
            'name' => 'Категория объявления',
            'description' => $description,
            'meta_title' => $meta_title,
            'meta_description' => $meta_description,
            'action' => route('admin.seo.ad-category')
        ];


        $seo_field = SeoField::where('index', 'ad-tag')->first();
        if ($seo_field) {
            $meta_title = (bool)$seo_field->meta_title;
            $meta_description = (bool)$seo_field->meta_description;
            $description = (bool)$seo_field->description;
        } else {
            $meta_title = false;
            $meta_description = false;
            $description = false;
        }
        $items[] = [
            'name' => 'Тег',
            'description' => $description,
            'meta_title' => $meta_title,
            'meta_description' => $meta_description,
            'action' => route('admin.seo.ad-tag')
        ];


        $seo_field = SeoField::where('index', 'ad-country')->first();
        if ($seo_field) {
            $meta_title = (bool)$seo_field->meta_title;
            $meta_description = (bool)$seo_field->meta_description;
            $description = (bool)$seo_field->description;
        } else {
            $meta_title = false;
            $meta_description = false;
            $description = false;
        }
        $items[] = [
            'name' => 'Страна',
            'description' => $description,
            'meta_title' => $meta_title,
            'meta_description' => $meta_description,
            'action' => route('admin.seo.ad-country')
        ];


        $seo_field = SeoField::where('index', 'ad-region')->first();
        if ($seo_field) {
            $meta_title = (bool)$seo_field->meta_title;
            $meta_description = (bool)$seo_field->meta_description;
            $description = (bool)$seo_field->description;
        } else {
            $meta_title = false;
            $meta_description = false;
            $description = false;
        }
        $items[] = [
            'name' => 'Область',
            'description' => $description,
            'meta_title' => $meta_title,
            'meta_description' => $meta_description,
            'action' => route('admin.seo.ad-region')
        ];


        $seo_field = SeoField::where('index', 'ad-city')->first();
        if ($seo_field) {
            $meta_title = (bool)$seo_field->meta_title;
            $meta_description = (bool)$seo_field->meta_description;
            $description = (bool)$seo_field->description;
        } else {
            $meta_title = false;
            $meta_description = false;
            $description = false;
        }
        $items[] = [
            'name' => 'Город',
            'description' => $description,
            'meta_title' => $meta_title,
            'meta_description' => $meta_description,
            'action' => route('admin.seo.ad-city')
        ];

        $seo_field = SeoField::where('index', 'search')->first();
        if ($seo_field) {
            $meta_title = (bool)$seo_field->meta_title;
            $meta_description = (bool)$seo_field->meta_description;
            $description = (bool)$seo_field->description;
        } else {
            $meta_title = false;
            $meta_description = false;
            $description = false;
        }
        $items[] = [
            'name' => 'Поиск',
            'description' => $description,
            'meta_title' => $meta_title,
            'meta_description' => $meta_description,
            'action' => route('admin.seo.search')
        ];

        $seo_field = SeoField::where('index', 'ad-user')->first();
        if ($seo_field) {
            $meta_title = (bool)$seo_field->meta_title;
            $meta_description = (bool)$seo_field->meta_description;
            $description = (bool)$seo_field->description;
        } else {
            $meta_title = false;
            $meta_description = false;
            $description = false;
        }
        $items[] = [
            'name' => 'Список объявлений пользователя',
            'description' => $description,
            'meta_title' => $meta_title,
            'meta_description' => $meta_description,
            'action' => route('admin.seo.ad-user')
        ];

        return view('admin.seo.list')->with(['items' => $items]);
    }

    public function update(Request $request) {
        //dd($request->get('index'));
        if ($request->has('index')) {


            $errors = [
                'meta_title.max' => 'Максимальная длина поля meta title :max символов',
                'meta_description.max' => 'Максимальная длина поля meta description :max символов',
            ];

            $request->validate([
                'meta_title' => 'max:255',
                'meta_description' => 'max:255',
            ], $errors);


            $index = $request->get('index');
            $seo_field = SeoField::where('index', $index)->first();

            if (!$seo_field) {
                $seo_field = new SeoField();
            }

            $seo_field->index = $index;
            $seo_field->meta_title = $request->get('meta_title') ?? $seo_field->meta_title;
            $seo_field->meta_description = $request->get('meta_description') ?? $seo_field->meta_description;
            $seo_field->description = $request->get('description') ?? $seo_field->description;

            $seo_field->save();

            return redirect(route('admin.seo'))->with('success', 'Ваши данные обновлены');
        }

    }
}
