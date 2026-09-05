<?php

namespace Tests\Feature\Admin\Reports;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryReportTest extends TestCase
{
    use RefreshDatabase;

    private function superadmin(): User
    {
        return User::factory()->create(['role' => 'superadmin']);
    }

    private function product(): Product
    {
        $category = Category::create([
            'name' => 'Cat',
            'slug' => 'cat-'.uniqid(),
            'status' => 'active',
        ]);

        return Product::create([
            'category_id' => $category->id,
            'name' => 'Prod '.uniqid(),
            'slug' => 'prod-'.uniqid(),
            'status' => 'active',
            'is_returnable' => false,
            'return_days' => 7,
        ]);
    }

    private function variant(Product $product, array $overrides = []): ProductVariant
    {
        static $n = 0;
        $n++;

        return ProductVariant::create(array_merge([
            'product_id' => $product->id,
            'variant_name' => 'Variant '.$n,
            'sku' => 'SKU-'.$n,
            'enable_tiered_pricing' => false,
            'stock_quantity' => 50,
            'low_stock_quantity' => 5,
            'stock_status' => 'in_stock',
            'is_default' => false,
            'status' => 'active',
            'sort_order' => 0,
        ], $overrides));
    }

    public function test_customer_is_denied(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'customer']))
            ->get(route('admin.reports.inventory.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_metrics_classify_stock_correctly(): void
    {
        $product = $this->product();
        $this->variant($product, ['stock_quantity' => 50]);                       // in stock
        $this->variant($product, ['stock_quantity' => 3]);                        // low (<= threshold 5)
        $this->variant($product, ['stock_quantity' => 5]);                        // low (boundary)
        $this->variant($product, ['stock_quantity' => 0]);                        // out (qty 0)
        $this->variant($product, ['stock_quantity' => 20, 'stock_status' => 'out_of_stock']); // out (status)
        $this->variant($product, ['stock_quantity' => 99, 'status' => 'inactive']); // not counted as available

        $response = $this->actingAs($this->superadmin())->get(route('admin.reports.inventory.index'));

        $response->assertOk();
        $stats = $response->viewData('stats');
        $this->assertSame(5, $stats['active_variants']);
        $this->assertSame(1, $stats['in_stock']);
        $this->assertSame(2, $stats['low_stock']);
        $this->assertSame(2, $stats['out_of_stock']);
        $this->assertSame(1, $stats['inactive_variants']);
    }

    public function test_stock_filter_low_stock(): void
    {
        $product = $this->product();
        $this->variant($product, ['sku' => 'LOW-1', 'stock_quantity' => 2]);
        $this->variant($product, ['sku' => 'FULL-1', 'stock_quantity' => 80]);

        $response = $this->actingAs($this->superadmin())
            ->get(route('admin.reports.inventory.index', ['stock' => 'low_stock']));

        $response->assertSee('LOW-1')->assertDontSee('FULL-1');
    }

    public function test_no_date_filter_accepted_but_harmless(): void
    {
        $this->actingAs($this->superadmin())
            ->get(route('admin.reports.inventory.index', ['stock' => 'bogus']))
            ->assertSessionHasErrors('stock');
    }

    public function test_export_returns_csv(): void
    {
        $product = $this->product();
        $this->variant($product, ['sku' => 'EXP-SKU-1', 'stock_quantity' => 4]);

        $response = $this->actingAs($this->superadmin())->get(route('admin.reports.inventory.export'));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
        $content = $response->streamedContent();
        $this->assertStringContainsString('EXP-SKU-1', $content);
        $this->assertStringContainsString('low_stock', $content);
    }
}
