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

        $items[] = [
            'name' => 'Объявление',
            'description' => false,
            'meta_title' => false,
            'meta_description' => false,
            'action' => route('admin.seo.ad')
        ];

        $items[] = [
            'name' => 'Категория объявления',
            'description' => false,
            'meta_title' => false,
            'meta_description' => false,
            'action' => route('admin.seo.ad-category')
        ];

        $items[] = [
            'name' => 'Тег',
            'description' => false,
            'meta_title' => false,
            'meta_description' => false,
            'action' => route('admin.seo.ad-tag')
        ];

        $items[] = [
            'name' => 'Страна',
            'description' => false,
            'meta_title' => false,
            'meta_description' => false,
            'action' => route('admin.seo.ad-country')
        ];

        $items[] = [
            'name' => 'Область',
            'description' => false,
            'meta_title' => false,
            'meta_description' => false,
            'action' => route('admin.seo.ad-region')
        ];

        $items[] = [
            'name' => 'Город',
            'description' => false,
            'meta_title' => true,
            'meta_description' => false,
            'action' => route('admin.seo.ad-city')
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
