<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Ad extends Model
{
    protected $fillable = [
        'category_id',
        'city_id',
        'user_id',
        'currency_id',
        'image',
        'images',
        'name',
        'slug',
        'content',
        'price',
        'telephone',
        'email',
        'meta_title',
        'meta_description',
        'status',
        'date_active'
    ];

    protected $dates = ['date_active'];

    /**
     * Дата начала показа объявления
     * @return false|string
     */
    public function getDateStartAttribute() {
        $date = Carbon::createFromFormat($this->getDateFormat(), $this->getOriginal('date_active'));
        return date('d.m.Y', strtotime($date));
    }

    /**
     * Дата окончания показа объявления
     * @return false|string
     */
    public function getDateEndAttribute() {
        $date = Carbon::createFromFormat($this->getDateFormat(), $this->getOriginal('date_active'));
        return date('d.m.Y', strtotime($date->addDays(30)));
    }

    /**
     * Дата добавления объявления
     * @return false|string
     */
    public function getDateActiveAttribute($value) {
        return date('Y-m-d', strtotime($value));
    }

    public function getImagesAttribute($value) {
        return explode(', ', $value);
    }

    public function setImagesAttribute(array $value) {

        //dd($value);
        $images = array_unique($value);

        foreach ($images as $key => $image) {
            if (!$image) unset($images[$key]);
        }

        //dd($images);

        $this->attributes['images'] = implode(', ', $images);
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
                $all_slugs = Ad::select('slug')
                    ->where('slug', 'LIKE', $slug . '%')
                    ->where('id', '<>', $this->attributes['id'])
                    ->get();
            } else {
                $all_slugs = Ad::select('slug')
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

    public function getUrlAttribute($slug) {
        return 'ads/' . $slug;
    }

    /**
     * Обратная связь к пользователю
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Связь с тегами
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tags()
    {
        return $this->belongsToMany('App\AdTag', 'ad_tag', 'ad_id', 'tag_id');
    }

    public function currency() {
        return $this->belongsTo(AdCurrency::class, 'currency_id');
    }

    /**
     * Связь с категорией
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(AdCategory::class, 'category_id') ?? null;
    }

    /**
     * Связь с городом
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function city()
    {
        return $this->belongsTo(AdCity::class, 'city_id');
    }

    public static function getLoopArray($data) : array
    {
        $ads = [];
        foreach ($data as $ad) {
            //$city_url_path = $ad->country_slug . '/' . $ad->region_slug . '/'. $ad->city_slug;

            if (Storage::disk('s3')->exists($ad->image)) {
                $image = Storage::disk('s3')->url($ad->image);
            } else {
                $image = 'http://placehold.it/300x300';
            }

            $ads[] = [
                'id' => $ad->id,
                'name' => $ad->name,
                'url' => route('ad.page', ['slug' => $ad->slug]),
                //'image' => Storage::get($ad->image),
                'image' => $image,
                'price' => AdCurrency::convert($ad->price),
                'content' => Str::words(strip_tags($ad->content), 20, "..."),
                'city' => $ad->city,
                'city_url' => route('country.page', ['country' => $ad->country_slug]),
                'country' => $ad->country,
                'country_url' => route('country.page', ['country' => $ad->country_slug, 'region' => $ad->region_slug, 'city' => $ad->city_slug])
            ];
        }

        return $ads;
    }

    public static function getAds($defaults = true)
    {
        return DB::table('ads')
            ->select(['ads.id',
                'ads.slug',
                'ads.name',
                'ads.image',
                'ads.content',
                'ads.price',
                'ads.currency_id',
                'ad_cities.name AS city',
                'ad_cities.slug AS city_slug',
                'ad_regions.slug AS region_slug',
                'ad_countries.name AS country',
                'ad_countries.slug AS country_slug'
            ])
            ->leftJoin('ad_categories', 'ad_categories.id', '=', 'ads.category_id')
            ->leftJoin('ad_cities', 'ad_cities.id', '=', 'ads.city_id')
            ->leftJoin('ad_regions', 'ad_cities.region_id', '=', 'ad_regions.id')
            ->leftJoin('ad_countries', 'ad_regions.country_id', '=', 'ad_countries.id')
            ->where('status', 1)
            ->orderBy('ads.created_at', 'desc');
    }
}
