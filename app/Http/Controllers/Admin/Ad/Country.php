<?php

namespace App\Http\Controllers\Admin\Ad;

use App\AdCountry;
use App\Ad;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Country extends Controller
{
    private $per_page = 20;

    public function showForm(Request $request, $country_id = null)
    {
        $direction = $request->get('direction') ?? 'ASC';
        $order = $request->get('order') ?? 'name';
        $page = $request->get('page') ?? 1;

        $countries = AdCountry::select('id', 'image', 'name')
            ->orderBy('name', 'asc')->get();

        //получаем кол-во
        foreach ($countries as $country) {
            $count = 0;
            foreach ($country->regions as $region) {
                foreach ($region->cities as $city) {
                    $count += Ad::where('city_id', '=', $city->id)->count();
                }
            }
            $country['ads_count'] = $count;
        }

        if ($order == 'ads_count') {
            $size = count($countries);
            //сортируем по количеству
            if ($direction == 'desc') {
                do {
                    $swapped = false;
                    for ($i = 0; $i < $size - 1; $i++) {
                        if ($countries[$i]->ads_count < $countries[$i + 1]->ads_count) {
                            $temp = $countries[$i];
                            $countries[$i] = $countries[$i + 1];
                            $countries[$i + 1] = $temp;
                            $swapped = true;
                        }
                    }
                    $size--;
                } while ($swapped);
            } else {
                do {
                    $swapped = false;
                    for ($i = 0; $i < $size - 1; $i++) {
                        if ($countries[$i]->ads_count > $countries[$i + 1]->ads_count) {
                            $temp = $countries[$i];
                            $countries[$i] = $countries[$i + 1];
                            $countries[$i + 1] = $temp;
                            $swapped = true;
                        }
                    }
                    $size--;
                } while ($swapped);
            }
        }

        //пагинация
        $countries = new LengthAwarePaginator(
            $countries->forPage($page, $this->per_page),
            count($countries),
            $this->per_page,
            $page,
            ['path' => url('admin/countries?order='.$order.'&direction='.$direction)]
        );

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
            'direction' => $direction,
            'order' => $order,
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

        if ($country_id) {
            $country = AdCountry::find($country_id);

            if (!$country->regions->count()) {
                $country->delete();
                $data['success'] = 'Информация о стране удалена';
            } else {
                $data['error'] = 'Удалить страну можно только есль с ней не связан ни один регион.';
            }

        } else {
            $data['error'] = 'Ошибка удаления страны. Возможно, эта страна была удалена раньше';
        }
        return redirect(route('admin.adCountries'))->with($data);
    }

}
