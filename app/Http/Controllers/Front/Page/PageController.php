<?php

namespace App\Http\Controllers\Front\Page;

use App\Http\Controllers\Controller;
use App\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function page($slug) {
        $data['page'] = Page::where('slug', $slug)->first();

        return view('front.page.page')->with($data);
    }
}
