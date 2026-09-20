<?php

namespace Tests\Feature\Admin;

use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Reported on production: a Razorpay TEST order (customer page: Confirmed, RAZORPAY · Paid)
 * could not be found in Admin → Sales → Orders. This walks the customer's exact journey
 * (checkout → gateway order → browser callback → webhook) and asserts the order is
 * findable and correct on every admin surface, including every filter that could hide it.
 */
class RazorpayOrderAdminVisibilityTest extends TestCase
{
    use RefreshDatabase;

    private function placePaidRazorpayOrder(string $gatewayPaymentId = 'pay_admin123'): Order
    {
        config(['services.razorpay.key' => 'rzp_test_key', 'services.razorpay.secret' => 'test_secret', 'services.razorpay.webhook_secret' => 'wh_secret']);
        Http::fake(['https://api.razorpay.com/v1/orders' => Http::response(['id' => 'order_admin123', 'amount' => 120800, 'currency' => 'INR'], 200)]);

        $customer = User::factory()->create(['name' => 'Razor Customer', 'email' => 'razor@example.test']);
        $category = Category::create(['name' => 'Bottles', 'slug' => 'bottles-'.uniqid(), 'status' => 'active']);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Copper Travel Bottle', 'slug' => 'copper-'.uniqid(), 'status' => 'active', 'is_returnable' => false, 'return_days' => 7]);
        $variant = ProductVariant::create(['product_id' => $product->id, 'variant_name' => 'Single', 'sku' => 'CTB-1', 'enable_tiered_pricing' => false, 'single_quantity' => 1, 'single_price' => 1208, 'stock_quantity' => 10, 'low_stock_quantity' => 1, 'stock_status' => 'in_stock', 'is_default' => true, 'status' => 'active', 'sort_order' => 0]);
        $address = Address::create(['user_id' => $customer->id, 'type' => 'shipping', 'name' => 'Razor Customer', 'phone' => '9876543210', 'address_line_1' => '1 Test Road', 'city' => 'Delhi', 'state' => 'Delhi', 'country' => 'India', 'pincode' => '110001', 'is_default' => true]);

        $this->actingAs($customer);
        $this->postJson('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 1]);
        $this->post('/checkout', ['address_id' => $address->id, 'payment_method' => 'razorpay']);
        $order = Order::where('user_id', $customer->id)->firstOrFail();

        // Browser callback (customer returns from Razorpay Checkout) ...
        $this->post(route('orders.pay.verify', $order), [
            'razorpay_payment_id' => $gatewayPaymentId,
            'razorpay_order_id' => 'order_admin123',
            'razorpay_signature' => hash_hmac('sha256', 'order_admin123|'.$gatewayPaymentId, 'test_secret'),
        ]);

        // ... and the signed webhook arrives afterwards (must be a harmless duplicate).
        $body = json_encode(['event' => 'payment.captured', 'payload' => ['payment' => ['entity' => ['id' => $gatewayPaymentId, 'order_id' => 'order_admin123', 'status' => 'captured']]]]);
        $this->call('POST', '/api/webhooks/razorpay', [], [], [], ['HTTP_X-Razorpay-Signature' => hash_hmac('sha256', $body, 'wh_secret'), 'CONTENT_TYPE' => 'application/json'], $body);

        $this->app['auth']->forgetGuards();

        return $order->fresh(['payment']);
    }

    private function admin(): User
    {
        // The customer's session (with its flash message) must not leak into the admin requests.
        $this->flushSession();

        return User::factory()->create(['role' => 'superadmin']);
    }

    /** @return array<int, string> order numbers rendered as rows of the admin list */
    private function rowsOf(string $html): array
    {
        preg_match_all('~<strong>#([A-Z0-9-]+)</strong>~', $html, $m);

        return $m[1];
    }

    public function test_the_stored_razorpay_order_matches_what_the_customer_sees(): void
    {
        $order = $this->placePaidRazorpayOrder();

        $this->assertSame('razorpay', $order->payment_method);
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('confirmed', $order->order_status);
        $this->assertEquals(1208.00, (float) $order->grand_total);
        $this->assertSame('paid', $order->payment->status);
        $this->assertSame('pay_admin123', $order->payment->gateway_payment_id);
        $this->assertSame('order_admin123', $order->payment->gateway_order_id);
        $this->assertSame(1, Order::count(), 'Exactly one order — no duplicate created by callback + webhook.');
        $this->assertSame(1, Payment::count());
    }

    public function test_paid_razorpay_order_is_the_first_row_in_admin_sales_orders(): void
    {
        $order = $this->placePaidRazorpayOrder();
        // Older orders of every other method must not push it off page 1.
        foreach (['cod', 'manual_upi', 'bank_transfer'] as $i => $method) {
            Order::create(['user_id' => $order->user_id, 'order_number' => 'OLD-'.$i, 'subtotal' => 10, 'grand_total' => 10, 'payment_method' => $method, 'payment_status' => 'pending', 'order_status' => 'pending', 'created_at' => now()->subDays(2 + $i)]);
        }

        $this->actingAs($this->admin())->get('/admin/sales/orders')
            ->assertOk()->assertSeeInOrder([$order->order_number, 'RAZORPAY'])
            ->assertSee('Razor Customer')->assertSee('razor@example.test')->assertSee('₹1,208.00')->assertSee('Paid')->assertSee('Confirmed');
    }

    public function test_every_admin_filter_combination_that_should_match_returns_the_order(): void
    {
        $order = $this->placePaidRazorpayOrder();
        $admin = $this->admin();
        $today = now()->toDateString();

        $matching = [
            '' => '',
            'search=' . $order->order_number => '',
            'search='.substr($order->order_number, -5) => '',
            'search=Razor+Customer' => '',
            'search=razor@example.test' => '',
            'order_status=confirmed' => '',
            'payment_status=paid' => '',
            'order_status=confirmed&payment_status=paid' => '',
            "from={$today}&to={$today}" => '',
            'payment_method=razorpay' => '',
            "search={$order->order_number}&order_status=confirmed&payment_status=paid&from={$today}&to={$today}" => '',
        ];

        $hidden = [];

        foreach (array_keys($matching) as $query) {
            $response = $this->actingAs($admin)->get('/admin/sales/orders'.($query ? '?'.$query : ''))->assertOk();

            if (! in_array($order->order_number, $this->rowsOf($response->getContent()), true)) {
                $hidden[] = '?'.$query;
            }
        }

        $this->assertSame([], $hidden, 'Admin Sales → Orders hides the order for these queries: '.implode(' | ', $hidden));
    }

    public function test_filters_that_should_not_match_do_hide_it(): void
    {
        $order = $this->placePaidRazorpayOrder();
        $admin = $this->admin();

        $leaked = [];

        foreach (['order_status=pending', 'payment_status=pending', 'payment_method=cod', 'payment_method=bank_transfer', 'from='.now()->addDays(2)->toDateString(), 'to='.now()->subDays(2)->toDateString(), 'search=NOPE-123'] as $query) {
            $response = $this->actingAs($admin)->get('/admin/sales/orders?'.$query)->assertOk();

            if (in_array($order->order_number, $this->rowsOf($response->getContent()), true)) {
                $leaked[] = '?'.$query;
            }
        }

        $this->assertSame([], $leaked, 'These filters failed to exclude a non-matching order: '.implode(' | ', $leaked));
    }

    public function test_razorpay_order_and_payment_are_correct_on_detail_payments_print_and_export(): void
    {
        $order = $this->placePaidRazorpayOrder();
        $admin = $this->admin();

        $this->actingAs($admin)->get("/admin/sales/orders/{$order->id}")->assertOk()
            ->assertSee($order->order_number)->assertSee('Copper Travel Bottle')->assertSee('₹1,208.00');

        $this->actingAs($admin)->get('/admin/sales/payments?payment_method=razorpay&status=paid')->assertOk()
            ->assertSee($order->order_number)->assertSee('RAZORPAY');
        $this->actingAs($admin)->get('/admin/sales/payments?search='.$order->order_number)->assertOk()->assertSee($order->order_number);

        $this->actingAs($admin)->get("/admin/sales/payments/{$order->payment->id}")->assertOk()
            ->assertSee('pay_admin123')->assertSee('order_admin123');

        $this->actingAs($admin)->get("/admin/sales/orders/{$order->id}/print")->assertOk()
            ->assertSee('Razorpay (online payment)')->assertSee('pay_admin123')->assertSee('Paid');

        $csv = $this->actingAs($admin)->get('/admin/sales/orders/export')->assertOk()->streamedContent();
        $this->assertStringContainsString($order->order_number, $csv);
        $this->assertStringContainsString('razorpay', $csv);
    }

    public function test_dashboard_and_reports_count_the_paid_razorpay_order(): void
    {
        $order = $this->placePaidRazorpayOrder();

        $html = $this->actingAs($this->admin())->get('/admin/dashboard')->assertOk()->getContent();

        $this->assertStringContainsString('1,208', $html, 'Revenue should include the paid Razorpay order.');
        $this->assertStringContainsString($order->order_number, $html, 'Recent orders should list it.');
    }
}
