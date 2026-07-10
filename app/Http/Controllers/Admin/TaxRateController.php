<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTaxRateRequest;
use App\Http\Requests\Admin\UpdateTaxRateRequest;
use App\Models\TaxRate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaxRateController extends Controller
{
    public function index(Request $request): View
    {
        $taxRates = TaxRate::withCount('products')
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.catalog.tax-rates.index', compact('taxRates'));
    }

    public function create(): View
    {
        return view('admin.catalog.tax-rates.create');
    }

    public function store(StoreTaxRateRequest $request): RedirectResponse
    {
        TaxRate::create($request->validated());

        return redirect()->route('admin.catalog.tax-rates.index')->with('success', 'Tax rate created.');
    }

    public function edit(TaxRate $taxRate): View
    {
        return view('admin.catalog.tax-rates.edit', compact('taxRate'));
    }

    public function update(UpdateTaxRateRequest $request, TaxRate $taxRate): RedirectResponse
    {
        $taxRate->update($request->validated());

        return redirect()->route('admin.catalog.tax-rates.index')->with('success', 'Tax rate updated.');
    }

    public function destroy(TaxRate $taxRate): RedirectResponse
    {
        if ($taxRate->products()->exists()) {
            return back()->with('error', 'Cannot delete a tax rate that is assigned to products.');
        }

        $taxRate->delete();

        return back()->with('success', 'Tax rate deleted.');
    }
}
