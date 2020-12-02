<?php

namespace App\Http\Controllers\Front\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileTypeController extends Controller
{
    public function index() {
        return view('front.user.profile.type');
    }

    public function switchIsShopOwner(Request $request) {
        $request->validate([
            'is_shop_owner' => 'required|boolean'
        ]);

        Auth::user()->setShopOwner($request->get('is_shop_owner'));
        return redirect()->back()->with('success', 'Тип профиля изменен');
    }
}
