<?php

namespace App\Http\Controllers\Admin\Ad;

use App\AdCity;
use App\Ad;
use App\AdCountry;
use App\AdRegion;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class City extends Controller
{

    /**
     * Количество регионов / областей на странице
     * @var int
     */
    private $per_page = 20;


    /**
     * Показать страницу с формой добавления городов
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showForm(Request $request, $city_id = null)
    {
        $countries = AdCountry::all();
        $regions = null;
        $city = null;

        $direction = $request->get('direction') ?? 'ASC';
        $order = $request->get('order') ?? 'name';
        
        $cities = AdCity::withCount('ads')->orderBy($order, $direction)
            ->paginate($this->per_page);
            

        if ($city_id) {
            $city = AdCity::find($city_id);
            $regions = AdRegion::find($city->region_id)->country->regions;

            $action = route('admin.adCities.update');
        } else {
            $action = route('admin.adCities.create');
        }
        return view('admin.ad.city')->with([
            'countries' => $countries,
            'regions' => $regions,
            'direction' => $direction,
            'order' => $order,
            'cities' => $cities,
            'city' => $city,
            'action' => $action,
            'action_search' => route('admin.adCities.search')
        ]);
    }

    /**
     * Обработчик для формы поиска по городам
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function search(Request $request)
    {
        $requested_city = $request->name;

        $countries = AdCountry::all();
        $regions = null;
        $city = null;

        $cities = AdCity::where('name', 'like', '%' . $requested_city . '%')
            ->orderBy('name', 'ASC')
            ->paginate($this->per_page);


        $action = route('admin.adCities.create');

        return view('admin.ad.city')->with([
            'countries' => $countries,
            'regions' => $regions,
            'cities' => $cities,
            'city' => $city,
            'action' => $action,
            'action_search' => route('admin.adCities.search'),
            'requested_city' => $requested_city
        ]);
    }

    /**
     * Создать новый город через форму добавления
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function create(Request $request)
    {
        $errors = [
            'name.required' => 'Введите название',
            'region_id.required' => 'Город должен быть привязан к области. Выберите область!',
            'region_id.not_in' => 'Город должен быть привязан к области. Выберите область!',
            'slug.unique' => 'Такой slug уже существует',
            'meta_title.max' => 'Максимальная длина поля Meta-тег title: :max символов',
            'meta_description.max' => 'Максимальная длина поля Meta-тег description: :max символов'
        ];

        $request->validate([
            'name' => 'required|string',
            'region_id' => 'required|integer|not_in:0',
            'slug' => 'unique:ad_cities',
            'meta_title' => 'max:255',
            'meta_description' => 'max:255',

        ], $errors);

        AdCity::create($request->all());

        return redirect(route('admin.adCities'))
            ->with('success', 'Город добавлен');
    }

    /**
     * Изменить информацию о существующем городе
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request)
    {
        $errors = [
            'name.required' => 'Введите название',
            'region_id.required' => 'Город должен быть привязан к области. Выберите область!',
            'region_id.not_in' => 'Город должен быть привязан к области. Выберите область!',
            'slug.unique' => 'Такой slug уже используется',
            'meta_title.max' => 'Максимальная длина поля Meta-тег title: :max символов',
            'meta_description.max' => 'Максимальная длина поля Meta-тег description: :max символов',
        ];

        $request->validate([
            'name' => 'required|string',
            'region_id' => 'required|integer|not_in:0',
            'slug' => 'unique:ad_cities,slug,' . $request->city_id,
            'meta_title' => 'max:255',
            'meta_description' => 'max:255',
        ], $errors);

        $city = AdCity::find($request->city_id);

        if ($city) {
            $city->fill($request->all())->save();
        }

        return redirect(route('admin.adCities'))
            ->with('success', "Данные города {$city->name} обновлены");
    }

    /**
     * Удалить город.
     * В случае, если к городу привязаны объявления - уведомляем что удаление невозможно
     * @param $city_id integer Идентификатор города
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Exception
     */
    public function delete($city_id)
    {
        if ($city_id)
        {
            AdCity::find($city_id)->delete();
        }

        return redirect(route('admin.adCities'))
            ->with('success', 'Город удален с лица земли.');
    }
}
