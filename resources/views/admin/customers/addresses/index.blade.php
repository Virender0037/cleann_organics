<x-admin-layout title="Customer Addresses">

    <main class="pc-container-edit admin-content-padded">

        <x-admin.page-header title="Customer Addresses" subtitle="Manage customer billing and shipping addresses">
            <x-slot:actions>
                <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-light">
                    <i class="ph ph-arrow-left me-1"></i>
                    Back
                </a>
            </x-slot:actions>
        </x-admin.page-header>

        <x-admin.breadcrumb :items="[
            ['label' => 'Customers', 'url' => route('admin.customers.index')],
            ['label' => 'Addresses'],
        ]" />

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="row">

            <!-- Billing Address -->
            <div class="col-lg-6">

                <div class="card mb-4">

                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="ph ph-receipt me-2"></i>
                            Billing Address
                        </h5>

                        @if ($billingAddress)
                            <a href="{{ route('admin.customers.addresses.edit', [$customer, $billingAddress]) }}"
                             class="btn btn-sm btn-warning"
                             title="Edit Address">
                                <i class="ph ph-pencil-simple"></i>
                            </a>
                        @endif
                    </div>

                    <div class="card-body">
                        @if ($billingAddress)
                            <p class="mb-1"><strong>{{ $billingAddress->name }}</strong></p>

                            <p class="mb-1">{{ $billingAddress->address_line_1 }}</p>

                            @if ($billingAddress->address_line_2)
                                <p class="mb-1">{{ $billingAddress->address_line_2 }}</p>
                            @endif

                            <p class="mb-1">{{ $billingAddress->city }}, {{ $billingAddress->state }}</p>

                            <p class="mb-1">{{ $billingAddress->country }} - {{ $billingAddress->pincode }}</p>

                            <p class="mb-0">Mobile : {{ $billingAddress->phone }}</p>
                        @else
                            <p class="text-muted mb-0">No billing address on file.</p>
                        @endif
                    </div>

                </div>

            </div>

            <!-- Shipping Address -->
            <div class="col-lg-6">

                <div class="card mb-4">

                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="ph ph-truck me-2"></i>
                            Shipping Address
                        </h5>

                        @if ($shippingAddress)
                            <a href="{{ route('admin.customers.addresses.edit', [$customer, $shippingAddress]) }}"
                            class="btn btn-sm btn-warning"
                            title="Edit Address">
                                <i class="ph ph-pencil-simple"></i>
                            </a>
                        @endif
                    </div>

                    <div class="card-body">
                        @if ($shippingAddress)
                            <p class="mb-1"><strong>{{ $shippingAddress->name }}</strong></p>

                            <p class="mb-1">{{ $shippingAddress->address_line_1 }}</p>

                            @if ($shippingAddress->address_line_2)
                                <p class="mb-1">{{ $shippingAddress->address_line_2 }}</p>
                            @endif

                            <p class="mb-1">{{ $shippingAddress->city }}, {{ $shippingAddress->state }}</p>

                            <p class="mb-1">{{ $shippingAddress->country }} - {{ $shippingAddress->pincode }}</p>

                            <p class="mb-0">Mobile : {{ $shippingAddress->phone }}</p>
                        @else
                            <p class="text-muted mb-0">No shipping address on file.</p>
                        @endif
                    </div>

                </div>

            </div>

        </div>

        <!-- Additional Addresses -->

        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">

                <h5 class="mb-0">Saved Addresses</h5>

                <a href="{{ route('admin.customers.addresses.create', $customer) }}"
                class="btn btn-primary btn-sm">
                    <i class="ph ph-plus me-1"></i>
                    Add Address
                </a>

            </div>

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead>

                        <tr>

                            <th>#</th>

                            <th>Address Type</th>

                            <th>Recipient</th>

                            <th>City</th>

                            <th>State</th>

                            <th>Country</th>

                            <th>Default</th>

                            <th width="120">Action</th>

                        </tr>

                        </thead>

                        <tbody>

                        @forelse ($addresses as $address)
                            <tr>

                                <td>{{ $address->id }}</td>

                                <td>
                                    <span class="badge {{ $address->type === 'billing' ? 'bg-info' : 'bg-primary' }}">
                                        {{ ucfirst($address->type) }}
                                    </span>
                                </td>

                                <td>{{ $address->name }}</td>

                                <td>{{ $address->city }}</td>

                                <td>{{ $address->state }}</td>

                                <td>{{ $address->country }}</td>

                                <td>
                                    <span class="badge {{ $address->is_default ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $address->is_default ? 'Yes' : 'No' }}
                                    </span>
                                </td>

                                <td>

                                    <a href="{{ route('admin.customers.addresses.edit', [$customer, $address]) }}"
                                    class="btn btn-sm btn-warning"
                                    title="Edit Address">
                                        <i class="ph ph-pencil-simple"></i>
                                    </a>

                                    <form action="{{ route('admin.customers.addresses.destroy', [$customer, $address]) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this address?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="ph ph-trash"></i>
                                        </button>
                                    </form>

                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <x-admin.empty-state>No saved addresses.</x-admin.empty-state>
                                </td>
                            </tr>
                        @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </main>

</x-admin-layout>