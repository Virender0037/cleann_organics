<?php

namespace Tests\Feature\Storefront;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderHistoryTest extends TestCase
{
    use RefreshDatabase;

    private function order(User $user, array $overrides = []): Order
    {
        static $n = 0;
        $n++;

        return Order::create(array_merge([
            'user_id' => $user->id,
            'order_number' => sprintf('ORD-TEST-%06d', $n),
            'subtotal' => 100,
            'discount_amount' => 0,
            'shipping_amount' => 0,
            'tax_amount' => 0,
            'grand_total' => 100,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'order_status' => 'pending',
            'billing_same_as_shipping' => true,
            'shipping_name' => 'Jane Doe',
        ], $overrides));
    }

    public function test_guest_is_redirected_from_order_history(): void
    {
        $this->get('/order-history')->assertRedirect(route('sign-in'));
    }

    public function test_customer_sees_only_their_own_orders(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->order($user, ['order_number' => 'ORD-MINE-A']);
        $this->order($other, ['order_number' => 'ORD-STRANGER-A']);

        $response = $this->actingAs($user)->get('/order-history');

        $response->assertOk();
        $response->assertSee('ORD-MINE-A');
        $response->assertDontSee('ORD-STRANGER-A');
    }

    public function test_orders_are_listed_newest_first(): void
    {
        $user = User::factory()->create();

        $older = $this->order($user, ['order_number' => 'ORD-OLDER-01']);
        $newer = $this->order($user, ['order_number' => 'ORD-NEWER-01']);
        // created_at isn't fillable — set it directly so ordering is deterministic.
        $older->forceFill(['created_at' => now()->subDays(3)])->save();
        $newer->forceFill(['created_at' => now()->subDay()])->save();

        $response = $this->actingAs($user)->get('/order-history');

        $response->assertOk()->assertSeeInOrder(['ORD-NEWER-01', 'ORD-OLDER-01']);
    }

    public function test_order_history_paginates(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 13; $i++) {
            $this->order($user);
        }

        $page1 = $this->actingAs($user)->get('/order-history');
        $page1->assertOk();
        // 10 per page → a second page exists.
        $page1->assertSee('order-history?page=2', false);

        $page2 = $this->actingAs($user)->get('/order-history?page=2');
        $page2->assertOk();
        $this->assertSame(13, $user->orders()->count());
    }

    public function test_empty_order_history_shows_a_useful_empty_state(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/order-history');

        $response->assertOk()->assertSee('placed any orders')->assertSee(route('shop'), false);
    }

    public function test_view_details_links_point_at_the_real_order_route(): void
    {
        $user = User::factory()->create();
        $order = $this->order($user);

        $response = $this->actingAs($user)->get('/order-history');

        $response->assertOk()->assertSee(route('orders.show', $order), false);
    }

    // ------------------------------------------------------------------
    // Legacy cleanup
    // ------------------------------------------------------------------

    public function test_legacy_order_details_route_redirects_to_order_history(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/order-details');

        $response->assertRedirect(route('order-history'));
    }
}
