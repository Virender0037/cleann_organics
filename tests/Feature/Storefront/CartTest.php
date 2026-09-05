<?php

namespace Tests\Feature\Storefront;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Phase G — production shopping cart. Every mutation is asserted against
 * real database state (or, for guests, the real session payload) rather
 * than trusting the JSON response alone, since the whole point of the cart
 * service is that price/stock/ownership are never taken on the client's
 * word.
 */
class CartTest extends TestCase
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

    private function jsonHeaders(): array
    {
        return ['Accept' => 'application/json'];
    }

    // ------------------------------------------------------------------
    // Guest cart
    // ------------------------------------------------------------------

    public function test_guest_can_add_item_to_cart(): void
    {
        $variant = $this->variant($this->product($this->category(), 'Green Apple'));

        $response = $this->withHeaders($this->jsonHeaders())
            ->post('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 2]);

        $response->assertOk();
        $response->assertJson(['success' => true, 'itemCount' => 2]);
        $this->assertSame(['cart' => [$variant->id => 2]], ['cart' => session('cart')]);
    }

    public function test_guest_cart_persists_across_requests(): void
    {
        $variant = $this->variant($this->product($this->category(), 'Green Apple'));

        $this->withHeaders($this->jsonHeaders())->post('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 1]);

        $response = $this->get('/shopping-cart');

        $response->assertOk()->assertSee('Green Apple');
    }

    public function test_guest_adding_same_variant_twice_increments_not_duplicates(): void
    {
        $variant = $this->variant($this->product($this->category(), 'Green Apple'));

        $this->withHeaders($this->jsonHeaders())->post('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 1]);
        $response = $this->withHeaders($this->jsonHeaders())->post('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 2]);

        $response->assertJson(['itemCount' => 3]);
        $this->assertSame([$variant->id => 3], session('cart'));
    }

    public function test_guest_can_update_quantity(): void
    {
        $variant = $this->variant($this->product($this->category(), 'Green Apple'));
        $this->withHeaders($this->jsonHeaders())->post('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 1]);

        $response = $this->withHeaders($this->jsonHeaders())->patch('/cart/items/'.$variant->id, ['quantity' => 5]);

        $response->assertOk()->assertJson(['success' => true, 'itemCount' => 5]);
        $this->assertSame([$variant->id => 5], session('cart'));
    }

    public function test_guest_can_remove_item(): void
    {
        $variant = $this->variant($this->product($this->category(), 'Green Apple'));
        $this->withHeaders($this->jsonHeaders())->post('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 1]);

        $response = $this->withHeaders($this->jsonHeaders())->delete('/cart/items/'.$variant->id);

        $response->assertOk()->assertJson(['success' => true, 'itemCount' => 0]);
        $this->assertSame([], session('cart', []));
    }

    public function test_guest_can_clear_cart(): void
    {
        $variantA = $this->variant($this->product($this->category(), 'Green Apple'));
        $variantB = $this->variant($this->product($this->category(), 'Fresh Orange'));
        $this->withHeaders($this->jsonHeaders())->post('/cart/items', ['product_variant_id' => $variantA->id, 'quantity' => 1]);
        $this->withHeaders($this->jsonHeaders())->post('/cart/items', ['product_variant_id' => $variantB->id, 'quantity' => 1]);

        $response = $this->withHeaders($this->jsonHeaders())->delete('/cart');

        $response->assertOk()->assertJson(['success' => true, 'itemCount' => 0]);
        $this->assertNull(session('cart'));
    }

    public function test_guest_cannot_add_variant_of_inactive_product(): void
    {
        $product = $this->product($this->category(), 'Discontinued Fruit', ['status' => 'inactive']);
        $variant = $this->variant($product);

        $response = $this->withHeaders($this->jsonHeaders())
            ->post('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 1]);

        $response->assertStatus(422)->assertJson(['success' => false]);
        $this->assertSame([], session('cart', []));
    }

    public function test_guest_cannot_add_inactive_variant(): void
    {
        $variant = $this->variant($this->product($this->category(), 'Green Apple'), ['status' => 'inactive']);

        $response = $this->withHeaders($this->jsonHeaders())
            ->post('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 1]);

        $response->assertStatus(422)->assertJson(['success' => false]);
        $this->assertSame([], session('cart', []));
    }

    public function test_guest_add_quantity_is_clamped_to_stock(): void
    {
        $variant = $this->variant($this->product($this->category(), 'Green Apple'), ['stock_quantity' => 3]);

        $response = $this->withHeaders($this->jsonHeaders())
            ->post('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 50]);

        $response->assertOk()->assertJson(['success' => true, 'itemCount' => 3]);
        $this->assertSame([$variant->id => 3], session('cart'));
    }

    public function test_guest_cart_never_trusts_client_supplied_price(): void
    {
        $variant = $this->variant($this->product($this->category(), 'Green Apple'), ['single_price' => 100.00]);

        // A tampered client sends a fabricated price/subtotal — the request
        // doesn't even accept these fields, and the resulting line must
        // reflect the real ₹100 price regardless.
        $this->withHeaders($this->jsonHeaders())->post('/cart/items', [
            'product_variant_id' => $variant->id,
            'quantity' => 2,
            'unit_price' => 1,
            'price' => 1,
            'subtotal' => 1,
            'total' => 1,
        ]);

        $response = $this->get('/shopping-cart');

        $response->assertOk()->assertSee('₹100.00')->assertSee('₹200.00');
    }

    // ------------------------------------------------------------------
    // Authenticated cart
    // ------------------------------------------------------------------

    public function test_authenticated_add_creates_cart_item_row(): void
    {
        $user = User::factory()->create();
        $variant = $this->variant($this->product($this->category(), 'Green Apple'));

        $this->actingAs($user)->withHeaders($this->jsonHeaders())
            ->post('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 2])
            ->assertOk();

        $this->assertDatabaseHas('cart_items', [
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);
        $this->assertDatabaseHas('carts', ['user_id' => $user->id]);
    }

    public function test_authenticated_cart_is_scoped_to_owner(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $variant = $this->variant($this->product($this->category(), 'Green Apple'));

        $this->actingAs($owner)->withHeaders($this->jsonHeaders())
            ->post('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 1]);

        $this->actingAs($other)->get('/shopping-cart')->assertOk()->assertDontSee('Green Apple');
    }

    public function test_authenticated_user_cannot_mutate_another_users_cart_item(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $variant = $this->variant($this->product($this->category(), 'Green Apple'));

        $this->actingAs($owner)->withHeaders($this->jsonHeaders())
            ->post('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 1]);
        $item = CartItem::first();

        $updateResponse = $this->actingAs($attacker)->withHeaders($this->jsonHeaders())
            ->patch('/cart/items/'.$item->id, ['quantity' => 9]);
        $updateResponse->assertStatus(422)->assertJson(['success' => false]);

        $destroyResponse = $this->actingAs($attacker)->withHeaders($this->jsonHeaders())
            ->delete('/cart/items/'.$item->id);
        $destroyResponse->assertStatus(422)->assertJson(['success' => false]);

        $this->assertDatabaseHas('cart_items', ['id' => $item->id, 'quantity' => 1]);
    }

    public function test_authenticated_adding_same_variant_again_increments_not_duplicates(): void
    {
        $user = User::factory()->create();
        $variant = $this->variant($this->product($this->category(), 'Green Apple'));

        $this->actingAs($user)->withHeaders($this->jsonHeaders())
            ->post('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 1]);
        $this->actingAs($user)->withHeaders($this->jsonHeaders())
            ->post('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 2]);

        $this->assertSame(1, CartItem::where('product_variant_id', $variant->id)->count());
        $this->assertDatabaseHas('cart_items', ['product_variant_id' => $variant->id, 'quantity' => 3]);
    }

    public function test_authenticated_can_update_quantity(): void
    {
        $user = User::factory()->create();
        $variant = $this->variant($this->product($this->category(), 'Green Apple'));
        $this->actingAs($user)->withHeaders($this->jsonHeaders())
            ->post('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 1]);
        $item = CartItem::first();

        $response = $this->actingAs($user)->withHeaders($this->jsonHeaders())
            ->patch('/cart/items/'.$item->id, ['quantity' => 4]);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('cart_items', ['id' => $item->id, 'quantity' => 4]);
    }

    public function test_authenticated_can_remove_item(): void
    {
        $user = User::factory()->create();
        $variant = $this->variant($this->product($this->category(), 'Green Apple'));
        $this->actingAs($user)->withHeaders($this->jsonHeaders())
            ->post('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 1]);
        $item = CartItem::first();

        $response = $this->actingAs($user)->withHeaders($this->jsonHeaders())->delete('/cart/items/'.$item->id);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
    }

    public function test_authenticated_can_clear_cart(): void
    {
        $user = User::factory()->create();
        $variantA = $this->variant($this->product($this->category(), 'Green Apple'));
        $variantB = $this->variant($this->product($this->category(), 'Fresh Orange'));
        $this->actingAs($user)->withHeaders($this->jsonHeaders())
            ->post('/cart/items', ['product_variant_id' => $variantA->id, 'quantity' => 1]);
        $this->actingAs($user)->withHeaders($this->jsonHeaders())
            ->post('/cart/items', ['product_variant_id' => $variantB->id, 'quantity' => 1]);
        $cart = Cart::where('user_id', $user->id)->first();

        $response = $this->actingAs($user)->withHeaders($this->jsonHeaders())->delete('/cart');

        $response->assertOk()->assertJson(['success' => true, 'itemCount' => 0]);
        $this->assertSame(0, $cart->items()->count());
    }

    // ------------------------------------------------------------------
    // Guest -> authenticated merge
    // ------------------------------------------------------------------

    public function test_guest_cart_merges_into_account_on_login(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);
        $variant = $this->variant($this->product($this->category(), 'Green Apple'));

        $this->withHeaders($this->jsonHeaders())->post('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 2]);

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $this->assertDatabaseHas('cart_items', ['product_variant_id' => $variant->id, 'quantity' => 2]);
        $this->assertDatabaseHas('carts', ['user_id' => $user->id]);
    }

    public function test_guest_cart_merges_into_account_on_registration(): void
    {
        $variant = $this->variant($this->product($this->category(), 'Green Apple'));
        $this->withHeaders($this->jsonHeaders())->post('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 1]);

        $this->post('/register', [
            'name' => 'New Shopper',
            'email' => 'shopper@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::where('email', 'shopper@example.com')->firstOrFail();
        $this->assertDatabaseHas('carts', ['user_id' => $user->id]);
        $this->assertDatabaseHas('cart_items', ['product_variant_id' => $variant->id, 'quantity' => 1]);
    }

    public function test_merge_combines_quantities_for_the_same_variant(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);
        $variant = $this->variant($this->product($this->category(), 'Green Apple'), ['stock_quantity' => 20]);

        // Something already sitting in the account's own cart.
        $cart = Cart::create(['user_id' => $user->id]);
        $cart->items()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 2,
            'unit_price' => 100,
            'total_price' => 200,
        ]);

        $this->withHeaders($this->jsonHeaders())->post('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 3]);

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $this->assertDatabaseHas('cart_items', ['product_variant_id' => $variant->id, 'quantity' => 5]);
        $this->assertSame(1, CartItem::where('product_variant_id', $variant->id)->count());
    }

    public function test_merge_never_exceeds_current_stock(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);
        $variant = $this->variant($this->product($this->category(), 'Green Apple'), ['stock_quantity' => 4]);

        $cart = Cart::create(['user_id' => $user->id]);
        $cart->items()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 2,
            'unit_price' => 100,
            'total_price' => 200,
        ]);

        $this->withHeaders($this->jsonHeaders())->post('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 5]);
        // Guest side alone is clamped to 4; account already holds 2 more.

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $this->assertDatabaseHas('cart_items', ['product_variant_id' => $variant->id, 'quantity' => 4]);
    }

    public function test_guest_session_cart_is_cleared_after_merge(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);
        $variant = $this->variant($this->product($this->category(), 'Green Apple'));

        $this->withHeaders($this->jsonHeaders())->post('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 1]);
        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $this->assertNull(session('cart'));
    }

    // ------------------------------------------------------------------
    // Pricing (reuses Phase F tier semantics — never hardcoded thresholds)
    // ------------------------------------------------------------------

    public function test_single_tier_price_is_used(): void
    {
        $variant = $this->variant($this->product($this->category(), 'Green Apple'), [
            'enable_tiered_pricing' => false,
            'single_price' => 149.50,
        ]);

        $this->withHeaders($this->jsonHeaders())->post('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 1]);

        $this->get('/shopping-cart')->assertSee('₹149.50');
    }

    public function test_standard_tier_price_applies_once_quantity_reached(): void
    {
        $variant = $this->variant($this->product($this->category(), 'Bulk Apple'), [
            'enable_tiered_pricing' => true,
            'single_quantity' => 1,
            'single_price' => 100.00,
            'standard_quantity' => 10,
            'standard_price' => 80.00,
            'stock_quantity' => 100,
        ]);

        $this->withHeaders($this->jsonHeaders())->post('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 10]);

        $this->get('/shopping-cart')->assertSee('₹80.00')->assertSee('₹800.00');
    }

    public function test_discount_tier_price_applies_at_higher_quantity(): void
    {
        $variant = $this->variant($this->product($this->category(), 'Bulk Apple'), [
            'enable_tiered_pricing' => true,
            'single_quantity' => 1,
            'single_price' => 100.00,
            'standard_quantity' => 10,
            'standard_price' => 80.00,
            'discount_quantity' => 30,
            'discount_price' => 60.00,
            'stock_quantity' => 100,
        ]);

        $this->withHeaders($this->jsonHeaders())->post('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 30]);

        $this->get('/shopping-cart')->assertSee('₹60.00')->assertSee('₹1,800.00');
    }

    public function test_unit_price_recalculates_when_quantity_crosses_a_tier_boundary(): void
    {
        $variant = $this->variant($this->product($this->category(), 'Bulk Apple'), [
            'enable_tiered_pricing' => true,
            'single_quantity' => 1,
            'single_price' => 100.00,
            'standard_quantity' => 10,
            'standard_price' => 80.00,
            'stock_quantity' => 100,
        ]);

        $this->withHeaders($this->jsonHeaders())->post('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 1]);
        $this->get('/shopping-cart')->assertSee('₹100.00');

        $this->withHeaders($this->jsonHeaders())->patch('/cart/items/'.$variant->id, ['quantity' => 10]);
        $this->get('/shopping-cart')->assertSee('₹80.00')->assertDontSee('₹100.00 x');
    }

    public function test_cart_subtotal_is_the_sum_of_line_subtotals(): void
    {
        $variantA = $this->variant($this->product($this->category(), 'Green Apple'), ['single_price' => 100.00]);
        $variantB = $this->variant($this->product($this->category(), 'Fresh Orange'), ['single_price' => 50.00]);

        $this->withHeaders($this->jsonHeaders())->post('/cart/items', ['product_variant_id' => $variantA->id, 'quantity' => 2]);
        $this->withHeaders($this->jsonHeaders())->post('/cart/items', ['product_variant_id' => $variantB->id, 'quantity' => 3]);

        // 2*100 + 3*50 = 350
        $this->get('/shopping-cart')->assertSee('₹350.00');
    }

    // ------------------------------------------------------------------
    // Storefront integration
    // ------------------------------------------------------------------

    public function test_header_item_count_reflects_cart_on_any_page(): void
    {
        $variant = $this->variant($this->product($this->category(), 'Green Apple'));
        $this->withHeaders($this->jsonHeaders())->post('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 3]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('id="mini-cart-count">3<', false);
    }

    public function test_mini_cart_partial_shows_real_items(): void
    {
        $variant = $this->variant($this->product($this->category(), 'Green Apple'));
        $this->withHeaders($this->jsonHeaders())->post('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 1]);

        $response = $this->get('/cart/mini');

        $response->assertOk()->assertSee('Green Apple');
    }

    public function test_cart_page_shows_real_items_not_static_ones(): void
    {
        $variant = $this->variant($this->product($this->category(), 'Green Apple'));
        $this->withHeaders($this->jsonHeaders())->post('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 1]);

        $response = $this->get('/shopping-cart');

        $response->assertOk();
        $response->assertSee('Green Apple');
        $response->assertDontSee('Fresh orange');
    }

    public function test_product_card_add_to_cart_uses_the_cards_own_variant(): void
    {
        $category = $this->category();
        $product = $this->product($category, 'Green Apple');
        $variant = $this->variant($product);

        $shopPage = $this->get('/shop');
        $shopPage->assertOk();
        $shopPage->assertSee('name="product_variant_id" value="'.$variant->id.'"', false);

        $response = $this->withHeaders($this->jsonHeaders())
            ->post('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 1]);

        $response->assertOk()->assertJson(['success' => true]);
    }

    public function test_product_detail_page_adds_the_selected_variant(): void
    {
        $product = $this->product($this->category(), 'Two Variant Product');
        $this->variant($product, ['variant_name' => 'Small', 'is_default' => false, 'sort_order' => 0]);
        $large = $this->variant($product, ['variant_name' => 'Large', 'is_default' => true, 'sort_order' => 1]);

        $page = $this->get('/products/'.$product->slug);
        $page->assertOk();
        $page->assertSee('id="add-to-cart-variant-id" value="'.$large->id.'"', false);

        $response = $this->withHeaders($this->jsonHeaders())
            ->post('/cart/items', ['product_variant_id' => $large->id, 'quantity' => 1]);

        $response->assertOk();
        $this->assertSame([$large->id => 1], session('cart'));
    }

    public function test_out_of_stock_variant_is_rejected_server_side_even_if_requested_directly(): void
    {
        $variant = $this->variant($this->product($this->category(), 'Green Apple'), [
            'stock_quantity' => 0,
            'stock_status' => 'out_of_stock',
        ]);

        $response = $this->withHeaders($this->jsonHeaders())
            ->post('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 1]);

        $response->assertStatus(422)->assertJson(['success' => false]);
        $this->assertSame([], session('cart', []));
    }

    // ------------------------------------------------------------------
    // Phase G final QA — boundary conditions
    // ------------------------------------------------------------------

    public function test_boundary_stock_of_one_allows_exactly_one_and_clamps_beyond(): void
    {
        $variant = $this->variant($this->product($this->category(), 'Green Apple'), ['stock_quantity' => 1]);

        $ok = $this->withHeaders($this->jsonHeaders())
            ->post('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 1]);
        $ok->assertOk()->assertJson(['success' => true, 'message' => 'Added to cart.']);

        $over = $this->withHeaders($this->jsonHeaders())
            ->post('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 1]);
        $over->assertOk();
        $this->assertSame(1, $over->json('itemCount'));
        $this->assertStringContainsString('adjusted', $over->json('message'));
    }

    public function test_boundary_quantity_exactly_equal_to_stock_is_not_flagged_as_adjusted(): void
    {
        $variant = $this->variant($this->product($this->category(), 'Green Apple'), ['stock_quantity' => 5]);

        $response = $this->withHeaders($this->jsonHeaders())
            ->post('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 5]);

        $response->assertOk()->assertJson(['success' => true, 'message' => 'Added to cart.', 'itemCount' => 5]);
    }

    public function test_boundary_quantity_one_more_than_stock_is_clamped_with_message(): void
    {
        $variant = $this->variant($this->product($this->category(), 'Green Apple'), ['stock_quantity' => 5]);

        $response = $this->withHeaders($this->jsonHeaders())
            ->post('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 6]);

        $response->assertOk()->assertJson(['success' => true, 'itemCount' => 5]);
        $this->assertStringContainsString('Only 5 in stock', $response->json('message'));
    }

    public function test_zero_quantity_is_rejected_by_validation(): void
    {
        $variant = $this->variant($this->product($this->category(), 'Green Apple'));

        $response = $this->withHeaders($this->jsonHeaders())
            ->post('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 0]);

        $response->assertStatus(422);
        $this->assertSame([], session('cart', []));
    }

    public function test_negative_quantity_is_rejected_by_validation(): void
    {
        $variant = $this->variant($this->product($this->category(), 'Green Apple'));

        $response = $this->withHeaders($this->jsonHeaders())
            ->post('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => -3]);

        $response->assertStatus(422);
        $this->assertSame([], session('cart', []));
    }

    public function test_nonexistent_variant_id_is_rejected_by_validation(): void
    {
        $response = $this->withHeaders($this->jsonHeaders())
            ->post('/cart/items', ['product_variant_id' => 999999, 'quantity' => 1]);

        $response->assertStatus(422);
        $this->assertSame([], session('cart', []));
    }

    public function test_zero_quantity_update_is_rejected(): void
    {
        $variant = $this->variant($this->product($this->category(), 'Green Apple'));
        $this->withHeaders($this->jsonHeaders())->post('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 1]);

        $response = $this->withHeaders($this->jsonHeaders())->patch('/cart/items/'.$variant->id, ['quantity' => 0]);

        $response->assertStatus(422);
        // Original quantity untouched.
        $this->assertSame([$variant->id => 1], session('cart'));
    }

    public function test_cannot_add_variant_of_inactive_category(): void
    {
        $category = $this->category(['status' => 'inactive']);
        $variant = $this->variant($this->product($category, 'Orphan Apple'));

        $response = $this->withHeaders($this->jsonHeaders())
            ->post('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 1]);

        $response->assertStatus(422)->assertJson(['success' => false]);
    }

    /**
     * Guest and authenticated carts must apply identical visibility/stock/
     * pricing rules — this pins that down directly by exercising both
     * paths against the same variant fixture.
     */
    public function test_guest_and_authenticated_carts_apply_identical_business_rules(): void
    {
        $variant = $this->variant($this->product($this->category(), 'Parity Apple'), [
            'enable_tiered_pricing' => true,
            'single_quantity' => 1,
            'single_price' => 100.00,
            'standard_quantity' => 10,
            'standard_price' => 80.00,
            'stock_quantity' => 12,
        ]);

        $guestResponse = $this->withHeaders($this->jsonHeaders())
            ->post('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 15]);

        $user = User::factory()->create();
        $authResponse = $this->actingAs($user)->withHeaders($this->jsonHeaders())
            ->post('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 15]);

        // Both clamp to the same stock ceiling and land on the same tier price.
        $this->assertSame(12, $guestResponse->json('itemCount'));
        $this->assertSame(12, $authResponse->json('itemCount'));
        $this->assertSame($guestResponse->json('subtotal'), $authResponse->json('subtotal'));
    }

    // ------------------------------------------------------------------
    // Tier pricing — exact threshold boundaries
    // ------------------------------------------------------------------

    public function test_tier_boundary_one_below_standard_threshold_uses_single_price(): void
    {
        $variant = $this->variant($this->product($this->category(), 'Bulk Apple'), [
            'enable_tiered_pricing' => true,
            'single_quantity' => 1,
            'single_price' => 100.00,
            'standard_quantity' => 10,
            'standard_price' => 80.00,
            'stock_quantity' => 100,
        ]);

        $this->assertSame(100.00, $variant->unitPriceForQuantity(9));
    }

    public function test_tier_boundary_exactly_at_standard_threshold_uses_standard_price(): void
    {
        $variant = $this->variant($this->product($this->category(), 'Bulk Apple'), [
            'enable_tiered_pricing' => true,
            'single_quantity' => 1,
            'single_price' => 100.00,
            'standard_quantity' => 10,
            'standard_price' => 80.00,
            'stock_quantity' => 100,
        ]);

        $this->assertSame(80.00, $variant->unitPriceForQuantity(10));
    }

    public function test_tier_boundary_one_above_standard_threshold_still_uses_standard_price(): void
    {
        $variant = $this->variant($this->product($this->category(), 'Bulk Apple'), [
            'enable_tiered_pricing' => true,
            'single_quantity' => 1,
            'single_price' => 100.00,
            'standard_quantity' => 10,
            'standard_price' => 80.00,
            'stock_quantity' => 100,
        ]);

        $this->assertSame(80.00, $variant->unitPriceForQuantity(11));
    }

    public function test_tier_boundary_one_below_discount_threshold_uses_standard_price(): void
    {
        $variant = $this->variant($this->product($this->category(), 'Bulk Apple'), [
            'enable_tiered_pricing' => true,
            'single_quantity' => 1,
            'single_price' => 100.00,
            'standard_quantity' => 10,
            'standard_price' => 80.00,
            'discount_quantity' => 30,
            'discount_price' => 60.00,
            'stock_quantity' => 100,
        ]);

        $this->assertSame(80.00, $variant->unitPriceForQuantity(29));
    }

    public function test_tier_boundary_exactly_at_discount_threshold_uses_discount_price(): void
    {
        $variant = $this->variant($this->product($this->category(), 'Bulk Apple'), [
            'enable_tiered_pricing' => true,
            'single_quantity' => 1,
            'single_price' => 100.00,
            'standard_quantity' => 10,
            'standard_price' => 80.00,
            'discount_quantity' => 30,
            'discount_price' => 60.00,
            'stock_quantity' => 100,
        ]);

        $this->assertSame(60.00, $variant->unitPriceForQuantity(30));
    }

    public function test_tier_boundary_one_above_discount_threshold_still_uses_discount_price(): void
    {
        $variant = $this->variant($this->product($this->category(), 'Bulk Apple'), [
            'enable_tiered_pricing' => true,
            'single_quantity' => 1,
            'single_price' => 100.00,
            'standard_quantity' => 10,
            'standard_price' => 80.00,
            'discount_quantity' => 30,
            'discount_price' => 60.00,
            'stock_quantity' => 100,
        ]);

        $this->assertSame(60.00, $variant->unitPriceForQuantity(31));
    }

    public function test_crossing_a_tier_threshold_via_cart_update_changes_unit_price_line_and_cart_subtotal(): void
    {
        $variant = $this->variant($this->product($this->category(), 'Bulk Apple'), [
            'enable_tiered_pricing' => true,
            'single_quantity' => 1,
            'single_price' => 100.00,
            'standard_quantity' => 10,
            'standard_price' => 80.00,
            'stock_quantity' => 100,
        ]);

        $this->withHeaders($this->jsonHeaders())->post('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 9]);
        $before = $this->get('/shopping-cart');
        $before->assertSee('₹100.00')->assertSee('₹900.00'); // 9 * 100

        $this->withHeaders($this->jsonHeaders())->patch('/cart/items/'.$variant->id, ['quantity' => 10]);
        $after = $this->get('/shopping-cart');
        $after->assertSee('₹80.00')->assertSee('₹800.00'); // 10 * 80, both line and cart subtotal since it's the only line
    }

    // ------------------------------------------------------------------
    // Merge — additional edge cases
    // ------------------------------------------------------------------

    public function test_merge_keeps_guest_only_and_auth_only_variants_separately(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);
        $guestOnly = $this->variant($this->product($this->category(), 'Guest Only Apple'));
        $authOnly = $this->variant($this->product($this->category(), 'Auth Only Apple'));

        $cart = Cart::create(['user_id' => $user->id]);
        $cart->items()->create([
            'product_variant_id' => $authOnly->id,
            'quantity' => 1,
            'unit_price' => 100,
            'total_price' => 100,
        ]);

        $this->withHeaders($this->jsonHeaders())->post('/cart/items', ['product_variant_id' => $guestOnly->id, 'quantity' => 2]);

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $this->assertDatabaseHas('cart_items', ['product_variant_id' => $guestOnly->id, 'quantity' => 2]);
        $this->assertDatabaseHas('cart_items', ['product_variant_id' => $authOnly->id, 'quantity' => 1]);
        $this->assertSame(2, CartItem::count());
    }

    public function test_merge_drops_out_of_stock_guest_variant_without_failing_the_whole_merge(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);
        $goodVariant = $this->variant($this->product($this->category(), 'Good Apple'));
        $badVariant = $this->variant($this->product($this->category(), 'Bad Apple'));

        $this->withHeaders($this->jsonHeaders())->post('/cart/items', ['product_variant_id' => $goodVariant->id, 'quantity' => 1]);
        // Goes out of stock after being added to the guest cart, before login.
        $badVariant->update(['stock_quantity' => 0, 'stock_status' => 'out_of_stock']);
        session()->put('cart', session('cart', []) + [$badVariant->id => 1]);

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $this->assertDatabaseHas('cart_items', ['product_variant_id' => $goodVariant->id]);
        $this->assertDatabaseMissing('cart_items', ['product_variant_id' => $badVariant->id]);
    }

    public function test_merge_drops_inactive_product_variant_without_failing_the_whole_merge(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);
        $goodVariant = $this->variant($this->product($this->category(), 'Good Apple'));
        $product = $this->product($this->category(), 'Discontinued Apple', ['status' => 'inactive']);
        $badVariant = $this->variant($product);

        session()->put('cart', [$goodVariant->id => 1, $badVariant->id => 1]);

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $this->assertDatabaseHas('cart_items', ['product_variant_id' => $goodVariant->id]);
        $this->assertDatabaseMissing('cart_items', ['product_variant_id' => $badVariant->id]);
    }

    public function test_registration_triggers_merge_exactly_once(): void
    {
        $variant = $this->variant($this->product($this->category(), 'Green Apple'));
        $this->withHeaders($this->jsonHeaders())->post('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 1]);

        $this->post('/register', [
            'name' => 'Once Shopper',
            'email' => 'once@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // If the merge ran twice, quantity would be 2 instead of 1.
        $this->assertDatabaseHas('cart_items', ['product_variant_id' => $variant->id, 'quantity' => 1]);
        $this->assertSame(1, CartItem::where('product_variant_id', $variant->id)->count());
    }

    // ------------------------------------------------------------------
    // Edge cases — data disappearing out from under a cart line
    // ------------------------------------------------------------------

    public function test_guest_cart_survives_variant_being_hard_deleted(): void
    {
        $variant = $this->variant($this->product($this->category(), 'Green Apple'));
        $this->withHeaders($this->jsonHeaders())->post('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 1]);

        $variant->forceDelete();

        $response = $this->get('/shopping-cart');

        $response->assertOk();
        $this->assertStringNotContainsString('Exception', $response->getContent());
    }

    public function test_guest_cart_shows_unavailable_when_variant_soft_deleted(): void
    {
        $variant = $this->variant($this->product($this->category(), 'Green Apple'));
        $this->withHeaders($this->jsonHeaders())->post('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 1]);

        $variant->delete(); // SoftDeletes

        $response = $this->get('/shopping-cart');

        $response->assertOk();
        // Soft-deleted variant is invisible to a normal find(), so the line
        // is dropped entirely rather than crashing or showing stale data.
        $response->assertDontSee('Green Apple');
    }

    public function test_cart_line_becomes_unavailable_when_variant_goes_inactive_after_being_added(): void
    {
        $variant = $this->variant($this->product($this->category(), 'Green Apple'));
        $this->withHeaders($this->jsonHeaders())->post('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 1]);

        $variant->update(['status' => 'inactive']);

        $response = $this->get('/shopping-cart');

        $response->assertOk()->assertSee('No longer available');
        // Excluded from the payable subtotal/count while still visible for removal.
        $response->assertSee('₹0.00');
    }

    public function test_cart_line_becomes_unavailable_when_product_goes_inactive_after_being_added(): void
    {
        $product = $this->product($this->category(), 'Green Apple');
        $variant = $this->variant($product);
        $this->withHeaders($this->jsonHeaders())->post('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 1]);

        $product->update(['status' => 'inactive']);

        $response = $this->get('/shopping-cart');

        $response->assertOk()->assertSee('No longer available');
    }

    public function test_cart_line_becomes_unavailable_when_category_goes_inactive_after_being_added(): void
    {
        $category = $this->category();
        $product = $this->product($category, 'Green Apple');
        $variant = $this->variant($product);
        $this->withHeaders($this->jsonHeaders())->post('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 1]);

        $category->update(['status' => 'inactive']);

        $response = $this->get('/shopping-cart');

        $response->assertOk()->assertSee('No longer available');
    }

    public function test_cart_quantity_clamps_down_when_stock_drops_below_cart_quantity(): void
    {
        $variant = $this->variant($this->product($this->category(), 'Green Apple'), ['stock_quantity' => 10]);
        $this->withHeaders($this->jsonHeaders())->post('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 8]);

        $variant->update(['stock_quantity' => 3]);

        $response = $this->get('/shopping-cart');

        $response->assertOk()->assertSee('₹300.00'); // clamped to 3 * 100
        $this->assertSame([$variant->id => 3], session('cart'));
    }

    public function test_header_mini_cart_does_not_error_when_product_has_no_usable_image(): void
    {
        $variant = $this->variant($this->product($this->category(), 'Imageless Apple'));
        $this->withHeaders($this->jsonHeaders())->post('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 1]);

        // No ProductVariantImage rows created at all for this variant.
        $response = $this->get('/');

        $response->assertOk()->assertSee('Imageless Apple');
    }
}
