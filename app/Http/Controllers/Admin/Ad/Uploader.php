<?php

namespace App\Http\Controllers\Admin\Ad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Uploader extends Controller
{
    public function index() {
        return view('admin.ad.uploader');
    }
}
