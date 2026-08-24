<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class AdTag extends Model
{
    protected $fillable = [
        'name',
        'name_uk',
        'slug',
        'content',
        'content_uk',
        'meta_title',
        'meta_title_uk',
        'meta_description',
        'meta_description_uk',
    ];
    public $timestamps = false;

    /**
     * Мовні аксесори — той самий підхід, що в AdCategory/AdCity/AdRegion.
     */
    public function getNameAttribute($value)
    {
        if (app()->getLocale() === 'uk' && !empty($this->attributes['name_uk'] ?? null)) {
            return $this->attributes['name_uk'];
        }
        return $value;
    }

    public function getMetaTitleAttribute($value)
    {
        if (app()->getLocale() === 'uk' && !empty($this->attributes['meta_title_uk'] ?? null)) {
            return $this->attributes['meta_title_uk'];
        }
        return $value;
    }

    public function getMetaDescriptionAttribute($value)
    {
        if (app()->getLocale() === 'uk' && !empty($this->attributes['meta_description_uk'] ?? null)) {
            return $this->attributes['meta_description_uk'];
        }
        return $value;
    }

    public function getContentAttribute($value)
    {
        if (app()->getLocale() === 'uk' && !empty($this->attributes['content_uk'] ?? null)) {
            return $this->attributes['content_uk'];
        }
        return $value;
    }

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
     * Получить список тегов для объявлений.
     *
     * ВАЖЛИВО: переведено з сирого DB::table() на Eloquent (AdTag::)
     * навмисно — інакше мовний аксесор getNameAttribute() не спрацює,
     * бо DB::table() повертає звичайні stdClass, не моделі.
     *
     * @param $ads
     * @return \Illuminate\Support\Collection
     */
    public static function getAdsTags($ads) {
        $ads_ids = [];
        foreach ($ads as $ad) {
            $ads_ids[] = $ad['id'];
        }
        return AdTag::select('ad_tags.id', 'ad_tags.name', 'ad_tags.name_uk', 'ad_tags.slug')
            ->distinct()
            ->leftJoin('ad_tag', 'ad_tags.id', '=', 'ad_tag.tag_id')
            ->whereIn('ad_tag.ad_id', $ads_ids)
            ->orderBy('ad_tags.slug', 'asc')
            ->take(30)
            ->get();
    }
    /**
     * Получить список тегов для объявления (той самий підхід — Eloquent
     * замість сирого DB::table(), щоб працював мовний аксесор).
     *
     * @param $ad
     * @return \Illuminate\Support\Collection
     */
    public static function getAdTags($ad) {
        return AdTag::select('ad_tags.id', 'ad_tags.name', 'ad_tags.name_uk', 'ad_tags.slug')
            ->distinct()
            ->leftJoin('ad_tag', 'ad_tags.id', '=', 'ad_tag.tag_id')
            ->where('ad_tag.ad_id', '=', $ad['id'])
            ->orderBy('ad_tags.slug', 'asc')
            ->take(30)
            ->get();
    }
}