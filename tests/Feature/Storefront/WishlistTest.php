<?php

namespace Tests\Feature\Storefront;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class WishlistTest extends TestCase
{
    use RefreshDatabase;

    private function category(array $overrides = []): Category
    {
        return Category::create(array_merge([
            'name' => 'Fruits',
            'slug' => 'fruits-'.uniqid(),
            'status' => 'active',
        ], $overrides));
    }

    private function product(Category $category, string $name, array $overrides = []): Product
    {
        return Product::create(array_merge([
            'category_id' => $category->id,
            'name' => $name,
            'slug' => Str::slug($name).'-'.uniqid(),
            'status' => 'active',
            'is_returnable' => false,
            'return_days' => 7,
        ], $overrides));
    }

    private function variant(Product $product, array $overrides = []): ProductVariant
    {
        return $product->variants()->create(array_merge([
            'variant_name' => 'Variant',
            'enable_tiered_pricing' => false,
            'single_quantity' => 1,
            'single_price' => 100.00,
            'stock_quantity' => 10,
            'low_stock_quantity' => 5,
            'stock_status' => 'in_stock',
            'is_default' => true,
            'status' => 'active',
            'sort_order' => 0,
        ], $overrides));
    }

    public function test_guest_cannot_persist_a_wishlist_item(): void
    {
        $product = $this->product($this->category(), 'Green Apple');

        $response = $this->post('/wishlist', ['product_id' => $product->id]);

        $response->assertRedirect(route('sign-in'));
        $this->assertDatabaseCount('wishlists', 0);
    }

    public function test_guest_visiting_wishlist_page_is_redirected_to_sign_in(): void
    {
        $this->get('/wishlist')->assertRedirect(route('sign-in'));
    }

    public function test_authenticated_customer_can_add_a_product(): void
    {
        $user = User::factory()->create();
        $product = $this->product($this->category(), 'Green Apple');

        $response = $this->actingAs($user)->post('/wishlist', ['product_id' => $product->id]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('wishlists', ['user_id' => $user->id, 'product_id' => $product->id]);
    }

    public function test_duplicate_add_does_not_create_a_duplicate_row(): void
    {
        $user = User::factory()->create();
        $product = $this->product($this->category(), 'Green Apple');

        $this->actingAs($user)->post('/wishlist', ['product_id' => $product->id]);
        $this->actingAs($user)->post('/wishlist', ['product_id' => $product->id]);

        $this->assertSame(1, Wishlist::where('user_id', $user->id)->where('product_id', $product->id)->count());
    }

    public function test_customer_can_remove_own_item(): void
    {
        $user = User::factory()->create();
        $product = $this->product($this->category(), 'Green Apple');
        Wishlist::create(['user_id' => $user->id, 'product_id' => $product->id]);

        $response = $this->actingAs($user)->delete('/wishlist/'.$product->id);

        $response->assertRedirect();
        $this->assertDatabaseMissing('wishlists', ['user_id' => $user->id, 'product_id' => $product->id]);
    }

    /**
     * The critical ownership test: another customer's wishlist row must be
     * completely unreachable, however the id is supplied. Deletion here is
     * scoped by Auth::id() + product_id together, never a bare wishlist row
     * id, so there is structurally no id/product combination an attacker
     * could submit that would touch someone else's row.
     */
    public function test_customer_cannot_remove_another_customers_item(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $product = $this->product($this->category(), 'Green Apple');
        Wishlist::create(['user_id' => $owner->id, 'product_id' => $product->id]);

        $this->actingAs($attacker)->delete('/wishlist/'.$product->id);

        $this->assertDatabaseHas('wishlists', ['user_id' => $owner->id, 'product_id' => $product->id]);
    }

    public function test_inactive_product_is_rejected_on_add(): void
    {
        $user = User::factory()->create();
        $product = $this->product($this->category(), 'Discontinued Apple', ['status' => 'inactive']);

        $response = $this->actingAs($user)->post('/wishlist', ['product_id' => $product->id]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('wishlists', 0);
    }

    public function test_product_in_inactive_category_is_rejected_on_add(): void
    {
        $user = User::factory()->create();
        $category = $this->category(['status' => 'inactive']);
        $product = $this->product($category, 'Orphan Apple');

        $response = $this->actingAs($user)->post('/wishlist', ['product_id' => $product->id]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('wishlists', 0);
    }

    public function test_nonexistent_product_is_rejected_by_validation(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/wishlist', ['product_id' => 999999]);

        $response->assertSessionHasErrors('product_id');
        $this->assertDatabaseCount('wishlists', 0);
    }

    public function test_wishlist_page_shows_real_product(): void
    {
        $user = User::factory()->create();
        $product = $this->product($this->category(), 'Green Apple');
        $this->variant($product);
        Wishlist::create(['user_id' => $user->id, 'product_id' => $product->id]);

        $response = $this->actingAs($user)->get('/wishlist');

        $response->assertOk()->assertSee('Green Apple')->assertSee('₹100.00');
    }

    public function test_wishlist_page_empty_state(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/wishlist');

        $response->assertOk()->assertSee('Your wishlist is empty.')->assertSee(route('shop'), false);
    }

    public function test_wishlist_handles_a_product_that_became_unavailable_without_crashing(): void
    {
        $user = User::factory()->create();
        $product = $this->product($this->category(), 'Green Apple');
        $this->variant($product, ['stock_quantity' => 0, 'stock_status' => 'out_of_stock']);
        Wishlist::create(['user_id' => $user->id, 'product_id' => $product->id]);

        $response = $this->actingAs($user)->get('/wishlist');

        $response->assertOk()->assertSee('Out of Stock');
    }

    public function test_header_wishlist_count_reflects_real_count(): void
    {
        $user = User::factory()->create();
        $productA = $this->product($this->category(), 'Green Apple');
        $productB = $this->product($this->category(), 'Fresh Orange');
        Wishlist::create(['user_id' => $user->id, 'product_id' => $productA->id]);
        Wishlist::create(['user_id' => $user->id, 'product_id' => $productB->id]);

        $response = $this->actingAs($user)->get('/shop');

        $response->assertOk();
        $response->assertSee('id="wishlist-count">2<', false);
    }

    public function test_header_wishlist_count_is_zero_for_guests(): void
    {
        $response = $this->get('/shop');

        $response->assertOk();
        $response->assertSee('id="wishlist-count">0<', false);
    }

    public function test_add_to_cart_from_wishlist_uses_real_cart(): void
    {
        $user = User::factory()->create();
        $product = $this->product($this->category(), 'Green Apple');
        $variant = $this->variant($product);
        Wishlist::create(['user_id' => $user->id, 'product_id' => $product->id]);

        $response = $this->actingAs($user)->withHeaders(['Accept' => 'application/json'])
            ->post('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 1]);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('cart_items', ['product_variant_id' => $variant->id, 'quantity' => 1]);
    }

    public function test_product_card_heart_reflects_wishlisted_state(): void
    {
        $user = User::factory()->create();
        $product = $this->product($this->category(), 'Green Apple');
        $this->variant($product);
        Wishlist::create(['user_id' => $user->id, 'product_id' => $product->id]);

        $response = $this->actingAs($user)->get('/shop');

        $response->assertOk();
        $response->assertSee('aria-label="Remove Green Apple from wishlist"', false);
        $response->assertSee('aria-pressed="true"', false);
    }

    public function test_product_detail_wishlist_button_shows_add_when_not_wishlisted(): void
    {
        $user = User::factory()->create();
        $product = $this->product($this->category(), 'Green Apple');
        $this->variant($product, ['is_default' => true]);

        $page = $this->actingAs($user)->get('/products/'.$product->slug);

        $page->assertOk()->assertSee('aria-label="Add to wishlist"', false);
    }

    /**
     * Wishlisted via a direct DB write, not a POST, deliberately: WishlistService
     * memoizes its per-request id lookup (see its class docblock) so it is
     * correctly reset between real requests, but PHPUnit reuses one
     * application/container across every call in a single test method — a
     * POST-then-GET here would read back the pre-POST cached (empty) state,
     * which is a test-harness artifact, not a production behavior.
     */
    public function test_product_detail_wishlist_button_shows_remove_when_already_wishlisted(): void
    {
        $user = User::factory()->create();
        $product = $this->product($this->category(), 'Green Apple');
        $this->variant($product, ['is_default' => true]);
        Wishlist::create(['user_id' => $user->id, 'product_id' => $product->id]);

        $page = $this->actingAs($user)->get('/products/'.$product->slug);

        $page->assertOk()->assertSee('aria-label="Remove from wishlist"', false);
    }
}
