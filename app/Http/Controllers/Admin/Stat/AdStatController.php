<?php

namespace App\Http\Controllers\Admin\Stat;

use App\Ad;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdStatController extends Controller
{
    public function index() {
        $data = [];

        //$data['ads_count'] = Ad::groupBy('')
          //  dd($data['ads_count']);

        return view('admin.stat.ads');
    }
}
