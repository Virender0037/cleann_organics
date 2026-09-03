<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\TaxRate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use Tests\TestCase;

/**
 * Uses literal relative paths (not the route() helper) for request URLs,
 * matching the rest of this test suite's convention — APP_URL points at a
 * XAMPP subdirectory (/cleann_organics/public), which route() bakes into
 * generated URLs but the test HTTP kernel expects app-root-relative paths.
 */
class AdminCsvXlsxImportTest extends TestCase
{
    use RefreshDatabase;

    private const CATEGORIES_IMPORT = '/admin/catalog/categories/import';

    private const CATEGORIES_TEMPLATE = '/admin/catalog/categories/import/template';

    private const PRODUCTS_IMPORT = '/admin/catalog/products/import';

    private const PRODUCTS_TEMPLATE = '/admin/catalog/products/import/template';

    private const VARIANTS_IMPORT = '/admin/catalog/variants/import';

    private const VARIANTS_TEMPLATE = '/admin/catalog/variants/import/template';

    private function csvFile(array $rows, bool $withBom = false): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'import').'.csv';
        $handle = fopen($path, 'w');

        if ($withBom) {
            fwrite($handle, "\xEF\xBB\xBF");
        }

        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        fclose($handle);

        return new UploadedFile($path, basename($path), 'text/csv', null, true);
    }

    private function xlsxFile(array $rows): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'import').'.xlsx';

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($rows as $rowIndex => $row) {
            foreach ($row as $colIndex => $value) {
                $sheet->setCellValue([$colIndex + 1, $rowIndex + 1], $value);
            }
        }

        (new XlsxWriter($spreadsheet))->save($path);

        return new UploadedFile($path, basename($path), 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }

    // ---------- Categories ----------

    public function test_category_import_valid_csv(): void
    {
        $file = $this->csvFile([
            ['name', 'status', 'slug'],
            ['Organic Foods', 'active', 'organic-foods'],
        ]);

        $response = $this->post(self::CATEGORIES_IMPORT, ['file' => $file]);

        $response->assertRedirect(self::CATEGORIES_IMPORT);
        $response->assertSessionHas('import_results.success', 1);
        $this->assertDatabaseHas('categories', ['slug' => 'organic-foods', 'status' => 'active']);
    }

    public function test_category_import_bom_csv_does_not_break_required_column_check(): void
    {
        $file = $this->csvFile([
            ['name', 'status'],
            ['Organic Oils', 'active'],
        ], withBom: true);

        $response = $this->post(self::CATEGORIES_IMPORT, ['file' => $file]);

        $response->assertRedirect(self::CATEGORIES_IMPORT);
        $response->assertSessionMissing('error');
        $response->assertSessionHas('import_results.success', 1);
        $this->assertDatabaseHas('categories', ['name' => 'Organic Oils']);
    }

    public function test_category_import_valid_xlsx(): void
    {
        $file = $this->xlsxFile([
            ['name', 'status', 'slug'],
            ['Organic Spices', 'active', 'organic-spices'],
        ]);

        $response = $this->post(self::CATEGORIES_IMPORT, ['file' => $file]);

        $response->assertRedirect(self::CATEGORIES_IMPORT);
        $response->assertSessionHas('import_results.success', 1);
        $this->assertDatabaseHas('categories', ['slug' => 'organic-spices']);
    }

    public function test_category_import_missing_required_column(): void
    {
        $file = $this->csvFile([
            ['name', 'slug'],
            ['Organic Foods', 'organic-foods'],
        ]);

        $response = $this->post(self::CATEGORIES_IMPORT, ['file' => $file]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('status', session('error'));
        $this->assertDatabaseCount('categories', 0);
    }

    public function test_category_import_invalid_status_row(): void
    {
        $file = $this->csvFile([
            ['name', 'status'],
            ['Bad Category', 'not-a-status'],
        ]);

        $response = $this->post(self::CATEGORIES_IMPORT, ['file' => $file]);

        $response->assertSessionHas('import_results.success', 0);
        $errors = session('import_results')['errors'];
        $this->assertCount(1, $errors);
        $this->assertSame(2, $errors[0]['row']);
        $this->assertDatabaseCount('categories', 0);
    }

    // ---------- Products ----------

    public function test_product_import_valid_csv(): void
    {
        Category::create(['name' => 'Organic Foods', 'slug' => 'organic-foods', 'status' => 'active', 'sort_order' => 1]);

        $file = $this->csvFile([
            ['name', 'status', 'category_slug', 'is_returnable', 'is_featured', 'is_latest', 'is_best_seller'],
            ['Organic Honey', 'active', 'organic-foods', '1', '0', '0', '0'],
        ]);

        $response = $this->post(self::PRODUCTS_IMPORT, ['file' => $file]);

        $response->assertRedirect(self::PRODUCTS_IMPORT);
        $response->assertSessionHas('import_results.success', 1);
        $this->assertDatabaseHas('products', ['name' => 'Organic Honey', 'status' => 'active']);
    }

    public function test_product_import_bom_csv(): void
    {
        Category::create(['name' => 'Organic Foods', 'slug' => 'organic-foods', 'status' => 'active', 'sort_order' => 1]);

        $file = $this->csvFile([
            ['name', 'status', 'category_slug', 'is_returnable', 'is_featured', 'is_latest', 'is_best_seller'],
            ['Cold Pressed Oil', 'active', 'organic-foods', '0', '0', '0', '0'],
        ], withBom: true);

        $response = $this->post(self::PRODUCTS_IMPORT, ['file' => $file]);

        $response->assertSessionMissing('error');
        $response->assertSessionHas('import_results.success', 1);
        $this->assertDatabaseHas('products', ['name' => 'Cold Pressed Oil']);
    }

    public function test_product_import_valid_xlsx(): void
    {
        Category::create(['name' => 'Organic Foods', 'slug' => 'organic-foods', 'status' => 'active', 'sort_order' => 1]);
        TaxRate::create(['name' => 'GST 5%', 'percentage' => 5, 'status' => 'active']);

        $file = $this->xlsxFile([
            ['name', 'status', 'category_slug', 'tax_rate_name', 'is_returnable', 'is_featured', 'is_latest', 'is_best_seller'],
            ['Organic Turmeric', 'active', 'organic-foods', 'GST 5%', '1', '0', '0', '0'],
        ]);

        $response = $this->post(self::PRODUCTS_IMPORT, ['file' => $file]);

        $response->assertSessionHas('import_results.success', 1);
        $this->assertDatabaseHas('products', ['name' => 'Organic Turmeric', 'is_returnable' => true]);
    }

    public function test_product_import_missing_required_column(): void
    {
        $file = $this->csvFile([
            ['name', 'category_slug'],
            ['Organic Honey', 'organic-foods'],
        ]);

        $response = $this->post(self::PRODUCTS_IMPORT, ['file' => $file]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('status', session('error'));
        $this->assertDatabaseCount('products', 0);
    }

    public function test_product_import_invalid_category_slug_row(): void
    {
        $file = $this->csvFile([
            ['name', 'status', 'category_slug'],
            ['Ghost Product', 'active', 'does-not-exist'],
        ]);

        $response = $this->post(self::PRODUCTS_IMPORT, ['file' => $file]);

        $response->assertSessionHas('import_results.success', 0);
        $errors = session('import_results')['errors'];
        $this->assertCount(1, $errors);
        $this->assertDatabaseCount('products', 0);
    }

    // ---------- Product Variants ----------

    private function makeProduct(): Product
    {
        $category = Category::create(['name' => 'Organic Foods', 'slug' => 'organic-foods', 'status' => 'active', 'sort_order' => 1]);

        return Product::create([
            'category_id' => $category->id,
            'name' => 'Organic Honey',
            'slug' => 'organic-honey',
            'status' => 'active',
            'sort_order' => 1,
        ]);
    }

    public function test_variant_import_valid_csv(): void
    {
        $this->makeProduct();

        $file = $this->csvFile([
            ['product_slug', 'sku', 'status', 'stock_status', 'enable_tiered_pricing', 'is_default'],
            ['organic-honey', 'HON-500', 'active', 'in_stock', '0', '1'],
        ]);

        $response = $this->post(self::VARIANTS_IMPORT, ['file' => $file]);

        $response->assertRedirect(self::VARIANTS_IMPORT);
        $response->assertSessionHas('import_results.success', 1);
        $this->assertDatabaseHas('product_variants', ['sku' => 'HON-500']);
    }

    public function test_variant_import_bom_csv_regression(): void
    {
        // Regression test for the originally reported bug: a BOM-prefixed
        // CSV must not trigger "missing required column(s): product_slug".
        $this->makeProduct();

        $file = $this->csvFile([
            ['product_slug', 'sku', 'status', 'stock_status', 'enable_tiered_pricing', 'is_default'],
            ['organic-honey', 'HON-BOM', 'active', 'in_stock', '0', '0'],
        ], withBom: true);

        $response = $this->post(self::VARIANTS_IMPORT, ['file' => $file]);

        $response->assertSessionMissing('error');
        $response->assertSessionHas('import_results.success', 1);
        $this->assertDatabaseHas('product_variants', ['sku' => 'HON-BOM']);
    }

    public function test_variant_import_valid_xlsx_preserves_leading_zero_sku(): void
    {
        $this->makeProduct();

        $file = $this->xlsxFile([
            ['product_slug', 'sku', 'barcode', 'status', 'stock_status', 'enable_tiered_pricing', 'is_default'],
            ['organic-honey', 'SKU-0042', '00123456789', 'active', 'in_stock', '0', '0'],
        ]);

        $response = $this->post(self::VARIANTS_IMPORT, ['file' => $file]);

        $response->assertSessionHas('import_results.success', 1);
        $this->assertDatabaseHas('product_variants', ['sku' => 'SKU-0042', 'barcode' => '00123456789']);
    }

    public function test_variant_import_csv_and_xlsx_produce_equivalent_result(): void
    {
        $this->makeProduct();

        $rows = [
            ['product_slug', 'sku', 'status', 'stock_status', 'enable_tiered_pricing', 'is_default'],
            ['organic-honey', 'HON-PARITY-1', 'active', 'in_stock', '0', '0'],
        ];

        $csvResponse = $this->post(self::VARIANTS_IMPORT, ['file' => $this->csvFile($rows)]);
        $csvResponse->assertSessionHas('import_results.success', 1);

        $rows[1][1] = 'HON-PARITY-2';
        $xlsxResponse = $this->post(self::VARIANTS_IMPORT, ['file' => $this->xlsxFile($rows)]);
        $xlsxResponse->assertSessionHas('import_results.success', 1);

        $this->assertDatabaseHas('product_variants', ['sku' => 'HON-PARITY-1', 'status' => 'active', 'stock_status' => 'in_stock']);
        $this->assertDatabaseHas('product_variants', ['sku' => 'HON-PARITY-2', 'status' => 'active', 'stock_status' => 'in_stock']);
    }

    public function test_variant_import_missing_required_column(): void
    {
        $this->makeProduct();

        $file = $this->csvFile([
            ['sku', 'status'],
            ['HON-500', 'active'],
        ]);

        $response = $this->post(self::VARIANTS_IMPORT, ['file' => $file]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('product_slug', session('error'));
        $this->assertDatabaseCount('product_variants', 0);
    }

    public function test_variant_import_invalid_status_row(): void
    {
        $this->makeProduct();

        $file = $this->csvFile([
            ['product_slug', 'sku', 'status', 'stock_status', 'enable_tiered_pricing', 'is_default'],
            ['organic-honey', 'HON-BAD', 'not-a-status', 'in_stock', '0', '0'],
        ]);

        $response = $this->post(self::VARIANTS_IMPORT, ['file' => $file]);

        $response->assertSessionHas('import_results.success', 0);
        $errors = session('import_results')['errors'];
        $this->assertCount(1, $errors);
        $this->assertDatabaseCount('product_variants', 0);
    }

    public function test_variant_import_duplicate_sku_partial_success(): void
    {
        $product = $this->makeProduct();
        ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'HON-DUP',
            'status' => 'active',
            'stock_status' => 'in_stock',
            'enable_tiered_pricing' => false,
            'is_default' => false,
            'sort_order' => 0,
        ]);

        $file = $this->csvFile([
            ['product_slug', 'sku', 'status', 'stock_status', 'enable_tiered_pricing', 'is_default'],
            ['organic-honey', 'HON-DUP', 'active', 'in_stock', '0', '0'],
            ['organic-honey', 'HON-NEW', 'active', 'in_stock', '0', '0'],
        ]);

        $response = $this->post(self::VARIANTS_IMPORT, ['file' => $file]);

        $results = session('import_results');
        $this->assertSame(1, $results['success']);
        $this->assertCount(1, $results['skipped']);
        $this->assertDatabaseHas('product_variants', ['sku' => 'HON-NEW']);
    }

    public function test_variant_import_tiered_pricing_validation_failure(): void
    {
        $this->makeProduct();

        $file = $this->csvFile([
            ['product_slug', 'sku', 'status', 'stock_status', 'enable_tiered_pricing', 'is_default'],
            ['organic-honey', 'HON-TIER', 'active', 'in_stock', '1', '0'],
        ]);

        $response = $this->post(self::VARIANTS_IMPORT, ['file' => $file]);

        $results = session('import_results');
        $this->assertSame(0, $results['success']);
        $this->assertCount(1, $results['errors']);
        $this->assertStringContainsString('tiered pricing', $results['errors'][0]['reason']);
    }

    // ---------- Cross-cutting: unsupported type / empty file ----------

    public function test_import_rejects_unsupported_extension(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'import').'.pdf';
        file_put_contents($path, '%PDF-1.4 not a real pdf');
        $file = new UploadedFile($path, 'file.pdf', 'application/pdf', null, true);

        $response = $this->post(self::CATEGORIES_IMPORT, ['file' => $file]);

        $response->assertSessionHasErrors('file');
    }

    public function test_import_rejects_empty_file_cleanly(): void
    {
        // A genuinely 0-byte upload fails Laravel's own mimes:csv,txt,xlsx
        // check (content-sniffing can't identify an empty file as any of
        // them) before it ever reaches the parser — verify that's a clean
        // validation error, not a 500.
        $path = tempnam(sys_get_temp_dir(), 'import').'.csv';
        file_put_contents($path, '');
        $file = new UploadedFile($path, 'empty.csv', 'text/csv', null, true);

        $response = $this->post(self::CATEGORIES_IMPORT, ['file' => $file]);

        $response->assertSessionHasErrors('file');
    }

    public function test_templates_still_download_as_csv(): void
    {
        $this->get(self::CATEGORIES_TEMPLATE)->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->get(self::PRODUCTS_TEMPLATE)->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->get(self::VARIANTS_TEMPLATE)->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
