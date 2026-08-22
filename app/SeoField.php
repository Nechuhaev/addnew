<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class SeoField extends Model
{
    public $timestamps = false;

    /**
     * Той самий мовний аксесор-підхід, що в AdCategory/AdCity.
     * SeoField містить шаблони SEO-текстів (з підстановками на кшталт
     * ---filtered_name---), які підставляються в контролерах категорій/
     * міст/тегів тощо — перекладаючи тут, одразу перекладаємо ВСІ
     * сторінки, що використовують цей шаблон.
     */
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

    public function getDescriptionAttribute($value)
    {
        if (app()->getLocale() === 'uk' && !empty($this->attributes['description_uk'] ?? null)) {
            return $this->attributes['description_uk'];
        }
        return $value;
    }
}