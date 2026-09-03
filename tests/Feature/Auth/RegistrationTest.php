<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        // The storefront's real registration page is /create-account (Shopery
        // view); the Breeze GET /register route is intentionally kept only so
        // route('register') resolves internally, and forwards here — see
        // routes/auth.php.
        $response = $this->get('/register');

        $response->assertRedirect(route('create-account'));
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        // RegisteredUserController redirects to the storefront's real customer
        // dashboard route, user-dashboard — see app/Http/Controllers/Auth/RegisteredUserController.php.
        $response->assertRedirect(route('user-dashboard', absolute: false));
    }
}
