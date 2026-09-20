<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * QA sweep: every parameterless admin GET page is (a) unreachable for guests and
 * customers and (b) renders without a server error for a superadmin; plus
 * registration mass-assignment, XSS escaping and 404 behaviour.
 */
class AdminSweepAndSecurityTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<int, string> */
    private function adminGetPages(): array
    {
        return collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($r) => str_starts_with($r->uri(), 'admin')
                && in_array('GET', $r->methods(), true)
                && ! str_contains($r->uri(), '{')
                && $r->uri() !== 'admin/login')
            ->map(fn ($r) => '/'.$r->uri())
            ->unique()->sort()->values()->all();
    }

    public function test_the_sweep_actually_covers_the_admin_panel(): void
    {
        $this->assertGreaterThan(90, count($this->adminGetPages()), 'The route sweep found suspiciously few admin pages.');
    }

    public function test_every_admin_page_is_closed_to_guests_and_customers(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $open = [];

        foreach ($this->adminGetPages() as $url) {
            $guest = $this->get($url);
            if (! $guest->isRedirect() || ! str_contains((string) $guest->headers->get('Location'), '/admin/login')) {
                $open[] = "guest {$url} → {$guest->getStatusCode()}";
            }

            $asCustomer = $this->actingAs($customer)->get($url);
            if (! $asCustomer->isRedirect() || ! str_contains((string) $asCustomer->headers->get('Location'), '/admin/login')) {
                $open[] = "customer {$url} → {$asCustomer->getStatusCode()}";
            }
            $this->app['auth']->forgetGuards();
        }

        $this->assertSame([], $open, "Admin pages reachable without an admin session:\n".implode("\n", $open));
    }

    public function test_every_admin_page_renders_for_a_superadmin_without_a_server_error(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $broken = [];

        foreach ($this->adminGetPages() as $url) {
            $response = $this->actingAs($admin)->get($url);

            if ($response->getStatusCode() >= 400) {
                $broken[] = "{$url} → {$response->getStatusCode()}";
            }
        }

        $this->assertSame([], $broken, "Admin pages returning errors:\n".implode("\n", $broken));
    }

    public function test_registration_ignores_a_smuggled_role_and_status(): void
    {
        $this->post('/register', [
            'name' => 'Sneaky', 'email' => 'sneaky@example.test', 'password' => 'Password!234', 'password_confirmation' => 'Password!234',
            'role' => 'superadmin', 'status' => 'active', 'is_admin' => 1,
        ]);

        $user = User::where('email', 'sneaky@example.test')->first();

        $this->assertNotNull($user);
        $this->assertNotSame('superadmin', $user->role);
        $this->assertSame('customer', $user->role);
    }

    public function test_contact_message_html_is_escaped_in_the_admin_inbox(): void
    {
        $message = ContactMessage::create(['name' => '<img src=x onerror=alert(1)>', 'email' => 'x@example.test', 'subject' => '<b>Hi</b>', 'message' => "<script>alert('xss')</script>"]);

        $html = $this->actingAs(User::factory()->create(['role' => 'superadmin']))->get('/admin/cms/contact-messages/'.$message->id)->assertOk()->getContent();

        $this->assertStringNotContainsString("<script>alert('xss')</script>", $html);
        $this->assertStringNotContainsString('<img src=x onerror=alert(1)>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    public function test_review_text_and_names_are_escaped_on_the_product_page(): void
    {
        $category = Category::create(['name' => 'Cleaners', 'slug' => 'c-'.uniqid(), 'status' => 'active']);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Safe <i>Name</i>', 'slug' => 'safe-name', 'status' => 'active', 'is_returnable' => false, 'return_days' => 7]);
        $product->variants()->create(['variant_name' => 'V', 'enable_tiered_pricing' => false, 'single_quantity' => 1, 'single_price' => 50, 'stock_quantity' => 5, 'low_stock_quantity' => 1, 'stock_status' => 'in_stock', 'is_default' => true, 'status' => 'active', 'sort_order' => 0]);
        $author = User::factory()->create(['name' => '<script>alert(2)</script>']);
        ProductReview::create(['user_id' => $author->id, 'product_id' => $product->id, 'rating' => 5, 'title' => '<b>bold</b>', 'review' => '<script>alert(3)</script> great', 'status' => 'approved']);

        $html = $this->get('/products/safe-name')->assertOk()->getContent();

        $this->assertStringNotContainsString('<script>alert(2)</script>', $html);
        $this->assertStringNotContainsString('<script>alert(3)</script>', $html);
        $this->assertStringNotContainsString('Safe <i>Name</i>', $html);
    }

    public function test_unknown_urls_return_404_not_500(): void
    {
        foreach (['/products/does-not-exist', '/category/does-not-exist', '/singleblog/does-not-exist', '/page/does-not-exist', '/definitely-not-a-page'] as $url) {
            $this->get($url)->assertNotFound();
        }

        $this->actingAs(User::factory()->create())->get('/orders/999999')->assertNotFound();
    }
}
