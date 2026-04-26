<?php

namespace App\Http\Controllers\Front\User;

use App\Ad;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function ads()
    {
        if (Auth::user()->is_shop_owner) {
            return redirect()->route('profile.shop.dashboard');
        }

        $data['ads'] = Auth::user()->ads()->orderBy('created_at')->paginate(15);
        return view('front.user.profile.ads')->with($data);
    }
    public function edit()
    {
        $user = Auth::user();
        return view('front.user.profile.edit')->with([
            'user' => $user,
            'action' => route('profile.update')
        ]);
    }
    public function password() {
        return view('front.user.profile.password')->with([
            'action' => route('profile.password.update')
        ]);
    }

    public function updateUser(Request $request) {
        $user = User::find(Auth::id());


        $errors = [
            'image' => 'Файл, который Вы пытаетесь загрузить является изображением',
            'mimes' => 'Форматы изображений, которые могут быть загружены: :values',
            'user_avatar.max' => 'Размер файла не может превышать :max килобайт',
            'firstname.max' => 'Имя не может содержать больше :max символов (мы не утвержаем, это ограничение нашей базы)',
            'lastname.max' => 'Максимальное количество символов для поля "фамилия" составляет: :max символов.',
            'telephone.max' => 'Максимальное количество символов для поля "телефон" составляет: :max символов.',
            'site_url.max' => 'Максимальное количество символов для поля "Сайт" составляет: :max символов.',
            'site_url.url' => 'Данные из поля "ссылка на сайт" не распознаны как ссылка.',
            'twitter_url.max' => 'Максимальное количество символов для поля "Twitter" составляет: :max символов.',
            'facebook_url.max' => 'Максимальное количество символов для поля "Facebook" составляет: :max символов.',
            'info.max' => 'Максимальное количество символов для поля "Обо мне" составляет: :max символов.',
        ];
        $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:1024',
            'firstname' => 'max:100',
            'lastname' => 'max:100',
            'telephone' => 'max:32',
            'site_url' => 'nullable|url|max:255',
            'twitter_url' => 'max:255',
            'facebook_url' => 'max:255',
            'info' => 'max:3000',
        ], $errors);
        // Remove previous user's thumbnail

        if (Auth::user()->image && File::exists(Auth::user()->image)) {
            File::delete(Auth::user()->image);
        }

        if ($request->image) {
            $image_name = 'avatar-' . Auth::id() . '.' . $request->image->getClientOriginalExtension();
            $request->image->move(public_path('images'), $image_name);
            $user->image = '/images/' . $image_name;
        }


        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->telephone = $request->telephone;
        $user->site_url = $request->site_url;
        $user->twitter_url = $request->twitter_url;
        $user->facebook_url = $request->facebook_url;
        $user->telegram_url = $request->telegram_url;
        $user->instagram_url = $request->instagram_url;
        $user->info = $request->info;


        $user->save();

        return redirect()->back()->with('success', 'Ваши данные обновлены');
    }

    public function updatePassword(Request $request) {

        $errors = [
            'required' => 'Введите пароль',
            'confirmed' => 'Пароли не совпадают',
            'min' => 'Пароль должен содержать минимум :min символов'
        ];

        $request->validate([
            'new_password' => 'required|confirmed|min:6'
        ], $errors);

        $user = User::find(Auth::id());
        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect()->back()->with('success', 'Пароль изменён');
    }
}
