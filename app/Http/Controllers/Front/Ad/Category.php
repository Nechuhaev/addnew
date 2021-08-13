<?php

namespace App\Http\Controllers\Front\Ad;

use App\Ad;
use App\AdCategory;
use App\AdCity;
use App\AdCountry;
use App\AdRegion;
use App\AdTag;
use App\Http\Controllers\Controller;
use App\SeoField;
use Doctrine\DBAL\Query\QueryBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

class Category extends Controller
{
    private $filter = null;

    private $category_id = [];

    private $included_categories = [];

    public function page($category, $subcategory = null) {
        return $this->getList($category, $subcategory, null);
    }

    public function filter($filter, $category, $subcategory = null) {
        return $this->getList($category, $subcategory, $filter);
    }

    private function getList($category, $subcategory, $filter = null) {

        $this->filter = $filter;

        $categorySlug = $subcategory ?? $category;

        $entity = AdCategory::where('slug', $categorySlug)->first();

        if (!$entity) abort(404);

        // фикс ошибки с ad-category/slug
        if (!$entity->parent_id && $subcategory) return  redirect(route('category.page', [
            'category' => $subcategory
        ]));

        $this->category_id = $entity->id;



        $this->included_categories[] = $entity->id;
        if ($entity->children()) {
            foreach ($entity->children()->pluck('id') as $category_in) {
                $this->included_categories[] = $category_in;
            }
        }


        $results = $this->getFilteredResultsQuery()->paginate(15);

        // SEO fields
        $seo_field = SeoField::where('index', 'ad-category')->first();

        if ($this->filter) {
//            $filter_entity = AdCountry::whereSlug($this->filter)->first() ?? AdRegion::whereSlug($this->filter)->first() ?? AdCity::whereSlug($this->filter)->first();
            $filter_entity = AdRegion::whereSlug($this->filter)->first() ?? AdCity::whereSlug($this->filter)->first();

            if (!$filter_entity) {
                return redirect('/', 301);
            }
        }

        if ($seo_field) {
            $parent = $entity->parent;
            $entity_values = [
                '---category_name---'  => $entity->name,
                '---parent_name---'    => ($parent) ? $entity->parent->name : '',
                '---filtered_name---'  => $filter_entity->name ?? "доска бесплатных объявлений Addnew.biz в Украине",
                '---full_filtered_name---'    => $filter_entity->address_format ?? "доска бесплатных объявлений Addnew.biz в Украине",
            ];
            $meta = [
                'meta_title' => strtr($entity->meta_title, $entity_values) ?? strtr($seo_field->meta_title, $entity_values),
                'meta_description' => strtr($entity->meta_description, $entity_values) ?? strtr($seo_field->meta_description, $entity_values),
                'description' => strtr($entity->content, $entity_values) ?? strtr($seo_field->description, $entity_values)
            ];
        } else {
            $entity_values = [
                '---filtered_name---'  => $filter_entity->name ?? "доска бесплатных объявлений Addnew.biz в Украине",
                '---full_filtered_name---'    => $filter_entity->address_format ?? "доска бесплатных объявлений Addnew.biz в Украине",
            ];

            $meta = [
                'meta_title' => strtr($entity->meta_title, $entity_values),
                'meta_description' => strtr($entity->meta_description, $entity_values),
                'description' => strtr($entity->content, $entity_values),
            ];
        }


        if (request()->get('page')) {
            $meta['description'] = false;
        }

        $microdata_info = $this->getMicrodata();

        $ads = Ad::getLoopArray($results);

        return view('front.ad.category')->with([
            'entity' => $entity,
            'filters' => $this->getFilters(),
            'filter' => $this->filter,
            'ads' => $ads,
            'links' => $results->onEachSide(1)->links('front.widgets.paginate'),
            'tags' => AdTag::getAdsTags($ads),
            'breadcrumbs' => (!$this->filter) ? 'category.page' : 'filtered.category.page',
            'microdata' => $microdata_info,
            'meta' => $meta
        ]);
    }


    private function getMicrodata() {
        $query = DB::table('ad_categories')
            ->selectRaw('min(ads.price) as min, max(ads.price) as max, count(ads.id) as ads_count')
            ->leftJoin('ads', 'ad_categories.id', '=', 'ads.category_id')
            ->leftJoin('ad_cities', 'ad_cities.id', '=', 'ads.city_id')
            ->leftJoin('ad_regions', 'ad_regions.id', '=', 'ad_cities.region_id')
            ->leftJoin('ad_countries', 'ad_countries.id', '=', 'ad_regions.country_id')
            ->whereIn('ad_categories.id', $this->included_categories)
            ->where('ads.price', '>', 0);

        if ($this->filter) {
            $entity = AdCountry::whereSlug($this->filter)->first();
            $condition = 'country_id';
            if (!$entity) {
                $entity = AdRegion::whereSlug($this->filter)->first();
                $condition = 'region_id';
                if (!$entity) {
                    $entity = AdCity::whereSlug($this->filter)->first();
                    $condition = 'city_id';
                }
            }

            $query->where($condition, $entity->id);
        }

        return $query->first();
    }

    private function getFilteredResultsQuery() : Builder {
        if ($this->filter) {
            $entity = AdCountry::whereSlug($this->filter)->first();
            $condition = 'country_id';
            if (!$entity) {
                $entity = AdRegion::whereSlug($this->filter)->first();
                $condition = 'region_id';
                if (!$entity) {
                    $entity = AdCity::whereSlug($this->filter)->first();
                    $condition = 'city_id';
                }
            }
        }
        //dd($this->filter);
        $results = Ad::getAds()->whereIn('ad_categories.id', $this->included_categories);
                
        // только Украина
        $results->where('country_id', '62');
        if ($this->filter) {
            if (!$entity) abort(404);
            $results->where($condition, $entity->id);
        }

        //dd($results);
        return $results;
    }

    /**
     * Вернуть набор фильтров, который будет актуальным для текущей страницы
     * @return array
     */
    private function getFilters() {

        $filters = null;
        if ($this->filter) {

            // Фильтр по городам
            $entity = AdRegion::whereSlug($this->filter)->first();

            if ($entity) {
                $results = Ad::getAds()
                    ->selectRaw('COUNT(ads.id) as ads_count')
                    ->whereIn('ad_categories.id', $this->included_categories)
                    ->where('region_id', $entity->id)
                    ->groupBy('city_id')
                    ->get()->toArray();

                //dd($results);
                $ids = array_column($results, 'city_id');
                $ads_counts = array_column($results, 'ads_count', 'city_id');

                $filters = AdCity::find($ids);
            }

        } else {

            // Фильтр по областям
            $entity = AdCountry::getCurrentCountry();


            $results = Ad::getAds()
                ->selectRaw('COUNT(ads.id) as ads_count')
                ->whereIn('ad_categories.id', $this->included_categories)
                ->where('country_id', $entity->id)
                ->groupBy('region_id')
                ->get()->toArray();

            $ids = array_column($results, 'region_id');
            $ads_counts = array_column($results, 'ads_count', 'region_id');
            $filters = AdRegion::find($ids);

        }


        if ($filters) {
            $data = [];
            foreach ($filters as $filter) {

                $category = AdCategory::find($this->category_id);

                if ($category->parent_id) {
                    $url = route('filtered_subcategory.page', [
                        'filter' => $filter->slug,
                        'category' => $category->parent->slug,
                        'subcategory' => $category->slug
                    ]);
                } else {
                    $url = route('filtered_category.page', [
                        'filter' => $filter->slug,
                        'category' => $category->slug,
                    ]);
                }


                //dd($this->category_id);

                $data[] = [
                    'name' => $filter->name,
                    'ads_count' => $ads_counts[$filter->id],
                    'url' => $url,
                    'image' => $filter->image ?? null,
                ];
            }

            usort($data, function ($a, $b) {
                return $a['ads_count'] < $b['ads_count'];
            });

            return $data;
        } else {
            return null;
        }

    }
}
