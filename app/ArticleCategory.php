<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ArticleCategory extends Model
{
    protected $fillable = ['name', 'slug', 'content', 'sort_order'];

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
}
