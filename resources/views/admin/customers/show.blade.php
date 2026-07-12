<x-admin-layout title="Customer Details">

    <main class="pc-container-edit">

        <x-admin.page-header title="Customer Details" subtitle="View customer profile, orders, wishlist and activity">
            <x-slot:actions>
                <a href="{{ route('admin.customers.index') }}" class="btn btn-light">
                    <i class="ph ph-arrow-left me-1"></i>
                    Back
                </a>
            </x-slot:actions>
        </x-admin.page-header>

        <x-admin.breadcrumb :items="[
            ['label' => 'Customers', 'url' => route('admin.customers.index')],
            ['label' => 'Customer Details'],
        ]" />

        @include('admin.partials.alerts')

        <div class="row">

            <div class="col-lg-4">

                <div class="card mb-4">
                    <div class="card-body text-center">
                        <img src="{{ $customer->avatar ? \Illuminate\Support\Facades\Storage::url($customer->avatar) : 'https://placehold.co/120x120' }}"
                             class="rounded-circle mb-3"
                             width="120"
                             height="120"
                             alt="Customer">

                        <h5 class="mb-1">{{ $customer->name }}</h5>
                        <p class="text-muted mb-2">{{ $customer->email }}</p>

                        <x-admin.status-badge :status="$customer->status" />

                        <hr>

                        <p class="mb-1"><strong>Phone:</strong> {{ $customer->phone ?? '—' }}</p>
                        <p class="mb-0"><strong>Joined:</strong> {{ $customer->created_at->format('d M Y') }}</p>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Customer Summary</h5>
                    </div>

                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Orders</span>
                            <strong>{{ $customer->orders_count }}</strong>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Spent</span>
                            <strong>₹{{ number_format((float) ($customer->total_spent ?? 0), 2) }}</strong>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span>Wishlist Items</span>
                            <strong>{{ $customer->wishlists->count() }}</strong>
                        </div>

                        <div class="d-flex justify-content-between">
                            <span>Reviews</span>
                            <strong>{{ $customer->reviews->count() }}</strong>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Default Address</h5>
                        <a href="{{ route('admin.customers.addresses.index', $customer) }}" class="btn btn-sm btn-light">
                            <i class="ph ph-map-pin"></i>
                        </a>
                    </div>

                    <div class="card-body">
                        @if ($defaultAddress)
                            <p class="mb-0">
                                {{ $defaultAddress->name }}<br>
                                {{ $defaultAddress->address_line_1 }}@if ($defaultAddress->address_line_2), {{ $defaultAddress->address_line_2 }}@endif<br>
                                {{ $defaultAddress->city }}, {{ $defaultAddress->state }}<br>
                                {{ $defaultAddress->country }} - {{ $defaultAddress->pincode }}
                            </p>
                        @else
                            <p class="mb-0 text-muted">No default address set.</p>
                        @endif
                    </div>
                </div>

            </div>

            <div class="col-lg-8">

                <x-admin.table-card title="Order History" class="mb-4">
                    <x-slot:head>
                        <th>Order</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Action</th>
                    </x-slot:head>

                    @forelse ($customer->orders as $order)
                        <tr>
                            <td>#{{ $order->order_number }}</td>
                            <td>{{ $order->created_at->format('d M Y') }}</td>
                            <td>₹{{ number_format((float) $order->grand_total, 2) }}</td>
                            <td>
                                <x-admin.status-badge :status="$order->payment_status" />
                            </td>
                            <td>
                                <x-admin.status-badge :status="$order->order_status" />
                            </td>
                            <td>
                                <a href="{{ route('admin.sales.orders.show', $order) }}"
                                   class="btn btn-sm btn-info">
                                    <i class="ph ph-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-admin.empty-state>No orders yet.</x-admin.empty-state>
                            </td>
                        </tr>
                    @endforelse
                </x-admin.table-card>

                <x-admin.table-card title="Wishlist" class="mb-4">
                    <x-slot:head>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Added On</th>
                    </x-slot:head>

                    @forelse ($customer->wishlists as $wishlist)
                        <tr>
                            <td>{{ $wishlist->product->name ?? '—' }}</td>
                            <td>{{ $wishlist->product->category->name ?? '—' }}</td>
                            <td>{{ $wishlist->created_at->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">
                                <x-admin.empty-state>No wishlist items.</x-admin.empty-state>
                            </td>
                        </tr>
                    @endforelse
                </x-admin.table-card>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Product Reviews</h5>
                    </div>

                    <div class="card-body">
                        @forelse ($customer->reviews as $review)
                            <div class="{{ ! $loop->last ? 'border-bottom pb-3 mb-3' : '' }}">
                                <strong>{{ $review->product->name ?? '—' }}</strong>
                                <p class="mb-1">{{ str_repeat('⭐', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</p>
                                <p class="mb-0">{{ $review->review }}</p>
                            </div>
                        @empty
                            <p class="text-muted mb-0">No reviews yet.</p>
                        @endforelse
                    </div>
                </div>

            </div>

        </div>

    </main>

</x-admin-layout>