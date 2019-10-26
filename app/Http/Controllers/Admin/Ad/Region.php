<?php

namespace App\Http\Controllers\Admin\Ad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Region extends Controller
{
    public function showForm() {
        return view('admin.ad.region');
    }
}
