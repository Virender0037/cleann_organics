<?php

namespace Tests\Feature\Admin;

use App\Models\Coupon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use Tests\TestCase;

class AdminCouponCsvXlsxImportTest extends TestCase
{
    use RefreshDatabase;

    private const COUPONS_IMPORT = '/admin/sales/coupons/import';

    private const COUPONS_TEMPLATE = '/admin/sales/coupons/import/template';

    private const COUPONS_EXPORT = '/admin/sales/coupons/export';

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

    private function validRow(array $overrides = []): array
    {
        return array_replace([
            'code' => 'SAVE20',
            'type' => 'percentage',
            'value' => '20',
            'minimum_order_amount' => '500',
            'maximum_discount_amount' => '200',
            'usage_limit' => '100',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => 'active',
        ], $overrides);
    }

    private const HEADER = ['code', 'type', 'value', 'minimum_order_amount', 'maximum_discount_amount', 'usage_limit', 'start_date', 'end_date', 'status'];

    public function test_coupon_import_valid_csv(): void
    {
        $row = $this->validRow();
        $file = $this->csvFile([self::HEADER, array_values($row)]);

        $response = $this->post(self::COUPONS_IMPORT, ['file' => $file]);

        $response->assertRedirect(self::COUPONS_IMPORT);
        $response->assertSessionHas('import_results.success', 1);
        $this->assertDatabaseHas('coupons', ['code' => 'SAVE20', 'type' => 'percentage', 'used_count' => 0]);
    }

    public function test_coupon_import_valid_xlsx(): void
    {
        $row = $this->validRow(['code' => 'SAVEXLSX']);
        $file = $this->xlsxFile([self::HEADER, array_values($row)]);

        $response = $this->post(self::COUPONS_IMPORT, ['file' => $file]);

        $response->assertSessionHas('import_results.success', 1);
        $this->assertDatabaseHas('coupons', ['code' => 'SAVEXLSX']);
    }

    public function test_coupon_import_duplicate_code(): void
    {
        Coupon::create([
            'code' => 'SAVE20', 'type' => 'percentage', 'value' => 10,
            'start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'status' => 'active',
        ]);

        $row = $this->validRow();
        $file = $this->csvFile([self::HEADER, array_values($row)]);

        $response = $this->post(self::COUPONS_IMPORT, ['file' => $file]);

        $results = session('import_results');
        $this->assertSame(0, $results['success']);
        $this->assertCount(1, $results['skipped']);
        $this->assertDatabaseCount('coupons', 1);
    }

    public function test_coupon_import_fixed_type_valid(): void
    {
        $row = $this->validRow(['code' => 'FLAT100', 'type' => 'fixed', 'value' => '100', 'maximum_discount_amount' => '']);
        $file = $this->csvFile([self::HEADER, array_values($row)]);

        $response = $this->post(self::COUPONS_IMPORT, ['file' => $file]);

        $response->assertSessionHas('import_results.success', 1);
        $this->assertDatabaseHas('coupons', ['code' => 'FLAT100', 'type' => 'fixed', 'value' => 100]);
    }

    public function test_coupon_import_fixed_type_allows_value_above_100(): void
    {
        $row = $this->validRow(['code' => 'FLAT500', 'type' => 'fixed', 'value' => '500']);
        $file = $this->csvFile([self::HEADER, array_values($row)]);

        $response = $this->post(self::COUPONS_IMPORT, ['file' => $file]);

        $response->assertSessionHas('import_results.success', 1);
        $this->assertDatabaseHas('coupons', ['code' => 'FLAT500', 'value' => 500]);
    }

    public function test_coupon_import_percentage_type_rejects_value_above_100(): void
    {
        $row = $this->validRow(['type' => 'percentage', 'value' => '150']);
        $file = $this->csvFile([self::HEADER, array_values($row)]);

        $response = $this->post(self::COUPONS_IMPORT, ['file' => $file]);

        $results = session('import_results');
        $this->assertSame(0, $results['success']);
        $this->assertCount(1, $results['errors']);
        $this->assertDatabaseCount('coupons', 0);
    }

    public function test_coupon_import_invalid_type(): void
    {
        $row = $this->validRow(['type' => 'buy_one_get_one']);
        $file = $this->csvFile([self::HEADER, array_values($row)]);

        $response = $this->post(self::COUPONS_IMPORT, ['file' => $file]);

        $results = session('import_results');
        $this->assertSame(0, $results['success']);
        $this->assertCount(1, $results['errors']);
        $this->assertStringContainsString('Invalid type', $results['errors'][0]['message']);
    }

    public function test_coupon_import_invalid_value(): void
    {
        $row = $this->validRow(['value' => 'not-a-number']);
        $file = $this->csvFile([self::HEADER, array_values($row)]);

        $response = $this->post(self::COUPONS_IMPORT, ['file' => $file]);

        $results = session('import_results');
        $this->assertSame(0, $results['success']);
        $this->assertCount(1, $results['errors']);
    }

    public function test_coupon_import_invalid_date_format_rejected(): void
    {
        // Approved format is strictly YYYY-MM-DD -- a different (even
        // otherwise valid) format must be rejected, not silently parsed.
        $row = $this->validRow(['start_date' => '01/01/2026']);
        $file = $this->csvFile([self::HEADER, array_values($row)]);

        $response = $this->post(self::COUPONS_IMPORT, ['file' => $file]);

        $results = session('import_results');
        $this->assertSame(0, $results['success']);
        $this->assertCount(1, $results['errors']);
        $this->assertStringContainsString('start_date', $results['errors'][0]['message']);
    }

    public function test_coupon_import_end_date_before_start_date(): void
    {
        $row = $this->validRow(['start_date' => '2026-06-01', 'end_date' => '2026-01-01']);
        $file = $this->csvFile([self::HEADER, array_values($row)]);

        $response = $this->post(self::COUPONS_IMPORT, ['file' => $file]);

        $results = session('import_results');
        $this->assertSame(0, $results['success']);
        $this->assertCount(1, $results['errors']);
        $this->assertStringContainsString('end_date', $results['errors'][0]['message']);
    }

    public function test_coupon_import_invalid_status(): void
    {
        $row = $this->validRow(['status' => 'draft']);
        $file = $this->csvFile([self::HEADER, array_values($row)]);

        $response = $this->post(self::COUPONS_IMPORT, ['file' => $file]);

        $results = session('import_results');
        $this->assertSame(0, $results['success']);
        $this->assertCount(1, $results['errors']);
        $this->assertDatabaseCount('coupons', 0);
    }

    public function test_coupon_import_missing_required_header(): void
    {
        $file = $this->csvFile([
            ['code', 'type', 'value'],
            ['SAVE20', 'percentage', '20'],
        ]);

        $response = $this->post(self::COUPONS_IMPORT, ['file' => $file]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('start_date', session('error'));
        $this->assertDatabaseCount('coupons', 0);
    }

    public function test_coupon_import_ignores_used_count_column_and_always_starts_at_zero(): void
    {
        // used_count is not an approved import column; even if a malicious
        // or careless upload includes it, it must be ignored entirely.
        $header = [...self::HEADER, 'used_count'];
        $row = [...array_values($this->validRow(['code' => 'HACKED'])), '9999'];

        $file = $this->csvFile([$header, $row]);

        $response = $this->post(self::COUPONS_IMPORT, ['file' => $file]);

        $response->assertSessionHas('import_results.success', 1);
        $this->assertDatabaseHas('coupons', ['code' => 'HACKED', 'used_count' => 0]);
    }

    public function test_coupon_template_downloads_as_csv(): void
    {
        $this->get(self::COUPONS_TEMPLATE)->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_coupon_export_downloads_and_includes_used_count(): void
    {
        Coupon::create([
            'code' => 'SAVE20', 'type' => 'percentage', 'value' => 20,
            'start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'status' => 'active',
        ]);

        $response = $this->get(self::COUPONS_EXPORT);
        $response->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('used_count', $response->streamedContent());
    }
}
