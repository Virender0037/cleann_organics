<?php

namespace Tests\Feature\Admin\Reports;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductsReportTest extends TestCase
{
    use RefreshDatabase;

    private function superadmin(): User
    {
        return User::factory()->create(['role' => 'superadmin']);
    }

    private function category(string $name = 'Organic Foods'): Category
    {
        return Category::create([
            'name' => $name,
            'slug' => Str::slug($name).'-'.uniqid(),
            'status' => 'active',
        ]);
    }

    private function product(Category $category, string $name = 'Organic Honey'): Product
    {
        return Product::create([
            'category_id' => $category->id,
            'name' => $name,
            'slug' => Str::slug($name).'-'.uniqid(),
            'status' => 'active',
            'is_returnable' => false,
            'return_days' => 7,
        ]);
    }

    private function order(User $user, string $paymentStatus = 'paid', ?string $createdAt = null): Order
    {
        static $n = 0;
        $n++;

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => sprintf('ORD-P-%06d', $n),
            'subtotal' => 100,
            'discount_amount' => 0,
            'shipping_amount' => 0,
            'tax_amount' => 0,
            'grand_total' => 100,
            'payment_method' => 'cod',
            'payment_status' => $paymentStatus,
            'order_status' => 'confirmed',
            'billing_same_as_shipping' => true,
            'shipping_name' => 'Jane Doe',
        ]);

        if ($createdAt) {
            $order->forceFill(['created_at' => $createdAt])->save();
        }

        return $order;
    }

    private function item(Order $order, ?Product $product, string $name, int $qty, float $total): void
    {
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product?->id,
            'product_name' => $name,
            'quantity' => $qty,
            'unit_price' => $qty > 0 ? $total / $qty : $total,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_price' => $total,
        ]);
    }

    public function test_customer_is_denied(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'customer']))
            ->get(route('admin.reports.products.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_units_sold_distinct_orders_and_revenue(): void
    {
        $user = User::factory()->create();
        $product = $this->product($this->category());

        $o1 = $this->order($user);
        $this->item($o1, $product, 'Organic Honey', 2, 200);
        $o2 = $this->order($user);
        $this->item($o2, $product, 'Organic Honey', 3, 300);

        $response = $this->actingAs($this->superadmin())->get(route('admin.reports.products.index'));

        $response->assertOk();
        $row = collect($response->viewData('rows')->items())->firstWhere('name', 'Organic Honey');
        $this->assertSame(5, $row['units_sold']);
        $this->assertSame(2, $row['order_count']);
        $this->assertSame(500.0, $row['revenue']);
    }

    public function test_only_paid_orders_are_aggregated(): void
    {
        $user = User::factory()->create();
        $product = $this->product($this->category());

        $paid = $this->order($user, 'paid');
        $this->item($paid, $product, 'Organic Honey', 1, 100);
        $pending = $this->order($user, 'pending');
        $this->item($pending, $product, 'Organic Honey', 9, 900);

        $response = $this->actingAs($this->superadmin())->get(route('admin.reports.products.index'));

        $row = collect($response->viewData('rows')->items())->firstWhere('name', 'Organic Honey');
        $this->assertSame(1, $row['units_sold']);
        $this->assertSame(100.0, $row['revenue']);
    }

    public function test_deleted_product_still_represented_by_snapshot_name(): void
    {
        $user = User::factory()->create();
        $order = $this->order($user);
        $this->item($order, null, 'Discontinued Tea', 4, 160);

        $response = $this->actingAs($this->superadmin())->get(route('admin.reports.products.index'));

        $row = collect($response->viewData('rows')->items())->firstWhere('name', 'Discontinued Tea');
        $this->assertNotNull($row);
        $this->assertNull($row['product_id']);
        $this->assertSame(4, $row['units_sold']);
    }

    public function test_category_filter(): void
    {
        $user = User::factory()->create();
        $foods = $this->category('Foods');
        $oils = $this->category('Oils');
        $honey = $this->product($foods, 'Honey');
        $oil = $this->product($oils, 'Oil');

        $o = $this->order($user);
        $this->item($o, $honey, 'Honey', 1, 50);
        $this->item($o, $oil, 'Oil', 1, 70);

        $response = $this->actingAs($this->superadmin())
            ->get(route('admin.reports.products.index', ['category_id' => $foods->id]));

        $names = collect($response->viewData('rows')->items())->pluck('name');
        $this->assertTrue($names->contains('Honey'));
        $this->assertFalse($names->contains('Oil'));
    }

    public function test_date_filter(): void
    {
        $user = User::factory()->create();
        $product = $this->product($this->category());

        $old = $this->order($user, 'paid', now()->subMonths(4)->toDateTimeString());
        $this->item($old, $product, 'Organic Honey', 7, 700);
        $recent = $this->order($user, 'paid', now()->toDateTimeString());
        $this->item($recent, $product, 'Organic Honey', 1, 100);

        $response = $this->actingAs($this->superadmin())->get(route('admin.reports.products.index', [
            'from' => now()->subMonth()->toDateString(),
        ]));

        $row = collect($response->viewData('rows')->items())->firstWhere('name', 'Organic Honey');
        $this->assertSame(1, $row['units_sold']);
    }

    public function test_export_returns_csv(): void
    {
        $user = User::factory()->create();
        $product = $this->product($this->category());
        $o = $this->order($user);
        $this->item($o, $product, 'Organic Honey', 2, 200);

        $response = $this->actingAs($this->superadmin())->get(route('admin.reports.products.export'));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
        $this->assertStringContainsString('Organic Honey', $response->streamedContent());
    }
}
