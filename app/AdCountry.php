<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdCountry extends Model
{
    protected $fillable = [
        'name',
        'image',
        'slug',
        'content',
        'meta_title',
        'meta_description',
        'sort_order'
    ];

    /**
     * Слаг должен содержать английские символы
     * И быть уникальным
     * @param $value
     */
    public function setSlugAttribute($value) {
        if (!isset($value)) {
            // Похожите

            $slug = str_slug($this->attributes['name']);

            if (isset($this->attributes['id'])) {
                $all_slugs = AdCountry::select('slug')
                    ->where('slug', 'LIKE', $slug . '%')
                    ->where('id', '<>', $this->attributes['id'])
                    ->get();
            } else {
                $all_slugs = AdCountry::select('slug')
                    ->where('slug', 'LIKE', $slug . '%')
                    ->get();
            }

            if ($all_slugs->contains('slug', $slug)) {
                for ($i = 1; $i < 100; $i++) {
                    $new_slug = $slug.'-'.$i;
                    if (! $all_slugs->contains('slug', $new_slug)) {
                        $this->attributes['slug'] = $new_slug;
                        break;
                    }

                    if ($i == 99) {
                        $this->attributes['slug'] = $slug . time();
                    }
                }

            } else {
                $this->attributes['slug'] = $slug;
            }


        } else {
            $this->attributes['slug'] = $value;
        }
    }

    public function getImageAttribute() {
        if (env('APP_ENV') == 'local') {
            return 'http://placehold.it/100x100';
        } else {
            return $this->attributes['image'];
        }
    }

    /**
     * Полный адресс
     * @return string
     */
    public function getAddressFormatAttribute() {
        return $this->name;
    }

    /**
     * Ссылка на запись страны
     * @return string
     */
    public function getUrlAttribute() {

        return route('country.page', [
            'country' => $this->slug,
        ]);

    }
    /**
     * Связь с областями
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function regions() {
        return $this->hasMany(AdRegion::class, 'country_id');
    }

    /**
     * Количество городов, которые отнесены к стране
     * @return int
     */
    public function getTotalCitiesAttribute()
    {
        $total_cities = 0;

        $this->regions()->each(function ($region) use (&$total_cities) {
            $total_cities += $region->cities->count();
        });

        return $total_cities;
    }


}
