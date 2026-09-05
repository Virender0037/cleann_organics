<?php

namespace Tests\Feature\Storefront;

use App\Models\Address;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function order(User $user, string $status = 'pending', array $overrides = []): Order
    {
        return Order::create(array_merge([
            'user_id' => $user->id,
            'order_number' => 'ORD-'.now()->format('Ymd').'-'.strtoupper(fake()->bothify('??####')),
            'subtotal' => 100,
            'discount_amount' => 0,
            'shipping_amount' => 0,
            'tax_amount' => 0,
            'grand_total' => 100,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'order_status' => $status,
            'billing_same_as_shipping' => true,
            'shipping_name' => 'Jane Doe',
        ], $overrides));
    }

    public function test_guest_is_redirected_from_the_dashboard(): void
    {
        $this->get('/user-dashboard')->assertRedirect(route('sign-in'));
    }

    public function test_authenticated_customer_sees_the_dashboard(): void
    {
        $user = User::factory()->create(['name' => 'Priya Sharma']);

        $response = $this->actingAs($user)->get('/user-dashboard');

        $response->assertOk()->assertSee('Hello, Priya Sharma');
        $response->assertSee('noindex', false);
    }

    public function test_dashboard_metrics_are_scoped_to_the_authenticated_customer(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->order($user, 'pending');
        $this->order($user, 'shipped');
        $this->order($user, 'delivered');
        $this->order($user, 'cancelled');
        // Another customer's orders must not count.
        $this->order($other, 'delivered');
        $this->order($other, 'pending');

        Address::create(['user_id' => $user->id, 'type' => 'shipping', 'name' => 'H', 'phone' => '1', 'address_line_1' => 'L', 'city' => 'C', 'state' => 'S', 'country' => 'India', 'pincode' => '1', 'is_default' => true]);

        $response = $this->actingAs($user)->get('/user-dashboard');

        $response->assertOk();
        // total 4, active 2 (pending + shipped), delivered 1, cancelled 1, addresses 1
        $response->assertSeeInOrder(['Total Orders']);
        $this->assertSame(4, $user->orders()->count());
        $this->assertSame(2, $user->orders()->whereNotIn('order_status', ['delivered', 'cancelled'])->count());
    }

    public function test_recent_orders_are_scoped_to_the_authenticated_customer(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $mine = $this->order($user, 'pending', ['order_number' => 'ORD-MINE-000001']);
        $theirs = $this->order($other, 'pending', ['order_number' => 'ORD-THEIRS-0001']);

        $response = $this->actingAs($user)->get('/user-dashboard');

        $response->assertOk();
        $response->assertSee('ORD-MINE-000001');
        $response->assertDontSee('ORD-THEIRS-0001');
    }

    public function test_dashboard_shows_an_empty_state_with_no_orders(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/user-dashboard');

        $response->assertOk()->assertSee('placed any orders');
    }
}
