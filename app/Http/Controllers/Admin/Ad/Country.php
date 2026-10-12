<?php

namespace App\Http\Controllers\Admin\Ad;

use App\AdCountry;
use App\Ad;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Country extends Controller
{
    private $per_page = 20;

    public function showForm(Request $request, $country_id = null)
    {
        [$countries, $sort, $dir] = $this->listCountries($request);

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
            'sort' => $sort,
            'dir' => $dir,
            'countries' => $countries,
            'perPageOptions' => self::PER_PAGE,
        ]);
    }

    public function search(Request $request)
    {
        // Старий URL пошуку: тепер пошук — фільтр списку
        return redirect()->route('admin.adCountries', array_filter(['q' => $request->get('name')]));
    }

    const PER_PAGE = [20, 50, 100];
    const SORTS = ['id', 'name', 'regions_count', 'cities_count', 'ads_count', 'active_count'];

    /**
     * Країни з кількістю областей / міст / оголошень одним запитом
     * (раніше — COUNT на кожне місто кожної області кожної країни).
     */
    protected function listCountries(Request $request): array
    {
        $DB = \Illuminate\Support\Facades\DB::class;
        $regions = $DB::table('ad_regions')->selectRaw('country_id, COUNT(*) AS regions_count')->groupBy('country_id');
        $stats = $DB::table('ad_regions as r')
            ->join('ad_cities as c', 'c.region_id', '=', 'r.id')
            ->leftJoin('ads as a', 'a.city_id', '=', 'c.id')
            ->selectRaw('r.country_id, COUNT(DISTINCT c.id) AS cities_count, COUNT(a.id) AS ads_count, COALESCE(SUM(a.status = 1), 0) AS active_count')
            ->groupBy('r.country_id');

        $query = AdCountry::query()
            ->leftJoinSub($regions, 'rg', 'rg.country_id', '=', 'ad_countries.id')
            ->leftJoinSub($stats, 'st', 'st.country_id', '=', 'ad_countries.id')
            ->select('ad_countries.*')
            ->selectRaw('COALESCE(rg.regions_count, 0) AS regions_count, COALESCE(st.cities_count, 0) AS cities_count, COALESCE(st.ads_count, 0) AS ads_count, COALESCE(st.active_count, 0) AS active_count');

        $q = trim((string) ($request->get('q') ?? $request->get('name')));
        if ($q !== '') {
            $like = '%' . addcslashes($q, '%_\\') . '%';
            $query->where(function ($w) use ($q, $like) {
                $w->where('ad_countries.name', 'like', $like)
                    ->orWhere('ad_countries.name_uk', 'like', $like)
                    ->orWhere('ad_countries.slug', 'like', $like);
                if (ctype_digit($q)) {
                    $w->orWhere('ad_countries.id', (int) $q);
                }
            });
        }
        if ($request->get('has_ads') === 'yes') {
            $query->whereRaw('COALESCE(st.ads_count, 0) > 0');
        } elseif ($request->get('has_ads') === 'no') {
            $query->whereRaw('COALESCE(st.ads_count, 0) = 0');
        }
        foreach (['regions_count' => 'rg', 'cities_count' => 'st', 'ads_count' => 'st', 'active_count' => 'st'] as $col => $alias) {
            $min = $request->input('min.' . $col);
            if ($min !== null && $min !== '' && is_numeric($min)) {
                $query->whereRaw("COALESCE({$alias}.{$col}, 0) >= ?", [(int) $min]);
            }
        }

        $sort = in_array($request->get('sort'), self::SORTS, true) ? $request->get('sort') : 'name';
        $dir = $request->get('dir') === 'desc' ? 'desc' : 'asc';
        $query->orderBy(in_array($sort, ['id', 'name'], true) ? 'ad_countries.' . $sort : $sort, $dir)->orderBy('ad_countries.id');

        $perPage = in_array((int) $request->get('per_page'), self::PER_PAGE, true) ? (int) $request->get('per_page') : self::PER_PAGE[0];

        return [$query->paginate($perPage)->appends($request->except('page')), $sort, $dir];
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

    public function deleteMany($ids)
    {
        $ids = explode(',', $ids);

        if ($ids) {
            foreach ($ids as $id) {
                $country = AdCountry::find($id);

                if (!$country->regions->count()) {
                    $country->delete();
                }
            }
            return route('admin.adCountries');
        }
    }
}
