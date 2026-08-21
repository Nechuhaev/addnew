<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class AdCity extends Model
{
    protected $fillable = [
        'region_id',
        'name',
        'name_uk',
        'slug',
        'content',
        'meta_title',
        'meta_description',
        'sort_order'
    ];

    /**
     * Мовний аксесор — той самий підхід, що в AdCategory.
     */
    public function getNameAttribute($value)
    {
        if (app()->getLocale() === 'uk' && !empty($this->attributes['name_uk'] ?? null)) {
            return $this->attributes['name_uk'];
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
        return $this->name . ', ' . $this->region->name . ', ' . $this->region->country->name;
    }
    /**
     * Ссылка на страницу города
     * @return string
     */
    public function getUrlAttribute() {
        return route('city.page', [
            'country' => $this->region->country->slug,
            'region' => $this->region->slug,
            'city' => $this->slug
        ]);
    }
    /**
     * Полный путь к городу с разделителем >
     * @return string
     */
    public function getPathAttribute() {
        $path = [
            $this->region->country->name,
            $this->region->name,
            $this->name
        ];
        return implode(' > ', $path);
    }
    /**
     * Обратная связь с областями
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function region()
    {
        return $this->belongsTo(AdRegion::class, 'region_id');
    }
    /**
     * Связь с объявлениями
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ads()
    {
        return $this->hasMany(Ad::class, 'city_id');
    }
}