<?php

namespace App\Http\Controllers\Admin\Ad;

use App\AdCountry;
use App\AdRegion;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Region extends Controller
{
    /**
     * Количество регионов / областей на странице
     * @var int
     */
    private $per_page = 20;

    /**
     * Отобразить страницу с формой добавления
     * И списком областей / регионов
     *
     * @param null $region_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showForm($region_id = null) {

        $countries_list = AdCountry::select(['id', 'name'])->get()->toArray();

        $regions = AdRegion::orderBy('name', 'ASC')
            ->paginate($this->per_page);


        $region = null;

        if ($region_id) {
            $region = AdRegion::find($region_id);
            $action = route('admin.adRegions.update');
        } else {
            $action = route('admin.adRegions.create');
        }
        return view('admin.ad.region')->with([
            'action' => $action,
            'countries' => $countries_list,
            'region' => $region,
            'regions' => $regions,
            'action_search' => route('admin.adRegions.search')
        ]);
    }

    /**
     * Обработчик для формы поиска областей
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function search(Request $request)
    {
        $requested_region = $request->name;

        $countries_list = AdCountry::select(['id', 'name'])->get()->toArray();

        $regions = AdRegion::where('name', 'like', '%' . $requested_region . '%')
            ->orderBy('name', "ASC")->paginate($this->per_page);

        $region = null;

        $action = route('admin.adRegions.create');

        return view('admin.ad.region')->with([
            'countries' => $countries_list,
            'action' => $action,
            'region' => $region,
            'regions' => $regions,
            'action_search' => route('admin.adRegions.search'),
            'requested_region' => $requested_region
        ]);
    }

    /**
     * Создать новую область
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function create(Request $request)
    {
        $errors = [
            'name.required' => 'Введите название',
            'country_id.required' => 'Область должа быть привязана к стране. Выберите страну!',
            'country_id.not_in' => 'Область должа быть привязана к стране. Выберите страну!',
            'slug.unique' => 'Такой slug уже существует',
            'meta_title.max' => 'Максимальная длина поля Meta-тег title: :max символов',
            'meta_description.max' => 'Максимальная длина поля Meta-тег description: :max символов'
        ];

        $request->validate([
            'name' => 'required|string',
            'country_id' => 'required|integer|not_in:0',
            'slug' => 'unique:ad_regions',
            'meta_title' => 'max:255',
            'meta_description' => 'max:255',

        ], $errors);

        AdRegion::create($request->all());

        return redirect(route('admin.adRegions'))->with('success', 'Область добавлена');
    }

    /**
     * Обновить область через форму
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request)
    {
        $errors = [
            'name.required' => 'Введите название',
            'country_id.required' => 'Область должа быть привязана к стране. Выберите страну!',
            'country_id.not_in' => 'Область должа быть привязана к стране. Выберите страну!',
            'slug.unique' => 'Такой slug уже используется',
            'meta_title.max' => 'Максимальная длина поля Meta-тег title: :max символов',
            'meta_description.max' => 'Максимальная длина поля Meta-тег description: :max символов',
        ];

        $request->validate([
            'name' => 'required|string',
            'country_id' => 'required|integer|not_in:0',
            'slug' => 'unique:ad_regions,slug,' . $request->region_id,
            'meta_title' => 'max:255',
            'meta_description' => 'max:255',
        ], $errors);

        $region = AdRegion::find($request->region_id);

        if ($region) {
            $region->fill($request->all())->save();
        }

        return redirect(route('admin.adRegions'))->with('success', 'Данные региона / области обновлены');
    }

    /**
     * Удалить регион / область
     * @param $region_id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Exception
     */
    public function delete($region_id)
    {
        if ($region_id) {
            $region = AdRegion::find($region_id);

            if (!$region->cities->count()) {
                $region->delete();
                $data['success'] = 'Информация об области удалена';
            } else {
                $data['error'] = 'Удалить информацию об области можно только есль с ней не связан ни один город.';
            }

        } else {
            $data['error'] = 'Ошибка удаления области. Возможно, она была удалена раньше';
        }
        return redirect(route('admin.adRegions'))->with($data);
    }

}
