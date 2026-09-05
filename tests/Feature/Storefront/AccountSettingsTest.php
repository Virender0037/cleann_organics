<?php

namespace Tests\Feature\Storefront;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase J consolidates profile + password + addresses onto /account-setting
 * (and /profile, the same page). The underlying update actions are still
 * Breeze's ProfileController / Auth\PasswordController — covered in more
 * depth by ProfileTest and PasswordUpdateTest; this file covers the
 * Phase-J-specific additions (phone, protected fields, page rendering).
 */
class AccountSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_account_settings(): void
    {
        $this->get('/account-setting')->assertRedirect(route('sign-in'));
    }

    public function test_account_settings_page_renders_with_profile_password_and_address_sections(): void
    {
        $user = User::factory()->create(['name' => 'Ravi Kumar']);

        $response = $this->actingAs($user)->get('/account-setting');

        $response->assertOk();
        $response->assertSee('Ravi Kumar', false);
        $response->assertSee('Update Password');
        $response->assertSee('My Addresses');
        $response->assertSee('noindex', false);
    }

    public function test_profile_page_and_account_setting_are_the_same_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/profile')->assertOk()->assertSee('My Addresses');
    }

    public function test_customer_can_update_name_email_and_phone(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => 'New Name',
            'email' => 'new@example.com',
            'phone' => '9876500000',
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect('/profile');

        $user->refresh();
        $this->assertSame('New Name', $user->name);
        $this->assertSame('new@example.com', $user->email);
        $this->assertSame('9876500000', $user->phone);
    }

    public function test_phone_over_twenty_characters_is_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/account-setting')->patch('/profile', [
            'name' => 'Name',
            'email' => $user->email,
            'phone' => str_repeat('9', 21),
        ]);

        $response->assertSessionHasErrors('phone');
    }

    public function test_role_and_status_cannot_be_changed_through_the_profile_form(): void
    {
        $user = User::factory()->create(['role' => 'customer', 'status' => 'active']);

        $this->actingAs($user)->patch('/profile', [
            'name' => 'Name',
            'email' => $user->email,
            'phone' => '123',
            'role' => 'superadmin',
            'status' => 'inactive',
            'email_verified_at' => null,
            'provider' => 'evil',
            'provider_id' => '999',
        ]);

        $user->refresh();
        $this->assertSame('customer', $user->role);
        $this->assertSame('active', $user->status);
        $this->assertNull($user->provider);
        $this->assertNull($user->provider_id);
    }

    public function test_changing_email_resets_verification_but_unchanged_email_does_not(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        // Unchanged email keeps verification.
        $this->actingAs($user)->patch('/profile', ['name' => 'X', 'email' => $user->email, 'phone' => null]);
        $this->assertNotNull($user->refresh()->email_verified_at);

        // Changed email clears it.
        $this->actingAs($user)->patch('/profile', ['name' => 'X', 'email' => 'changed@example.com', 'phone' => null]);
        $this->assertNull($user->refresh()->email_verified_at);
    }

    public function test_invalid_email_is_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/account-setting')->patch('/profile', [
            'name' => 'Name',
            'email' => 'not-an-email',
            'phone' => null,
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_password_update_uses_the_existing_secure_breeze_flow(): void
    {
        $user = User::factory()->create();

        // wrong current password → rejected
        $this->actingAs($user)->from('/account-setting')->put('/password', [
            'current_password' => 'wrong-password',
            'password' => 'a-new-strong-password',
            'password_confirmation' => 'a-new-strong-password',
        ])->assertSessionHasErrorsIn('updatePassword', 'current_password');

        // mismatched confirmation → rejected
        $this->actingAs($user)->from('/account-setting')->put('/password', [
            'current_password' => 'password',
            'password' => 'a-new-strong-password',
            'password_confirmation' => 'different',
        ])->assertSessionHasErrorsIn('updatePassword', 'password');

        // correct → updated
        $this->actingAs($user)->from('/account-setting')->put('/password', [
            'current_password' => 'password',
            'password' => 'a-new-strong-password',
            'password_confirmation' => 'a-new-strong-password',
        ])->assertSessionHasNoErrors();

        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('a-new-strong-password', $user->refresh()->password));
    }
}
