<?php

namespace Tests\Feature\Admin;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Uses literal relative paths (not the route() helper) for request URLs —
 * see AdminCsvXlsxImportTest for why: APP_URL points at a XAMPP
 * subdirectory, which route() bakes into generated URLs but the test HTTP
 * kernel expects app-root-relative paths.
 */
class GeneralSettingsFrontendTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget('settings.general');
    }

    private function superadmin(): User
    {
        return User::factory()->create(['role' => 'superadmin']);
    }

    public function test_updating_general_settings_is_reflected_on_the_homepage(): void
    {
        Storage::fake('public');
        $admin = $this->superadmin();

        $response = $this->actingAs($admin)->put('/admin/settings/general', [
            'site_name' => 'Updated Brand Name',
            'timezone' => 'Asia/Kolkata',
            'currency' => 'INR',
            'language' => 'en',
            'company_phone' => '+91-1112223333',
            'company_address' => 'Updated Warehouse Address',
        ]);

        $response->assertRedirect();

        $home = $this->get('/');
        $home->assertOk();
        $home->assertSee('Updated Brand Name');
        $home->assertSee('+91-1112223333');
        $home->assertSee('Updated Warehouse Address');
    }

    public function test_logo_upload_is_reflected_on_the_homepage(): void
    {
        Storage::fake('public');
        $admin = $this->superadmin();

        $this->actingAs($admin)->put('/admin/settings/general', [
            'site_name' => 'Brand',
            'timezone' => 'Asia/Kolkata',
            'currency' => 'INR',
            'language' => 'en',
            'logo' => UploadedFile::fake()->image('logo.jpg'),
        ]);

        $logoPath = Setting::group('general')['logo'];
        Storage::disk('public')->assertExists($logoPath);

        $home = $this->get('/');
        $home->assertSee(Storage::url($logoPath), false);
    }

    public function test_frontend_falls_back_safely_when_settings_are_empty(): void
    {
        // No settings saved at all — must render without error and without
        // a broken/empty src, using the pre-existing hardcoded defaults.
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Cleann Organics');
    }

    public function test_settings_cache_is_invalidated_after_admin_update(): void
    {
        Storage::fake('public');
        $admin = $this->superadmin();

        // Warm the cache with the old value.
        Setting::cached('general');

        $this->actingAs($admin)->put('/admin/settings/general', [
            'site_name' => 'Freshly Cached Name',
            'timezone' => 'Asia/Kolkata',
            'currency' => 'INR',
            'language' => 'en',
        ]);

        $this->assertSame('Freshly Cached Name', Setting::cached('general')['site_name']);
    }
}
