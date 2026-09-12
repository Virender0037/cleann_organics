<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerWishlistScopingTest extends TestCase
{
    use RefreshDatabase;

    private function superadmin(): User
    {
        return User::factory()->create(['role' => 'superadmin']);
    }

    private function product(): Product
    {
        $category = Category::create(['name' => 'Fruits', 'slug' => 'fruits-'.uniqid(), 'status' => 'active']);

        return Product::create([
            'category_id' => $category->id,
            'name' => 'Green Apple',
            'slug' => 'green-apple-'.uniqid(),
            'status' => 'active',
            'is_returnable' => false,
            'return_days' => 7,
        ]);
    }

    /**
     * A staff member viewing customer A's wishlist page must not be able to
     * delete customer B's wishlist row by guessing its id in the URL —
     * {customer} and {wishlist} are independent route parameters, so the
     * controller has to check ownership itself rather than trust the URL
     * shape implies it.
     */
    public function test_cannot_delete_a_wishlist_row_belonging_to_a_different_customer(): void
    {
        $customerA = User::factory()->create();
        $customerB = User::factory()->create();
        $product = $this->product();

        $wishlistRow = Wishlist::create(['user_id' => $customerB->id, 'product_id' => $product->id]);

        $this->actingAs($this->superadmin())
            ->delete(route('admin.customers.wishlists.destroy', ['customer' => $customerA, 'wishlist' => $wishlistRow]))
            ->assertNotFound();

        $this->assertDatabaseHas('wishlists', ['id' => $wishlistRow->id]);
    }

    public function test_can_delete_a_wishlist_row_belonging_to_the_url_customer(): void
    {
        $customer = User::factory()->create();
        $product = $this->product();

        $wishlistRow = Wishlist::create(['user_id' => $customer->id, 'product_id' => $product->id]);

        $this->actingAs($this->superadmin())
            ->delete(route('admin.customers.wishlists.destroy', ['customer' => $customer, 'wishlist' => $wishlistRow]))
            ->assertRedirect();

        $this->assertDatabaseMissing('wishlists', ['id' => $wishlistRow->id]);
    }
}
