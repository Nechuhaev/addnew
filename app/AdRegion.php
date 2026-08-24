<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class AdRegion extends Model
{
    protected $fillable = [
        'country_id',
        'name',
        'name_uk',
        'slug',
        'content',
        'content_uk',
        'meta_title',
        'meta_title_uk',
        'meta_description',
        'meta_description_uk',
        'sort_order'
    ];

    /**
     * Мовні аксесори — той самий підхід, що в AdCategory/AdCity.
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
                $all_slugs = AdRegion::select('slug')
                    ->where('slug', 'LIKE', $slug . '%')
                    ->where('id', '<>', $this->attributes['id'])
                    ->get();
            } else {
                $all_slugs = AdRegion::select('slug')
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
     * Полный адресс
     * @return string
     */
    public function getAddressFormatAttribute() {
        return $this->name . ', ' . $this->country->name;
    }
    public function getUrlAttribute() {
        return route('region.page', [
            'country' => $this->country->slug,
            'region' => $this->slug,
        ]);
    }
    /**
     * Обратная связь к странам (Один регион - одна страна)
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(AdCountry::class, 'country_id');
    }
    /**
     * Связь с городами (один регион - много городов)
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cities()
    {
        return $this->hasMany(AdCity::class, 'region_id');
    }
}