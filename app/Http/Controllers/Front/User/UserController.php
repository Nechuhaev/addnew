<?php

namespace App\Http\Controllers\Front\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function ads()
    {
        return view('front.user.profile.ads');
    }
    public function edit()
    {
        return view('front.user.profile.edit');
    }
}
