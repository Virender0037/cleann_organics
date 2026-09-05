<?php

namespace Tests\Feature\Admin\Reports;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class CouponsReportTest extends TestCase
{
    use RefreshDatabase;

    private function superadmin(): User
    {
        return User::factory()->create(['role' => 'superadmin']);
    }

    private function coupon(array $overrides = []): Coupon
    {
        static $n = 0;
        $n++;

        return Coupon::create(array_merge([
            'code' => 'CODE'.$n,
            'type' => 'percentage',
            'value' => 10,
            'minimum_order_amount' => 0,
            'usage_limit' => null,
            'used_count' => 0,
            'start_date' => now()->subDay(),
            'end_date' => now()->addMonth(),
            'status' => 'active',
        ], $overrides));
    }

    private function order(User $user, Coupon $coupon, string $paymentStatus, float $discount, float $grandTotal): Order
    {
        static $n = 0;
        $n++;

        return Order::create([
            'user_id' => $user->id,
            'coupon_id' => $coupon->id,
            'order_number' => sprintf('ORD-C-%06d', $n),
            'subtotal' => $grandTotal + $discount,
            'discount_amount' => $discount,
            'shipping_amount' => 0,
            'tax_amount' => 0,
            'grand_total' => $grandTotal,
            'payment_method' => 'cod',
            'payment_status' => $paymentStatus,
            'order_status' => 'confirmed',
            'billing_same_as_shipping' => true,
            'shipping_name' => 'Jane Doe',
        ]);
    }

    private function rowFor(TestResponse $response, string $code): ?array
    {
        return collect($response->viewData('rows')->items())->firstWhere('code', $code);
    }

    public function test_customer_is_denied(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'customer']))
            ->get(route('admin.reports.coupons.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_derived_state(): void
    {
        $this->coupon(['code' => 'ACT', 'status' => 'active', 'start_date' => now()->subDay(), 'end_date' => now()->addDay()]);
        $this->coupon(['code' => 'UP', 'status' => 'active', 'start_date' => now()->addWeek(), 'end_date' => now()->addMonth()]);
        $this->coupon(['code' => 'EXP', 'status' => 'active', 'start_date' => now()->subMonth(), 'end_date' => now()->subDay()]);
        $this->coupon(['code' => 'INACT', 'status' => 'inactive']);

        $response = $this->actingAs($this->superadmin())->get(route('admin.reports.coupons.index'));

        $response->assertOk();
        $this->assertSame('active', $this->rowFor($response, 'ACT')['state']);
        $this->assertSame('upcoming', $this->rowFor($response, 'UP')['state']);
        $this->assertSame('expired', $this->rowFor($response, 'EXP')['state']);
        $this->assertSame('inactive', $this->rowFor($response, 'INACT')['state']);
    }

    public function test_order_count_discount_and_paid_revenue(): void
    {
        $user = User::factory()->create();
        $coupon = $this->coupon(['code' => 'PROMO']);

        $this->order($user, $coupon, 'paid', 20, 180);
        $this->order($user, $coupon, 'paid', 30, 270);
        $this->order($user, $coupon, 'pending', 50, 450);

        $response = $this->actingAs($this->superadmin())->get(route('admin.reports.coupons.index'));

        $row = $this->rowFor($response, 'PROMO');
        $this->assertSame(3, $row['orders_count']);
        $this->assertSame(100.0, $row['discount_generated']); // all coupon orders
        $this->assertSame(450.0, $row['paid_revenue']);       // paid orders only
    }

    public function test_type_filter(): void
    {
        $this->coupon(['code' => 'PCT', 'type' => 'percentage']);
        $this->coupon(['code' => 'FIX', 'type' => 'fixed']);

        $response = $this->actingAs($this->superadmin())
            ->get(route('admin.reports.coupons.index', ['type' => 'fixed']));

        $codes = collect($response->viewData('rows')->items())->pluck('code');
        $this->assertTrue($codes->contains('FIX'));
        $this->assertFalse($codes->contains('PCT'));
    }

    public function test_state_filter(): void
    {
        $this->coupon(['code' => 'LIVE', 'status' => 'active', 'start_date' => now()->subDay(), 'end_date' => now()->addDay()]);
        $this->coupon(['code' => 'DEAD', 'status' => 'inactive']);

        $response = $this->actingAs($this->superadmin())
            ->get(route('admin.reports.coupons.index', ['state' => 'inactive']));

        $codes = collect($response->viewData('rows')->items())->pluck('code');
        $this->assertTrue($codes->contains('DEAD'));
        $this->assertFalse($codes->contains('LIVE'));
    }

    public function test_search_by_code(): void
    {
        $this->coupon(['code' => 'FINDME10']);
        $this->coupon(['code' => 'HIDDEN20']);

        $response = $this->actingAs($this->superadmin())
            ->get(route('admin.reports.coupons.index', ['search' => 'FINDME']));

        $response->assertSee('FINDME10')->assertDontSee('HIDDEN20');
    }

    public function test_invalid_state_rejected(): void
    {
        $this->actingAs($this->superadmin())
            ->get(route('admin.reports.coupons.index', ['state' => 'bogus']))
            ->assertSessionHasErrors('state');
    }

    public function test_export_returns_csv(): void
    {
        $user = User::factory()->create();
        $coupon = $this->coupon(['code' => 'CSVCOUP']);
        $this->order($user, $coupon, 'paid', 15, 135);

        $response = $this->actingAs($this->superadmin())->get(route('admin.reports.coupons.export'));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
        $this->assertStringContainsString('CSVCOUP', $response->streamedContent());
    }
}
