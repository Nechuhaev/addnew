<?php

namespace App\Http\Controllers\Admin\Seo;

use App\Http\Controllers\Controller;
use App\SeoField;
use Illuminate\Http\Request;

class AdCountry extends Controller
{
    public function form() {

        $seo_field = SeoField::where('index', 'ad-country')->first();

        if ($seo_field) {
            $default['meta_title'] = $seo_field->meta_title;
            $default['meta_description'] = $seo_field->meta_description;
            $default['description'] = $seo_field->description;
        } else {
            $default['meta_title'] = old('meta_title');
            $default['meta_description'] = old('meta_description');
            $default['description'] = old('description');
        }

        return view('admin.seo.ad-country')->with($default);
    }
}
