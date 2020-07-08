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
        'code',
        'slug',
        'content',
        'is_product',
        'brand',
        'stock',
        'condition',
        'url',
        'is_product',
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

    public function getDateCreatedAttribute() {
        $date = Carbon::createFromFormat($this->getDateFormat(), $this->getOriginal('created_at'));
        return date('d.m.Y', strtotime($date));
    }

    /**
     * Дополнительные изображения загружаем как массив
     * @param $value
     * @return array
     */
    public function getImagesAttribute($value) {
        return explode(', ', $this->attributes['images']);

    }

    /**
     * Дополнительны изображения сохраняем как строку
     * С разделителем ,
     * @param array $value
     */
    public function setImagesAttribute(array $value) {

        $images = array_unique($value);

        foreach ($images as $key => $image) {
            if (!$image) unset($images[$key]);
        }

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

    /**
     * Стоимость в формате цена + символ валюты
     * @return string
     */
    public function getFormattedPriceAttribute() {
        $_currency = AdCurrency::whereId((int)$this->attributes['currency_id'])->first();
        return $this->attributes['price'] . ' ' . $_currency['symbol'];
    }

    /**
     * Ссылка на страницу объявления
     * @return string
     */
    public function getUrlAttribute() {
        return '/ads/' . $this->attributes['slug'];
    }

    public function getFullUrlAttribute() {
        return env('APP_URL') . $this->url;
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
     * Обратная связь с валютами
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency() {
        return $this->belongsTo(AdCurrency::class, 'currency_id');
    }

    /**
     * Связь с тегами
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tags()
    {
        return $this->belongsToMany('App\AdTag', 'ad_tag', 'ad_id', 'tag_id');
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

            if (!$ad->image) {
                $image = asset('assets/front/img/placeholder.png');
            } else {
                $image = $ad->image;
            }
//            if (is_file($ad->image)) {
//                $image = $ad->image;
//            } else {
//                $image = 'http://placehold.it/300x300';
//            }
            //$image = $ad->image;


            $ads[] = [
                'id' => $ad->id,
                'name' => $ad->name,
                'date_active' => date ("Y-m-d H:i", strtotime($ad->date_active)),
                'url' => route('ad.page', ['slug' => $ad->slug]),
                'image' => $image,
                'price' => AdCurrency::convert($ad->price),
                'content' => Str::words(strip_tags($ad->content), 20, "..."),
                'city' => $ad->city,
                'city_url' => route('city.page', ['country' => $ad->country_slug, 'region' => $ad->region_slug, 'city' => $ad->city_slug]),
                'country' => $ad->country,
                'country_url' => route('country.page', ['country' => $ad->country_slug])
            ];
        }

        return $ads;
    }

    public static function getAds($defaults = true)
    {
        return DB::table('ads')
            ->select(['ads.id',
                'ads.slug',
                'ads.date_active',
                'ads.name',
                'ads.image',
                'ads.content',
                'ads.price',
                'ads.currency_id',
                'ad_cities.id AS city_id',
                'ad_cities.name AS city',
                'ad_cities.slug AS city_slug',
                'ad_regions.id AS region_id',
                'ad_regions.slug AS region_slug',
                'ad_countries.id AS country_id',
                'ad_countries.name AS country',
                'ad_countries.slug AS country_slug'
            ])
            ->leftJoin('ad_categories', 'ad_categories.id', '=', 'ads.category_id')
            ->leftJoin('ad_cities', 'ad_cities.id', '=', 'ads.city_id')
            ->leftJoin('ad_regions', 'ad_cities.region_id', '=', 'ad_regions.id')
            ->leftJoin('ad_countries', 'ad_regions.country_id', '=', 'ad_countries.id')
            ->whereIn('status', [1, 2])
            ->orderBy('ads.date_active', 'desc');
    }

    public function getStatusAttribute() {
        if ($this->attributes['status'] == 0) {
            $status = 'suspend';
        }

        if ($this->attributes['status'] == 1) {
            $status = 'active';
        }

        if ($this->attributes['status'] == 2) {
            $status = 'archive';
        }

        return $status;
    }

}
