<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdTag extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'content',
        'meta_title',
        'meta_description'
    ];

    public $timestamps = false;

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
                $all_slugs = AdTag::select('slug')
                    ->where('slug', 'LIKE', $slug . '%')
                    ->where('id', '<>', $this->attributes['id'])
                    ->get();
            } else {
                $all_slugs = AdTag::select('slug')
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

    public function ads()
    {
        return $this->belongsToMany('App\Ad', 'ad_tag', 'tag_id', 'ad_id');
    }
}
