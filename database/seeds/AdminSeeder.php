<?php

use Illuminate\Database\Seeder;
use App\User;


class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = new User();

        $user->firstname = 'Anatolii';
        $user->lastname = 'Koziura';
        $user->email = 'anatolii.koziura@gmail.com';
        $user->is_admin = 1;
        $user->password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

        $user->save();
    }
}
