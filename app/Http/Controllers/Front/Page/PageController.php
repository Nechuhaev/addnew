<?php

namespace App\Http\Controllers\Front\Page;

use App\Http\Controllers\Controller;
use App\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function page($slug) {
        $page = Page::where('slug', $slug)->first();

        if ($page) {

            $data['page'] = $page;

            return view('front.page.page')->with($data);
        }

    }

}
