<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

    public function getUrlAttribute() {
        return route('tag', ['slug' => $this->slug]);
    }


    public function ads()
    {
        return $this->belongsToMany('App\Ad', 'ad_tag', 'tag_id', 'ad_id');
    }


    /**
     * Получить список тегов для объявлений
     * @param $ads
     * @return \Illuminate\Support\Collection
     */
    public static function getAdsTags($ads) {

        $ads_ids = [];
        foreach ($ads as $ad) {
            $ads_ids[] = $ad['id'];
        }

        $tags = DB::table('ad_tags')
            ->select(DB::raw("DISTINCT(ad_tags.slug)"), 'ad_tags.id', 'ad_tags.name')
            ->leftJoin('ad_tag', 'ad_tags.id', '=', 'ad_tag.tag_id')
            ->whereIn('ad_tag.ad_id', $ads_ids)->orderBy('ad_tags.slug', 'asc')
            ->take(30)
            ->get();

        return $tags;
    }

    /**
     * Получить список тегов для объявления
     * @param $ad
     * @return \Illuminate\Support\Collection
     */
    public static function getAdTags($ad) {

        $tags = DB::table('ad_tags')
            ->select(DB::raw("DISTINCT(ad_tags.slug)"), 'ad_tags.id', 'ad_tags.name')
            ->leftJoin('ad_tag', 'ad_tags.id', '=', 'ad_tag.tag_id')
            ->where('ad_tag.ad_id', '=', $ad['id'])->orderBy('ad_tags.slug', 'asc')
            ->take(30)
            ->get();

        return $tags;
    }
}
