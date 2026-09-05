<?php

namespace Tests\Feature\Admin\Reports;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesReportTest extends TestCase
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

        $order = Order::create(array_merge([
            'user_id' => $user->id,
            'order_number' => sprintf('ORD-S-%06d', $n),
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

        if (isset($overrides['created_at'])) {
            $order->forceFill(['created_at' => $overrides['created_at']])->save();
        }

        return $order;
    }

    public function test_guest_is_redirected_from_sales_report(): void
    {
        $this->get(route('admin.reports.sales.index'))->assertRedirect(route('admin.login'));
    }

    public function test_customer_is_denied_admin_access(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'customer']))
            ->get(route('admin.reports.sales.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_superadmin_is_allowed(): void
    {
        $this->actingAs($this->superadmin())
            ->get(route('admin.reports.sales.index'))
            ->assertOk();
    }

    public function test_paid_revenue_counts_only_paid_orders(): void
    {
        $user = User::factory()->create();
        $this->order($user, ['payment_status' => 'paid', 'grand_total' => 500]);
        $this->order($user, ['payment_status' => 'pending', 'grand_total' => 999]);
        $this->order($user, ['payment_status' => 'failed', 'grand_total' => 999]);
        $this->order($user, ['payment_status' => 'refunded', 'grand_total' => 999]);

        $response = $this->actingAs($this->superadmin())->get(route('admin.reports.sales.index'));

        $response->assertOk();
        $metrics = $response->viewData('metrics');
        $this->assertSame(500.0, $metrics['paid_revenue']);
        $this->assertSame(1, $metrics['paid_orders']);
    }

    public function test_placed_order_value_excludes_cancelled_only(): void
    {
        $user = User::factory()->create();
        $this->order($user, ['payment_status' => 'pending', 'grand_total' => 100]);
        $this->order($user, ['payment_status' => 'paid', 'grand_total' => 200]);
        $this->order($user, ['order_status' => 'cancelled', 'grand_total' => 9999]);

        $response = $this->actingAs($this->superadmin())->get(route('admin.reports.sales.index'));

        $this->assertSame(300.0, $response->viewData('metrics')['placed_order_value']);
    }

    public function test_refunded_value_uses_payment_status(): void
    {
        $user = User::factory()->create();
        $this->order($user, ['payment_status' => 'refunded', 'grand_total' => 250]);

        $response = $this->actingAs($this->superadmin())->get(route('admin.reports.sales.index'));

        $this->assertSame(250.0, $response->viewData('metrics')['refunded_value']);
    }

    public function test_average_order_value_is_zero_when_no_paid_orders(): void
    {
        $user = User::factory()->create();
        $this->order($user, ['payment_status' => 'pending']);

        $response = $this->actingAs($this->superadmin())->get(route('admin.reports.sales.index'));

        $this->assertSame(0.0, $response->viewData('metrics')['average_order_value']);
        $this->assertSame(0.0, $response->viewData('metrics')['paid_revenue']);
    }

    public function test_component_breakdown_sums_paid_orders(): void
    {
        $user = User::factory()->create();
        $this->order($user, [
            'payment_status' => 'paid',
            'subtotal' => 100, 'discount_amount' => 10, 'shipping_amount' => 5, 'tax_amount' => 8, 'grand_total' => 103,
        ]);
        $this->order($user, [
            'payment_status' => 'paid',
            'subtotal' => 200, 'discount_amount' => 0, 'shipping_amount' => 15, 'tax_amount' => 12, 'grand_total' => 227,
        ]);

        $response = $this->actingAs($this->superadmin())->get(route('admin.reports.sales.index'));

        $breakdown = $response->viewData('breakdown');
        $this->assertSame(300.0, $breakdown['subtotal']);
        $this->assertSame(10.0, $breakdown['discount_amount']);
        $this->assertSame(20.0, $breakdown['shipping_amount']);
        $this->assertSame(20.0, $breakdown['tax_amount']);
        $this->assertSame(330.0, $breakdown['grand_total']);
    }

    public function test_date_range_filters_paid_revenue(): void
    {
        $user = User::factory()->create();
        $this->order($user, ['payment_status' => 'paid', 'grand_total' => 100, 'created_at' => now()->subMonths(3)]);
        $this->order($user, ['payment_status' => 'paid', 'grand_total' => 400, 'created_at' => now()]);

        $response = $this->actingAs($this->superadmin())->get(route('admin.reports.sales.index', [
            'from' => now()->startOfMonth()->toDateString(),
            'to' => now()->endOfMonth()->toDateString(),
        ]));

        $this->assertSame(400.0, $response->viewData('metrics')['paid_revenue']);
    }

    public function test_export_returns_csv_with_paid_data(): void
    {
        $user = User::factory()->create();
        $this->order($user, ['payment_status' => 'paid', 'grand_total' => 150, 'created_at' => now()]);

        $response = $this->actingAs($this->superadmin())->get(route('admin.reports.sales.export'));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
        $this->assertStringContainsString('paid_revenue', $response->streamedContent());
        $this->assertStringContainsString('150.00', $response->streamedContent());
    }

    public function test_invalid_date_range_is_rejected(): void
    {
        $this->actingAs($this->superadmin())
            ->get(route('admin.reports.sales.index', ['from' => '2026-05-01', 'to' => '2026-04-01']))
            ->assertSessionHasErrors('to');
    }
}
