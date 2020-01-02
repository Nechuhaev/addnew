<?php

namespace App\Http\Controllers\Front\Page;

use App\Http\Controllers\Controller;
use App\Page;
use App\SeoField;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function page($slug) {
        $page = Page::where('slug', $slug)->first();

        if ($page) {

            $data['page'] = $page;

            return view('front.page.page')->with($data);
        }

        abort(404);
    }

    function contacts() {
        // SEO поля
        $seo_field = SeoField::where('index', 'contacts')->first();
        if ($seo_field) {
            $data['meta'] = [
                'meta_title' => $seo_field->meta_title,
                'meta_description' => $seo_field->meta_description,
                'description' => $seo_field->description
            ];
        } else {
            $data['meta'] = [
                'meta_title' => false,
                'meta_description' => false,
                'description' => false
            ];
        }
        return view('front.page.contacts')->with($data);
    }

}
