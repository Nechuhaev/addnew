<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\ArticleCategory
 *
 * @property int $id
 * @property string $name
 * @property string|null $name_uk
 * @property string $slug
 * @property string|null $content
 * @property string|null $content_uk
 * @property string|null $meta_title
 * @property string|null $meta_title_uk
 * @property string|null $meta_description
 * @property string|null $meta_description_uk
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @mixin \Eloquent
 */
class ArticleCategory extends Model
{
    protected $fillable = [
        'name', 'name_uk',
        'slug',
        'content', 'content_uk',
        'meta_title', 'meta_title_uk',
        'meta_description', 'meta_description_uk',
        'sort_order',
    ];

    public function articles() {
        return $this->belongsToMany(Article::class);
    }

    /**
     * Мовні аксесори — той самий підхід, що в AdCategory/AdTag/AdCity.
     */
    public function getNameAttribute($value)
    {
        if (app()->getLocale() === 'uk' && !empty($this->attributes['name_uk'] ?? null)) {
            return $this->attributes['name_uk'];
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
                $all_slugs = ArticleCategory::select('slug')
                    ->where('slug', 'LIKE', $slug . '%')
                    ->where('id', '<>', $this->attributes['id'])
                    ->get();
            } else {
                $all_slugs = ArticleCategory::select('slug')
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
        return route('blog.category', ['slug' => $this->slug]);
    }
}
