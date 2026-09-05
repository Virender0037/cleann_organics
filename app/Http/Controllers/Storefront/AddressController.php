<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\StoreAddressRequest;
use App\Http\Requests\Storefront\UpdateAddressRequest;
use App\Models\Address;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Customer-facing address book. Mirrors Admin\CustomerAddressController's
 * validation and single-default logic exactly, but every query is scoped
 * to Auth::user()->addresses() rather than an admin-supplied {customer} —
 * there is no route parameter identifying which customer's addresses these
 * are, so there is nothing for a request to spoof to reach someone else's.
 */
class AddressController extends Controller
{
    public function index(): View
    {
        return view('account-setting', [
            'addresses' => Auth::user()->addresses()->latest()->get(),
        ]);
    }

    public function store(StoreAddressRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['country'] = $data['country'] ?? 'India';
        $data['is_default'] = $request->boolean('is_default');

        $address = Auth::user()->addresses()->create($data);

        if ($address->is_default) {
            $this->enforceSingleDefaultAddress($address);
        }

        return back()->with('success', 'Address added.');
    }

    /**
     * Scoped by Auth::id() + address id together — never by the address id
     * alone — so there is no id a request could supply that would ever
     * touch another customer's address.
     */
    public function update(UpdateAddressRequest $request, Address $address): RedirectResponse
    {
        if ($address->user_id !== Auth::id()) {
            abort(404);
        }

        $data = $request->validated();
        $data['country'] = $data['country'] ?? 'India';
        $data['is_default'] = $request->boolean('is_default');

        $address->update($data);

        if ($address->is_default) {
            $this->enforceSingleDefaultAddress($address);
        }

        return back()->with('success', 'Address updated.');
    }

    public function destroy(Address $address): RedirectResponse
    {
        if ($address->user_id !== Auth::id()) {
            abort(404);
        }

        $address->delete();

        return back()->with('success', 'Address deleted.');
    }

    private function enforceSingleDefaultAddress(Address $address): void
    {
        Address::where('user_id', $address->user_id)
            ->where('id', '!=', $address->id)
            ->update(['is_default' => false]);
    }
}
