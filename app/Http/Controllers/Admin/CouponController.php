<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCouponRequest;
use App\Http\Requests\Admin\UpdateCouponRequest;
use App\Models\Coupon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

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
