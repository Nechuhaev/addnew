<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    /**
     * Повернутись з акаунту магазину назад в акаунт адміна.
     * Доступно будь-якому залогіненому користувачу (бо під час
     * impersonation залогінений саме власник магазину, не адмін) —
     * але реально спрацює, тільки якщо в сесії є impersonator_id.
     */
    public function leave()
    {
        $originalAdminId = session('impersonator_id');

        if (!$originalAdminId) {
            return redirect('/');
        }

        session()->forget('impersonator_id');

        $admin = User::find($originalAdminId);
        if ($admin) {
            Auth::login($admin);
        } else {
            Auth::logout();
        }

        return redirect(route('admin.shops'))->with('success', 'Ви повернулись в адмін-акаунт.');
    }
}