<?php

namespace Tests\Feature\Admin;

use App\Models\ContactMessage;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\ReturnRequest;
use App\Models\ShippingMethod;
use App\Models\ShippingRate;
use App\Models\ShippingZone;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Admin audit regression tests: CRUD/validation for Coupons, Shipping, Contact Messages,
 * Returns and order status flow, plus honesty checks for the placeholder screens.
 */
class AdminModulesAuditTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'superadmin']);
    }

    private function order(?User $user = null): Order
    {
        $user ??= User::factory()->create();

        return Order::forceCreate([
            'user_id' => $user->id,
            'order_number' => 'ORD-T-'.random_int(100000, 999999),
            'subtotal' => 500, 'grand_total' => 500,
            'payment_method' => 'cod', 'payment_status' => 'pending', 'order_status' => 'pending',
        ]);
    }

    private function couponPayload(array $override = []): array
    {
        return array_merge([
            'code' => 'SAVE10', 'type' => 'percentage', 'value' => 10,
            'minimum_order_amount' => 200, 'maximum_discount_amount' => 100, 'usage_limit' => 5,
            'start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'status' => 'active',
        ], $override);
    }

    // ---------------------------------------------------------------- Coupons

    public function test_coupon_create_edit_toggle_and_delete(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.sales.coupons.store'), $this->couponPayload())
            ->assertRedirect(route('admin.sales.coupons.index'));
        $coupon = Coupon::where('code', 'SAVE10')->firstOrFail();

        $this->actingAs($admin)->put(route('admin.sales.coupons.update', $coupon), $this->couponPayload(['status' => 'inactive', 'value' => 15]))
            ->assertRedirect(route('admin.sales.coupons.index'));
        $this->assertSame('inactive', $coupon->fresh()->status);
        $this->assertEquals(15, $coupon->fresh()->value);

        $this->actingAs($admin)->get(route('admin.sales.coupons.index', ['search' => 'SAVE']))->assertOk()->assertSee('SAVE10');
        $this->actingAs($admin)->get(route('admin.sales.coupons.edit', $coupon))->assertOk();

        $this->actingAs($admin)->delete(route('admin.sales.coupons.destroy', $coupon))->assertSessionHas('success');
        $this->assertDatabaseMissing('coupons', ['code' => 'SAVE10']);
    }

    public function test_coupon_validation_rejects_bad_input(): void
    {
        $admin = $this->admin();
        Coupon::create($this->couponPayload());

        $bad = [
            'duplicate code' => $this->couponPayload(),
            'percentage over 100' => $this->couponPayload(['code' => 'A1', 'value' => 101]),
            'negative value' => $this->couponPayload(['code' => 'A2', 'value' => -1]),
            'end before start' => $this->couponPayload(['code' => 'A3', 'start_date' => '2026-06-01', 'end_date' => '2026-01-01']),
            'zero usage limit' => $this->couponPayload(['code' => 'A4', 'usage_limit' => 0]),
            'unknown type' => $this->couponPayload(['code' => 'A5', 'type' => 'bogus']),
            'unknown status' => $this->couponPayload(['code' => 'A6', 'status' => 'maybe']),
        ];

        foreach ($bad as $label => $payload) {
            $this->actingAs($admin)->post(route('admin.sales.coupons.store'), $payload)->assertSessionHasErrors(null, null, $label);
        }
        $this->assertSame(1, Coupon::count());
    }

    public function test_used_coupon_cannot_be_deleted_and_used_count_is_not_mass_assignable_from_the_form(): void
    {
        $admin = $this->admin();
        $coupon = Coupon::create($this->couponPayload());
        $order = $this->order();
        $order->forceFill(['coupon_id' => $coupon->id])->save();

        $this->actingAs($admin)->delete(route('admin.sales.coupons.destroy', $coupon))->assertSessionHas('error');
        $this->assertDatabaseHas('coupons', ['id' => $coupon->id]);

        $this->actingAs($admin)->put(route('admin.sales.coupons.update', $coupon), $this->couponPayload(['used_count' => 99]));
        $this->assertSame(0, (int) $coupon->fresh()->used_count);
    }

    // --------------------------------------------------------------- Shipping

    public function test_shipping_zone_rate_and_method_crud(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.shipping.zones.store'), [
            'name' => 'Punjab', 'state' => 'Punjab', 'zone_type' => 'state', 'status' => 'active',
        ])->assertRedirect();
        $zone = ShippingZone::where('name', 'Punjab')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.shipping.zones.store'), ['name' => 'x'])->assertSessionHasErrors(['zone_type', 'status']);

        $this->actingAs($admin)->post(route('admin.shipping.rates.store'), [
            'shipping_zone_id' => $zone->id, 'min_weight' => 0, 'max_weight' => 5, 'shipping_charge' => 49,
            'free_shipping_above' => 399, 'status' => 'active',
        ])->assertRedirect();
        $rate = ShippingRate::firstOrFail();

        $this->actingAs($admin)->post(route('admin.shipping.rates.store'), [
            'shipping_zone_id' => $zone->id, 'min_weight' => 5, 'max_weight' => 1, 'shipping_charge' => 49, 'status' => 'active',
        ])->assertSessionHasErrors('max_weight');
        $this->actingAs($admin)->post(route('admin.shipping.rates.store'), [
            'shipping_zone_id' => 9999, 'min_weight' => 0, 'shipping_charge' => 49, 'status' => 'active',
        ])->assertSessionHasErrors('shipping_zone_id');

        $this->actingAs($admin)->put(route('admin.shipping.rates.update', $rate), [
            'shipping_zone_id' => $zone->id, 'min_weight' => 0, 'max_weight' => 5, 'shipping_charge' => 59, 'status' => 'inactive',
        ])->assertRedirect();
        $this->assertEquals(59, $rate->fresh()->shipping_charge);

        $this->actingAs($admin)->post(route('admin.shipping.methods.store'), [
            'name' => 'Standard', 'code' => 'standard', 'status' => 'active',
        ])->assertRedirect();
        $this->actingAs($admin)->post(route('admin.shipping.methods.store'), [
            'name' => 'Dup', 'code' => 'standard', 'status' => 'active',
        ])->assertSessionHasErrors('code');
        $this->assertSame(1, ShippingMethod::count());

        foreach (['zones' => $zone, 'rates' => $rate] as $slug => $model) {
            $this->actingAs($admin)->get(route("admin.shipping.$slug.index"))->assertOk();
            $this->actingAs($admin)->get(route("admin.shipping.$slug.edit", $model))->assertOk();
        }
    }

    // ------------------------------------------------------- Contact messages

    public function test_contact_message_read_replied_and_delete_flow(): void
    {
        $admin = $this->admin();
        $message = ContactMessage::create(['name' => 'Asha', 'email' => 'asha@example.test', 'subject' => 'Hi', 'message' => 'Hello there', 'status' => 'unread']);

        $this->actingAs($admin)->get(route('admin.cms.contact-messages.index', ['status' => 'unread', 'search' => 'Asha']))->assertOk()->assertSee('Asha');

        $this->actingAs($admin)->get(route('admin.cms.contact-messages.show', $message))->assertOk();
        $this->assertSame('read', $message->fresh()->status);

        $this->actingAs($admin)->put(route('admin.cms.contact-messages.update-status', $message), ['status' => 'replied'])->assertSessionHas('success');
        $this->assertSame('replied', $message->fresh()->status);
        $this->actingAs($admin)->put(route('admin.cms.contact-messages.update-status', $message), ['status' => 'garbage'])->assertSessionHasErrors('status');

        $this->actingAs($admin)->delete(route('admin.cms.contact-messages.destroy', $message))->assertSessionHas('success');
        $this->assertDatabaseMissing('contact_messages', ['id' => $message->id]);
    }

    // ---------------------------------------------------------------- Returns

    public function test_return_status_can_only_be_approved_or_rejected_and_is_admin_only(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create();
        $order = $this->order($user);
        $return = ReturnRequest::create(['order_id' => $order->id, 'user_id' => $user->id, 'return_number' => 'RET-T-1', 'reason' => 'Damaged', 'status' => 'requested', 'refund_amount' => 0]);

        $this->actingAs($user)->patch(route('admin.sales.returns.status', $return), ['status' => 'approved'])->assertRedirect();
        $this->assertSame('requested', $return->fresh()->status);

        $this->actingAs($admin)->patch(route('admin.sales.returns.status', $return), ['status' => 'refunded'])->assertSessionHasErrors('status');
        $this->actingAs($admin)->patch(route('admin.sales.returns.status', $return), ['status' => 'approved'])->assertSessionHas('success');
        $this->assertSame('approved', $return->fresh()->status);
        $this->assertNotNull($return->fresh()->approved_at);
        // Approving a return is a status flag only: nothing refunds money or restores stock.
        $this->assertSame('pending', $order->fresh()->payment_status);
    }

    // ------------------------------------------------------------ Order status

    public function test_order_status_only_moves_forward_and_cannot_be_cancelled_from_the_panel(): void
    {
        $admin = $this->admin();
        $order = $this->order();

        $this->actingAs($admin)->patch(route('admin.sales.orders.status.update', $order), ['status' => 'shipped'])->assertSessionHas('success');
        $this->assertSame('shipped', $order->fresh()->order_status);

        $this->actingAs($admin)->patch(route('admin.sales.orders.status.update', $order), ['status' => 'confirmed'])->assertSessionHas('error');
        $this->assertSame('shipped', $order->fresh()->order_status);

        // There is no cancel transition in the admin panel (documented gap, not a silent failure).
        $this->actingAs($admin)->patch(route('admin.sales.orders.status.update', $order), ['status' => 'cancelled'])->assertSessionHasErrors('status');
        $this->assertSame('shipped', $order->fresh()->order_status);
    }

    // ------------------------------------------- Placeholders & settings honesty

    public function test_administration_placeholders_say_they_are_not_implemented_and_forms_are_disabled(): void
    {
        $admin = $this->admin();

        foreach (['roles.index', 'roles.create', 'roles.edit', 'permissions.index', 'activity-logs.index', 'users.create', 'users.edit'] as $name) {
            $html = $this->actingAs($admin)->get(route("admin.administration.$name"))->assertOk()->getContent();
            $this->assertStringContainsString('data-not-implemented', $html, "$name gives no not-implemented notice");
        }

        foreach (['roles.create', 'roles.edit', 'users.create', 'users.edit'] as $name) {
            $this->assertStringContainsString('<fieldset disabled>', $this->actingAs($admin)->get(route("admin.administration.$name"))->getContent(), $name);
        }
    }

    public function test_payment_settings_page_is_honest_and_never_echoes_saved_secrets(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->put(route('admin.settings.payment.update'), [
            'default_currency' => 'INR', 'currency_symbol_position' => 'before',
            'razorpay_key_id' => 'rzp_test_abc', 'razorpay_secret_key' => 'SUPER-SECRET-VALUE-123',
            'upi_id' => 'shop@upi',
        ])->assertSessionHas('success');

        $html = $this->actingAs($admin)->get(route('admin.settings.payment.index'))->assertOk()->getContent();
        $this->assertStringContainsString('data-payment-settings-note', $html);
        $this->assertStringNotContainsString('SUPER-SECRET-VALUE-123', $html);
        $this->assertStringContainsString('shop@upi', $html);
        $this->assertDatabaseMissing('settings', ['value' => 'SUPER-SECRET-VALUE-123']);
    }

    // -------------------------------------------------------- Customer scoping

    public function test_customer_admin_pages_only_show_that_customers_data(): void
    {
        $admin = $this->admin();
        $a = User::factory()->create(['name' => 'Alpha Customer']);
        $b = User::factory()->create(['name' => 'Bravo Customer']);
        $orderB = $this->order($b);

        $html = $this->actingAs($admin)->get(route('admin.customers.show', $a))->assertOk()->getContent();
        $this->assertStringContainsString('Alpha Customer', $html);
        $this->assertStringNotContainsString($orderB->order_number, $html);
        $this->assertStringNotContainsString('Bravo Customer', $html);
    }
    // ------------------------------------------- Order snapshot & report filters

    public function test_admin_order_detail_shows_the_frozen_address_not_the_customers_edited_one(): void
    {
        $admin = $this->admin();
        $customer = User::factory()->create();
        $address = \App\Models\Address::create([
            'user_id' => $customer->id, 'type' => 'shipping', 'name' => 'Asha', 'phone' => '9000000000',
            'address_line_1' => '12 Original Street', 'city' => 'Ludhiana', 'state' => 'Punjab', 'country' => 'India', 'pincode' => '141001', 'is_default' => true,
        ]);
        $order = $this->order($customer);
        $order->forceFill([
            'address_id' => $address->id, 'shipping_name' => 'Asha', 'shipping_phone' => '9000000000',
            'shipping_address_line_1' => '12 Original Street', 'shipping_city' => 'Ludhiana', 'shipping_state' => 'Punjab',
            'shipping_country' => 'India', 'shipping_pincode' => '141001', 'shipping_zone_name' => 'Punjab',
        ])->save();

        // The customer edits their saved address after ordering.
        $address->update(['address_line_1' => '99 Changed Road', 'city' => 'Delhi']);

        $html = $this->actingAs($admin)->get(route('admin.sales.orders.show', $order))->assertOk()->getContent();
        $this->assertStringContainsString('12 Original Street', $html);
        $this->assertStringContainsString('9000000000', $html);
        $this->assertStringContainsString('Shipping zone: Punjab', $html);
        $this->assertStringNotContainsString('99 Changed Road', $html);
    }

    public function test_order_and_sales_reports_can_filter_by_razorpay_and_manual_upi(): void
    {
        $admin = $this->admin();

        foreach (['razorpay', 'manual_upi', 'cod', 'bank_transfer'] as $method) {
            foreach (['admin.reports.orders.index', 'admin.reports.sales.index'] as $route) {
                $this->actingAs($admin)->get(route($route, ['payment_method' => $method]))->assertOk();
            }
        }
    }
}
