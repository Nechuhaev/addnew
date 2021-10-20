<?php

namespace App\Http\Controllers\Front\User\Auth;

use App\Http\Controllers\Controller;
use App\Mail\UserPasswordDetails;
use App\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;

class BusinessRegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = '/profile';

    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showRegistrationForm() {
        return view('front.user.business-register');
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        // User password
        $user_password = $data['password'];

        // add to maichimp list
        $email = $data['email'];

        $apiKey = '94492d7246f58de6bdc22950014e9744-us19';
        $listId = 'b435fcadb5';

        $memberId = md5(strtolower($email));
        $dataCenter = substr($apiKey,strpos($apiKey,'-')+1);
        $url = 'https://' . $dataCenter . '.api.mailchimp.com/3.0/lists/' . $listId . '/members/' . $memberId;

        $json = json_encode([
            'email_address' => $email,
            'status'        => 'subscribed',
        ]);

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_USERPWD, 'user:' . $apiKey);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Notify user
        Mail::to($data['email'])->send(new UserPasswordDetails($data['email'], $user_password));

        // Register user
        return User::create([
            'email' => $data['email'],
            'password' => Hash::make($user_password),
            'is_shop_owner' => 1,
            'firstname' => $data['shop_name'] ?? '',
            'site_url' => $data['shop_url'] ?? '',
        ]);

    }


    /**
     * Registration handle
     *
     * @param Request $request
     * @return $this
     */

    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        event(new Registered($user = $this->create($request->all())));

        $this->guard()->login($user);

        return $this->registered($request, $user)
            ?: redirect($this->redirectPath())
                ->with('success', __('user/register.text_thank_you', ['email' => $request->get('email')]));
    }

    protected function validator(array $data) {
        $errors = [
            'email.required' => __('errors.email.required'),
            'email.string' => __('errors.email.required'),
            'email.email' => __('errors.email.required'),
            'email.max' => __('errors.email.max'),
            'email.unique' => __('errors.email.unique'),
            'password.required' => "Пароль не заполнен",
            'password.min' => "Минимальное количество символов в пароле: :min",
            'password.confirmed' => "Пароли не совпадают",
            'shop_url.url' => "Введите верную ссылку на Ваш интернет-магазин. Пример: https://addnew.biz"
        ];

        return Validator::make($data, [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'min:6', 'confirmed'],
            'shop_url' => ['nullable', 'url']
        ], $errors);
    }
}
