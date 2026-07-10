<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCustomerAddressRequest;
use App\Http\Requests\Admin\UpdateCustomerAddressRequest;
use App\Models\Address;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CustomerAddressController extends Controller
{
    public function index(User $customer): View
    {
        $addresses = $customer->addresses()->latest()->get();
        $billingAddress = $addresses->firstWhere('type', 'billing');
        $shippingAddress = $addresses->firstWhere('type', 'shipping');

        return view('admin.customers.addresses.index', compact('customer', 'addresses', 'billingAddress', 'shippingAddress'));
    }

    public function create(User $customer): View
    {
        return view('admin.customers.addresses.create', compact('customer'));
    }

    public function store(StoreCustomerAddressRequest $request, User $customer): RedirectResponse
    {
        $data = $request->validated();
        $data['country'] = $data['country'] ?? 'India';

        $address = $customer->addresses()->create($data);

        if ($address->is_default) {
            $this->enforceSingleDefaultAddress($address);
        }

        return redirect()->route('admin.customers.addresses.index', $customer)->with('success', 'Address created.');
    }

    public function edit(User $customer, Address $address): View
    {
        return view('admin.customers.addresses.edit', compact('customer', 'address'));
    }

    public function update(UpdateCustomerAddressRequest $request, User $customer, Address $address): RedirectResponse
    {
        $data = $request->validated();
        $data['country'] = $data['country'] ?? 'India';

        $address->update($data);

        if ($address->is_default) {
            $this->enforceSingleDefaultAddress($address);
        }

        return redirect()->route('admin.customers.addresses.index', $customer)->with('success', 'Address updated.');
    }

    public function destroy(User $customer, Address $address): RedirectResponse
    {
        $address->delete();

        return back()->with('success', 'Address deleted.');
    }

    /**
     * Ensure only the given address is marked as default for its customer.
     */
    private function enforceSingleDefaultAddress(Address $address): void
    {
        Address::where('user_id', $address->user_id)
            ->where('id', '!=', $address->id)
            ->update(['is_default' => false]);
    }
}
