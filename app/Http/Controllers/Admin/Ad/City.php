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

        [$cities, $sort, $dir] = $this->listCities($request);

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
            'sort' => $sort,
            'dir' => $dir,
            'cities' => $cities,
            'city' => $city,
            'action' => $action,
            'allRegions' => AdRegion::orderBy('name')->get(['id', 'name', 'name_uk', 'country_id']),
            'perPageOptions' => self::PER_PAGE,
        ]);
    }

    /**
     * Обработчик для формы поиска по городам
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function search(Request $request)
    {
        // Старий URL пошуку: тепер пошук — фільтр списку
        return redirect()->route('admin.adCities', array_filter(['q' => $request->get('name')]));
    }

    const PER_PAGE = [20, 50, 100];
    const SORTS = ['id', 'name', 'region', 'country', 'ads_count', 'active_count'];

    /**
     * Міста з кількістю оголошень (усіх і активних) одним запитом, з фільтрами й сортуванням.
     */
    protected function listCities(Request $request): array
    {
        $stats = \Illuminate\Support\Facades\DB::table('ads')
            ->selectRaw('city_id, COUNT(*) AS ads_count, SUM(status = 1) AS active_count')
            ->groupBy('city_id');

        $query = AdCity::with('region.country')
            ->leftJoinSub($stats, 'st', 'st.city_id', '=', 'ad_cities.id')
            ->select('ad_cities.*')
            ->selectRaw('COALESCE(st.ads_count, 0) AS ads_count, COALESCE(st.active_count, 0) AS active_count');

        $q = trim((string) ($request->get('q') ?? $request->get('name')));
        if ($q !== '') {
            $like = '%' . addcslashes($q, '%_\\') . '%';
            $query->where(function ($w) use ($q, $like) {
                $w->where('ad_cities.name', 'like', $like)
                    ->orWhere('ad_cities.name_uk', 'like', $like)
                    ->orWhere('ad_cities.slug', 'like', $like);
                if (ctype_digit($q)) {
                    $w->orWhere('ad_cities.id', (int) $q);
                }
            });
        }
        if ($request->filled('region_id')) {
            $query->where('ad_cities.region_id', (int) $request->get('region_id'));
        }
        if ($request->filled('country_id')) {
            $query->whereIn('ad_cities.region_id', AdRegion::where('country_id', (int) $request->get('country_id'))->select('id'));
        }
        if ($request->get('has_ads') === 'yes') {
            $query->whereRaw('COALESCE(st.ads_count, 0) > 0');
        } elseif ($request->get('has_ads') === 'no') {
            $query->whereRaw('COALESCE(st.ads_count, 0) = 0');
        }
        foreach (['ads_count', 'active_count'] as $col) {
            $min = $request->input('min.' . $col);
            if ($min !== null && $min !== '' && is_numeric($min)) {
                $query->whereRaw("COALESCE(st.{$col}, 0) >= ?", [(int) $min]);
            }
        }

        $sort = in_array($request->get('sort'), self::SORTS, true) ? $request->get('sort') : 'name';
        $dir = $request->get('dir') === 'desc' ? 'desc' : 'asc';
        if ($sort === 'region') {
            $query->orderBy(AdRegion::select('name')->whereColumn('ad_regions.id', 'ad_cities.region_id'), $dir);
        } elseif ($sort === 'country') {
            $query->orderBy(
                AdCountry::select('ad_countries.name')
                    ->join('ad_regions', 'ad_regions.country_id', '=', 'ad_countries.id')
                    ->whereColumn('ad_regions.id', 'ad_cities.region_id')
                    ->limit(1),
                $dir
            );
        } elseif (in_array($sort, ['ads_count', 'active_count'], true)) {
            $query->orderBy($sort, $dir);
        } else {
            $query->orderBy('ad_cities.' . $sort, $dir);
        }
        $query->orderBy('ad_cities.id');

        $perPage = in_array((int) $request->get('per_page'), self::PER_PAGE, true) ? (int) $request->get('per_page') : self::PER_PAGE[0];

        return [$query->paginate($perPage)->appends($request->except('page')), $sort, $dir];
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

    /**
     * Удалить города.
     * @param $city_id integer Идентификатор города
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Exception
     */
    public function deleteMany($ids)
    {
        $ids = explode(',', $ids);

        if ($ids) {
            foreach ($ids as $id) {
                AdCity::find($id)->delete();
            }
            return route('admin.adCities');
        }
    }
}
