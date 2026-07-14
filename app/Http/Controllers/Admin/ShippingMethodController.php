<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreShippingMethodRequest;
use App\Http\Requests\Admin\UpdateShippingMethodRequest;
use App\Models\ShippingMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShippingMethodController extends Controller
{
    public function index(Request $request): View
    {
        $methods = ShippingMethod::query()
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.shipping.methods.index', compact('methods'));
    }

    public function create(): View
    {
        return view('admin.shipping.methods.create');
    }

    public function store(StoreShippingMethodRequest $request): RedirectResponse
    {
        ShippingMethod::create($request->validated());

        return redirect()->route('admin.shipping.methods.index')->with('success', 'Shipping method created.');
    }

    public function edit(ShippingMethod $method): View
    {
        return view('admin.shipping.methods.edit', compact('method'));
    }

    public function update(UpdateShippingMethodRequest $request, ShippingMethod $method): RedirectResponse
    {
        $method->update($request->validated());

        return redirect()->route('admin.shipping.methods.index')->with('success', 'Shipping method updated.');
    }

    public function destroy(ShippingMethod $method): RedirectResponse
    {
        $method->delete();

        return back()->with('success', 'Shipping method deleted.');
    }
}
