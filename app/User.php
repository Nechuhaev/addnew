<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getFullnameAttribute() {
        return ucfirst($this->firstname) . ' ' . ucfirst($this->lastname);
    }

    /**
     * Get user's registration date
     *
     * @return false|string
     */
    public function getCreatedDateAttribute() {
        return date('d-m-Y', strtotime($this->created_at));
    }

    public function getAvatarTextAttribute() {
        $text = '';
        // Если пользователь заполнил имя и фамилию
        if ($this->firstname) {
            $text = trim($this->firstname)[0] . '.';
        }

        if ($this->lastname) {
            $text .= trim($this->lastname)[0] . '.';
        }
        if ($text == '') {
            // Если пользователь заполнил только username
            $text = trim($this->email)[0] . '.';
        }

        return $text;

    }

    public function getAvatarGradientAttribute() {
        $gradients = [
            'background: linear-gradient(#c1cfdc, #da89c1);',
            'background: linear-gradient(#9bc4ea, #ea86cc);',
            'background: linear-gradient(#9beabb, #ea86cc);',
            'background: linear-gradient(#9beabb, #ba86ea);',
            'background: linear-gradient(#add4bd, #e8e5ea);',

        ];

        return $gradients[array_rand($gradients)];
    }

}
