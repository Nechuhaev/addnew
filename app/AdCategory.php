<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class AdCategory extends Model
{
    protected $fillable = [
        'parent_id',
        'image',
        'name',
        'name_uk',
        'slug',
        'content',
        'meta_title',
        'meta_description',
        'sort_order'
    ];

    /**
     * Мовний аксесор: якщо поточна локаль uk і є переклад name_uk —
     * повертаємо його, інакше — стару колонку name (яка лишається
     * фактичним джерелом для ru). ОДНА ця зміна робить всю навігацію,
     * хлібні крихти й сторінки категорій мовно-залежними, без потреби
     * чіпати інші файли, що звертаються до $category->name.
     */
    public function getNameAttribute($value)
    {
        if (app()->getLocale() === 'uk' && !empty($this->attributes['name_uk'] ?? null)) {
            return $this->attributes['name_uk'];
        }
        return $value;
    }

    /**
     * Получить родительскую категорию
     * @return mixed|static
     */
    public function getParentAttribute()
    {
        if ($this->parent_id) {
            return AdCategory::find($this->parent_id);
        }
    }
    /**
     * Полный путь категории
     * @return string
     */
    public function getPathAttribute()
    {
        $path = "";
        if ($this->parent_id) {
            $path .= $this->parent->name . ' > ';
        }
        $path .= $this->name;
        return $path;
    }
    /**
     * Слаг должен содержать английские символы
     * И быть уникальным
     * @param $value
     */
    public function setSlugAttribute($value)
    {
        if (!isset($value)) {
            // Похожите
            $slug = str_slug($this->attributes['name']);
            if (isset($this->attributes['id'])) {
                $all_slugs = AdCategory::select('slug')
                    ->where('slug', 'LIKE', $slug . '%')
                    ->where('id', '<>', $this->attributes['id'])
                    ->get();
            } else {
                $all_slugs = AdCategory::select('slug')
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
        $parent = $this->parent;
        if ($parent) {
            $url = route('sub_category.page', [
                'category' => $parent->slug,
                'subcategory' => $this->slug
            ]);
        } else {
            $url = route('category.page', ['category' => $this->slug]);
        }
        return $url;
    }
    public function getFilteredUrl($filter)
    {
        $parent = $this->parent;
        if ($parent) {
            $url = route('filtered_subcategory.page', [
                'filter' => $filter,
                'category' => $parent->slug,
                'subcategory' => $this->slug
            ]);
        } else {
            $url = route('filtered_category.page', [
                'filter' => $filter,
                'category' => $this->slug
            ]);
        }
        return $url;
    }
    /**
     * Связь категорий с таблицей объявлений
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ads()
    {
        return $this->hasMany(Ad::class, 'category_id');
    }
    public function children() {
        return $this->hasMany(AdCategory::class, 'parent_id');
    }
    public function parent() {
        return $this->belongsTo(AdCategory::class, 'parent_id');
    }
}