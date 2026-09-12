<?php

namespace Tests\Feature\Storefront;

use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ManualUpiPaymentTest extends TestCase
{
    use RefreshDatabase;

    private function category(): Category
    {
        return Category::create([
            'name' => 'Fruits',
            'slug' => 'fruits-'.uniqid(),
            'status' => 'active',
        ]);
    }

    private function product(): Product
    {
        return Product::create([
            'category_id' => $this->category()->id,
            'name' => 'Green Apple',
            'slug' => 'green-apple-'.uniqid(),
            'status' => 'active',
            'is_returnable' => false,
            'return_days' => 7,
        ]);
    }

    private function variant(Product $product): ProductVariant
    {
        return $product->variants()->create([
            'variant_name' => 'Variant',
            'enable_tiered_pricing' => false,
            'single_quantity' => 1,
            'single_price' => 100.00,
            'stock_quantity' => 10,
            'low_stock_quantity' => 5,
            'stock_status' => 'in_stock',
            'is_default' => true,
            'status' => 'active',
            'sort_order' => 0,
            'weight' => 1.00,
        ]);
    }

    private function address(User $user): Address
    {
        return Address::create([
            'user_id' => $user->id,
            'type' => 'shipping',
            'name' => 'Jane Doe',
            'phone' => '9876543210',
            'address_line_1' => '221B Baker Street',
            'city' => 'Delhi',
            'state' => 'Delhi',
            'country' => 'India',
            'pincode' => '110001',
            'is_default' => true,
        ]);
    }

    private function configureManualUpi(string $upiId = 'merchant@upi'): void
    {
        Setting::setMany('payment', ['upi_id' => $upiId, 'enable_upi' => '1']);
        Setting::forget('payment');
    }

    /** Places a manual_upi order for the given user via a fresh cart + address, returns the Order. */
    private function placeManualUpiOrder(User $user): Order
    {
        $address = $this->address($user);
        $variant = $this->variant($this->product());

        $this->actingAs($user);
        $this->postJson('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 1]);

        $this->post('/checkout', [
            'address_id' => $address->id,
            'payment_method' => 'manual_upi',
        ]);

        return Order::where('user_id', $user->id)->latest()->firstOrFail();
    }

    public function test_placing_a_manual_upi_order_redirects_to_the_pay_upi_page(): void
    {
        $this->configureManualUpi();

        $user = User::factory()->create();
        $order = $this->placeManualUpiOrder($user);

        $this->assertSame('manual_upi', $order->payment_method);
        $this->assertSame('pending', $order->payment_status);
        $this->assertSame('pending', $order->payment->status);

        $this->get(route('orders.manual-upi.pay', $order))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('merchant@upi');
    }

    public function test_pay_upi_page_shows_graceful_message_when_no_upi_id_is_configured(): void
    {
        // Deliberately not calling configureManualUpi() — no upi_id set.
        $user = User::factory()->create();
        $order = $this->placeManualUpiOrder($user);

        $this->get(route('orders.manual-upi.pay', $order))
            ->assertOk()
            ->assertSee("isn't available right now", false);
    }

    public function test_pay_upi_page_404s_for_a_non_manual_upi_order(): void
    {
        $user = User::factory()->create();
        $address = $this->address($user);
        $variant = $this->variant($this->product());

        $this->actingAs($user);
        $this->postJson('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 1]);
        $this->post('/checkout', ['address_id' => $address->id, 'payment_method' => 'cod']);

        $order = Order::where('user_id', $user->id)->latest()->firstOrFail();

        $this->get(route('orders.manual-upi.pay', $order))->assertNotFound();
    }

    public function test_customer_cannot_view_another_customers_pay_upi_page(): void
    {
        $this->configureManualUpi();

        $owner = User::factory()->create();
        $order = $this->placeManualUpiOrder($owner);

        $intruder = User::factory()->create();
        $this->actingAs($intruder)->get(route('orders.manual-upi.pay', $order))->assertNotFound();
    }

    public function test_submitting_a_reference_without_a_screenshot_stores_it_and_stays_pending(): void
    {
        $this->configureManualUpi();

        $user = User::factory()->create();
        $order = $this->placeManualUpiOrder($user);

        $response = $this->post(route('orders.manual-upi.pay.store', $order), [
            'upi_reference' => 'UTR123456789',
        ]);

        $response->assertRedirect(route('orders.show', $order));

        $order->refresh();
        $this->assertSame('UTR123456789', $order->payment->upi_reference);
        $this->assertNotNull($order->payment->submitted_at);
        $this->assertNull($order->payment->proof_path);
        // Never auto-marked paid — screenshot is optional, verification is manual.
        $this->assertSame('pending', $order->payment_status);
        $this->assertSame('pending', $order->payment->status);
    }

    public function test_submitting_a_screenshot_stores_it_privately_and_not_on_the_public_disk(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $this->configureManualUpi();

        $user = User::factory()->create();
        $order = $this->placeManualUpiOrder($user);

        $screenshot = UploadedFile::fake()->image('proof.jpg', 400, 400);

        $this->post(route('orders.manual-upi.pay.store', $order), [
            'upi_reference' => 'UTR999',
            'screenshot' => $screenshot,
        ]);

        $order->refresh();
        $this->assertNotNull($order->payment->proof_path);
        $this->assertStringStartsWith('manual-upi-proofs/', $order->payment->proof_path);
        // The stored filename is a server-generated UUID, not the client's original name.
        $this->assertStringNotContainsString('proof.jpg', $order->payment->proof_path);

        Storage::disk('local')->assertExists($order->payment->proof_path);
        Storage::disk('public')->assertMissing($order->payment->proof_path);
    }

    public function test_screenshot_upload_rejects_a_disguised_non_image_file(): void
    {
        Storage::fake('local');
        $this->configureManualUpi();

        $user = User::factory()->create();
        $order = $this->placeManualUpiOrder($user);

        $fakeExecutable = UploadedFile::fake()->create('shell.jpg', 10, 'application/x-php');

        $response = $this->post(route('orders.manual-upi.pay.store', $order), [
            'upi_reference' => 'UTR999',
            'screenshot' => $fakeExecutable,
        ]);

        $response->assertSessionHasErrors('screenshot');
        $this->assertNull($order->fresh()->payment->proof_path);
    }

    public function test_resubmitting_a_new_screenshot_deletes_the_previous_orphan_file(): void
    {
        Storage::fake('local');
        $this->configureManualUpi();

        $user = User::factory()->create();
        $order = $this->placeManualUpiOrder($user);

        $this->post(route('orders.manual-upi.pay.store', $order), [
            'upi_reference' => 'UTR-first',
            'screenshot' => UploadedFile::fake()->image('first.jpg'),
        ]);
        $firstPath = $order->fresh()->payment->proof_path;
        Storage::disk('local')->assertExists($firstPath);

        $this->post(route('orders.manual-upi.pay.store', $order), [
            'upi_reference' => 'UTR-second',
            'screenshot' => UploadedFile::fake()->image('second.jpg'),
        ]);
        $secondPath = $order->fresh()->payment->proof_path;

        $this->assertNotSame($firstPath, $secondPath);
        Storage::disk('local')->assertMissing($firstPath);
        Storage::disk('local')->assertExists($secondPath);
    }

    public function test_order_owner_can_view_their_own_proof_but_a_stranger_cannot(): void
    {
        Storage::fake('local');
        $this->configureManualUpi();

        $owner = User::factory()->create();
        $order = $this->placeManualUpiOrder($owner);

        $this->post(route('orders.manual-upi.pay.store', $order), [
            'upi_reference' => 'UTR123',
            'screenshot' => UploadedFile::fake()->image('proof.jpg'),
        ]);

        $this->actingAs($owner)->get(route('orders.manual-upi.proof', $order))->assertOk();

        $stranger = User::factory()->create();
        $this->actingAs($stranger)->get(route('orders.manual-upi.proof', $order))->assertNotFound();
    }

    public function test_admin_verify_marks_payment_paid_and_confirms_order(): void
    {
        $this->configureManualUpi();

        $user = User::factory()->create();
        $order = $this->placeManualUpiOrder($user);
        $this->post(route('orders.manual-upi.pay.store', $order), ['upi_reference' => 'UTR-VERIFY']);

        $admin = User::factory()->create(['role' => 'superadmin']);
        $response = $this->actingAs($admin)->patch(route('admin.sales.payments.verify', $order->payment), [
            'admin_note' => 'Matched bank statement',
        ]);

        $response->assertRedirect(route('admin.sales.payments.show', $order->payment));

        $order->refresh();
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('confirmed', $order->order_status);
        $this->assertSame('paid', $order->payment->status);
        $this->assertSame('UTR-VERIFY', $order->payment->transaction_id);
        $this->assertNotNull($order->payment->paid_at);
    }

    public function test_admin_verify_is_idempotent_on_duplicate_calls(): void
    {
        $this->configureManualUpi();

        $user = User::factory()->create();
        $order = $this->placeManualUpiOrder($user);
        $this->post(route('orders.manual-upi.pay.store', $order), ['upi_reference' => 'UTR-DUP']);

        $admin = User::factory()->create(['role' => 'superadmin']);
        $this->actingAs($admin)->patch(route('admin.sales.payments.verify', $order->payment));
        $firstPaidAt = $order->fresh()->payment->paid_at;

        $this->actingAs($admin)->patch(route('admin.sales.payments.verify', $order->payment));

        $order->refresh();
        $this->assertSame('paid', $order->payment->status);
        $this->assertTrue($firstPaidAt->equalTo($order->payment->paid_at));
    }

    public function test_admin_reject_requires_a_note_and_lets_the_customer_resubmit(): void
    {
        Storage::fake('local');
        $this->configureManualUpi();

        $user = User::factory()->create();
        $order = $this->placeManualUpiOrder($user);
        $this->post(route('orders.manual-upi.pay.store', $order), ['upi_reference' => 'UTR-BAD']);

        $admin = User::factory()->create(['role' => 'superadmin']);

        // No note — rejected.
        $this->actingAs($admin)
            ->patch(route('admin.sales.payments.reject', $order->payment))
            ->assertSessionHasErrors('admin_note');

        $this->actingAs($admin)->patch(route('admin.sales.payments.reject', $order->payment), [
            'admin_note' => 'Reference does not match any received payment.',
        ]);

        $order->refresh();
        $this->assertSame('rejected', $order->payment->status);
        $this->assertSame('pending', $order->payment_status);
        $this->assertNotNull($order->payment->rejected_at);

        // Customer resubmits.
        $this->actingAs($user)->post(route('orders.manual-upi.pay.store', $order), [
            'upi_reference' => 'UTR-CORRECTED',
        ]);

        $order->refresh();
        $this->assertSame('pending', $order->payment->status);
        $this->assertSame('UTR-CORRECTED', $order->payment->upi_reference);
        $this->assertNull($order->payment->rejected_at);
    }

    public function test_verify_and_reject_404_for_a_non_manual_upi_payment(): void
    {
        $user = User::factory()->create();
        $address = $this->address($user);
        $variant = $this->variant($this->product());

        $this->actingAs($user);
        $this->postJson('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 1]);
        $this->post('/checkout', ['address_id' => $address->id, 'payment_method' => 'cod']);
        $order = Order::where('user_id', $user->id)->latest()->firstOrFail();

        $admin = User::factory()->create(['role' => 'superadmin']);
        $this->actingAs($admin)->patch(route('admin.sales.payments.verify', $order->payment))->assertNotFound();
        $this->actingAs($admin)->patch(route('admin.sales.payments.reject', $order->payment), ['admin_note' => 'x'])->assertNotFound();
    }

    public function test_place_order_still_accepts_upi_and_bank_transfer(): void
    {
        $user = User::factory()->create();
        $address = $this->address($user);
        $variant = $this->variant($this->product());

        $this->actingAs($user);
        $this->postJson('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 1]);

        $response = $this->post('/checkout', ['address_id' => $address->id, 'payment_method' => 'bank_transfer']);
        $order = Order::where('user_id', $user->id)->latest()->firstOrFail();

        $response->assertRedirect(route('orders.show', $order));
        $this->assertSame('bank_transfer', $order->payment_method);
    }
}
