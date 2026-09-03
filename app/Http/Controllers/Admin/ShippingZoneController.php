<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreShippingZoneRequest;
use App\Http\Requests\Admin\UpdateShippingZoneRequest;
use App\Models\ShippingZone;
use App\Services\Admin\SpreadsheetImportReader;
use App\Services\CsvExporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ShippingZoneController extends Controller
{
    public function index(Request $request): View
    {
        $zones = ShippingZone::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('state', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('pincode', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.shipping.zones.index', compact('zones'));
    }

    public function export(Request $request, CsvExporter $exporter): StreamedResponse
    {
        $zones = ShippingZone::withCount('rates')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('state', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('pincode', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->orderBy('name')
            ->lazy(200);

        $headers = ['id', 'name', 'state', 'city', 'pincode', 'zone_type', 'status', 'rates_count'];

        $rows = $zones->map(fn (ShippingZone $zone) => [
            $zone->id,
            $zone->name,
            $zone->state,
            $zone->city,
            $zone->pincode,
            $zone->zone_type,
            $zone->status,
            $zone->rates_count,
        ]);

        return $exporter->stream('shipping-zones.csv', $headers, $rows);
    }

    public function importForm(): View
    {
        return view('admin.shipping.zones.import');
    }

    public function downloadTemplate(CsvExporter $exporter): StreamedResponse
    {
        $headers = ['name', 'state', 'city', 'pincode', 'zone_type', 'status'];

        $exampleRows = [
            ['North Delhi', 'Delhi', 'New Delhi', '110001', 'Local', 'active'],
            ['Mumbai Metro', 'Maharashtra', 'Mumbai', '400001', 'Regional', 'active'],
        ];

        return $exporter->stream('shipping-zones-import-template.csv', $headers, $exampleRows);
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
            Log::error('Shipping zone import: failed to parse uploaded file.', ['exception' => $e]);

            return back()->with('error', 'Unable to read the uploaded file. Please check the file and try again.');
        }

        $header = $parsed['header'];
        $rows = $parsed['rows'];

        $missingColumns = array_diff(['name', 'zone_type', 'status'], $header);

        if (! empty($missingColumns)) {
            return back()->with('error', 'Invalid file: missing required column(s): '.implode(', ', $missingColumns));
        }

        $success = 0;
        $skipped = [];
        $errors = [];

        // Import-only duplicate protection (no DB unique constraint exists
        // for shipping_zones): normalize name+state+city+pincode so blank
        // values, whitespace, and casing don't create near-duplicate rows.
        $existingKeys = [];

        foreach (ShippingZone::select('name', 'state', 'city', 'pincode')->cursor() as $zone) {
            $existingKeys[$this->normalizeZoneKey($zone->name, $zone->state, $zone->city, $zone->pincode)] = true;
        }

        foreach ($rows as $entry) {
            $rowNum = $entry['row'];
            $data = $entry['data'];

            $name = $data['name'] ?? null;
            $zoneType = $data['zone_type'] ?? null;
            $status = $data['status'] ?? null;

            if (blank($name)) {
                $errors[] = ['row' => $rowNum, 'message' => 'Missing required field: name'];

                continue;
            }

            if (blank($zoneType)) {
                $errors[] = ['row' => $rowNum, 'message' => 'Missing required field: zone_type'];

                continue;
            }

            if (! in_array($status, ['active', 'inactive'], true)) {
                $errors[] = ['row' => $rowNum, 'message' => "Invalid status '{$status}' (must be active or inactive)"];

                continue;
            }

            $state = $data['state'] ?? null;
            $city = $data['city'] ?? null;
            $pincode = $data['pincode'] ?? null;

            $key = $this->normalizeZoneKey($name, $state, $city, $pincode);

            if (isset($existingKeys[$key])) {
                $skipped[] = ['row' => $rowNum, 'name' => $name, 'message' => 'Duplicate zone (matching name, state, city and pincode already exists)'];

                continue;
            }

            try {
                ShippingZone::create([
                    'name' => $name,
                    'state' => $state,
                    'city' => $city,
                    'pincode' => $pincode,
                    'zone_type' => $zoneType,
                    'status' => $status,
                ]);
            } catch (\Throwable $e) {
                $errors[] = ['row' => $rowNum, 'message' => 'Failed to create shipping zone: '.$e->getMessage()];

                continue;
            }

            $existingKeys[$key] = true;
            $success++;
        }

        return redirect()->route('admin.shipping.zones.import')
            ->with('import_results', [
                'success' => $success,
                'skipped' => $skipped,
                'errors' => $errors,
            ]);
    }

    /**
     * Normalize a zone's identity fields for import-only duplicate
     * detection: trimmed, case-insensitive, with blank/null treated alike.
     */
    private function normalizeZoneKey(?string $name, ?string $state, ?string $city, ?string $pincode): string
    {
        $normalize = fn (?string $value) => mb_strtolower(trim((string) $value));

        return implode('|', [
            $normalize($name),
            $normalize($state),
            $normalize($city),
            $normalize($pincode),
        ]);
    }

    public function create(): View
    {
        return view('admin.shipping.zones.create');
    }

    public function store(StoreShippingZoneRequest $request): RedirectResponse
    {
        ShippingZone::create($request->validated());

        return redirect()->route('admin.shipping.zones.index')->with('success', 'Shipping zone created.');
    }

    public function edit(ShippingZone $zone): View
    {
        return view('admin.shipping.zones.edit', compact('zone'));
    }

    public function update(UpdateShippingZoneRequest $request, ShippingZone $zone): RedirectResponse
    {
        $zone->update($request->validated());

        return redirect()->route('admin.shipping.zones.index')->with('success', 'Shipping zone updated.');
    }

    public function destroy(ShippingZone $zone): RedirectResponse
    {
        if ($zone->rates()->exists()) {
            return back()->with('error', 'Cannot delete a shipping zone that has rates. Delete its rates first.');
        }

        $zone->delete();

        return back()->with('success', 'Shipping zone deleted.');
    }
}
