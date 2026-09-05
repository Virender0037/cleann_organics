<?php

namespace Tests\Feature\Storefront;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductReviewTest extends TestCase
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

    private function validPayload(int $productId, array $overrides = []): array
    {
        return array_merge([
            'product_id' => $productId,
            'rating' => 4,
            'title' => 'Pretty good',
            'review' => 'This held up well and tasted fresh on arrival.',
        ], $overrides);
    }

    // ------------------------------------------------------------------
    // Display (Phase F regression)
    // ------------------------------------------------------------------

    public function test_only_approved_reviews_appear_publicly(): void
    {
        $product = $this->product($this->category(), 'Green Apple');
        $this->variant($product);
        $user = User::factory()->create();

        $product->reviews()->create(['user_id' => $user->id, 'rating' => 5, 'review' => 'Approved text here', 'status' => 'approved']);
        $product->reviews()->create(['user_id' => User::factory()->create()->id, 'rating' => 1, 'review' => 'Pending text here', 'status' => 'pending']);
        $product->reviews()->create(['user_id' => User::factory()->create()->id, 'rating' => 1, 'review' => 'Rejected text here', 'status' => 'rejected']);

        $response = $this->get('/products/'.$product->slug);

        $response->assertOk();
        $response->assertSee('Approved text here');
        $response->assertDontSee('Pending text here');
        $response->assertDontSee('Rejected text here');
    }

    // ------------------------------------------------------------------
    // Submission
    // ------------------------------------------------------------------

    public function test_guest_cannot_submit_a_review(): void
    {
        $product = $this->product($this->category(), 'Green Apple');

        $response = $this->post('/reviews', $this->validPayload($product->id));

        $response->assertRedirect(route('sign-in'));
        $this->assertDatabaseCount('product_reviews', 0);
    }

    public function test_authenticated_customer_can_submit_a_review(): void
    {
        $user = User::factory()->create();
        $product = $this->product($this->category(), 'Green Apple');

        $response = $this->actingAs($user)->post('/reviews', $this->validPayload($product->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('product_reviews', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'rating' => 4,
            'status' => 'pending',
        ]);
    }

    public function test_valid_review_always_creates_a_pending_record(): void
    {
        $user = User::factory()->create();
        $product = $this->product($this->category(), 'Green Apple');

        $this->actingAs($user)->post('/reviews', $this->validPayload($product->id, ['status' => 'approved']));

        // Even though 'status' was smuggled into the request body, the row
        // is pending — the controller hardcodes it, and the field isn't
        // even in the Form Request's validated() output.
        $this->assertDatabaseHas('product_reviews', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'status' => 'pending',
        ]);
        $this->assertDatabaseMissing('product_reviews', ['status' => 'approved']);
    }

    public function test_user_id_cannot_be_spoofed(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $product = $this->product($this->category(), 'Green Apple');

        $this->actingAs($user)->post('/reviews', $this->validPayload($product->id, ['user_id' => $otherUser->id]));

        $this->assertDatabaseHas('product_reviews', ['product_id' => $product->id, 'user_id' => $user->id]);
        $this->assertDatabaseMissing('product_reviews', ['user_id' => $otherUser->id]);
    }

    public function test_rating_below_one_is_rejected(): void
    {
        $user = User::factory()->create();
        $product = $this->product($this->category(), 'Green Apple');

        $response = $this->actingAs($user)->post('/reviews', $this->validPayload($product->id, ['rating' => 0]));

        $response->assertSessionHasErrors('rating');
        $this->assertDatabaseCount('product_reviews', 0);
    }

    public function test_rating_above_five_is_rejected(): void
    {
        $user = User::factory()->create();
        $product = $this->product($this->category(), 'Green Apple');

        $response = $this->actingAs($user)->post('/reviews', $this->validPayload($product->id, ['rating' => 6]));

        $response->assertSessionHasErrors('rating');
        $this->assertDatabaseCount('product_reviews', 0);
    }

    public function test_empty_review_content_is_rejected(): void
    {
        $user = User::factory()->create();
        $product = $this->product($this->category(), 'Green Apple');

        $response = $this->actingAs($user)->post('/reviews', $this->validPayload($product->id, ['review' => '']));

        $response->assertSessionHasErrors('review');
        $this->assertDatabaseCount('product_reviews', 0);
    }

    public function test_too_short_review_content_is_rejected(): void
    {
        $user = User::factory()->create();
        $product = $this->product($this->category(), 'Green Apple');

        $response = $this->actingAs($user)->post('/reviews', $this->validPayload($product->id, ['review' => 'meh']));

        $response->assertSessionHasErrors('review');
        $this->assertDatabaseCount('product_reviews', 0);
    }

    public function test_inactive_product_cannot_be_reviewed(): void
    {
        $user = User::factory()->create();
        $product = $this->product($this->category(), 'Discontinued Apple', ['status' => 'inactive']);

        $response = $this->actingAs($user)->post('/reviews', $this->validPayload($product->id));

        $response->assertSessionHasErrors('product_id');
        $this->assertDatabaseCount('product_reviews', 0);
    }

    public function test_nonexistent_product_cannot_be_reviewed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/reviews', $this->validPayload(999999));

        $response->assertSessionHasErrors('product_id');
        $this->assertDatabaseCount('product_reviews', 0);
    }

    // ------------------------------------------------------------------
    // Duplicate policy
    // ------------------------------------------------------------------

    public function test_duplicate_review_for_the_same_product_is_rejected(): void
    {
        $user = User::factory()->create();
        $product = $this->product($this->category(), 'Green Apple');
        ProductReview::create(['user_id' => $user->id, 'product_id' => $product->id, 'rating' => 5, 'review' => 'First review here', 'status' => 'pending']);

        $response = $this->actingAs($user)->post('/reviews', $this->validPayload($product->id));

        $response->assertSessionHasErrors('product_id');
        $this->assertSame(1, ProductReview::where('user_id', $user->id)->where('product_id', $product->id)->count());
    }

    public function test_a_different_customer_can_still_review_the_same_product(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $product = $this->product($this->category(), 'Green Apple');
        ProductReview::create(['user_id' => $userA->id, 'product_id' => $product->id, 'rating' => 5, 'review' => 'First review here', 'status' => 'pending']);

        $response = $this->actingAs($userB)->post('/reviews', $this->validPayload($product->id));

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('product_reviews', ['user_id' => $userB->id, 'product_id' => $product->id]);
    }

    public function test_same_customer_can_review_a_different_product(): void
    {
        $user = User::factory()->create();
        $category = $this->category();
        $productA = $this->product($category, 'Green Apple');
        $productB = $this->product($category, 'Fresh Orange');
        ProductReview::create(['user_id' => $user->id, 'product_id' => $productA->id, 'rating' => 5, 'review' => 'First review here', 'status' => 'pending']);

        $response = $this->actingAs($user)->post('/reviews', $this->validPayload($productB->id));

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('product_reviews', ['user_id' => $user->id, 'product_id' => $productB->id]);
    }

    // ------------------------------------------------------------------
    // Moderation / privacy
    // ------------------------------------------------------------------

    public function test_pending_review_is_not_shown_publicly(): void
    {
        $product = $this->product($this->category(), 'Green Apple');
        $this->variant($product);
        $user = User::factory()->create();
        ProductReview::create(['user_id' => $user->id, 'product_id' => $product->id, 'rating' => 3, 'review' => 'Awaiting moderation text', 'status' => 'pending']);

        $response = $this->get('/products/'.$product->slug);

        $response->assertOk()->assertDontSee('Awaiting moderation text');
    }

    public function test_rejected_review_is_not_shown_publicly(): void
    {
        $product = $this->product($this->category(), 'Green Apple');
        $this->variant($product);
        $user = User::factory()->create();
        ProductReview::create(['user_id' => $user->id, 'product_id' => $product->id, 'rating' => 1, 'review' => 'Rejected content text', 'status' => 'rejected']);

        $response = $this->get('/products/'.$product->slug);

        $response->assertOk()->assertDontSee('Rejected content text');
    }

    public function test_approved_review_is_shown_publicly(): void
    {
        $product = $this->product($this->category(), 'Green Apple');
        $this->variant($product);
        $user = User::factory()->create();
        ProductReview::create(['user_id' => $user->id, 'product_id' => $product->id, 'rating' => 5, 'review' => 'Approved content text', 'status' => 'approved']);

        $response = $this->get('/products/'.$product->slug);

        $response->assertOk()->assertSee('Approved content text');
    }

    public function test_new_submission_shows_a_pending_moderation_message_to_its_author(): void
    {
        $user = User::factory()->create();
        $product = $this->product($this->category(), 'Green Apple');
        $this->variant($product);
        ProductReview::create(['user_id' => $user->id, 'product_id' => $product->id, 'rating' => 4, 'review' => 'My own pending review', 'status' => 'pending']);

        $response = $this->actingAs($user)->get('/products/'.$product->slug);

        $response->assertOk();
        $response->assertSee('awaiting moderation');
        // Still not in the public list, even to its own author.
        $response->assertDontSee('My own pending review');
    }

    // ------------------------------------------------------------------
    // Rating aggregate (Phase F regression — must stay live, not stale)
    // ------------------------------------------------------------------

    public function test_rating_aggregate_only_counts_approved_reviews_and_updates_on_moderation(): void
    {
        $product = $this->product($this->category(), 'Green Apple', [
            // Deliberately wrong stored values — proves the storefront
            // never trusts these stale columns.
            'average_rating' => 1.0,
            'review_count' => 99,
        ]);
        $this->variant($product);

        $review = ProductReview::create(['user_id' => User::factory()->create()->id, 'product_id' => $product->id, 'rating' => 5, 'review' => 'Great produce overall', 'status' => 'pending']);

        // Still pending: doesn't count yet.
        $this->get('/products/'.$product->slug)->assertSee('0 Reviews');

        // Admin approves it — the live calculation must reflect this
        // immediately, with no separate "recalculate" step required.
        $review->update(['status' => 'approved']);

        $this->get('/products/'.$product->slug)->assertSee('1 Review')->assertDontSee('99 Review');
    }
}
