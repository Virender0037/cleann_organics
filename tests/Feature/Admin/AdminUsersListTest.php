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
class AdminUsersListTest extends TestCase
{
    use RefreshDatabase;

    public function test_administration_users_shows_the_real_authenticated_admin(): void
    {
        $admin = User::factory()->create([
            'name' => 'Real Admin Name',
            'email' => 'genuine.owner@cleannorganics.example',
            'role' => 'superadmin',
        ]);

        $response = $this->actingAs($admin)->get('/admin/administration/users');

        $response->assertOk();
        $response->assertSee('Real Admin Name');
        $response->assertSee('genuine.owner@cleannorganics.example');
        $response->assertDontSee('Admin User');
        $response->assertDontSee('admin@example.com');
    }

    public function test_administration_users_excludes_customers(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $customer = User::factory()->create(['name' => 'A Customer', 'role' => 'customer']);

        $response = $this->actingAs($admin)->get('/admin/administration/users');

        $response->assertOk();
        $response->assertDontSee('A Customer');
    }

    public function test_administration_users_search_works(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        User::factory()->create(['name' => 'Findable Admin', 'role' => 'superadmin']);
        User::factory()->create(['name' => 'Other Admin', 'role' => 'superadmin']);

        $response = $this->actingAs($admin)->get('/admin/administration/users?search=Findable');

        $response->assertOk();
        $response->assertSee('Findable Admin');
        $response->assertDontSee('Other Admin');
    }
}
