<?php

namespace App\Http\Controllers\API;

use App\User as UserModel;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use \App\Http\Resources\User as UserResource;

class User extends Controller
{
    public function autocomplete($search = null) {
        if ($search) {
            $users = UserModel::where('firstname', 'like', "%$search%")
                ->orWhere('lastname', 'like', "%$search%")
                ->orWhere('email', 'like', "%$search%")
                ->orderBy('lastname', 'asc')
                ->orderBy('firstname', 'asc')
                ->orderBy('email', 'asc')
                ->take(10)
                ->get();
        } else {
            $users = UserModel::orderBy('lastname', 'asc')
                ->orderBy('firstname', 'asc')
                ->orderBy('email', 'asc')
                ->take(10)
                ->get();
        }

        if ($users) {
            return UserResource::collection($users);
        } else {
            return response()->json(['error' => 'Категорий не найдено']);
        }
    }
}
