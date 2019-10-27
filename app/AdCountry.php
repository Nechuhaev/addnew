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

    /**
     * Значение по умолчанию для sort_order
     * @param $value
     */
    public function setSortOrderAttribute($value) {
        if (!isset($value)) {
            $this->attributes['sort_order'] = 0;
        } else {
            $this->attributes['sort_order'] = (int)$value;
        }
    }
}
