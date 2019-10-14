<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTest extends TestCase
{
    /**
     * Login form showing
     *
     * @return void
     */
    public function testShowLoginPageForm()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertViewIs("front.user.login");
    }

    /**
     * Registration form showing
     *
     */
    public function testShowRegisterForm()
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertViewIs("front.user.register");
    }

    /**
     * Forgot password form showing
     */
    public function testShowForgotPasswordForm() {
        $response = $this->get('/password/reset');

        $response->assertStatus(200);
        $response->assertViewIs("front.user.forgot");
    }

}
