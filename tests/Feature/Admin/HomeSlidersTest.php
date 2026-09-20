<?php

namespace Tests\Feature\Admin;

use App\Models\HomeBanner;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\HomeBenefitSeeder;
use Database\Seeders\HomeHeroSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Hero slides + Benefits strip: one table (home_banners, split by section),
 * fully admin-managed, rendered on the homepage active-only in sort order.
 */
class HomeSlidersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'superadmin']);
    }

    private function hero(array $overrides = []): HomeBanner
    {
        return HomeBanner::create(array_merge([
            'section' => 'hero', 'title' => 'Slide', 'image' => 'home-banners/x.jpg', 'link_type' => 'url', 'link_url' => '/shop',
            'sort_order' => 0, 'status' => 'active',
        ], $overrides));
    }

    private function benefit(array $overrides = []): HomeBanner
    {
        return HomeBanner::create(array_merge([
            'section' => 'benefit', 'title' => 'Benefit', 'icon' => 'leaf', 'link_type' => 'none', 'sort_order' => 0, 'status' => 'active',
        ], $overrides));
    }

    // ------------------------------------------------------------------
    // Admin access
    // ------------------------------------------------------------------

    public function test_only_admins_can_reach_slider_management(): void
    {
        $this->get('/admin/cms/banners')->assertRedirect('/admin/login');
        $this->actingAs(User::factory()->create(['role' => 'customer']))->get('/admin/cms/banners')->assertRedirect('/admin/login');
    }

    public function test_list_shows_only_the_requested_section_in_ascending_sort_order(): void
    {
        $this->hero(['title' => 'Second Hero', 'sort_order' => 20]);
        $this->hero(['title' => 'First Hero', 'sort_order' => 5]);
        $this->benefit(['title' => 'A Benefit Card']);

        $response = $this->actingAs($this->admin())->get('/admin/cms/banners?section=hero');

        $response->assertOk()->assertSeeInOrder(['First Hero', 'Second Hero'])->assertDontSee('A Benefit Card');
        $this->actingAs($this->admin())->get('/admin/cms/banners?section=benefit')->assertOk()->assertSee('A Benefit Card')->assertDontSee('First Hero');
    }

    // ------------------------------------------------------------------
    // Create / validation / uploads
    // ------------------------------------------------------------------

    public function test_admin_creates_a_hero_slide_with_uploaded_images_stored_under_generated_names(): void
    {
        $this->actingAs($this->admin())->post('/admin/cms/banners', [
            'section' => 'hero', 'title' => 'Big Sale', 'subtitle' => 'Now on', 'button_text' => 'Shop Now',
            'image' => UploadedFile::fake()->image('my photo.jpg', 1920, 720),
            'mobile_image' => UploadedFile::fake()->image('mobile.webp', 960, 960),
            'alt_text' => 'A descriptive alt', 'link_type' => 'url', 'link_url' => '/shop', 'opens_new_tab' => '1',
            'sort_order' => 3, 'status' => 'active',
        ])->assertRedirect();

        $banner = HomeBanner::firstOrFail();
        $this->assertSame('hero', $banner->section);
        $this->assertTrue($banner->opens_new_tab);
        $this->assertSame('A descriptive alt', $banner->alt_text);
        $this->assertStringStartsWith('home-banners/', $banner->image);
        $this->assertStringNotContainsString('my photo', $banner->image, 'Client filename must not be trusted.');
        Storage::disk('public')->assertExists($banner->image);
        Storage::disk('public')->assertExists($banner->mobile_image);
    }

    public function test_hero_slide_requires_an_image(): void
    {
        $this->actingAs($this->admin())->post('/admin/cms/banners', [
            'section' => 'hero', 'link_type' => 'url', 'link_url' => '/shop', 'status' => 'active',
        ])->assertSessionHasErrors('image');
    }

    public function test_non_image_and_executable_uploads_are_rejected(): void
    {
        $base = ['section' => 'hero', 'link_type' => 'url', 'link_url' => '/shop', 'status' => 'active'];

        $this->actingAs($this->admin())->post('/admin/cms/banners', $base + ['image' => UploadedFile::fake()->create('shell.php', 10, 'application/x-php')])->assertSessionHasErrors('image');
        $this->actingAs($this->admin())->post('/admin/cms/banners', $base + ['image' => UploadedFile::fake()->create('evil.jpg', 10, 'text/plain')])->assertSessionHasErrors('image');
        $this->assertSame(0, HomeBanner::count());
    }

    public function test_benefit_needs_a_title_and_an_icon_or_an_image_and_can_be_icon_only(): void
    {
        $admin = $this->admin();
        $base = ['section' => 'benefit', 'link_type' => 'none', 'status' => 'active'];

        $this->actingAs($admin)->post('/admin/cms/banners', $base + ['title' => 'No visual'])->assertSessionHasErrors('icon');
        $this->actingAs($admin)->post('/admin/cms/banners', $base + ['icon' => 'truck'])->assertSessionHasErrors('title');
        $this->actingAs($admin)->post('/admin/cms/banners', $base + ['title' => 'Fast', 'icon' => 'truck'])->assertSessionHasNoErrors();
        $this->assertSame('truck', HomeBanner::firstOrFail()->icon);
    }

    public function test_unknown_icon_and_javascript_links_are_rejected(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/cms/banners', ['section' => 'benefit', 'title' => 'X', 'icon' => '<script>', 'link_type' => 'none', 'status' => 'active'])->assertSessionHasErrors('icon');
        $this->actingAs($admin)->post('/admin/cms/banners', ['section' => 'benefit', 'title' => 'X', 'icon' => 'leaf', 'link_type' => 'url', 'link_url' => 'javascript:alert(1)', 'status' => 'active'])->assertSessionHasErrors('link_url');
    }

    // ------------------------------------------------------------------
    // Replace / remove / delete → file cleanup
    // ------------------------------------------------------------------

    public function test_replacing_an_image_deletes_the_old_file(): void
    {
        $admin = $this->admin();
        Storage::disk('public')->put('home-banners/old.jpg', 'x');
        $banner = $this->hero(['image' => 'home-banners/old.jpg']);

        $this->actingAs($admin)->put("/admin/cms/banners/{$banner->id}", [
            'section' => 'hero', 'link_type' => 'url', 'link_url' => '/shop', 'status' => 'active',
            'image' => UploadedFile::fake()->image('new.png', 1920, 720),
        ])->assertRedirect();

        Storage::disk('public')->assertMissing('home-banners/old.jpg');
        Storage::disk('public')->assertExists($banner->fresh()->image);
    }

    public function test_remove_checkbox_clears_a_benefit_image_and_deletes_the_file(): void
    {
        Storage::disk('public')->put('home-banners/badge.png', 'x');
        $banner = $this->benefit(['image' => 'home-banners/badge.png']);

        $this->actingAs($this->admin())->put("/admin/cms/banners/{$banner->id}", [
            'section' => 'benefit', 'title' => 'Benefit', 'icon' => 'leaf', 'link_type' => 'none', 'status' => 'active', 'remove_image' => '1',
        ])->assertRedirect();

        Storage::disk('public')->assertMissing('home-banners/badge.png');
        $this->assertNull($banner->fresh()->image);
    }

    public function test_a_hero_slide_cannot_lose_its_only_image(): void
    {
        Storage::disk('public')->put('home-banners/keep.jpg', 'x');
        $banner = $this->hero(['image' => 'home-banners/keep.jpg']);

        $this->actingAs($this->admin())->put("/admin/cms/banners/{$banner->id}", [
            'section' => 'hero', 'link_type' => 'url', 'link_url' => '/shop', 'status' => 'active', 'remove_image' => '1',
        ])->assertSessionHasErrors('image');

        Storage::disk('public')->assertExists('home-banners/keep.jpg');
    }

    public function test_deleting_an_item_removes_its_files(): void
    {
        Storage::disk('public')->put('home-banners/d.jpg', 'x');
        Storage::disk('public')->put('home-banners/m.jpg', 'x');
        $banner = $this->hero(['image' => 'home-banners/d.jpg', 'mobile_image' => 'home-banners/m.jpg']);

        $this->actingAs($this->admin())->delete("/admin/cms/banners/{$banner->id}")->assertRedirect();

        $this->assertDatabaseCount('home_banners', 0);
        Storage::disk('public')->assertMissing('home-banners/d.jpg');
        Storage::disk('public')->assertMissing('home-banners/m.jpg');
    }

    public function test_admin_can_toggle_status(): void
    {
        $banner = $this->benefit();

        $this->actingAs($this->admin())->patch("/admin/cms/banners/{$banner->id}/toggle")->assertRedirect();
        $this->assertSame('inactive', $banner->fresh()->status);

        $this->actingAs($this->admin())->patch("/admin/cms/banners/{$banner->id}/toggle");
        $this->assertSame('active', $banner->fresh()->status);
    }

    public function test_create_and_edit_forms_render_for_both_sections(): void
    {
        $admin = $this->admin();
        $hero = $this->hero(['text_position' => 'right']);
        $benefit = $this->benefit();

        $this->actingAs($admin)->get('/admin/cms/banners/create?section=hero')->assertOk()->assertSee('Text card side')->assertSee('proportions');
        $this->actingAs($admin)->get('/admin/cms/banners/create?section=benefit')->assertOk()->assertDontSee('Text card side')->assertSee('Icon');
        $this->actingAs($admin)->get("/admin/cms/banners/{$hero->id}/edit")->assertOk()->assertSee('value="right" selected', false);
        $this->actingAs($admin)->get("/admin/cms/banners/{$benefit->id}/edit")->assertOk();
    }

    // ------------------------------------------------------------------
    // Homepage rendering
    // ------------------------------------------------------------------

    public function test_homepage_shows_active_benefits_only_in_sort_order(): void
    {
        $this->benefit(['title' => 'Later Card', 'sort_order' => 20]);
        $this->benefit(['title' => 'Earlier Card', 'sort_order' => 10]);
        $this->benefit(['title' => 'Hidden Card', 'status' => 'inactive']);

        $this->get('/')->assertOk()->assertSeeInOrder(['Earlier Card', 'Later Card'])->assertDontSee('Hidden Card');
    }

    public function test_free_shipping_card_follows_the_live_threshold_setting(): void
    {
        $this->benefit(['title' => 'Free Shipping', 'subtitle' => 'Free Shipping on Orders Above ₹{free_shipping_threshold}']);

        $this->get('/')->assertOk()->assertSee('Free Shipping on Orders Above ₹399');

        Setting::setMany('storefront', ['free_shipping_threshold' => '499']);
        Setting::forget('storefront');

        $this->get('/')->assertOk()->assertSee('Free Shipping on Orders Above ₹499')->assertDontSee('Above ₹399');
    }

    public function test_empty_sections_render_nothing_instead_of_a_broken_slider(): void
    {
        $this->get('/')->assertOk()->assertDontSee('home-benefits', false)->assertDontSee('home-hero__slider', false);
    }

    public function test_hero_renders_alt_text_new_tab_and_a_single_slide_has_no_controls(): void
    {
        $this->hero(['title' => 'Only Slide', 'alt_text' => 'Basket of natural cleaners', 'opens_new_tab' => true]);

        $this->get('/')->assertOk()
            ->assertSee('alt="Basket of natural cleaners"', false)
            ->assertSee('target="_blank" rel="noopener noreferrer"', false)
            ->assertDontSee('home-hero__nav--next', false);
    }

    public function test_hero_with_several_slides_gets_arrows_and_dots(): void
    {
        $this->hero(['title' => 'One']);
        $this->hero(['title' => 'Two']);

        $this->get('/')->assertOk()->assertSee('home-hero__nav--next', false)->assertSee('home-hero__dots', false);
    }

    public function test_inactive_hero_slides_are_hidden(): void
    {
        $this->hero(['title' => 'Live Slide']);
        $this->hero(['title' => 'Off Slide', 'status' => 'inactive']);

        $this->get('/')->assertOk()->assertSee('Live Slide')->assertDontSee('Off Slide');
    }

    public function test_benefit_without_a_link_is_not_an_anchor_and_new_tab_links_are_safe(): void
    {
        $this->benefit(['title' => 'Plain Card', 'link_type' => 'none']);
        $this->benefit(['title' => 'Linked Card', 'link_type' => 'url', 'link_url' => '/contact-us', 'opens_new_tab' => true]);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('~<div class="home-benefits__card">\s*<span class="home-benefits__icon">.*?Plain Card~s', $html);
        $this->assertMatchesRegularExpression('~<a href="/contact-us"\s+class="home-benefits__card"\s+target="_blank"\s+rel="noopener noreferrer"~', $html);
    }

    public function test_missing_benefit_image_file_falls_back_to_the_icon(): void
    {
        $this->benefit(['title' => 'Broken Image Card', 'image' => 'home-banners/gone.png', 'icon' => 'shield']);

        $this->get('/')->assertOk()->assertSee('Broken Image Card')->assertDontSee('home-banners/gone.png');
    }

    public function test_old_hardcoded_shipping_strip_is_gone(): void
    {
        $this->get('/')->assertOk()->assertDontSee('shipping-container', false)->assertDontSee('cards-ship__item', false);
    }

    // ------------------------------------------------------------------
    // Hero frame / text card (uploaded 16:9 art was cropped by a fixed 8:3 frame)
    // ------------------------------------------------------------------

    public function test_hero_frame_follows_the_first_slide_image_ratio(): void
    {
        $path = UploadedFile::fake()->image('wide.png', 1120, 630)->store('home-banners', 'public');
        $this->hero(['title' => 'Wide Slide', 'image' => $path]);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('--hero-ratio: 1.7778;', $html);
        // No mobile image uploaded → the mobile frame uses the same (clamped) ratio, not a forced square.
        $this->assertStringContainsString('--hero-ratio-m: 1.7778;', $html);
    }

    public function test_hero_ratio_is_clamped_and_falls_back_when_the_file_is_missing(): void
    {
        $this->hero(['title' => 'Missing File', 'image' => 'home-banners/does-not-exist.png']);

        $this->get('/')->assertOk()->assertSee('--hero-ratio: 2.6667;', false);

        $tall = UploadedFile::fake()->image('tall.png', 400, 800)->store('home-banners', 'public');
        HomeBanner::query()->update(['image' => $tall]);
        \Illuminate\Support\Facades\Cache::flush();

        $this->get('/')->assertOk()->assertSee('--hero-ratio: 1.4;', false)->assertSee('--hero-ratio-m: 0.75;', false);
    }

    public function test_hero_text_card_side_and_cta_only_variants(): void
    {
        $this->hero(['title' => 'Right Side', 'subtitle' => 'Sub', 'text_position' => 'right']);
        $this->hero(['title' => null, 'subtitle' => null, 'button_text' => 'SHOP NOW', 'text_position' => 'left']);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('~home-hero__text home-hero__text--right\s+"~', $html);
        $this->assertMatchesRegularExpression('~home-hero__text home-hero__text--left home-hero__text--cta-only~', $html);
    }

    public function test_admin_can_set_text_position_and_invalid_values_are_rejected(): void
    {
        $admin = $this->admin();
        $base = ['section' => 'hero', 'link_type' => 'url', 'link_url' => '/shop', 'status' => 'active'];

        $this->actingAs($admin)->post('/admin/cms/banners', $base + ['image' => UploadedFile::fake()->image('a.jpg', 1600, 900), 'text_position' => 'right'])->assertRedirect();
        $this->assertSame('right', HomeBanner::firstOrFail()->text_position);

        $this->actingAs($admin)->post('/admin/cms/banners', $base + ['image' => UploadedFile::fake()->image('b.jpg', 1600, 900), 'text_position' => 'diagonal'])->assertSessionHasErrors('text_position');

        $this->actingAs($admin)->post('/admin/cms/banners', $base + ['image' => UploadedFile::fake()->image('c.jpg', 1600, 900)]);
        $this->assertSame('left', HomeBanner::orderByDesc('id')->first()->text_position, 'An omitted side defaults to left.');
    }

    public function test_hero_stylesheet_never_forces_a_fixed_banner_ratio_or_scales_text(): void
    {
        $css = file_get_contents(public_path('css/style.css'));

        $this->assertStringContainsString('.home-hero__media { display: block; width: 100%; aspect-ratio: var(--hero-ratio, 2.667);', $css);
        $this->assertStringNotContainsString('aspect-ratio: 8 / 3', $css);
        $this->assertDoesNotMatchRegularExpression('~\.home-hero__(title|subtitle|text)[^{]*\{[^}]*(scaleX|scale\(|letter-spacing|white-space:\s*nowrap)~', $css, 'Hero text must never be scaled or forced onto one line.');
    }

    // ------------------------------------------------------------------
    // Seeders
    // ------------------------------------------------------------------

    public function test_benefit_seeder_is_idempotent_and_never_resurrects_deleted_cards(): void
    {
        $this->seed(HomeBenefitSeeder::class);
        $this->seed(HomeBenefitSeeder::class);

        $this->assertSame(4, HomeBanner::where('section', 'benefit')->count());
        $this->assertSame(['Free Shipping', 'Customer Support 24/7', '100% Secure Payment', 'Money Back Guarantee'], HomeBanner::where('section', 'benefit')->ordered()->pluck('title')->all());
        $this->assertStringContainsString(HomeBanner::THRESHOLD_TOKEN, HomeBanner::where('title', 'Free Shipping')->value('subtitle'));

        HomeBanner::where('title', 'Money Back Guarantee')->delete();
        HomeBanner::where('title', 'Free Shipping')->update(['title' => 'Edited By Admin']);
        $this->seed(HomeBenefitSeeder::class);

        $this->assertSame(3, HomeBanner::where('section', 'benefit')->count());
        $this->assertDatabaseHas('home_banners', ['title' => 'Edited By Admin']);
    }

    public function test_hero_seeder_copies_optimised_images_once(): void
    {
        $this->seed(HomeHeroSeeder::class);
        $this->seed(HomeHeroSeeder::class);

        $slides = HomeBanner::where('section', 'hero')->ordered()->get();
        $this->assertCount(3, $slides);

        foreach ($slides as $slide) {
            Storage::disk('public')->assertExists($slide->image);
            Storage::disk('public')->assertExists($slide->mobile_image);
            $this->assertNotEmpty($slide->alt_text);
        }
    }
}
