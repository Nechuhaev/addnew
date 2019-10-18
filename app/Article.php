<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Str;
/**
 * App\Article
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $content
 * @property int $sort_order
 * @property string $image
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Article newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Article newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Article query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Article whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Article whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Article whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Article whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Article whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Article whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Article whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Article whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Article extends Model
{

    protected $fillable = ['name', 'slug', 'excerpt', 'content', 'sort_order', 'meta_title', 'meta_description', 'image'];

    public function categories() {
        return $this->belongsToMany(ArticleCategory::class);
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

    public function getExcerptAttribute() {
        $excerpt_length = 515;
        //return substr(strip_tags($this->content), $excerpt_length) . (strlen(strip_tags($this->content)) > $excerpt_length)? '...' : '';
        //$excerpt = mb_substr(strip_tags($this->content), 0, $excerpt_length) . (strlen(strip_tags($this->content)) > $excerpt_length) ? '...' : '';
        return Str::words(strip_tags($this->content), 90, " <a href=\"{$this->href}\" class=\"more-link\">Продолжить чтение...</a>");
    }

    public function getHrefAttribute() {
        return route('blog.article', ['slug' => $this->slug]);
    }
}
