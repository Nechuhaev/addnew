<?php

namespace App\Http\Controllers\Front\Brand;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index() {
        return view('front.brands.brands_list');
    }
}
