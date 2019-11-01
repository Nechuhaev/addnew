<?php

namespace App\Http\Controllers\Admin\Ad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Ad extends Controller
{
    public function show() {
        return view('admin.ad.ad');
    }
}
