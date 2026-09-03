<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Uses literal relative paths (not the route() helper) for request URLs —
 * see AdminCsvXlsxImportTest for why: APP_URL points at a XAMPP
 * subdirectory, which route() bakes into generated URLs but the test HTTP
 * kernel expects app-root-relative paths.
 */
class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    private function superadmin(array $overrides = []): User
    {
        return User::factory()->create(array_merge(['role' => 'superadmin'], $overrides));
    }

    private function customer(array $overrides = []): User
    {
        return User::factory()->create(array_merge(['role' => 'customer'], $overrides));
    }

    // ---------- 1-4: guest access ----------

    public function test_guest_visiting_admin_root_is_redirected_to_admin_login(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/admin/login');
    }

    public function test_guest_visiting_admin_dashboard_is_redirected_to_admin_login(): void
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect('/admin/login');
    }

    public function test_guest_visiting_several_admin_modules_is_redirected_to_admin_login(): void
    {
        foreach ([
            '/admin/catalog/categories',
            '/admin/catalog/products',
            '/admin/sales/coupons',
            '/admin/shipping/zones',
            '/admin/administration/users',
            '/admin/reports/sales',
            '/admin/settings/general',
        ] as $path) {
            $this->get($path)->assertRedirect('/admin/login');
        }
    }

    public function test_guest_can_view_admin_login_page(): void
    {
        $response = $this->get('/admin/login');

        $response->assertOk();
        $response->assertSee('Admin Sign In');
        $response->assertSee('name="email"', false);
        $response->assertSee('name="password"', false);
        // Explicitly must NOT contain any of the excluded auth features.
        $response->assertDontSee('Forgot', false);
        $response->assertDontSee('Reset Password', false);
        $response->assertDontSee('Change Password', false);
        $response->assertDontSee('Create Account', false);
        $response->assertDontSee('Register', false);
    }

    // ---------- 5-7: login attempts ----------

    public function test_invalid_login_returns_validation_error_and_preserves_email_not_password(): void
    {
        $response = $this->from('/admin/login')->post('/admin/login', [
            'email' => 'nobody@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect('/admin/login');
        $response->assertSessionHasErrors('email');
        $response->assertSessionHas('_old_input.email', 'nobody@example.com');
        $this->assertArrayNotHasKey('password', session('_old_input', []));
        $this->assertGuest();
    }

    public function test_customer_credentials_are_rejected_with_generic_message_and_not_logged_in(): void
    {
        $customer = $this->customer(['email' => 'customer@example.com']);

        $response = $this->from('/admin/login')->post('/admin/login', [
            'email' => $customer->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin/login');
        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString('do not have access', session('errors')->first('email'));
        $this->assertGuest();
    }

    public function test_valid_superadmin_login_authenticates_and_redirects_to_dashboard(): void
    {
        $admin = $this->superadmin(['email' => 'admin@example.com']);

        $response = $this->post('/admin/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($admin);
    }

    public function test_already_authenticated_superadmin_visiting_login_is_redirected_to_dashboard(): void
    {
        $admin = $this->superadmin();

        $response = $this->actingAs($admin)->get('/admin/login');

        $response->assertRedirect('/admin/dashboard');
    }

    // ---------- 8-9: authenticated superadmin access ----------

    public function test_authenticated_superadmin_can_view_dashboard(): void
    {
        $admin = $this->superadmin();

        $this->actingAs($admin)->get('/admin/dashboard')->assertOk();
    }

    public function test_authenticated_superadmin_can_access_several_admin_modules(): void
    {
        $admin = $this->superadmin();

        foreach ([
            '/admin/catalog/categories',
            '/admin/catalog/products',
            '/admin/sales/coupons',
            '/admin/shipping/zones',
        ] as $path) {
            $this->actingAs($admin)->get($path)->assertOk();
        }
    }

    public function test_authenticated_customer_cannot_access_admin_dashboard(): void
    {
        $customer = $this->customer();

        $response = $this->actingAs($customer)->get('/admin/dashboard');

        $response->assertRedirect('/admin/login');
        $response->assertSessionHas('error');
        // The customer's own (storefront) session must remain intact --
        // being denied admin access must not log them out entirely.
        $this->assertAuthenticatedAs($customer);
    }

    // ---------- 10-11: logout ----------

    public function test_admin_logout_is_post_with_csrf_and_redirects_to_admin_login(): void
    {
        $admin = $this->superadmin();

        $response = $this->actingAs($admin)->post('/admin/logout');

        $response->assertRedirect('/admin/login');
        $this->assertGuest();
    }

    public function test_admin_dashboard_inaccessible_after_logout(): void
    {
        $admin = $this->superadmin();

        $this->actingAs($admin)->post('/admin/logout');

        $this->get('/admin/dashboard')->assertRedirect('/admin/login');
    }

    public function test_admin_logout_rejects_get_request(): void
    {
        $admin = $this->superadmin();

        // No GET logout route exists -- Laravel correctly reports 405
        // (the URI is only registered for POST), not a working GET route.
        $response = $this->actingAs($admin)->get('/admin/logout');

        $response->assertStatus(405);
    }

    // ---------- 12-14: storefront regression ----------

    public function test_customer_storefront_login_still_works(): void
    {
        $customer = $this->customer(['email' => 'shopper@example.com']);

        $response = $this->post('/login', [
            'email' => $customer->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/user-dashboard');
        $this->assertAuthenticatedAs($customer);
    }

    public function test_customer_dashboard_still_accessible(): void
    {
        $customer = $this->customer();

        $this->actingAs($customer)->get('/user-dashboard')->assertOk();
    }

    public function test_storefront_logout_still_works(): void
    {
        $customer = $this->customer();

        $response = $this->actingAs($customer)->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }
}
