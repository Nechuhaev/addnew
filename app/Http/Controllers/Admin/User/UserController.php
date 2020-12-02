<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function showUserInformation(Request $request) {
        $user = User::where('id', (int)$request->id)->first();
        return view('admin.users.user', ['user' => $user]);
    }

    public function showUsersList(Request $request) {
        if ($request->has('order')) {
            $order = $request->get('order');
        } else {
            $order = 'created_at';
        }
        $direction = $request->get('direction') ?? 'desc';

        // Сортировка по username
        if ($order == 'username') {
            $users = User::withCount('ads')->orderBy('firstname', $direction)->orderBy('email', $direction)->paginate(15);
        } else {
            $users = User::withCount('ads')->orderBy($order, $direction)->paginate(15);
        }

        return view('admin.users.list', [
            'users' => $users,
            'order' => $order,
            'direction' => $direction,
            'action_search' => route('admin.users.search'),
        ]);
    }

    public function search(Request $request)
    {
        $s = $request->name;

        $users = User::where('email', 'like', '%' . $s . '%')
            ->orWhere('firstname', 'like', '%' . $s . '%')
            ->orWhere('lastname', 'like', '%' . $s . '%');


        if ($request->has('order')) {
            $order = $request->get('order');
        } else {
            $order = 'created_at';
        }
        $direction = $request->get('direction') ?? 'desc';

        // Сортировка по username
        if ($order == 'username') {
            $users = $users->orderBy('firstname', $direction)->orderBy('email', $direction)->paginate(15);
        } else {
            $users = $users->orderBy($order, $direction)->paginate(15);
        }

        return view('admin.users.list', [
            'users' => $users,
            'order' => $order,
            'direction' => $direction,
            's' => $s,
            'action_search' => route('admin.users.search'),
        ]);
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
            $user->telegram_url = $request->post('telegram_url');
            $user->instagram_url = $request->post('instagram_url');
            $user->info = $request->post('info');
            $user->is_admin = (int)$request->post('is_admin');
            $user->is_shop_owner = (int)$request->post('is_shop_owner');
        }


        if ($request->has('image')) {
            $user->image = $request->get('image');
        }

        if ($request->post('password')) {

            $validator = Validator::make($request->all(), ['password' => 'required|confirmed|min:6']);


            if (count($validator->errors())) {
                return redirect()->back()->withErrors($validator)->withInput();
            } else {
                $user->password = Hash::make($request->post('password'));
            }

        }
        $user->save();

        return redirect(route('admin.users'))->with('success', 'Данные пользователя обновлены!');
        //dd($request->post('firstname'));

    }



}
