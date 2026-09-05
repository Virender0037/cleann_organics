<?php

namespace Tests\Feature\Admin\Reports;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrdersReportTest extends TestCase
{
    use RefreshDatabase;

    private function superadmin(): User
    {
        return User::factory()->create(['role' => 'superadmin']);
    }

    private function order(User $user, array $overrides = []): Order
    {
        static $n = 0;
        $n++;

        return Order::create(array_merge([
            'user_id' => $user->id,
            'order_number' => sprintf('ORD-O-%06d', $n),
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

    public function test_guest_is_redirected(): void
    {
        $this->get(route('admin.reports.orders.index'))->assertRedirect(route('admin.login'));
    }

    public function test_customer_is_denied(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'customer']))
            ->get(route('admin.reports.orders.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_stats_reflect_real_orders(): void
    {
        $user = User::factory()->create();
        $this->order($user, ['order_status' => 'delivered']);
        $this->order($user, ['order_status' => 'shipped']);
        $this->order($user, ['order_status' => 'pending']);
        $this->order($user, ['order_status' => 'cancelled']);

        $response = $this->actingAs($this->superadmin())->get(route('admin.reports.orders.index'));

        $response->assertOk();
        $stats = $response->viewData('stats');
        $this->assertSame(4, $stats['total']);
        $this->assertSame(1, $stats['delivered']);
        $this->assertSame(2, $stats['in_progress']);
        $this->assertSame(1, $stats['cancelled']);
    }

    public function test_search_matches_order_number_and_customer(): void
    {
        $alice = User::factory()->create(['name' => 'Alice Findme', 'email' => 'alice@example.com']);
        $bob = User::factory()->create(['name' => 'Bob Other']);
        $this->order($alice, ['order_number' => 'ORD-ALICE-1']);
        $this->order($bob, ['order_number' => 'ORD-BOB-1']);

        $response = $this->actingAs($this->superadmin())
            ->get(route('admin.reports.orders.index', ['search' => 'Findme']));

        $response->assertOk()->assertSee('ORD-ALICE-1')->assertDontSee('ORD-BOB-1');
    }

    public function test_status_filter_narrows_results(): void
    {
        $user = User::factory()->create();
        $this->order($user, ['order_number' => 'ORD-DEL', 'order_status' => 'delivered']);
        $this->order($user, ['order_number' => 'ORD-PEND', 'order_status' => 'pending']);

        $response = $this->actingAs($this->superadmin())
            ->get(route('admin.reports.orders.index', ['order_status' => 'delivered']));

        $response->assertSee('ORD-DEL')->assertDontSee('ORD-PEND');
    }

    public function test_invalid_status_is_rejected(): void
    {
        $this->actingAs($this->superadmin())
            ->get(route('admin.reports.orders.index', ['order_status' => 'bogus']))
            ->assertSessionHasErrors('order_status');
    }

    public function test_pagination_present_with_many_orders(): void
    {
        $user = User::factory()->create();
        for ($i = 0; $i < 20; $i++) {
            $this->order($user);
        }

        $response = $this->actingAs($this->superadmin())->get(route('admin.reports.orders.index'));

        $response->assertOk()->assertSee('page=2', false);
    }

    public function test_empty_state_shown(): void
    {
        $this->actingAs($this->superadmin())
            ->get(route('admin.reports.orders.index'))
            ->assertOk()
            ->assertSee('No orders found');
    }

    public function test_export_returns_csv(): void
    {
        $user = User::factory()->create(['name' => 'Export Cust']);
        $this->order($user, ['order_number' => 'ORD-EXP-1']);

        $response = $this->actingAs($this->superadmin())->get(route('admin.reports.orders.export'));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
        $content = $response->streamedContent();
        $this->assertStringContainsString('ORD-EXP-1', $content);
        $this->assertStringContainsString('Export Cust', $content);
    }
}
