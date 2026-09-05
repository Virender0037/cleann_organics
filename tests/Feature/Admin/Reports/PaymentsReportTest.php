<?php

namespace Tests\Feature\Admin\Reports;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentsReportTest extends TestCase
{
    use RefreshDatabase;

    private function superadmin(): User
    {
        return User::factory()->create(['role' => 'superadmin']);
    }

    private function order(User $user): Order
    {
        static $n = 0;
        $n++;

        return Order::create([
            'user_id' => $user->id,
            'order_number' => sprintf('ORD-PAY-%06d', $n),
            'subtotal' => 100,
            'discount_amount' => 0,
            'shipping_amount' => 0,
            'tax_amount' => 0,
            'grand_total' => 100,
            'payment_method' => 'upi',
            'payment_status' => 'pending',
            'order_status' => 'pending',
            'billing_same_as_shipping' => true,
            'shipping_name' => 'Jane Doe',
        ]);
    }

    private function payment(Order $order, string $status, float $amount, array $overrides = []): Payment
    {
        return Payment::create(array_merge([
            'order_id' => $order->id,
            'transaction_id' => 'TXN-'.$order->id,
            'payment_method' => 'upi',
            'amount' => $amount,
            'status' => $status,
        ], $overrides));
    }

    public function test_customer_is_denied(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'customer']))
            ->get(route('admin.reports.payments.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_totals_use_payment_amount_by_status(): void
    {
        $user = User::factory()->create();
        $this->payment($this->order($user), 'paid', 500);
        $this->payment($this->order($user), 'paid', 250);
        $this->payment($this->order($user), 'pending', 100);
        $this->payment($this->order($user), 'failed', 999);
        $this->payment($this->order($user), 'refunded', 60);

        $response = $this->actingAs($this->superadmin())->get(route('admin.reports.payments.index'));

        $response->assertOk();
        $metrics = $response->viewData('metrics');
        $this->assertSame(750.0, $metrics['collected']);
        $this->assertSame(100.0, $metrics['pending']);
        $this->assertSame(999.0, $metrics['failed']);
        $this->assertSame(1, $metrics['failed_count']);
        $this->assertSame(60.0, $metrics['refunded']);
    }

    public function test_collected_matches_sum_of_paid_payment_amounts(): void
    {
        $user = User::factory()->create();
        $this->payment($this->order($user), 'paid', 120);
        $this->payment($this->order($user), 'paid', 80);

        $response = $this->actingAs($this->superadmin())->get(route('admin.reports.payments.index'));

        $expected = (float) Payment::where('status', 'paid')->sum('amount');
        $this->assertSame($expected, $response->viewData('metrics')['collected']);
    }

    public function test_status_filter(): void
    {
        $user = User::factory()->create();
        $this->payment($this->order($user), 'paid', 100, ['transaction_id' => 'TXN-PAID']);
        $this->payment($this->order($user), 'failed', 100, ['transaction_id' => 'TXN-FAILED']);

        $response = $this->actingAs($this->superadmin())
            ->get(route('admin.reports.payments.index', ['status' => 'paid']));

        $response->assertSee('TXN-PAID')->assertDontSee('TXN-FAILED');
    }

    public function test_search_matches_transaction_and_customer(): void
    {
        $alice = User::factory()->create(['name' => 'Alice Payer']);
        $bob = User::factory()->create(['name' => 'Bob Payer']);
        $this->payment($this->order($alice), 'paid', 100, ['transaction_id' => 'TXN-ALICE']);
        $this->payment($this->order($bob), 'paid', 100, ['transaction_id' => 'TXN-BOB']);

        $response = $this->actingAs($this->superadmin())
            ->get(route('admin.reports.payments.index', ['search' => 'Alice']));

        $response->assertSee('TXN-ALICE')->assertDontSee('TXN-BOB');
    }

    public function test_invalid_status_rejected(): void
    {
        $this->actingAs($this->superadmin())
            ->get(route('admin.reports.payments.index', ['status' => 'bogus']))
            ->assertSessionHasErrors('status');
    }

    public function test_export_returns_csv(): void
    {
        $user = User::factory()->create(['name' => 'Csv Payer']);
        $this->payment($this->order($user), 'paid', 175, ['transaction_id' => 'TXN-CSV-1']);

        $response = $this->actingAs($this->superadmin())->get(route('admin.reports.payments.export'));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
        $content = $response->streamedContent();
        $this->assertStringContainsString('TXN-CSV-1', $content);
        $this->assertStringContainsString('175.00', $content);
    }
}
