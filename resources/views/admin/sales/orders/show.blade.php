<x-admin-layout title="Order Details">
    <main class="pc-container-edit admin-content-padded">

        <x-admin.page-header title="Order #{{ $order->order_number }}" subtitle="Complete order details and activity">
            <x-slot:actions>
                <button class="btn btn-light-secondary me-2" disabled>
                    <i class="ph ph-download-simple me-1"></i>
                    Invoice
                </button>

                <button class="btn btn-success me-2" disabled>
                    <i class="ph ph-printer me-1"></i>
                    Print
                </button>

                <button class="btn btn-primary" disabled>
                    <i class="ph ph-arrows-clockwise me-1"></i>
                    Update Status
                </button>
            </x-slot:actions>
        </x-admin.page-header>

        <x-admin.breadcrumb :items="[
            ['label' => 'Sales'],
            ['label' => 'Orders', 'url' => route('admin.sales.orders.index')],
            ['label' => 'Order Details'],
        ]" />

        @include('admin.partials.alerts')

        <div class="row">

            <div class="col-lg-8">

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Order Timeline</h5>
                    </div>

                    <div class="card-body">
                        @if ($order->order_status === 'cancelled')
                            <p class="text-danger mb-0">
                                <strong>Order Cancelled</strong>
                                @if ($order->cancelled_at)
                                    — {{ $order->cancelled_at->format('d M Y, h:i A') }}
                                @endif
                            </p>
                            @if ($order->cancellation_reason)
                                <p class="text-muted mb-0 mt-2">{{ $order->cancellation_reason }}</p>
                            @endif
                        @else
                            @php
                                $stages = [
                                    'Placed' => $order->created_at,
                                    'Confirmed' => $order->confirmed_at,
                                    'Packed' => $order->packed_at,
                                    'Shipped' => $order->shipped_at,
                                    'Delivered' => $order->delivered_at,
                                ];
                            @endphp
                            <div class="d-flex justify-content-between text-center">
                                @foreach ($stages as $label => $timestamp)
                                    <div>
                                        <span class="badge {{ $timestamp ? 'bg-success' : 'bg-light text-dark' }} rounded-pill mb-2">
                                            {{ $timestamp ? '✓' : '○' }}
                                        </span>
                                        <p class="mb-0 fw-bold">{{ $label }}</p>
                                        <small>{{ $timestamp ? $timestamp->format('d M Y') : 'Pending' }}</small>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <x-admin.table-card title="Ordered Products" class="mb-4">
                    <x-slot:head>
                        <th>Product</th>
                        <th>Variant</th>
                        <th>SKU</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Tax</th>
                        <th>Total</th>
                    </x-slot:head>

                    @forelse ($order->items as $item)
                        <tr>
                            <td>
                                <strong>{{ $item->product_name }}</strong>
                            </td>
                            <td>{{ $item->variant_size ?? $item->variant_color ?? $item->variant_pack_quantity ?? '—' }}</td>
                            <td>{{ $item->variant_sku ?? '—' }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>₹{{ number_format((float) $item->unit_price, 2) }}</td>
                            <td>₹{{ number_format((float) $item->tax_amount, 2) }}</td>
                            <td>₹{{ number_format((float) $item->total_price, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-admin.empty-state>No items on this order.</x-admin.empty-state>
                            </td>
                        </tr>
                    @endforelse
                </x-admin.table-card>

                <div class="row">

                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5>Payment Details</h5>
                            </div>

                            <div class="card-body">
                                @if ($order->payment)
                                    <p><strong>Payment Method:</strong> {{ strtoupper($order->payment->payment_method) }}</p>
                                    <p><strong>Transaction ID:</strong> {{ $order->payment->transaction_id ?? '—' }}</p>
                                    <p><strong>Amount:</strong> ₹{{ number_format((float) $order->payment->amount, 2) }}</p>
                                    <p><strong>Status:</strong> <x-admin.status-badge :status="$order->payment->status" /></p>
                                    <p class="mb-0"><strong>Paid At:</strong> {{ $order->payment->paid_at ? $order->payment->paid_at->format('d M Y, h:i A') : '—' }}</p>
                                @else
                                    <p class="text-muted mb-0">No payment record found.</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5>Shipment Details</h5>
                            </div>

                            <div class="card-body">
                                <p class="text-muted mb-0">Not available — no shipment tracking is recorded for orders in this system yet.</p>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Internal Notes</h5>
                    </div>

                    <div class="card-body">
                        @if ($order->notes)
                            <p class="mb-0">{{ $order->notes }}</p>
                        @else
                            <p class="text-muted mb-0">No notes on this order.</p>
                        @endif
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Customer Communication</h5>
                    </div>

                    <div class="card-body">
                        <p class="text-muted mb-0">Not available — no communication log is recorded for orders in this system yet.</p>
                    </div>
                </div>

            </div>

            <div class="col-lg-4">

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Order Summary</h5>
                    </div>

                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal</span>
                            <strong>₹{{ number_format((float) $order->subtotal, 2) }}</strong>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span>Discount</span>
                            <strong class="text-success">-₹{{ number_format((float) $order->discount_amount, 2) }}</strong>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping</span>
                            <strong>₹{{ number_format((float) $order->shipping_amount, 2) }}</strong>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax</span>
                            <strong>₹{{ number_format((float) $order->tax_amount, 2) }}</strong>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between">
                            <h5>Total</h5>
                            <h5>₹{{ number_format((float) $order->grand_total, 2) }}</h5>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Customer</h5>
                    </div>

                    <div class="card-body">
                        <p class="mb-1"><strong>{{ $order->user->name ?? '—' }}</strong></p>
                        <p class="mb-1">{{ $order->user->email ?? '—' }}</p>
                        <p class="mb-0">{{ $order->user->phone ?? '—' }}</p>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>{{ $order->address && $order->address->type === 'billing' ? 'Billing Address' : 'Delivery Address' }}</h5>
                    </div>

                    <div class="card-body">
                        @if ($order->address)
                            <p class="mb-0">
                                {{ $order->address->name }}<br>
                                {{ $order->address->address_line_1 }}@if ($order->address->address_line_2), {{ $order->address->address_line_2 }}@endif<br>
                                {{ $order->address->city }}, {{ $order->address->state }}<br>
                                {{ $order->address->country }} - {{ $order->address->pincode }}
                            </p>
                        @else
                            <p class="text-muted mb-0">No address on file for this order.</p>
                        @endif
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Documents</h5>
                    </div>

                    <div class="card-body">
                        <p class="mb-3">
                            <strong>Invoice Number:</strong> {{ $order->invoice_number ?? '—' }}<br>
                            <strong>Invoice Date:</strong> {{ $order->invoice_date ? $order->invoice_date->format('d M Y') : '—' }}
                        </p>
                        <div class="d-grid gap-2">
                            <button class="btn btn-light-primary" disabled>
                                <i class="ph ph-file-pdf me-1"></i>
                                Download Invoice
                            </button>

                            <button class="btn btn-light-secondary" disabled>
                                <i class="ph ph-package me-1"></i>
                                Packing Slip
                            </button>

                            <button class="btn btn-light-secondary" disabled>
                                <i class="ph ph-truck me-1"></i>
                                Shipping Label
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Return & Refund History</h5>
                    </div>

                    <div class="card-body">
                        @forelse ($order->returns as $return)
                            <p class="{{ ! $loop->last ? 'border-bottom pb-2 mb-2' : 'mb-0' }}">
                                <a href="{{ route('admin.sales.returns.show', $return) }}"><strong>#{{ $return->return_number }}</strong></a> —
                                <x-admin.status-badge :status="$return->status" /><br>
                                <small class="text-muted">Refund: ₹{{ number_format((float) $return->refund_amount, 2) }}</small>
                            </p>
                        @empty
                            <p class="text-muted mb-0">No return or refund request found.</p>
                        @endforelse
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Activity Log</h5>
                    </div>

                    <div class="card-body">
                        <p class="text-muted mb-0">Not available — no activity log is recorded for orders in this system yet.</p>
                    </div>
                </div>

            </div>

        </div>

    </main>
</x-admin-layout>