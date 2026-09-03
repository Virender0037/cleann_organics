<?php

namespace Tests\Feature\Admin;

use App\Models\ShippingZone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use Tests\TestCase;

class AdminShippingZoneCsvXlsxImportTest extends TestCase
{
    use RefreshDatabase;

    private const ZONES_IMPORT = '/admin/shipping/zones/import';

    private const ZONES_TEMPLATE = '/admin/shipping/zones/import/template';

    private const ZONES_EXPORT = '/admin/shipping/zones/export';

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

    public function test_zone_import_valid_csv(): void
    {
        $file = $this->csvFile([
            ['name', 'state', 'city', 'pincode', 'status'],
            ['North Delhi', 'Delhi', 'New Delhi', '110001', 'active'],
        ]);

        $response = $this->post(self::ZONES_IMPORT, ['file' => $file]);

        $response->assertRedirect(self::ZONES_IMPORT);
        $response->assertSessionHas('import_results.success', 1);
        $this->assertDatabaseHas('shipping_zones', ['name' => 'North Delhi', 'pincode' => '110001']);
    }

    public function test_zone_import_valid_xlsx(): void
    {
        $file = $this->xlsxFile([
            ['name', 'state', 'city', 'pincode', 'status'],
            ['Mumbai Metro', 'Maharashtra', 'Mumbai', '400001', 'active'],
        ]);

        $response = $this->post(self::ZONES_IMPORT, ['file' => $file]);

        $response->assertSessionHas('import_results.success', 1);
        $this->assertDatabaseHas('shipping_zones', ['name' => 'Mumbai Metro']);
    }

    public function test_zone_import_nullable_state_city_pincode(): void
    {
        $file = $this->csvFile([
            ['name', 'state', 'city', 'pincode', 'status'],
            ['Pan India', '', '', '', 'active'],
        ]);

        $response = $this->post(self::ZONES_IMPORT, ['file' => $file]);

        $response->assertSessionHas('import_results.success', 1);
        $this->assertDatabaseHas('shipping_zones', ['name' => 'Pan India', 'state' => null, 'city' => null, 'pincode' => null]);
    }

    public function test_zone_import_duplicate_name_state_city_pincode_is_skipped(): void
    {
        ShippingZone::create(['name' => 'North Delhi', 'state' => 'Delhi', 'city' => 'New Delhi', 'pincode' => '110001', 'status' => 'active']);

        $file = $this->csvFile([
            ['name', 'state', 'city', 'pincode', 'status'],
            // Same combination, different case/whitespace -- must still match.
            [' north delhi ', ' DELHI ', 'New Delhi', '110001', 'inactive'],
        ]);

        $response = $this->post(self::ZONES_IMPORT, ['file' => $file]);

        $results = session('import_results');
        $this->assertSame(0, $results['success']);
        $this->assertCount(1, $results['skipped']);
        $this->assertDatabaseCount('shipping_zones', 1);
        // Original untouched -- import is create-only, never overwrites.
        $this->assertDatabaseHas('shipping_zones', ['name' => 'North Delhi', 'status' => 'active']);
    }

    public function test_zone_import_same_name_different_location_is_not_a_duplicate(): void
    {
        ShippingZone::create(['name' => 'Metro Zone', 'state' => 'Delhi', 'city' => null, 'pincode' => null, 'status' => 'active']);

        $file = $this->csvFile([
            ['name', 'state', 'city', 'pincode', 'status'],
            ['Metro Zone', 'Maharashtra', '', '', 'active'],
        ]);

        $response = $this->post(self::ZONES_IMPORT, ['file' => $file]);

        $response->assertSessionHas('import_results.success', 1);
        $this->assertDatabaseCount('shipping_zones', 2);
    }

    public function test_zone_import_within_file_duplicates_are_also_skipped(): void
    {
        $file = $this->csvFile([
            ['name', 'state', 'city', 'pincode', 'status'],
            ['North Delhi', 'Delhi', 'New Delhi', '110001', 'active'],
            ['North Delhi', 'Delhi', 'New Delhi', '110001', 'active'],
        ]);

        $response = $this->post(self::ZONES_IMPORT, ['file' => $file]);

        $results = session('import_results');
        $this->assertSame(1, $results['success']);
        $this->assertCount(1, $results['skipped']);
        $this->assertDatabaseCount('shipping_zones', 1);
    }

    public function test_zone_import_invalid_status(): void
    {
        $file = $this->csvFile([
            ['name', 'status'],
            ['Bad Zone', 'enabled'],
        ]);

        $response = $this->post(self::ZONES_IMPORT, ['file' => $file]);

        $results = session('import_results');
        $this->assertSame(0, $results['success']);
        $this->assertCount(1, $results['errors']);
        $this->assertDatabaseCount('shipping_zones', 0);
    }

    public function test_zone_import_missing_required_header(): void
    {
        $file = $this->csvFile([
            ['name', 'state'],
            ['North Delhi', 'Delhi'],
        ]);

        $response = $this->post(self::ZONES_IMPORT, ['file' => $file]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('status', session('error'));
        $this->assertDatabaseCount('shipping_zones', 0);
    }

    public function test_zone_import_blank_rows_are_skipped(): void
    {
        $file = $this->csvFile([
            ['name', 'status'],
            ['North Delhi', 'active'],
            ['', ''],
            ['South Chennai', 'active'],
        ]);

        $response = $this->post(self::ZONES_IMPORT, ['file' => $file]);

        $response->assertSessionHas('import_results.success', 2);
        $this->assertDatabaseCount('shipping_zones', 2);
    }

    public function test_zone_import_does_not_create_rates_or_methods(): void
    {
        $file = $this->csvFile([
            ['name', 'status'],
            ['North Delhi', 'active'],
        ]);

        $this->post(self::ZONES_IMPORT, ['file' => $file]);

        $this->assertDatabaseCount('shipping_rates', 0);
        $this->assertDatabaseCount('shipping_methods', 0);
    }

    public function test_zone_template_downloads_as_csv(): void
    {
        $this->get(self::ZONES_TEMPLATE)->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_zone_export_downloads(): void
    {
        ShippingZone::create(['name' => 'North Delhi', 'status' => 'active']);

        $this->get(self::ZONES_EXPORT)->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
