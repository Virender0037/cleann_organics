<?php

namespace Tests\Feature\Admin;

use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use Tests\TestCase;

/**
 * Uses literal relative paths (not the route() helper) for request URLs —
 * see AdminCsvXlsxImportTest for why: APP_URL points at a XAMPP
 * subdirectory, which route() bakes into generated URLs but the test HTTP
 * kernel expects app-root-relative paths.
 */
class AdminTagCsvXlsxImportTest extends TestCase
{
    use RefreshDatabase;

    private const TAGS_IMPORT = '/admin/catalog/tags/import';

    private const TAGS_TEMPLATE = '/admin/catalog/tags/import/template';

    private const TAGS_EXPORT = '/admin/catalog/tags/export';

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

    public function test_tag_import_valid_csv(): void
    {
        $file = $this->csvFile([
            ['name', 'slug', 'status'],
            ['Organic', 'organic', 'active'],
        ]);

        $response = $this->post(self::TAGS_IMPORT, ['file' => $file]);

        $response->assertRedirect(self::TAGS_IMPORT);
        $response->assertSessionHas('import_results.success', 1);
        $this->assertDatabaseHas('tags', ['slug' => 'organic', 'status' => 'active']);
    }

    public function test_tag_import_valid_xlsx(): void
    {
        $file = $this->xlsxFile([
            ['name', 'slug', 'status'],
            ['Bestseller', '', 'active'],
        ]);

        $response = $this->post(self::TAGS_IMPORT, ['file' => $file]);

        $response->assertSessionHas('import_results.success', 1);
        // Blank slug column must auto-generate from name via Str::slug().
        $this->assertDatabaseHas('tags', ['name' => 'Bestseller', 'slug' => 'bestseller']);
    }

    public function test_tag_import_bom_csv(): void
    {
        $file = $this->csvFile([
            ['name', 'status'],
            ['Vegan', 'active'],
        ], withBom: true);

        $response = $this->post(self::TAGS_IMPORT, ['file' => $file]);

        $response->assertSessionMissing('error');
        $response->assertSessionHas('import_results.success', 1);
        $this->assertDatabaseHas('tags', ['name' => 'Vegan']);
    }

    public function test_tag_import_missing_required_header(): void
    {
        $file = $this->csvFile([
            ['name', 'slug'],
            ['Organic', 'organic'],
        ]);

        $response = $this->post(self::TAGS_IMPORT, ['file' => $file]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('status', session('error'));
        $this->assertDatabaseCount('tags', 0);
    }

    public function test_tag_import_invalid_status(): void
    {
        $file = $this->csvFile([
            ['name', 'status'],
            ['Organic', 'enabled'],
        ]);

        $response = $this->post(self::TAGS_IMPORT, ['file' => $file]);

        $results = session('import_results');
        $this->assertSame(0, $results['success']);
        $this->assertCount(1, $results['errors']);
        $this->assertDatabaseCount('tags', 0);
    }

    public function test_tag_import_duplicate_slug_is_skipped_not_overwritten(): void
    {
        Tag::create(['name' => 'Organic', 'slug' => 'organic', 'status' => 'active']);

        $file = $this->csvFile([
            ['name', 'slug', 'status'],
            ['Organic Duplicate', 'organic', 'inactive'],
        ]);

        $response = $this->post(self::TAGS_IMPORT, ['file' => $file]);

        $results = session('import_results');
        $this->assertSame(0, $results['success']);
        $this->assertCount(1, $results['skipped']);
        $this->assertDatabaseCount('tags', 1);
        // The original tag must be untouched -- import is create-only.
        $this->assertDatabaseHas('tags', ['slug' => 'organic', 'name' => 'Organic', 'status' => 'active']);
    }

    public function test_tag_import_blank_rows_are_skipped(): void
    {
        $file = $this->csvFile([
            ['name', 'status'],
            ['Organic', 'active'],
            ['', ''],
            ['Bestseller', 'active'],
        ]);

        $response = $this->post(self::TAGS_IMPORT, ['file' => $file]);

        $response->assertSessionHas('import_results.success', 2);
        $this->assertDatabaseCount('tags', 2);
    }

    public function test_tag_import_does_not_touch_product_relationships(): void
    {
        $file = $this->csvFile([
            ['name', 'status'],
            ['Organic', 'active'],
        ]);

        $this->post(self::TAGS_IMPORT, ['file' => $file]);

        $tag = Tag::where('slug', 'organic')->firstOrFail();
        $this->assertSame(0, $tag->products()->count());
        $this->assertDatabaseCount('product_tag', 0);
    }

    public function test_tag_template_downloads_as_csv(): void
    {
        $this->get(self::TAGS_TEMPLATE)->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_tag_export_downloads(): void
    {
        Tag::create(['name' => 'Organic', 'slug' => 'organic', 'status' => 'active']);

        $this->get(self::TAGS_EXPORT)->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
