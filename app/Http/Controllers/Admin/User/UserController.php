<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function showUserInformation(Request $request) {
        $user = User::where('id', (int)$request->id)->first();
        return view('admin.users.user', ['user' => $user]);
    }

    public function showUsersList() {
        $users = User::orderBy('created_at', 'desc')->paginate(15);
        return view('admin.users.list', ['users' => $users]);
    }

    public function update(Request $request) {
        $user_id = $request->post('user_id');
        $user = User::find($user_id);

        if ($user) {
            $user->firstname = $request->post('firstname');
            $user->lastname = $request->post('lastname');
            $user->telephone = $request->post('telephone');
            $user->site_url = $request->post('site_url');
            $user->facebook_url = $request->post('facebook_url');
            $user->twitter_url = $request->post('twitter_url');
            $user->info = $request->post('info');
            $user->is_admin = (int)$request->post('is_admin');
        }

        if ($request->post('password')) {

            $validator = Validator::make($request->all(), ['password' => 'required|confirmed|min:6']);


            if (count($validator->errors())) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

        }
        $user->save();

        return redirect(route('admin.users'))->with('success', 'Данные пользователя обновлены!');
        //dd($request->post('firstname'));

    }



}
