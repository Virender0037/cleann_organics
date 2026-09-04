<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Uses literal relative paths (not the route() helper) for request URLs —
 * see AdminCsvXlsxImportTest for why: APP_URL points at a XAMPP
 * subdirectory, which route() bakes into generated URLs but the test HTTP
 * kernel expects app-root-relative paths.
 */
class AdminProfileTest extends TestCase
{
    use RefreshDatabase;

    private function superadmin(array $overrides = []): User
    {
        return User::factory()->create(array_merge(['role' => 'superadmin'], $overrides));
    }

    public function test_admin_can_view_own_profile(): void
    {
        $admin = $this->superadmin(['name' => 'View Me']);

        $response = $this->actingAs($admin)->get('/admin/profile');

        $response->assertOk();
        $response->assertSee('View Me');
    }

    public function test_admin_can_update_name_email_and_phone(): void
    {
        $admin = $this->superadmin();

        $response = $this->actingAs($admin)->put('/admin/profile', [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'phone' => '9998887777',
        ]);

        $response->assertRedirect();
        $admin->refresh();
        $this->assertSame('Updated Name', $admin->name);
        $this->assertSame('updated@example.com', $admin->email);
        $this->assertSame('9998887777', $admin->phone);
    }

    public function test_email_must_be_unique_across_other_users(): void
    {
        $this->superadmin(['email' => 'taken@example.com']);
        $admin = $this->superadmin(['email' => 'mine@example.com']);

        $response = $this->actingAs($admin)->put('/admin/profile', [
            'name' => $admin->name,
            'email' => 'taken@example.com',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertSame('mine@example.com', $admin->fresh()->email);
    }

    public function test_admin_can_keep_their_own_current_email(): void
    {
        $admin = $this->superadmin(['email' => 'mine@example.com']);

        $response = $this->actingAs($admin)->put('/admin/profile', [
            'name' => 'Same Email Update',
            'email' => 'mine@example.com',
        ]);

        $response->assertSessionDoesntHaveErrors('email');
        $this->assertSame('Same Email Update', $admin->fresh()->name);
    }

    public function test_password_can_be_changed_with_correct_current_password(): void
    {
        $admin = $this->superadmin(['password' => Hash::make('old-password-123')]);

        $response = $this->actingAs($admin)->put('/admin/profile/password', [
            'current_password' => 'old-password-123',
            'password' => 'new-password-456',
            'password_confirmation' => 'new-password-456',
        ]);

        $response->assertRedirect();
        $this->assertTrue(Hash::check('new-password-456', $admin->fresh()->password));
    }

    public function test_password_change_fails_with_wrong_current_password(): void
    {
        $admin = $this->superadmin(['password' => Hash::make('old-password-123')]);

        $response = $this->actingAs($admin)->put('/admin/profile/password', [
            'current_password' => 'totally-wrong',
            'password' => 'new-password-456',
            'password_confirmation' => 'new-password-456',
        ]);

        $response->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('old-password-123', $admin->fresh()->password));
    }

    public function test_password_change_requires_confirmation_match(): void
    {
        $admin = $this->superadmin(['password' => Hash::make('old-password-123')]);

        $response = $this->actingAs($admin)->put('/admin/profile/password', [
            'current_password' => 'old-password-123',
            'password' => 'new-password-456',
            'password_confirmation' => 'does-not-match',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertTrue(Hash::check('old-password-123', $admin->fresh()->password));
    }

    public function test_avatar_can_be_uploaded(): void
    {
        Storage::fake('public');
        $admin = $this->superadmin();

        $response = $this->actingAs($admin)->put('/admin/profile', [
            'name' => $admin->name,
            'email' => $admin->email,
            'avatar' => UploadedFile::fake()->image('avatar.jpg'),
        ]);

        $response->assertRedirect();
        $this->assertNotNull($admin->fresh()->avatar);
        Storage::disk('public')->assertExists($admin->fresh()->avatar);
    }

    public function test_avatar_can_be_deleted(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('avatars/existing.jpg', 'fake-content');
        $admin = $this->superadmin(['avatar' => 'avatars/existing.jpg']);

        $response = $this->actingAs($admin)->delete('/admin/profile/avatar');

        $response->assertRedirect();
        $this->assertNull($admin->fresh()->avatar);
        Storage::disk('public')->assertMissing('avatars/existing.jpg');
    }

    public function test_replacing_avatar_removes_the_old_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('avatars/old.jpg', 'old-content');
        $admin = $this->superadmin(['avatar' => 'avatars/old.jpg']);

        $this->actingAs($admin)->put('/admin/profile', [
            'name' => $admin->name,
            'email' => $admin->email,
            'avatar' => UploadedFile::fake()->image('new.jpg'),
        ]);

        Storage::disk('public')->assertMissing('avatars/old.jpg');
        $this->assertNotSame('avatars/old.jpg', $admin->fresh()->avatar);
    }

    public function test_invalid_avatar_type_is_rejected(): void
    {
        Storage::fake('public');
        $admin = $this->superadmin();

        $response = $this->actingAs($admin)->put('/admin/profile', [
            'name' => $admin->name,
            'email' => $admin->email,
            'avatar' => UploadedFile::fake()->create('not-an-image.pdf', 10, 'application/pdf'),
        ]);

        $response->assertSessionHasErrors('avatar');
    }

    public function test_role_cannot_be_self_elevated_via_profile_update(): void
    {
        $admin = $this->superadmin(['role' => 'superadmin']);

        // Even if a tampered request smuggles a role/status field in, the
        // Form Request never validates or exposes them, so update() can
        // never receive them regardless of what's posted.
        $response = $this->actingAs($admin)->put('/admin/profile', [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => 'customer',
            'status' => 'inactive',
        ]);

        $response->assertRedirect();
        $this->assertSame('superadmin', $admin->fresh()->role);
        $this->assertSame('active', $admin->fresh()->status);
    }

    public function test_admin_can_only_ever_modify_their_own_profile(): void
    {
        $adminA = $this->superadmin(['name' => 'Admin A', 'email' => 'a@example.com']);
        $adminB = $this->superadmin(['name' => 'Admin B', 'email' => 'b@example.com']);

        $this->actingAs($adminB)->put('/admin/profile', [
            'name' => 'Admin B Renamed',
            'email' => 'b@example.com',
        ]);

        $this->assertSame('Admin A', $adminA->fresh()->name);
        $this->assertSame('Admin B Renamed', $adminB->fresh()->name);
    }

    public function test_guest_cannot_access_admin_profile(): void
    {
        $this->get('/admin/profile')->assertRedirect('/admin/login');
    }
}
