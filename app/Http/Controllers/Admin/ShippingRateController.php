<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreShippingRateRequest;
use App\Http\Requests\Admin\UpdateShippingRateRequest;
use App\Models\ShippingRate;
use App\Models\ShippingZone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShippingRateController extends Controller
{
    public function index(Request $request): View
    {
        $rates = ShippingRate::with('zone')
            ->when($request->filled('search'), fn ($query) => $query->whereHas('zone', fn ($query) => $query->where('name', 'like', '%'.$request->string('search').'%')))
            ->when($request->filled('zone_id'), fn ($query) => $query->where('shipping_zone_id', $request->integer('zone_id')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->orderBy('shipping_zone_id')
            ->orderBy('min_weight')
            ->paginate(15)
            ->withQueryString();

        $zones = ShippingZone::orderBy('name')->get();

        return view('admin.shipping.rates.index', compact('rates', 'zones'));
    }

    public function create(): View
    {
        $zones = ShippingZone::orderBy('name')->get();

        return view('admin.shipping.rates.create', compact('zones'));
    }

    public function store(StoreShippingRateRequest $request): RedirectResponse
    {
        ShippingRate::create($request->validated());

        return redirect()->route('admin.shipping.rates.index')->with('success', 'Shipping rate created.');
    }

    public function edit(ShippingRate $rate): View
    {
        $zones = ShippingZone::orderBy('name')->get();

        return view('admin.shipping.rates.edit', compact('rate', 'zones'));
    }

    public function update(UpdateShippingRateRequest $request, ShippingRate $rate): RedirectResponse
    {
        $rate->update($request->validated());

        return redirect()->route('admin.shipping.rates.index')->with('success', 'Shipping rate updated.');
    }

    public function destroy(ShippingRate $rate): RedirectResponse
    {
        $rate->delete();

        return back()->with('success', 'Shipping rate deleted.');
    }
}
