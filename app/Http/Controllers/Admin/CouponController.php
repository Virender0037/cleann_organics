<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCouponRequest;
use App\Http\Requests\Admin\UpdateCouponRequest;
use App\Models\Coupon;
use App\Services\Admin\SpreadsheetImportReader;
use App\Services\CsvExporter;
use DateTime;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CouponController extends Controller
{
    public function index(Request $request): View
    {
        $coupons = Coupon::withCount('orders')
            ->when($request->filled('search'), fn ($query) => $query->where('code', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->string('type')))
            ->when($request->filled('status'), function ($query) use ($request) {
                if ($request->string('status') === 'expired') {
                    $query->where('end_date', '<', now());
                } else {
                    $query->where('status', $request->string('status'));
                }
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.sales.coupons.index', compact('coupons'));
    }

    public function export(Request $request, CsvExporter $exporter): StreamedResponse
    {
        $coupons = Coupon::query()
            ->when($request->filled('search'), fn ($query) => $query->where('code', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->string('type')))
            ->when($request->filled('status'), function ($query) use ($request) {
                if ($request->string('status') === 'expired') {
                    $query->where('end_date', '<', now());
                } else {
                    $query->where('status', $request->string('status'));
                }
            })
            ->latest()
            ->lazy(200);

        $headers = ['id', 'code', 'type', 'value', 'minimum_order_amount', 'maximum_discount_amount', 'usage_limit', 'used_count', 'start_date', 'end_date', 'status'];

        $rows = $coupons->map(fn (Coupon $coupon) => [
            $coupon->id,
            $coupon->code,
            $coupon->type,
            $coupon->value,
            $coupon->minimum_order_amount,
            $coupon->maximum_discount_amount,
            $coupon->usage_limit,
            $coupon->used_count,
            $coupon->start_date->format('Y-m-d'),
            $coupon->end_date->format('Y-m-d'),
            $coupon->status,
        ]);

        return $exporter->stream('coupons.csv', $headers, $rows);
    }

    public function importForm(): View
    {
        return view('admin.sales.coupons.import');
    }

    public function downloadTemplate(CsvExporter $exporter): StreamedResponse
    {
        $headers = ['code', 'type', 'value', 'minimum_order_amount', 'maximum_discount_amount', 'usage_limit', 'start_date', 'end_date', 'status'];

        $exampleRows = [
            ['SAVE20', 'percentage', '20', '500', '200', '100', '2026-01-01', '2026-12-31', 'active'],
            ['FLAT100', 'fixed', '100', '999', '', '', '2026-01-01', '2026-06-30', 'active'],
        ];

        return $exporter->stream('coupons-import-template.csv', $headers, $exampleRows);
    }

    public function import(Request $request, SpreadsheetImportReader $reader): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx', 'max:2048'],
        ]);

        try {
            $parsed = $reader->parse($request->file('file'));
        } catch (\RuntimeException $e) {
            return back()->with('error', 'Unable to read the uploaded file: '.$e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Coupon import: failed to parse uploaded file.', ['exception' => $e]);

            return back()->with('error', 'Unable to read the uploaded file. Please check the file and try again.');
        }

        $header = $parsed['header'];
        $rows = $parsed['rows'];

        $requiredColumns = ['code', 'type', 'value', 'start_date', 'end_date', 'status'];
        $missingColumns = array_diff($requiredColumns, $header);

        if (! empty($missingColumns)) {
            return back()->with('error', 'Invalid file: missing required column(s): '.implode(', ', $missingColumns));
        }

        $success = 0;
        $skipped = [];
        $errors = [];
        $claimedCodes = [];

        foreach ($rows as $entry) {
            $rowNum = $entry['row'];
            $data = $entry['data'];

            $code = $data['code'] ?? null;

            if (blank($code)) {
                $errors[] = ['row' => $rowNum, 'code' => null, 'message' => 'Missing required field: code'];

                continue;
            }

            if (isset($claimedCodes[$code]) || Coupon::where('code', $code)->exists()) {
                $skipped[] = ['row' => $rowNum, 'code' => $code, 'message' => 'Duplicate code'];

                continue;
            }

            $type = $data['type'] ?? null;

            if (! in_array($type, ['fixed', 'percentage'], true)) {
                $errors[] = ['row' => $rowNum, 'code' => $code, 'message' => "Invalid type '{$type}' (must be fixed or percentage)"];

                continue;
            }

            $value = $data['value'] ?? null;
            $maxValue = $type === 'percentage' ? 100 : 999999.99;

            if (blank($value) || ! is_numeric($value) || (float) $value < 0 || (float) $value > $maxValue) {
                $errors[] = ['row' => $rowNum, 'code' => $code, 'message' => "Invalid value '{$value}' (must be numeric, 0-{$maxValue} for {$type})"];

                continue;
            }

            $numericFields = ['minimum_order_amount', 'maximum_discount_amount'];
            $invalidNumeric = null;

            foreach ($numericFields as $field) {
                $fieldValue = $data[$field] ?? null;

                if (blank($fieldValue)) {
                    continue;
                }

                if (! is_numeric($fieldValue) || (float) $fieldValue < 0) {
                    $invalidNumeric = $field;

                    break;
                }
            }

            if ($invalidNumeric !== null) {
                $errors[] = ['row' => $rowNum, 'code' => $code, 'message' => "Invalid {$invalidNumeric} '{$data[$invalidNumeric]}' (must be a non-negative number)"];

                continue;
            }

            $usageLimit = $data['usage_limit'] ?? null;

            if (! blank($usageLimit) && (! ctype_digit((string) $usageLimit) || (int) $usageLimit < 1)) {
                $errors[] = ['row' => $rowNum, 'code' => $code, 'message' => "Invalid usage_limit '{$usageLimit}' (must be an integer of at least 1)"];

                continue;
            }

            $startDate = $this->parseStrictDate($data['start_date'] ?? null);

            if ($startDate === null) {
                $errors[] = ['row' => $rowNum, 'code' => $code, 'message' => "Invalid start_date '{$data['start_date']}' (must be YYYY-MM-DD)"];

                continue;
            }

            $endDate = $this->parseStrictDate($data['end_date'] ?? null);

            if ($endDate === null) {
                $errors[] = ['row' => $rowNum, 'code' => $code, 'message' => "Invalid end_date '{$data['end_date']}' (must be YYYY-MM-DD)"];

                continue;
            }

            if ($endDate < $startDate) {
                $errors[] = ['row' => $rowNum, 'code' => $code, 'message' => 'end_date must be on or after start_date'];

                continue;
            }

            $status = $data['status'] ?? null;

            if (! in_array($status, ['active', 'inactive'], true)) {
                $errors[] = ['row' => $rowNum, 'code' => $code, 'message' => "Invalid status '{$status}' (must be active or inactive)"];

                continue;
            }

            try {
                Coupon::create([
                    'code' => $code,
                    'type' => $type,
                    'value' => $value,
                    'minimum_order_amount' => $data['minimum_order_amount'] ?? 0,
                    'maximum_discount_amount' => $data['maximum_discount_amount'] ?? null,
                    'usage_limit' => blank($usageLimit) ? null : (int) $usageLimit,
                    'used_count' => 0,
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'status' => $status,
                ]);
            } catch (\Throwable $e) {
                $errors[] = ['row' => $rowNum, 'code' => $code, 'message' => 'Failed to create coupon: '.$e->getMessage()];

                continue;
            }

            $claimedCodes[$code] = true;
            $success++;
        }

        return redirect()->route('admin.sales.coupons.import')
            ->with('import_results', [
                'success' => $success,
                'skipped' => $skipped,
                'errors' => $errors,
            ]);
    }

    /**
     * Strictly parse a date in Y-m-d format, rejecting anything else
     * (including otherwise-valid but differently-formatted dates) so the
     * documented import format is unambiguous.
     */
    private function parseStrictDate(?string $value): ?DateTime
    {
        if (blank($value)) {
            return null;
        }

        $date = DateTime::createFromFormat('!Y-m-d', $value);

        if ($date === false || $date->format('Y-m-d') !== $value) {
            return null;
        }

        return $date;
    }

    public function create(): View
    {
        return view('admin.sales.coupons.create');
    }

    public function store(StoreCouponRequest $request): RedirectResponse
    {
        Coupon::create($request->validated());

        return redirect()->route('admin.sales.coupons.index')->with('success', 'Coupon created.');
    }

    public function edit(Coupon $coupon): View
    {
        return view('admin.sales.coupons.edit', compact('coupon'));
    }

    public function update(UpdateCouponRequest $request, Coupon $coupon): RedirectResponse
    {
        $coupon->update($request->validated());

        return redirect()->route('admin.sales.coupons.index')->with('success', 'Coupon updated.');
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        if ($coupon->orders()->exists()) {
            return back()->with('error', 'Cannot delete a coupon that has been used on orders.');
        }

        $coupon->delete();

        return back()->with('success', 'Coupon deleted.');
    }
}
