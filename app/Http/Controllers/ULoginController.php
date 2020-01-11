<?php

namespace App\Http\Controllers;

use App\Mail\UserPasswordDetails;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ULoginController extends Controller
{
    public function login(Request $request)
    {
        // Get information about user.
        $data = file_get_contents('http://ulogin.ru/token.php?token=' . $_POST['token'] . '&host=' . $_SERVER['HTTP_HOST']);
        $user = json_decode($data, TRUE);

        $network = $user['network'];

        // Find user in DB.
        $userData = User::where('email', $user['email'])->first();

        // Check exist user.
        if (isset($userData->id)) {

            $userData->updated_at = Carbon::now()->toDateTimeString();
            $userData->save();
            Auth::loginUsingId($userData->id, TRUE);

            return redirect(route('profile.ads'));
        }
        // Make registration new user.
        else {

            $custom_password = Str::random(8);

            // Notify user
            Mail::to($data['email'])->send(new UserPasswordDetails($data['email'], $custom_password));

            // Register user
            $newUser = User::create([
                'email' => $user['email'],
                'password' => Hash::make($custom_password),
            ]);

            // Make login user.
            Auth::loginUsingId($newUser->id, TRUE);
            return redirect(route('profile.ads'));
        }
    }
}
