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
    protected $fillable = [
        'name', 'name_uk',
        'slug',
        'excerpt', 'excerpt_uk',
        'content', 'content_uk',
        'sort_order',
        'meta_title', 'meta_title_uk',
        'meta_description', 'meta_description_uk',
        'image', 'focus_keyword', 'tags', 'ai_generated'
    ];

    protected $casts = [
        'tags' => 'array',
        'ai_generated' => 'boolean',
    ];

    /**
     * Мовні аксесори — той самий підхід, що в AdCategory/AdCity/AdRegion/
     * AdCountry/AdTag/SeoField.
     */
    public function getNameAttribute($value)
    {
        if (app()->getLocale() === 'uk' && !empty($this->attributes['name_uk'] ?? null)) {
            return $this->attributes['name_uk'];
        }
        return $value;
    }

    public function getExcerptAttribute($value)
    {
        if (app()->getLocale() === 'uk' && !empty($this->attributes['excerpt_uk'] ?? null)) {
            return $this->attributes['excerpt_uk'];
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

    public function categories() {
        return $this->belongsToMany(ArticleCategory::class);
    }

    /**
     * Реальні перегляди статті (замість захардкоджених чисел, що були
     * в блозі раніше). Дедуплікація IP вже реалізована в ArticleView::record().
     */
    public function views()
    {
        return $this->hasMany(ArticleView::class);
    }

    public function getTotalViewsAttribute()
    {
        return $this->views()->where('event_type', 'view')->count();
    }

    public function getTodayViewsAttribute()
    {
        return $this->views()
            ->where('event_type', 'view')
            ->whereDate('created_at', now()->toDateString())
            ->count();
    }

    /**
     * Орієнтовний час читання — за кількістю слів у тексті статті
     * (стандартний підхід ~200 слів/хв), мінімум 1 хвилина.
     *
     * ВАЖЛИВО: str_word_count() не розпізнає кириличні літери як частину
     * слова за замовчуванням — завжди повертав ~0 для укр./рос. тексту,
     * тому час читання завжди був 1 хв. Рахуємо через regex за пробілами
     * замість цього — працює для будь-якої мови.
     */
    public function getReadTimeMinutesAttribute()
    {
        $text = strip_tags($this->content ?? '');
        preg_match_all('/\S+/u', $text, $matches);
        $wordCount = count($matches[0]);
        return max(1, (int) ceil($wordCount / 200));
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