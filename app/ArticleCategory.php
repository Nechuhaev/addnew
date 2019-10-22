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

    public function setSortOrderAttribute($value) {
        if (!isset($value)) {
            $this->attributes['sort_order'] = 0;
        } else {
            $this->attributes['sort_order'] = $value;
        }
    }

    public function setSlugAttribute($value) {
        if (!isset($value)) {
            $this->attributes['slug'] = str2url($this->attributes['name']);
        } else {
            $this->attributes['slug'] = $value;
        }
    }

    public function getHrefAttribute() {
        return route('blog.category', ['slug' => $this->slug]);
    }
}
