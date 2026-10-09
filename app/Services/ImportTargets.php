<?php

namespace App\Services;

use App\AdCategory;
use App\AdCity;
use App\AdRegion;

/**
 * Варіанти «категорія / місто для нових товарів» для імпорту й фіда:
 * категорії — підкатегорії, згруповані за батьківською; міста — за областю.
 */
class ImportTargets
{
    /** @return array [['label' => батьківська, 'options' => [id => назва]]] */
    public static function categories(): array
    {
        $groups = [];
        $parents = AdCategory::where('parent_id', 0)->orderBy('id')->get();
        $children = AdCategory::where('parent_id', '<>', 0)->orderBy('id')->get()->groupBy('parent_id');
        foreach ($parents as $parent) {
            $options = [];
            foreach ($children->get($parent->id, collect()) as $child) {
                $options[$child->id] = $child->name;
            }
            $groups[] = ['label' => $parent->name, 'options' => $options ?: [$parent->id => $parent->name]];
        }
        return $groups;
    }

    /** @return array [['label' => область, 'options' => [id => місто]]] */
    public static function cities(): array
    {
        $regions = AdRegion::orderBy('id')->get()->keyBy('id');
        $groups = [];
        foreach (AdCity::orderBy('region_id')->get()->groupBy('region_id') as $regionId => $cities) {
            $options = [];
            foreach ($cities->sortBy('name') as $city) {
                $options[$city->id] = $city->name;
            }
            $groups[] = ['label' => optional($regions->get($regionId))->name ?? '—', 'options' => $options];
        }
        return $groups;
    }
}
