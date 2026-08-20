<?php

namespace App\Http\Controllers\Front\User\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    protected const ALLOWED_PROVIDERS = ['google', 'facebook'];

    /**
     * Перекидає користувача на сторінку авторизації провайдера.
     */
    public function redirect(string $provider)
    {
        if (!in_array($provider, self::ALLOWED_PROVIDERS, true)) {
            abort(404);
        }

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Обробляє повернення від провайдера — знаходить/створює користувача
     * за email (той самий підхід, що вже використовується при звичайному
     * оформленні оголошення — App\Http\Controllers\Front\Ad\Ad::add()),
     * авторизує й перекидає в кабінет.
     */
    public function callback(string $provider)
    {
        if (!in_array($provider, self::ALLOWED_PROVIDERS, true)) {
            abort(404);
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Throwable $e) {
            return redirect(route('login'))->with('error', __('front.social_login_error'));
        }

        $email = $socialUser->getEmail();

        if (!$email) {
            return redirect(route('login'))->with('error', __('front.social_login_no_email'));
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            $user = User::create([
                'email' => $email,
                'firstname' => $socialUser->getName() ?: Str::before($email, '@'),
                'password' => Hash::make(Str::random(24)), // пароль не використовується для соц-входу
            ]);
        }

        Auth::login($user, true);

        return redirect()->intended(route('profile.ads'));
    }
}