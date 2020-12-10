<?php

namespace App\Http\Controllers\Front\User\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AllActionsController extends Controller
{
    public function index() {
        return view('front.user.profile.shop.unready');
    }
}
