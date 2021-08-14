<?php

namespace App;

use DateTime;
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
                $all_slugs = Article::select('slug')
                    ->where('slug', 'LIKE', $slug . '%')
                    ->where('id', '<>', $this->attributes['id'])
                    ->get();
            } else {
                $all_slugs = Article::select('slug')
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

    public function getReadMoreAttribute() {
        $excerpt_length = 515;

        return Str::words(strip_tags($this->excerpt), 90, " <a href=\"{$this->href}\" class=\"more-link\">Продолжить чтение...</a>");
    }

    public function getCreatedAtAttribute($value) {
        $date = new DateTime($value);
        //return $this->created_at->format('d.m.Y H:m');
        return $date->format('d.m.Y H:m');
    }

    public function getUrlAttribute() {
        return route('blog.article', ['slug' => $this->slug]);
    }
}
