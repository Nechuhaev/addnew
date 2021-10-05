<?php

namespace App\Http\Controllers\Admin\Ad;

use App\Ad as AdModel;
use App\AdCategory;
use App\AdCity;
use App\AdCurrency;
use App\AdTag;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;

class Ad extends Controller
{
    public function showList(Request $request) {
        //dd($request->all());
        if ($request->has('search')) {
            $ads = AdModel::where('name', 'like', '%' . $request->get('search') . '%')
                ->orWhere('content', 'like', '%' . $request->get('search') . '%')
                ->orWhere('email', 'like', '%' . $request->get('search') . '%')
                ->orWhere('telephone', 'like', '%' . $request->get('search') . '%')
                ->orderBy('created_at', 'desc')
                ->paginate(15);
        } else {
            $ads = AdModel::orderBy('created_at', 'desc')
                ->paginate(15);
        }

        return view('admin.ad.ads')->with([
            'ads' => $ads,
            'search_action' => route('admin.ads'),
            'search' => $request->get('search')
        ]);
    }

    /**
     * Форма добавления объявления
     * @return $this
     */
    public function show() {
        $currencies = AdCurrency::all();

//        $tempAd = new AdModel();
//
//        $tempAd->name = 'Name';
//        $tempAd->slug = 'temp-'.rand(1, 1000);
//        $tempAd->price = '29.3';
//        $tempAd->currency = AdCurrency::find(2);
//        $tempAd->content = 'temp content temp content temp content temp content temp content temp content temp content temp content';
//        $tempAd->user = User::find(1);
//        $tempAd->telephone = '099 9992 92 29';
//        $tempAd->email = 'anatolii@gmail.com';
//        $tempAd->category = AdCategory::find(rand(1,30));
//        $tempAd->city = AdCity::find(rand(1,100));
//        $tempAd->status = 1;

        return view('admin.ad.ad')->with([
            'action' => route('admin.ad.create'),
            'currencies' => $currencies,
            //'ad' => $tempAd
        ]);
    }


    /**
     * Форма редактирования объявления
     * @param $id
     * @return $this
     */
    public function edit($id)
    {
        $currencies = AdCurrency::all();
        $ad = AdModel::find($id);

        return view('admin.ad.ad')->with([
            'action' => route('admin.ad.update'),
            'ad' => $ad,
            'currencies' => $currencies
        ]);
    }

    /**
     * Добавить новое объявление
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function create(Request $request)
    {
        $errors = [
            'category_id.required' => "Категория не выбрана",
            'city_id.required' => "Город не выбран",
            'user_id.required' => "Выберите пользователя!",
            'currency_id.required' => "Выберите валюту",
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
            'currency_id' => 'required|integer',
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
        $all_tags = array_unique(array_map('trim', explode(',', $request->get('tags'))));
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

        $ad = AdModel::create($request);

        if ($tags_to_attach) {
            $ad->tags()->attach($tags_to_attach);
        }

        //Связать объявление с тегами
        return redirect(route('admin.ads'))
            ->with('success', 'Объявление добавлено');
    }


    /**
     * Обновить объявление
     * @param Request $request
     * @return $this
     */
    public function update(Request $request)
    {
        $errors = [
            'category_id.required' => "Категория не выбрана",
            'city_id.required' => "Город не выбран",
            'user_id.required' => "Выберите пользователя!",
            'currency_id.required' => "Выберите валюту",
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
            'currency_id' => 'required|integer',
            'image' => 'required',
            'name' => 'required|string',
            'slug' => 'unique:ads,slug,' . $request->get('ad_id'),
            'content' => 'required|min:30',
            'telephone' => 'required|min:7',
            'email' => 'required|email',
            'meta_title' => 'max:255',
            'meta_description' => 'max:255',
        ], $errors);


        // Сохранить теги
        $all_tags = array_unique(array_map('trim', explode(',', $request->get('tags'))));
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
        $ad = AdModel::find($request->get('ad_id'));
        $request = $request->all();

        $ad->fill($request)->save();

        if ($tags_to_attach) {
            $ad->tags()->detach();
            $ad->tags()->attach($tags_to_attach);
        }

        //Связать объявление с тегами
        return redirect(route('admin.ads'))
            ->with('success', 'Объявление обновлено');
    }

    public function delete(Request $request)
    {
        $ad = AdModel::find($request->get('ad_id'));
        if ($ad) {
            $tags = AdTag::getAdTags($ad);
            if ($tags) {
                foreach ($tags as $tag) {
                    AdTag::find($tag->id)->delete();
                }
            }

            $ad->delete();
            return redirect(route('admin.ads'))
                ->with('success', 'Объявление удалено');
        } else {
            return redirect(route('admin.ads'))
                ->with('error', 'Объявление не найдено');
        }
    }

    public function deleteMany($ids)
    {
        $ids = explode(',', $ids);

        if ($ids) {
            foreach ($ids as $id) {
                $ad = AdModel::find($id);
                if ($ad) {
                    $tags = AdTag::getAdTags($ad);
                    if ($tags) {
                        foreach ($tags as $tag) {
                            AdTag::find($tag->id)->delete();
                        }
                    }
                    $ad->delete();
                }
            }
            return route('admin.ads');
        }
    }

    public function archive(Request $request)
    {
        $ad = AdModel::find($request->get('ad_id'));

        if ($ad) {
            $ad->status = 0;
            $ad->save();
            return redirect(route('admin.ads'))
                ->with('success', 'Объявление теперь находится в архиве.');
        } else {
            return redirect(route('admin.ads'))
                ->with('error', 'Объявление не найдено');
        }
    }
}
