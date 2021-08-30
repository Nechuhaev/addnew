<?php

namespace App\Http\Controllers\Front\User\Auth;

use App\Http\Controllers\Controller;
use App\Mail\UserPasswordDetails;
use App\User;
use App\BlockedEmail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/login';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Override view registration form view
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showRegistrationForm() {
        return view('front.user.register');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {

        $errors = [
            'email.required' => __('errors.email.required'),
            'email.string' => __('errors.email.required'),
            'email.email' => __('errors.email.required'),
            'email.max' => __('errors.email.max'),
            'email.unique' => __('errors.email.unique'),
        ];

        Validator::extend('not_from_block_list',function($attribute, $value, $parameters){
            $emails = BlockedEmail::all();
            $mailbox = stristr($value, '@');
            foreach ($emails as $email) {
                if ('@'.$email->mailbox === $mailbox) {
                    return false;
                }
            }
            return true;
        }, "Почтовые адреса этого сервиса не поддерживается нашим сайтом. Пожалуйста, воспользуйтесь другим почтовым сервисом.");

        return Validator::make($data, [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users', 'not_from_block_list'],
        ], $errors);
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
        $custom_password = Str::random(8);

        $user = User::create([
            'email' => $data['email'],
            'password' => Hash::make($custom_password),
        ]);

        Mail::to($data['email'])->send(new UserPasswordDetails($data['email'], $custom_password));

        // add to maichimp list
        $email = $data['email'];

        $apiKey = '94492d7246f58de6bdc22950014e9744-us19';
        $listId = 'b435fcadb5';

        $memberId = md5(strtolower($email));
        $dataCenter = substr($apiKey,strpos($apiKey,'-')+1);
        $url = 'https://' . $dataCenter . '.api.mailchimp.com/3.0/lists/' . $listId . '/members/' . $memberId;
        //dd($url);

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


        // Register user
        return $user;



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

        //$this->guard()->login($user);

        return $this->registered($request, $user)
            ?: redirect($this->redirectPath())
                ->with('success', __('user/register.text_thank_you', ['email' => $request->get('email')]));
    }

}
