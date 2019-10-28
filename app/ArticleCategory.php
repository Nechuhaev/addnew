<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\ArticleCategory
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $content
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ArticleCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ArticleCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ArticleCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ArticleCategory whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ArticleCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ArticleCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ArticleCategory whereMetaDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ArticleCategory whereMetaTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ArticleCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ArticleCategory whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ArticleCategory whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ArticleCategory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ArticleCategory extends Model
{
    protected $fillable = ['name', 'slug', 'content', 'sort_order'];

    public function articles() {
        return $this->belongsToMany(Article::class);
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

    public function getHrefAttribute() {
        return route('blog.category', ['slug' => $this->slug]);
    }
}
