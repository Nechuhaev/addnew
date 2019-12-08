<?php

namespace App\Http\Controllers\Admin\Seo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdCategory extends Controller
{
    public function form() {
        return view('admin.seo.ad-region');
    }
}
