<?php

namespace App\Http\Controllers\Admin\Seo;

use App\Http\Controllers\Controller;
use App\SeoField;
use Illuminate\Http\Request;

class AdCategory extends Controller
{
    public function form() {

        $seo_field = SeoField::where('index', 'ad-category')->first();

        if ($seo_field) {
            $default['meta_title'] = $seo_field->getOriginal('meta_title');
            $default['meta_description'] = $seo_field->getOriginal('meta_description');
            $default['description'] = $seo_field->getOriginal('description');
            $default['meta_title_uk'] = $seo_field->getOriginal('meta_title_uk');
            $default['meta_description_uk'] = $seo_field->getOriginal('meta_description_uk');
            $default['description_uk'] = $seo_field->getOriginal('description_uk');
        } else {
            $default['meta_title'] = old('meta_title');
            $default['meta_description'] = old('meta_description');
            $default['description'] = old('description');
            $default['meta_title_uk'] = old('meta_title_uk');
            $default['meta_description_uk'] = old('meta_description_uk');
            $default['description_uk'] = old('description_uk');
        }

        return view('admin.seo.ad-category')->with($default);
    }
}
