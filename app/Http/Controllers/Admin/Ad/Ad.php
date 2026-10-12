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
        $query = AdModel::query();

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('content', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('telephone', 'like', '%' . $search . '%')
                    ->orWhereIn('user_id', function ($q) use ($search) {
                        $q->select('id')->from('users')->where('email', 'like', '%' . $search . '%');
                    });
            });
        }

        // Фільтри з посилань «кількість оголошень» (області, міста)
        $filters = [];
        if ($request->filled('region_id')) {
            $region = \App\AdRegion::find((int) $request->get('region_id'));
            $query->whereIn('city_id', function ($q) use ($request) {
                $q->select('id')->from('ad_cities')->where('region_id', (int) $request->get('region_id'));
            });
            $filters['region_id'] = 'Область: ' . (optional($region)->name ?? '#' . (int) $request->get('region_id'));
        }
        if ($request->filled('city_id')) {
            $city = \App\AdCity::find((int) $request->get('city_id'));
            $query->where('city_id', (int) $request->get('city_id'));
            $filters['city_id'] = 'Місто: ' . (optional($city)->name ?? '#' . (int) $request->get('city_id'));
        }
        $statuses = ['0' => 'на модерації', '1' => 'активні', '2' => 'архів'];
        if ($request->filled('status') && isset($statuses[(string) $request->get('status')])) {
            $query->where('status', (int) $request->get('status'));
            $filters['status'] = 'Статус: ' . $statuses[(string) $request->get('status')];
        }

        $ads = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->appends($request->only(['search', 'region_id', 'city_id', 'status']));

        return view('admin.ad.ads')->with([
            'ads' => $ads,
            'search_action' => route('admin.ads'),
            'search' => $request->get('search'),
            'filters' => $filters,
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
            'competitor_url' => 'nullable|url|max:500',
        ], array_merge($errors, [
            'competitor_url.url' => 'Посилання на конкурента має бути коректним URL (https://...)',
        ]));


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

        $ad->fill($request);
        // competitor_url не входить у $fillable — задаємо явно, лише якщо поле є у формі (товари)
        if (array_key_exists('competitor_url', $request)) {
            $ad->competitor_url = $request['competitor_url'] ?: null;
        }
        $ad->save();

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
