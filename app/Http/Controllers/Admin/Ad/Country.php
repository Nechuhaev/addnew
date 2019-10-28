<?php

namespace App\Http\Controllers\Admin\Ad;

use App\AdCountry;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Country extends Controller
{
    private $per_page = 20;

    public function showForm($country_id = null)
    {
        $countries = AdCountry::select('id', 'image', 'name')
            ->orderBy('name', 'asc')
            ->paginate($this->per_page);

        $country = null;

        if ($country_id)
        {
            $country = AdCountry::find($country_id);
            $action = route('admin.adCountries.update');
        } else {
            $action = route('admin.adCountries.create');
        }
        return view('admin.ad.country')->with([
            'action' => $action,
            'country' => $country,
            'countries' => $countries,
            'action_search' => route('admin.adCountries.search')
        ]);
    }

    public function search(Request $request)
    {
        $requested_country = $request->name;

        $countries = AdCountry::select(['id', 'name'])
            ->where('name', 'like', '%' . $requested_country . '%')
            ->orderBy('name', "ASC")->paginate($this->per_page);

        $country = null;

        $action = route('admin.adCountries.create');
        // TODO Вывести искомую страну во view
        return view('admin.ad.country')->with([
            'action' => $action,
            'country' => $country,
            'countries' => $countries,
            'action_search' => route('admin.adCountries.search'),
            'requested_country' => $requested_country
        ]);
    }

    public function create(Request $request)
    {
        $errors = [
            'name.required' => 'Введите название',
            'image.required' => 'Для страны должно быть назначено изображение',
            'slug.unique' => 'Такой slug уже существует',
            'meta_title.max' => 'Максимальная длина поля Meta-тег title: :max символов',
            'meta_description.max' => 'Максимальная длина поля Meta-тег description: :max символов'
        ];

        $request->validate([
            'name' => 'required|string',
            'image' => 'required|string',
            'slug' => 'unique:ad_tags',
            'meta_title' => 'max:255',
            'meta_description' => 'max:255',

        ], $errors);

        AdCountry::create($request->all());

        return redirect(route('admin.adCountries'))->with('success', 'Страна добавлена');
    }

    public function update(Request $request)
    {
        $errors = [
            'name.required' => 'Введите название',
            'image.required' => 'Для страны должно быть назначено изображение',
            'slug.unique' => 'Такой slug уже используется',
            'meta_title.max' => 'Максимальная длина поля Meta-тег title: :max символов',
            'meta_description.max' => 'Максимальная длина поля Meta-тег description: :max символов',
        ];

        $request->validate([
            'name' => 'required|string',
            'image' => 'required|string',
            'slug' => 'unique:ad_countries,slug,' . $request->country_id,
            'meta_title' => 'max:255',
            'meta_description' => 'max:255',
        ], $errors);

        $country = AdCountry::find($request->country_id);

        if ($country) {
            $country->fill($request->all())->save();
        }

        return redirect(route('admin.adCountries'))->with('success', 'Данные страны обновлены');
    }

    public function delete($country_id)
    {
        #TODO: синхронизация удаленной метки к объявлениям
        if ($country_id) {
            AdCountry::find($country_id)->delete();
        }
        return redirect(route('admin.adCountries'))->with('success', 'Данные страны удалены');
    }

}
