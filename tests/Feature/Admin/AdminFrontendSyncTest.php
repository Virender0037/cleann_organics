<?php

namespace Tests\Feature\Admin;

use App\Models\Faq;
use App\Models\HomeBanner;
use App\Models\Order;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\TeamMember;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Admin edit → database → frontend query → frontend display, end to end, for the CMS modules
 * whose changes must show on the public site. (Reported once: "Our Awesome Team" admin edits
 * did not reflect on the frontend because the About page was hardcoded template markup.)
 */
class AdminFrontendSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Page::create(['title' => 'About Us', 'slug' => 'about-us', 'content' => 'x', 'status' => 'active']);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'superadmin']);
    }

    private function teamPayload(array $overrides = []): array
    {
        return array_merge(['name' => 'Asha Verma', 'designation' => 'Founder', 'status' => 'active', 'sort_order' => 1], $overrides);
    }

    // ------------------------------------------------------------------
    // Team ("Meet Our Team")
    // ------------------------------------------------------------------

    public function test_team_member_full_lifecycle_is_reflected_on_the_about_page(): void
    {
        $admin = $this->admin();

        // create (with photo + a social link)
        $this->actingAs($admin)->post('/admin/cms/team-members', $this->teamPayload([
            'image' => UploadedFile::fake()->image('asha.jpg', 600, 600),
            'facebook_url' => 'https://facebook.com/asha',
        ]))->assertRedirect();
        $member = TeamMember::firstOrFail();
        Storage::disk('public')->assertExists($member->image);

        $about = $this->get('/about-us')->assertOk();
        $about->assertSee('Meet Our Team')->assertSee('Asha Verma')->assertSee('Founder')->assertSee(Storage::url($member->image), false)
            ->assertSee('https://facebook.com/asha', false);

        // edit name / designation / social links
        $this->actingAs($admin)->put("/admin/cms/team-members/{$member->id}", $this->teamPayload([
            'name' => 'Asha V. Sharma', 'designation' => 'Chief Executive', 'instagram_url' => 'https://instagram.com/asha', 'facebook_url' => '',
        ]))->assertRedirect();
        $this->get('/about-us')->assertOk()->assertSee('Asha V. Sharma')->assertSee('Chief Executive')->assertSee('https://instagram.com/asha', false)
            ->assertDontSee('>Founder<', false)->assertDontSee('https://facebook.com/asha', false);

        // replace the photo: new file shown, old file cleaned up
        $oldImage = $member->fresh()->image;
        $this->actingAs($admin)->put("/admin/cms/team-members/{$member->id}", $this->teamPayload(['name' => 'Asha V. Sharma', 'designation' => 'Chief Executive', 'image' => UploadedFile::fake()->image('new.png', 600, 600)]))->assertRedirect();
        $newImage = $member->fresh()->image;
        $this->assertNotSame($oldImage, $newImage);
        Storage::disk('public')->assertMissing($oldImage);
        Storage::disk('public')->assertExists($newImage);
        $this->get('/about-us')->assertSee(Storage::url($newImage), false)->assertDontSee(Storage::url($oldImage), false);

        // deactivate → hidden; reactivate → back
        $this->actingAs($admin)->put("/admin/cms/team-members/{$member->id}", $this->teamPayload(['name' => 'Asha V. Sharma', 'designation' => 'Chief Executive', 'status' => 'inactive']))->assertRedirect();
        $this->get('/about-us')->assertDontSee('Asha V. Sharma')->assertDontSee('Meet Our Team');
        $this->actingAs($admin)->put("/admin/cms/team-members/{$member->id}", $this->teamPayload(['name' => 'Asha V. Sharma', 'designation' => 'Chief Executive', 'status' => 'active']));
        $this->get('/about-us')->assertSee('Asha V. Sharma');

        // delete → gone, file removed
        $this->actingAs($admin)->delete("/admin/cms/team-members/{$member->id}")->assertRedirect();
        $this->get('/about-us')->assertDontSee('Asha V. Sharma')->assertDontSee('Meet Our Team');
        Storage::disk('public')->assertMissing($newImage);
    }

    public function test_team_sort_order_controls_the_frontend_order_and_no_template_member_overrides_it(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post('/admin/cms/team-members', $this->teamPayload(['name' => 'Second Person', 'sort_order' => 20]));
        $this->actingAs($admin)->post('/admin/cms/team-members', $this->teamPayload(['name' => 'First Person', 'sort_order' => 5]));

        $this->get('/about-us')->assertOk()->assertSeeInOrder(['First Person', 'Second Person'])
            ->assertDontSee('Cody Fisher')->assertDontSee('Jenny Wilson');
    }

    public function test_team_validation_and_upload_security(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/cms/team-members', ['status' => 'active'])->assertSessionHasErrors(['name', 'designation']);
        $this->actingAs($admin)->post('/admin/cms/team-members', $this->teamPayload(['facebook_url' => 'not a url']))->assertSessionHasErrors('facebook_url');
        $this->actingAs($admin)->post('/admin/cms/team-members', $this->teamPayload(['image' => UploadedFile::fake()->create('shell.php', 10, 'application/x-php')]))->assertSessionHasErrors('image');
        $this->actingAs($admin)->post('/admin/cms/team-members', $this->teamPayload(['image' => UploadedFile::fake()->image('big.jpg')->size(4096)]))->assertSessionHasErrors('image');
        $this->actingAs($admin)->post('/admin/cms/team-members', $this->teamPayload(['status' => 'weird']))->assertSessionHasErrors('status');
        $this->assertSame(0, TeamMember::count());
    }

    // ------------------------------------------------------------------
    // Hero slide + Benefits strip edits reach the homepage
    // ------------------------------------------------------------------

    public function test_editing_a_hero_slide_and_a_benefit_card_updates_the_homepage(): void
    {
        $admin = $this->admin();
        $hero = HomeBanner::create(['section' => 'hero', 'title' => 'Old Hero Title', 'image' => 'home-banners/a.jpg', 'link_type' => 'url', 'link_url' => '/shop', 'sort_order' => 1, 'status' => 'active']);
        $benefit = HomeBanner::create(['section' => 'benefit', 'title' => 'Old Benefit', 'icon' => 'leaf', 'link_type' => 'none', 'sort_order' => 1, 'status' => 'active']);

        $this->get('/')->assertSee('Old Hero Title')->assertSee('Old Benefit');

        $this->actingAs($admin)->put("/admin/cms/banners/{$hero->id}", ['section' => 'hero', 'title' => 'New Hero Title', 'subtitle' => 'Fresh subtitle', 'button_text' => 'Go', 'text_position' => 'right', 'link_type' => 'url', 'link_url' => '/shop', 'status' => 'active'])->assertRedirect();
        $this->actingAs($admin)->put("/admin/cms/banners/{$benefit->id}", ['section' => 'benefit', 'title' => 'New Benefit', 'icon' => 'truck', 'link_type' => 'none', 'status' => 'active'])->assertRedirect();

        $this->get('/')->assertOk()->assertSee('New Hero Title')->assertSee('Fresh subtitle')->assertSee('home-hero__text--right', false)->assertSee('New Benefit')
            ->assertDontSee('Old Hero Title')->assertDontSee('Old Benefit');
    }

    // ------------------------------------------------------------------
    // Testimonials + FAQs
    // ------------------------------------------------------------------

    public function test_testimonial_admin_changes_reach_the_frontend(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/cms/testimonials', ['name' => 'Priya Test', 'city' => 'Pune', 'rating' => 5, 'message' => 'Lovely products, fast delivery.', 'status' => 'active', 'sort_order' => 1])->assertRedirect();
        $t = Testimonial::firstOrFail();
        $this->get('/')->assertSee('Priya Test')->assertSee('Lovely products, fast delivery.');

        $this->actingAs($admin)->put("/admin/cms/testimonials/{$t->id}", ['name' => 'Priya Renamed', 'rating' => 4, 'message' => 'Updated review text.', 'status' => 'active'])->assertRedirect();
        $this->get('/')->assertSee('Priya Renamed')->assertSee('Updated review text.')->assertDontSee('Priya Test');

        $this->actingAs($admin)->put("/admin/cms/testimonials/{$t->id}", ['name' => 'Priya Renamed', 'rating' => 4, 'message' => 'Updated review text.', 'status' => 'inactive']);
        $this->get('/')->assertDontSee('Priya Renamed');

        $this->actingAs($admin)->post('/admin/cms/testimonials', ['name' => 'Bad', 'rating' => 9, 'message' => 'x', 'status' => 'active'])->assertSessionHasErrors('rating');
    }

    public function test_faq_admin_changes_reach_the_faq_page(): void
    {
        $admin = $this->admin();
        $topic = Faq::TOPICS[0];

        $this->actingAs($admin)->post('/admin/cms/faqs', ['question' => 'How fast is delivery?', 'answer' => 'Within 5 days.', 'topic' => $topic, 'status' => 'active', 'sort_order' => 1])->assertRedirect();
        $faq = Faq::firstOrFail();
        $this->get('/faq')->assertOk()->assertSee('How fast is delivery?')->assertSee('Within 5 days.');

        $this->actingAs($admin)->put("/admin/cms/faqs/{$faq->id}", ['question' => 'How fast is delivery?', 'answer' => 'Within 3 days now.', 'topic' => $topic, 'status' => 'active'])->assertRedirect();
        $this->get('/faq')->assertSee('Within 3 days now.')->assertDontSee('Within 5 days.');

        $this->actingAs($admin)->put("/admin/cms/faqs/{$faq->id}", ['question' => 'How fast is delivery?', 'answer' => 'x', 'topic' => $topic, 'status' => 'inactive']);
        $this->get('/faq')->assertDontSee('How fast is delivery?');

        $this->actingAs($admin)->post('/admin/cms/faqs', ['question' => '', 'answer' => '', 'topic' => 'not-a-topic', 'status' => 'active'])->assertSessionHasErrors(['question', 'answer', 'topic']);
    }

    // ------------------------------------------------------------------
    // Dashboard numbers = database
    // ------------------------------------------------------------------

    public function test_dashboard_numbers_match_the_database_and_cards_link_to_real_pages(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        User::factory()->create(['role' => 'customer']);
        $paid = fn (array $o) => Order::create($o + ['user_id' => $customer->id, 'subtotal' => $o['grand_total'], 'payment_method' => 'razorpay', 'order_status' => 'confirmed']);
        $paid(['order_number' => 'D-1', 'grand_total' => 100, 'payment_status' => 'paid']);
        $paid(['order_number' => 'D-2', 'grand_total' => 200.50, 'payment_status' => 'paid']);
        $paid(['order_number' => 'D-3', 'grand_total' => 999, 'payment_status' => 'pending', 'payment_method' => 'cod', 'order_status' => 'pending']);
        $paid(['order_number' => 'D-4', 'grand_total' => 50, 'payment_status' => 'failed', 'order_status' => 'cancelled']);

        $p = Product::create(['category_id' => \App\Models\Category::create(['name' => 'C', 'slug' => 'c', 'status' => 'active'])->id, 'name' => 'P1', 'slug' => 'p1', 'status' => 'active', 'is_returnable' => false, 'return_days' => 7]);
        $variant = fn (array $o) => ProductVariant::create($o + ['product_id' => $p->id, 'variant_name' => 'V', 'enable_tiered_pricing' => false, 'single_quantity' => 1, 'single_price' => 10, 'status' => 'active', 'sort_order' => 0, 'is_default' => false]);
        $variant(['sku' => 'A', 'stock_quantity' => 50, 'low_stock_quantity' => 5, 'stock_status' => 'in_stock']);
        $variant(['sku' => 'B', 'stock_quantity' => 3, 'low_stock_quantity' => 5, 'stock_status' => 'in_stock']);
        $variant(['sku' => 'C', 'stock_quantity' => 0, 'low_stock_quantity' => 5, 'stock_status' => 'out_of_stock']);

        $admin = $this->admin();
        $html = $this->actingAs($admin)->get('/admin/dashboard')->assertOk()->getContent();
        $text = trim(preg_replace('~\s+~', ' ', strip_tags($html)));

        $this->assertMatchesRegularExpression('~Total Revenue ₹300\.50~', $text, 'Revenue = only PAID orders (100 + 200.50).');
        $this->assertMatchesRegularExpression('~Total Orders 4~', $text);
        $this->assertMatchesRegularExpression('~Pending Orders 1~', $text);
        $this->assertMatchesRegularExpression('~Total Customers 2~', $text, 'Customers = role customer only (admin excluded).');
        $this->assertMatchesRegularExpression('~Total Products 1~', $text);
        $this->assertMatchesRegularExpression('~Low Stock Variants 1~', $text);
        $this->assertMatchesRegularExpression('~Out of Stock Variants 1~', $text);

        preg_match_all('~<a href="([^"]+)" class="card text-decoration-none text-reset admin-stat-link">~', $html, $links);
        $this->assertCount(7, $links[1], 'Every stat card links to its list.');
        foreach ($links[1] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    // ------------------------------------------------------------------
    // No dead buttons on the order / payment pages
    // ------------------------------------------------------------------

    public function test_order_and_payment_pages_have_no_unexplained_dead_buttons(): void
    {
        $customer = User::factory()->create();
        $order = Order::create(['user_id' => $customer->id, 'order_number' => 'BTN-1', 'subtotal' => 10, 'grand_total' => 10, 'payment_method' => 'razorpay', 'payment_status' => 'paid', 'order_status' => 'confirmed']);
        $payment = \App\Models\Payment::create(['order_id' => $order->id, 'payment_method' => 'razorpay', 'amount' => 10, 'status' => 'paid', 'gateway_order_id' => 'order_x', 'gateway_payment_id' => 'pay_x']);
        $admin = $this->admin();

        foreach (["/admin/sales/orders/{$order->id}", "/admin/sales/payments/{$payment->id}", '/admin/sales/payments'] as $url) {
            $html = $this->actingAs($admin)->get($url)->assertOk()->getContent();

            preg_match_all('~<button[^>]*\bdisabled\b[^>]*>~', $html, $dead);
            foreach ($dead[0] as $tag) {
                $this->assertStringContainsString('title="', $tag, "Disabled button without an explanation on {$url}: {$tag}");
                $this->assertStringContainsString('aria-disabled="true"', $tag, "Disabled button not marked aria-disabled on {$url}");
            }
        }

        $orderPage = $this->actingAs($admin)->get("/admin/sales/orders/{$order->id}")->getContent();
        $this->assertStringContainsString("/admin/sales/orders/{$order->id}/print", $orderPage);
        $this->assertStringContainsString('href="#update-status"', $orderPage);
        $this->assertStringContainsString('id="update-status"', $orderPage);
    }
}
