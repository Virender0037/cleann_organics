<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Admin → Sales → Orders → Print. The list's Print button used to be a
 * disabled placeholder with no route behind it.
 */
class OrderPrintTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'superadmin']);
    }

    private function order(array $overrides = [], array $items = []): Order
    {
        // First customer keeps a predictable email; later ones in the same test get unique ones.
        $email = User::where('email', 'asha@example.test')->exists() ? 'asha'.uniqid().'@example.test' : 'asha@example.test';
        $customer = User::factory()->create(['name' => 'Asha Verma', 'email' => $email]);

        $order = Order::create(array_merge([
            'user_id' => $customer->id,
            'order_number' => 'ORD-'.strtoupper(uniqid()),
            'subtotal' => 500, 'discount_amount' => 0, 'shipping_amount' => 0, 'tax_amount' => 76.27, 'grand_total' => 500,
            'payment_method' => 'cod', 'payment_status' => 'pending', 'order_status' => 'confirmed',
            'shipping_name' => 'Asha Verma', 'shipping_phone' => '9876543210',
            'shipping_address_line_1' => '221B Baker Street', 'shipping_address_line_2' => 'Near Park',
            'shipping_city' => 'Delhi', 'shipping_state' => 'Delhi', 'shipping_country' => 'India', 'shipping_pincode' => '110001',
            'billing_same_as_shipping' => true,
        ], $overrides));

        foreach ($items ?: [['product_name' => 'Rose Floor Cleaner', 'variant_sku' => 'RFC-500', 'variant_size' => '500 ml', 'quantity' => 2, 'unit_price' => 250, 'total_price' => 500]] as $item) {
            $order->items()->create($item);
        }

        return $order;
    }

    // ------------------------------------------------------------------
    // Access
    // ------------------------------------------------------------------

    public function test_guests_and_customers_cannot_reach_the_print_route(): void
    {
        $order = $this->order();

        $this->get("/admin/sales/orders/{$order->id}/print")->assertRedirect('/admin/login');
        $this->actingAs(User::factory()->create(['role' => 'customer']))->get("/admin/sales/orders/{$order->id}/print")->assertRedirect('/admin/login');
        // The order's own customer is still not an admin.
        $this->actingAs($order->user)->get("/admin/sales/orders/{$order->id}/print")->assertRedirect('/admin/login');
    }

    public function test_invalid_order_returns_404(): void
    {
        $this->actingAs($this->admin())->get('/admin/sales/orders/999999/print')->assertNotFound();
        $this->actingAs($this->admin())->get('/admin/sales/orders/not-a-number/print')->assertNotFound();
    }

    // ------------------------------------------------------------------
    // Content
    // ------------------------------------------------------------------

    public function test_print_page_shows_real_order_data_without_admin_chrome(): void
    {
        Setting::setMany('general', ['site_name' => 'Cleann Organics', 'company_name' => 'Cleann Organics Pvt', 'company_email' => 'hello@cleann.test', 'company_phone' => '+91 99999 11111', 'company_address' => '12 Green Street, Delhi', 'gst_number' => '07AAAAA0000A1Z5']);
        Setting::forget('general');
        $order = $this->order(['order_number' => 'ORD-PRINT-1']);

        $response = $this->actingAs($this->admin())->get("/admin/sales/orders/{$order->id}/print");

        $response->assertOk()
            ->assertSee('ORD-PRINT-1')
            ->assertSee('Asha Verma')->assertSee('asha@example.test')->assertSee('9876543210')
            ->assertSee('221B Baker Street, Near Park')->assertSee('Delhi, Delhi')->assertSee('India - 110001')
            ->assertSee('Rose Floor Cleaner')->assertSee('SKU: RFC-500')->assertSee('Size: 500 ml')
            ->assertSee('₹250.00')->assertSee('₹500.00')
            ->assertSee('Free')
            ->assertSee('Inclusive of all taxes')->assertSee('includes GST ₹76.27')
            ->assertSee('Billing address')->assertSee('Delivery address')
            ->assertSee('Cleann Organics Pvt')->assertSee('hello@cleann.test')->assertSee('GSTIN: 07AAAAA0000A1Z5')
            ->assertSee('@media print', false)
            ->assertDontSee('pc-sidebar', false)->assertDontSee('pc-header', false);
    }

    public function test_cod_order_reads_cod_to_be_paid_on_delivery(): void
    {
        $order = $this->order(['payment_method' => 'cod', 'payment_status' => 'pending']);

        $this->actingAs($this->admin())->get("/admin/sales/orders/{$order->id}/print")
            ->assertOk()->assertSee('COD — To be paid on delivery')->assertSee('Cash on Delivery (COD)');
    }

    public function test_paid_cod_order_no_longer_says_to_be_paid(): void
    {
        $order = $this->order(['payment_method' => 'cod', 'payment_status' => 'paid']);

        $this->actingAs($this->admin())->get("/admin/sales/orders/{$order->id}/print")
            ->assertOk()->assertSee('COD — Paid')->assertDontSee('To be paid on delivery');
    }

    public function test_manual_upi_order_shows_actual_method_status_and_reference(): void
    {
        $order = $this->order(['payment_method' => 'manual_upi', 'payment_status' => 'pending']);
        Payment::create(['order_id' => $order->id, 'payment_method' => 'manual_upi', 'amount' => 500, 'status' => 'rejected', 'upi_reference' => 'UTR123456789', 'admin_note' => 'Amount mismatch']);

        $this->actingAs($this->admin())->get("/admin/sales/orders/{$order->id}/print")
            ->assertOk()
            ->assertSee('UPI (manual payment)')->assertSee('Pending')
            ->assertSee('Payment record: Rejected')->assertSee('Amount mismatch')
            ->assertSee('UTR123456789')
            ->assertDontSee('To be paid on delivery');
    }

    public function test_razorpay_order_shows_gateway_reference_and_paid_status(): void
    {
        $order = $this->order(['payment_method' => 'razorpay', 'payment_status' => 'paid']);
        Payment::create(['order_id' => $order->id, 'payment_method' => 'razorpay', 'amount' => 500, 'status' => 'paid', 'gateway_payment_id' => 'pay_TEST123', 'paid_at' => now()]);

        $this->actingAs($this->admin())->get("/admin/sales/orders/{$order->id}/print")
            ->assertOk()->assertSee('Razorpay (online payment)')->assertSee('Paid')->assertSee('pay_TEST123');
    }

    public function test_bank_transfer_order_shows_manual_verification_wording(): void
    {
        $order = $this->order(['payment_method' => 'bank_transfer', 'payment_status' => 'pending']);

        $this->actingAs($this->admin())->get("/admin/sales/orders/{$order->id}/print")
            ->assertOk()->assertSee('Bank Transfer')->assertSee('Awaiting manual bank-transfer verification');
    }

    public function test_totals_show_discount_and_shipping_as_stored(): void
    {
        $order = $this->order(['subtotal' => 600, 'discount_amount' => 100, 'shipping_amount' => 49, 'tax_amount' => 0, 'grand_total' => 549]);

        $this->actingAs($this->admin())->get("/admin/sales/orders/{$order->id}/print")
            ->assertOk()->assertSee('₹600.00')->assertSee('− ₹100.00', false)->assertSee('₹49.00')->assertSee('₹549.00')
            ->assertSee('Inclusive of all taxes')->assertDontSee('includes GST');
    }

    public function test_older_order_with_tax_added_on_top_is_not_mislabelled_as_tax_inclusive(): void
    {
        // Placed before the tax-inclusive rule: grand = subtotal + tax.
        $order = $this->order(['subtotal' => 200, 'tax_amount' => 10, 'grand_total' => 210]);

        $this->actingAs($this->admin())->get("/admin/sales/orders/{$order->id}/print")
            ->assertOk()->assertSee('₹210.00')->assertSee('Tax shown above was added to the item total')->assertDontSee('Inclusive of all taxes');
    }

    public function test_different_orders_print_their_own_content(): void
    {
        $a = $this->order(['order_number' => 'ORD-AAA'], [['product_name' => 'Alpha Item', 'quantity' => 1, 'unit_price' => 500, 'total_price' => 500]]);
        $b = $this->order(['order_number' => 'ORD-BBB'], [['product_name' => 'Beta Item', 'quantity' => 1, 'unit_price' => 500, 'total_price' => 500]]);
        $admin = $this->admin();

        $this->actingAs($admin)->get("/admin/sales/orders/{$a->id}/print")->assertSee('Alpha Item')->assertSee('ORD-AAA')->assertDontSee('Beta Item');
        $this->actingAs($admin)->get("/admin/sales/orders/{$b->id}/print")->assertSee('Beta Item')->assertSee('ORD-BBB')->assertDontSee('Alpha Item');
    }

    public function test_customer_notes_and_other_text_are_escaped(): void
    {
        $order = $this->order(['notes' => "Leave at door\n<script>alert('x')</script>", 'shipping_name' => '<b>Evil</b>']);

        $html = $this->actingAs($this->admin())->get("/admin/sales/orders/{$order->id}/print")->assertOk()->getContent();

        $this->assertStringContainsString('Customer notes', $html);
        $this->assertStringContainsString('&lt;script&gt;alert(&#039;x&#039;)&lt;/script&gt;', $html);
        $this->assertStringNotContainsString("<script>alert('x')</script>", $html);
        $this->assertStringNotContainsString('<b>Evil</b>', $html);
    }

    // ------------------------------------------------------------------
    // Print dialog + entry points
    // ------------------------------------------------------------------

    public function test_print_dialog_opens_automatically_only_when_requested(): void
    {
        $order = $this->order();
        $admin = $this->admin();

        $this->actingAs($admin)->get("/admin/sales/orders/{$order->id}/print?auto=1")->assertOk()->assertSee('window.print()', false);
        // Without ?auto=1 the page has Print/Close buttons but no automatic dialog script.
        $plain = $this->actingAs($admin)->get("/admin/sales/orders/{$order->id}/print")->assertOk()->getContent();
        $this->assertStringNotContainsString("window.addEventListener('load'", $plain);
        $this->assertStringContainsString('onclick="window.print()"', $plain);
    }

    public function test_orders_list_print_button_is_a_working_new_tab_link(): void
    {
        $order = $this->order();

        $html = $this->actingAs($this->admin())->get('/admin/sales/orders')->assertOk()->getContent();

        $this->assertStringContainsString(route('admin.sales.orders.print', [$order, 'auto' => 1]), html_entity_decode($html));
        $this->assertMatchesRegularExpression('~<a href="[^"]*/print\?auto=1"\s+target="_blank"\s+rel="noopener"\s+class="btn btn-sm btn-success"~', $html);
        $this->assertDoesNotMatchRegularExpression('~<button[^>]*title="Print Invoice"[^>]*disabled~', $html);
    }

    public function test_order_detail_page_print_button_links_to_the_print_route(): void
    {
        $order = $this->order();

        $html = $this->actingAs($this->admin())->get("/admin/sales/orders/{$order->id}")->assertOk()->getContent();

        $this->assertStringContainsString("/admin/sales/orders/{$order->id}/print?auto=1", html_entity_decode($html));
    }
}
