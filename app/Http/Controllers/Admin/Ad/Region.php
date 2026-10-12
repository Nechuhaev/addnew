<?php

namespace App\Http\Controllers\Admin\Ad;

use App\AdCountry;
use App\AdRegion;
use App\AdCity;
use App\Ad;
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
    public function showForm(Request $request, $region_id = null) {

        [$regions, $sort, $dir] = $this->listRegions($request);
        $countries_list = AdCountry::select(['id', 'name'])->get()->toArray();

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
            'sort' => $sort,
            'dir' => $dir,
            'region' => $region,
            'regions' => $regions,
            'action_search' => route('admin.adRegions'),
            'perPageOptions' => self::PER_PAGE,
        ]);
    }

    const PER_PAGE = [20, 50, 100];
    const SORTS = ['id', 'name', 'country', 'cities_count', 'ads_count', 'active_count'];

    /**
     * Список областей з кількістю міст / оголошень одним запитом (раніше —
     * окремий COUNT на кожне місто кожної області), з фільтрами й сортуванням.
     */
    protected function listRegions(Request $request): array
    {
        $stats = \Illuminate\Support\Facades\DB::table('ad_cities as c')
            ->leftJoin('ads as a', 'a.city_id', '=', 'c.id')
            ->selectRaw('c.region_id, COUNT(DISTINCT c.id) AS cities_count, COUNT(a.id) AS ads_count, COALESCE(SUM(a.status = 1), 0) AS active_count')
            ->groupBy('c.region_id');

        $query = AdRegion::with('country')
            ->leftJoinSub($stats, 'st', 'st.region_id', '=', 'ad_regions.id')
            ->select('ad_regions.*')
            ->selectRaw('COALESCE(st.cities_count, 0) AS cities_count, COALESCE(st.ads_count, 0) AS ads_count, COALESCE(st.active_count, 0) AS active_count');

        $q = trim((string) ($request->get('q') ?? $request->get('name')));
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $like = '%' . addcslashes($q, '%_\\') . '%';
                $w->where('ad_regions.name', 'like', $like)
                    ->orWhere('ad_regions.name_uk', 'like', $like)
                    ->orWhere('ad_regions.slug', 'like', $like);
                if (ctype_digit($q)) {
                    $w->orWhere('ad_regions.id', (int) $q);
                }
            });
        }
        if ($request->filled('country_id')) {
            $query->where('ad_regions.country_id', (int) $request->get('country_id'));
        }
        if ($request->get('has_ads') === 'yes') {
            $query->whereRaw('COALESCE(st.ads_count, 0) > 0');
        } elseif ($request->get('has_ads') === 'no') {
            $query->whereRaw('COALESCE(st.ads_count, 0) = 0');
        }
        foreach (['cities_count', 'ads_count', 'active_count'] as $col) {
            $min = $request->input('min.' . $col);
            if ($min !== null && $min !== '' && is_numeric($min)) {
                $query->whereRaw("COALESCE(st.{$col}, 0) >= ?", [(int) $min]);
            }
        }

        $sort = in_array($request->get('sort'), self::SORTS, true) ? $request->get('sort') : 'name';
        $dir = $request->get('dir') === 'desc' ? 'desc' : 'asc';
        if ($sort === 'country') {
            $query->orderBy(AdCountry::select('name')->whereColumn('ad_countries.id', 'ad_regions.country_id'), $dir);
        } elseif (in_array($sort, ['cities_count', 'ads_count', 'active_count'], true)) {
            $query->orderBy($sort, $dir);
        } else {
            $query->orderBy('ad_regions.' . $sort, $dir);
        }
        $query->orderBy('ad_regions.id');

        $perPage = in_array((int) $request->get('per_page'), self::PER_PAGE, true) ? (int) $request->get('per_page') : self::PER_PAGE[0];
        $regions = $query->paginate($perPage)->appends($request->except('page'));

        return [$regions, $sort, $dir];
    }

    /**
     * Обработчик для формы поиска областей
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function search(Request $request)
    {
        // Старий URL пошуку: тепер пошук — фільтр списку
        return redirect()->route('admin.adRegions', array_filter(['q' => $request->get('name')]));
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

    /**
     * Удалить регионы / области
     * @param $region_id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Exception
     */
    public function deleteMany($ids)
    {
        $ids = explode(',', $ids);

        if ($ids) {
            foreach ($ids as $id) {
                $region = AdRegion::find($id);
    
                if (!$region->cities->count()) {
                    $region->delete();
                }
            }
            return route('admin.adRegions');
        }
    }

}
