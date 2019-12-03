<?php

namespace App\Http\Controllers\Front\Ad;

use App\AdCategory;
use App\AdCity;
use App\AdCountry;
use App\AdCurrency;
use App\AdRegion;
use App\Http\Controllers\Controller;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    public function  create_step_details(Request $request) {

        // Значения по умолчанию
        // Категория
        if (!$request->session()->has('ad.category_id')) {
            return redirect(route('ad.step.category'));
        }

        if ($request->method() == 'POST') {
            $errors = [
                'author.required' => 'Введите имя автора объявления',
                'author.min' => 'Имя автора не может быть короче :min символов',
            ];

            $request->validate([
                'author' => 'sometimes|required|min:3',
                'telephone' => 'required|min:6',
                'city_id' => 'required|exists:ad_cities,id',
                'email' => 'sometimes|required|email',
                'name' => 'required|min:10',
                'content' => 'required|min:70',
                'image' => 'required',
                'image.*' => 'image|max:1024|mimes:jpg,jpeg,bmp,png',
                'price' => 'required|numeric',
                'currency_id' => 'required|integer|exists:ad_currencies,id',
            ], $errors);

            $request->session()->put('ad.author', $request->get('author'));
            $request->session()->put('ad.telephone', $request->get('telephone'));
            $request->session()->put('ad.email', $request->get('email'));
            $request->session()->put('ad.city_id', $request->get('city_id'));
            $request->session()->put('ad.name', $request->get('name'));
            $request->session()->put('ad.tags', $request->get('tags'));
            $request->session()->put('ad.content', $request->get('content'));
            $request->session()->put('ad.price', $request->get('price'));
            $request->session()->put('ad.currency_id', $request->get('currency_id'));

            if ($request->hasFile('image')) {
                if ($request->session()->has('ad.images')) {
                    $request->session()->remove('ad.images');
                }
                $dir = 'ads/' . time();
                foreach ($request->file('image') as $key => $image) {
                    $filename = $key . '.' . $image->getClientOriginalExtension();
                    $filepath = $dir . '/' . $filename;
                    $image->storeAs('public', $filepath);
                    $request->session()->push('ad.images', $filepath);
                }
            }

            return redirect(route('ad.step.preview'));
        }

        $data['category'] = AdCategory::find($request->session()->get('ad.category_id'));
        //dd($request->get('author'));
        if ($request->session()->has('ad.author')) {
            $data['author'] = $request->session()->get('ad.author');
        } elseif (old('author')) {
            $data['author'] = old('author');
        } elseif (Auth::check()) {
            $data['author'] = Auth::user()->username;
        } else {
            $data['author'] = '';
        }

        if ($request->session()->has('ad.telephone')) {
            $data['telephone'] = $request->session()->get('ad.telephone');
        } elseif (old('telephone')) {
            $data['telephone'] = old('telephone');
        } elseif (Auth::check()) {
            $data['telephone'] = Auth::user()->telephone;
        } else {
            $data['telephone'] = '';
        }


        if ($request->session()->has('ad.email')) {
            $data['email'] = $request->session()->get('ad.email');
        } elseif (old('email')) {
            $data['email'] = old('email');
        } elseif (Auth::check()) {
            $data['email'] = Auth::user()->email;
        } else {
            $data['email'] = '';
        }


        if ($request->session()->has('ad.name')) {
            $data['name'] = $request->session()->get('ad.name');
        } elseif (old('name')) {
            $data['name'] = old('name');
        } else {
            $data['name'] = '';
        }

        if ($request->session()->has('ad.tags')) {
            $tags = json_encode(explode(',', $request->session()->get('ad.tags')));
        } elseif (old('tags')) {
            $tags = json_encode(explode(',', old('tags')));
        } else {
            $tags = '';
        }

        $data['tags'] = $tags;

        if ($request->session()->has('ad.content')) {
            $data['content'] = $request->session()->get('ad.content');
        } elseif (old('content')) {
            $data['content'] = old('content');
        } else {
            $data['content'] = '';
        }

        if ($request->session()->has('ad.price')) {
            $data['price'] = $request->session()->get('ad.price');
        } elseif (old('price')) {
            $data['price'] = old('price');
        } else {
            $data['price'] = '';
        }

        if ($request->session()->has('ad.currency_id')) {
            $data['currency_id'] = $request->session()->get('ad.currency_id');
        } elseif (old('currency_id')) {
            $data['currency_id'] = old('currency_id');
        } else {
            $data['currency_id'] = '';
        }

        $data['currencies'] = AdCurrency::select(['id', 'code'])->get()->toArray();

        $data['countries'] = AdCountry::select(['id', 'name'])->get()->toArray();

        $city = null;
        $city_id = 0;

        if ($request->session()->get('ad.city_id') || old('city_id')) {
            $city_id = $request->session()->get('ad.city_id') ?? old('city_id');
            $city = AdCity::find($city_id);
            //dd($city);
            $country_id = $city->region->country->id;
        } else {
            $country_id = old('country_id') ?? 0;
        }

        $data['country_id'] = $country_id;

        if ($country_id) {
            $data['regions'] = AdRegion::select(['id', 'name'])->where('country_id', $country_id)->get()->toArray();
        } else {
            $data['regions'] = null;
        }

        if ($city) {
            $region_id = $city->region->id;
        } else {
            $region_id = old('region_id') ?? 0;
        }

        $data['region_id'] = $region_id;

        if ($region_id) {
            $data['cities'] = AdCity::select(['id', 'name'])->where('region_id', $region_id)->get()->toArray();
        } else {
            $data['cities'] = null;
        }

        $data['city_id'] = $city_id;

        return view('front.ad.create_step_2')->with($data);
    }

    public function  create_step_preview() {

    }

    public function  create_step_success() {

    }
}
