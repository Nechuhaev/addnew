<?php

namespace App\Http\Controllers\Front\Ad;

use App\AdCategory;
use App\Http\Controllers\Controller;

use Carbon\Carbon;
use Illuminate\Http\Request;

class Ad extends Controller
{
    public function page($ad) {
        $ad = \App\Ad::where('slug', '=', $ad)->first();


        // Обновляем счетчик просмотров объявлений
        // Просмотры сегодня
        if(!Carbon::now()->isSameAs('d.m.Y', $ad->updated_at)) {
            $ad->today_views = 1; // Обнулим если сегодня новый день
        } else {
            $ad->today_views++;
        }
        // Просмотры всего
        $ad->total_views++;

        $ad->save();

        return view('front.ad.ad')->with([
            'ad' => $ad
        ]);
    }

    public function create_step_category(Request $request) {

        // Валидация, сохранение данных и переход к следующему шагу
        if ($request->method() == 'POST') {
            $errors = [
                'category_id.required' => "Категория не выбрана",
                'category_id.exists' => "Выберите категорию!",
            ];

            $request->validate([
                'category_id' => 'required|integer|exists:ad_categories,id',
            ], $errors);

            $request->session()->put('ad.category_id', $request->get('category_id'));

            return redirect(route('ad.step.details'));
        }

        //$request->session()->has('ad');
        $data['parent_categories'] = AdCategory::select(['id', 'name'])
            ->where('parent_id', 0)
            ->get()
            ->toArray();


        $data['selected_parent_id'] = null;
        $data['children_categories'] = null;
        $data['selected_child_id'] = null;
        if($request->session()->has('ad.category_id')) {
            $selected_category = AdCategory::find($request->session()->get('ad.category_id'));

            if ($selected_category->parent) {
                $data['selected_parent_id'] = $selected_category->parent->id;
                $data['children_categories'] = $selected_category->parent->children->toArray();
                $data['selected_child_id'] = $selected_category->id;
            } else {
                $data['selected_parent_id'] = $selected_category->id;
            }

        }


        return view('front.ad.create_step_1')->with($data);
    }

    public function  create_step_details() {
        return view('front.ad.create_step_2');
    }

    public function  create_step_preview() {

    }

    public function  create_step_success() {

    }
}
