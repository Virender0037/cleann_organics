<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreShippingZoneRequest;
use App\Http\Requests\Admin\UpdateShippingZoneRequest;
use App\Models\ShippingZone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

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
