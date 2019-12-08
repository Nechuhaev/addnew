<?php

namespace App\Http\Controllers\Admin\Seo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Ad extends Controller
{
    public function form() {
        return view('admin.seo.ad');
    }
}
