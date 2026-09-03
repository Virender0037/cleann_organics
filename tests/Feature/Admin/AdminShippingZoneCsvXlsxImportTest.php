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

    private const ZONES_INDEX = '/admin/shipping/zones';

    private const ZONES_IMPORT = '/admin/shipping/zones/import';

    private const ZONES_TEMPLATE = '/admin/shipping/zones/import/template';

    private const ZONES_EXPORT = '/admin/shipping/zones/export';

    private const ZONES_STORE = '/admin/shipping/zones';

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
            ['name', 'state', 'city', 'pincode', 'zone_type', 'status'],
            ['North Delhi', 'Delhi', 'New Delhi', '110001', 'Local', 'active'],
        ]);

        $response = $this->post(self::ZONES_IMPORT, ['file' => $file]);

        $response->assertRedirect(self::ZONES_IMPORT);
        $response->assertSessionHas('import_results.success', 1);
        $this->assertDatabaseHas('shipping_zones', ['name' => 'North Delhi', 'pincode' => '110001', 'zone_type' => 'Local']);
    }

    public function test_zone_import_valid_xlsx(): void
    {
        $file = $this->xlsxFile([
            ['name', 'state', 'city', 'pincode', 'zone_type', 'status'],
            ['Mumbai Metro', 'Maharashtra', 'Mumbai', '400001', 'Regional', 'active'],
        ]);

        $response = $this->post(self::ZONES_IMPORT, ['file' => $file]);

        $response->assertSessionHas('import_results.success', 1);
        $this->assertDatabaseHas('shipping_zones', ['name' => 'Mumbai Metro', 'zone_type' => 'Regional']);
    }

    public function test_zone_import_nullable_state_city_pincode(): void
    {
        $file = $this->csvFile([
            ['name', 'state', 'city', 'pincode', 'zone_type', 'status'],
            ['Pan India', '', '', '', 'National', 'active'],
        ]);

        $response = $this->post(self::ZONES_IMPORT, ['file' => $file]);

        $response->assertSessionHas('import_results.success', 1);
        $this->assertDatabaseHas('shipping_zones', ['name' => 'Pan India', 'state' => null, 'city' => null, 'pincode' => null, 'zone_type' => 'National']);
    }

    public function test_zone_import_accepts_free_text_zone_type(): void
    {
        // zone_type is free text, not an enum -- an arbitrary multi-word
        // value must be accepted as-is, proving no restricted value list.
        $file = $this->csvFile([
            ['name', 'status', 'zone_type'],
            ['Custom Zone', 'active', 'Local Delivery'],
        ]);

        $response = $this->post(self::ZONES_IMPORT, ['file' => $file]);

        $response->assertSessionHas('import_results.success', 1);
        $this->assertDatabaseHas('shipping_zones', ['name' => 'Custom Zone', 'zone_type' => 'Local Delivery']);
    }

    public function test_zone_import_missing_zone_type_header_is_rejected(): void
    {
        $file = $this->csvFile([
            ['name', 'state', 'city', 'pincode', 'status'],
            ['North Delhi', 'Delhi', 'New Delhi', '110001', 'active'],
        ]);

        $response = $this->post(self::ZONES_IMPORT, ['file' => $file]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('zone_type', session('error'));
        $this->assertDatabaseCount('shipping_zones', 0);
    }

    public function test_zone_import_blank_zone_type_value_is_rejected(): void
    {
        $file = $this->csvFile([
            ['name', 'zone_type', 'status'],
            ['North Delhi', '', 'active'],
        ]);

        $response = $this->post(self::ZONES_IMPORT, ['file' => $file]);

        $results = session('import_results');
        $this->assertSame(0, $results['success']);
        $this->assertCount(1, $results['errors']);
        $this->assertStringContainsString('zone_type', $results['errors'][0]['message']);
        $this->assertDatabaseCount('shipping_zones', 0);
    }

    public function test_zone_import_duplicate_name_state_city_pincode_is_skipped_regardless_of_zone_type(): void
    {
        // Existing zone simulates a legacy record with a NULL zone_type
        // (pre-dating this field). The importer must still detect the
        // duplicate purely on name+state+city+pincode, ignoring zone_type
        // on both sides of the comparison.
        ShippingZone::create(['name' => 'North Delhi', 'state' => 'Delhi', 'city' => 'New Delhi', 'pincode' => '110001', 'zone_type' => null, 'status' => 'active']);

        $file = $this->csvFile([
            ['name', 'state', 'city', 'pincode', 'zone_type', 'status'],
            // Same combination, different case/whitespace, and a zone_type
            // value the existing row doesn't even have -- must still match.
            [' north delhi ', ' DELHI ', 'New Delhi', '110001', 'Express', 'inactive'],
        ]);

        $response = $this->post(self::ZONES_IMPORT, ['file' => $file]);

        $results = session('import_results');
        $this->assertSame(0, $results['success']);
        $this->assertCount(1, $results['skipped']);
        $this->assertDatabaseCount('shipping_zones', 1);
        // Original untouched -- import is create-only, never overwrites.
        $this->assertDatabaseHas('shipping_zones', ['name' => 'North Delhi', 'status' => 'active', 'zone_type' => null]);
    }

    public function test_zone_import_same_name_different_location_is_not_a_duplicate(): void
    {
        ShippingZone::create(['name' => 'Metro Zone', 'state' => 'Delhi', 'city' => null, 'pincode' => null, 'zone_type' => 'Local', 'status' => 'active']);

        $file = $this->csvFile([
            ['name', 'state', 'city', 'pincode', 'zone_type', 'status'],
            ['Metro Zone', 'Maharashtra', '', '', 'Local', 'active'],
        ]);

        $response = $this->post(self::ZONES_IMPORT, ['file' => $file]);

        $response->assertSessionHas('import_results.success', 1);
        $this->assertDatabaseCount('shipping_zones', 2);
    }

    public function test_zone_import_within_file_duplicates_are_skipped_even_with_different_zone_type(): void
    {
        // Two rows with identical name+state+city+pincode but DIFFERENT
        // zone_type must still be treated as duplicates -- zone_type is
        // explicitly excluded from the duplicate key.
        $file = $this->csvFile([
            ['name', 'state', 'city', 'pincode', 'zone_type', 'status'],
            ['North Delhi', 'Delhi', 'New Delhi', '110001', 'Local', 'active'],
            ['North Delhi', 'Delhi', 'New Delhi', '110001', 'Express', 'active'],
        ]);

        $response = $this->post(self::ZONES_IMPORT, ['file' => $file]);

        $results = session('import_results');
        $this->assertSame(1, $results['success']);
        $this->assertCount(1, $results['skipped']);
        $this->assertDatabaseCount('shipping_zones', 1);
        $this->assertDatabaseHas('shipping_zones', ['name' => 'North Delhi', 'zone_type' => 'Local']);
    }

    public function test_zone_import_invalid_status(): void
    {
        $file = $this->csvFile([
            ['name', 'zone_type', 'status'],
            ['Bad Zone', 'Local', 'enabled'],
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
            ['name', 'zone_type', 'status'],
            ['North Delhi', 'Local', 'active'],
            ['', '', ''],
            ['South Chennai', 'Local', 'active'],
        ]);

        $response = $this->post(self::ZONES_IMPORT, ['file' => $file]);

        $response->assertSessionHas('import_results.success', 2);
        $this->assertDatabaseCount('shipping_zones', 2);
    }

    public function test_zone_import_does_not_create_rates_or_methods(): void
    {
        $file = $this->csvFile([
            ['name', 'zone_type', 'status'],
            ['North Delhi', 'Local', 'active'],
        ]);

        $this->post(self::ZONES_IMPORT, ['file' => $file]);

        $this->assertDatabaseCount('shipping_rates', 0);
        $this->assertDatabaseCount('shipping_methods', 0);
    }

    public function test_zone_template_downloads_as_csv_with_zone_type_column(): void
    {
        $response = $this->get(self::ZONES_TEMPLATE);

        $response->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('zone_type', $response->streamedContent());
    }

    public function test_zone_export_downloads_with_zone_type_column(): void
    {
        ShippingZone::create(['name' => 'North Delhi', 'zone_type' => 'Local', 'status' => 'active']);

        $response = $this->get(self::ZONES_EXPORT);

        $response->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $content = $response->streamedContent();
        $this->assertStringContainsString('zone_type', $content);
        $this->assertStringContainsString('Local', $content);
    }

    // ---------- Manual Add/Edit form validation (not import) ----------

    public function test_manual_zone_creation_requires_zone_type(): void
    {
        $response = $this->post(self::ZONES_STORE, [
            'name' => 'Manual Zone',
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('zone_type');
        $this->assertDatabaseCount('shipping_zones', 0);
    }

    public function test_manual_zone_creation_accepts_free_text_zone_type(): void
    {
        $response = $this->post(self::ZONES_STORE, [
            'name' => 'Manual Zone',
            'zone_type' => 'Local Delivery',
            'status' => 'active',
        ]);

        $response->assertRedirect(self::ZONES_INDEX);
        $this->assertDatabaseHas('shipping_zones', ['name' => 'Manual Zone', 'zone_type' => 'Local Delivery']);
    }

    public function test_manual_zone_update_requires_zone_type(): void
    {
        $zone = ShippingZone::create(['name' => 'Existing Zone', 'zone_type' => 'Local', 'status' => 'active']);

        $response = $this->put("/admin/shipping/zones/{$zone->id}", [
            'name' => 'Existing Zone',
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('zone_type');
        $this->assertDatabaseHas('shipping_zones', ['id' => $zone->id, 'zone_type' => 'Local']);
    }

    public function test_existing_zone_with_null_zone_type_remains_valid_after_migration(): void
    {
        // Simulates a pre-existing row from before the zone_type column
        // existed: inserted with a NULL zone_type, must remain readable
        // and listable without error.
        $zone = ShippingZone::create(['name' => 'Legacy Zone', 'status' => 'active']);

        $this->assertNull($zone->fresh()->zone_type);

        $this->get(self::ZONES_INDEX)->assertOk();
    }
}
