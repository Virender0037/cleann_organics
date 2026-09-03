<?php

namespace Tests\Unit\Services\Admin;

use App\Services\Admin\SpreadsheetImportReader;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use RuntimeException;
use Tests\TestCase;

class SpreadsheetImportReaderTest extends TestCase
{
    private function fileWithContent(string $content, string $extension): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'import').'.'.$extension;
        file_put_contents($path, $content);

        return new UploadedFile($path, 'upload.'.$extension, null, null, true);
    }

    public function test_csv_header_only_no_data_rows(): void
    {
        $result = (new SpreadsheetImportReader)->parse($this->fileWithContent("product_slug,sku,status\n", 'csv'));

        $this->assertSame(['product_slug', 'sku', 'status'], $result['header']);
        $this->assertSame([], $result['rows']);
    }

    public function test_csv_no_header_row_at_all_throws(): void
    {
        // A file containing only a UTF-8 BOM has nothing left after the BOM
        // is stripped, so fgetcsv() finds no header line at all.
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No header row found.');

        (new SpreadsheetImportReader)->parse($this->fileWithContent("\xEF\xBB\xBF", 'csv'));
    }

    public function test_csv_bom_is_stripped_from_first_header_only(): void
    {
        $result = (new SpreadsheetImportReader)->parse(
            $this->fileWithContent("\xEF\xBB\xBFproduct_slug,sku,status\norganic-honey,HON-1,active\n", 'csv')
        );

        $this->assertSame(['product_slug', 'sku', 'status'], $result['header']);
        // Row numbering matches the existing importers' convention: row 1
        // is the header, so the first data row is row 2.
        $this->assertSame(2, $result['rows'][0]['row']);
        $this->assertSame('organic-honey', $result['rows'][0]['data']['product_slug']);
    }

    public function test_csv_quoted_commas_are_parsed_correctly(): void
    {
        $result = (new SpreadsheetImportReader)->parse(
            $this->fileWithContent("name,status\n\"Honey, Pure\",active\n", 'csv')
        );

        $this->assertSame('Honey, Pure', $result['rows'][0]['data']['name']);
    }

    public function test_csv_blank_rows_are_skipped(): void
    {
        $result = (new SpreadsheetImportReader)->parse(
            $this->fileWithContent("name,status\nFoo,active\n\nBar,active\n", 'csv')
        );

        $this->assertCount(2, $result['rows']);
        $this->assertSame(2, $result['rows'][0]['row']);
        $this->assertSame(4, $result['rows'][1]['row']);
    }

    public function test_csv_header_whitespace_is_trimmed(): void
    {
        $result = (new SpreadsheetImportReader)->parse(
            $this->fileWithContent(" name , status \nFoo,active\n", 'csv')
        );

        $this->assertSame(['name', 'status'], $result['header']);
    }

    public function test_unsupported_extension_throws(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unsupported file format.');

        // Real PNG magic bytes so content-sniffing (not just the filename)
        // identifies this as an image, not text — matching how the reader
        // routes by guessed type rather than trusting the given extension.
        (new SpreadsheetImportReader)->parse($this->fileWithContent("\x89PNG\r\n\x1a\n", 'png'));
    }

    public function test_xlsx_skips_blank_rows_and_preserves_row_numbers(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'import').'.xlsx';
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue([1, 1], 'sku');
        $sheet->setCellValue([2, 1], 'status');
        // Row 2 intentionally left blank.
        $sheet->setCellValue([1, 3], 'HON-1');
        $sheet->setCellValue([2, 3], 'active');
        (new XlsxWriter($spreadsheet))->save($path);

        $file = new UploadedFile($path, 'upload.xlsx', null, null, true);
        $result = (new SpreadsheetImportReader)->parse($file);

        $this->assertSame(['sku', 'status'], $result['header']);
        $this->assertCount(1, $result['rows']);
        $this->assertSame(3, $result['rows'][0]['row']);
        $this->assertSame('HON-1', $result['rows'][0]['data']['sku']);
    }

    public function test_xlsx_numeric_sku_does_not_become_scientific_notation(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'import').'.xlsx';
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue([1, 1], 'barcode');
        // A large numeric value typed directly into a General-format cell.
        $sheet->setCellValueExplicit([1, 2], '4006381333931', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        (new XlsxWriter($spreadsheet))->save($path);

        $file = new UploadedFile($path, 'upload.xlsx', null, null, true);
        $result = (new SpreadsheetImportReader)->parse($file);

        $this->assertSame('4006381333931', $result['rows'][0]['data']['barcode']);
        $this->assertStringNotContainsString('E+', $result['rows'][0]['data']['barcode']);
    }
}
