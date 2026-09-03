<?php

namespace App\Services\Admin;

use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use RuntimeException;

/**
 * Parses an uploaded CSV or XLSX file into a normalized row structure shared
 * by every admin bulk-import screen:
 *
 *   ['header' => [...], 'rows' => [['row' => int, 'data' => array<string, ?string>], ...]]
 *
 * File parsing/header normalization only. Required-column checks, per-field
 * validation, and persistence remain the caller's responsibility.
 */
class SpreadsheetImportReader
{
    private const UTF8_BOM = "\xEF\xBB\xBF";

    /**
     * @return array{header: array<int, string>, rows: array<int, array{row: int, data: array<string, ?string>}>}
     *
     * @throws RuntimeException
     */
    public function parse(UploadedFile $file): array
    {
        $extension = strtolower($file->extension() ?: $file->getClientOriginalExtension());

        return match ($extension) {
            'xlsx' => $this->parseXlsx($file),
            'csv', 'txt' => $this->parseCsv($file),
            default => throw new RuntimeException('Unsupported file format.'),
        };
    }

    /**
     * @return array{header: array<int, string>, rows: array<int, array{row: int, data: array<string, ?string>}>}
     */
    private function parseCsv(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            throw new RuntimeException('Unable to read file.');
        }

        // A UTF-8 BOM is not whitespace, so trim() alone never strips it;
        // detect and skip it explicitly before the header line is read.
        if (fread($handle, 3) !== self::UTF8_BOM) {
            rewind($handle);
        }

        $headerLine = fgetcsv($handle);

        if (! is_array($headerLine)) {
            fclose($handle);

            throw new RuntimeException('No header row found.');
        }

        $header = $this->normalizeHeader($headerLine);

        $rows = [];
        $rowNumber = 1;

        while (($line = fgetcsv($handle)) !== false) {
            $rowNumber++;

            if (count($line) === 1 && trim((string) ($line[0] ?? '')) === '') {
                continue;
            }

            $rows[] = [
                'row' => $rowNumber,
                'data' => $this->combineRow($header, $line),
            ];
        }

        fclose($handle);

        return ['header' => $header, 'rows' => $rows];
    }

    /**
     * @return array{header: array<int, string>, rows: array<int, array{row: int, data: array<string, ?string>}>}
     */
    private function parseXlsx(UploadedFile $file): array
    {
        try {
            $reader = new XlsxReader;
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($file->getRealPath());
        } catch (\Throwable $e) {
            throw new RuntimeException('Unable to read spreadsheet.', previous: $e);
        }

        $sheet = $spreadsheet->getSheet(0);
        $highestRow = $sheet->getHighestDataRow();
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($sheet->getHighestDataColumn());

        $header = null;
        $rows = [];

        for ($rowNumber = 1; $rowNumber <= $highestRow; $rowNumber++) {
            $values = [];

            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                $values[] = $this->extractCellValue($sheet->getCell([$col, $rowNumber]));
            }

            if (! array_filter($values, fn ($value) => ! blank($value))) {
                continue;
            }

            if ($header === null) {
                $header = $this->normalizeHeader($values);

                continue;
            }

            $rows[] = [
                'row' => $rowNumber,
                'data' => $this->combineRow($header, $values),
            ];
        }

        $spreadsheet->disconnectWorksheets();

        if ($header === null) {
            throw new RuntimeException('No header row found.');
        }

        return ['header' => $header, 'rows' => $rows];
    }

    /**
     * Combine a header row with a data row using the same
     * trim/blank-to-null normalization for both CSV and XLSX sources.
     *
     * Matches the original CSV importers' behavior: short rows are padded
     * with null, but a row with more columns than the header is left for
     * array_combine() to reject rather than silently truncated.
     *
     * @param  array<int, string>  $header
     * @param  array<int, mixed>  $line
     * @return array<string, ?string>
     */
    private function combineRow(array $header, array $line): array
    {
        $data = array_combine($header, array_pad($line, count($header), null));

        return array_map(fn ($value) => blank($value) ? null : trim((string) $value), $data);
    }

    /**
     * Trim whitespace and strip a stray UTF-8 BOM from header cells only.
     * Deliberately does not rename/alias headers (e.g. "Product Slug").
     *
     * @param  array<int, mixed>  $header
     * @return array<int, string>
     */
    private function normalizeHeader(array $header): array
    {
        $header = array_values($header);

        if (isset($header[0])) {
            $header[0] = ltrim((string) $header[0], self::UTF8_BOM);
        }

        return array_map(fn ($value) => trim((string) $value), $header);
    }

    /**
     * Read a cell as a plain string, avoiding scientific notation on large
     * numeric codes (SKUs/barcodes) and never introducing date conversion.
     */
    private function extractCellValue(Cell $cell): ?string
    {
        $value = $cell->getValue();

        if ($value instanceof RichText) {
            return $value->getPlainText();
        }

        if (is_float($value)) {
            return rtrim(rtrim(sprintf('%.10F', $value), '0'), '.');
        }

        if (is_int($value)) {
            return (string) $value;
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return $value === null ? null : (string) $value;
    }
}
