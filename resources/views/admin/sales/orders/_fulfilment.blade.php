{{--
    Shipping / Fulfilment (NimbusPost) on the admin order page. Manual booking by design; FulfilmentService enforces
    the payment gate and one live shipment per order. Never shows credentials — only the NAMES of missing settings.
    Expects: $order (with shipments, activeShipment), $shipmentBlockers, $packageSuggestion, $nimbusConfigured, $nimbusMissing.
--}}
@php($live = $order->activeShipment)
<div class="card mb-4" id="fulfilment">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0">Shipping / Fulfilment</h5>
        <span class="text-muted small">Provider: <strong>NimbusPost</strong></span>
    </div>

    <div class="card-body">
        @unless ($nimbusConfigured)
            <div class="alert alert-warning">
                <strong>NimbusPost is not configured.</strong> Shipments can't be booked until these are set in the server's <code>.env</code>:
                <code>{{ implode(', ', $nimbusMissing) }}</code>.
            </div>
        @endunless

        @if ($errors->shipment->any())
            <div class="alert alert-danger">
                @foreach ($errors->shipment->all() as $message)
                    <div>{{ $message }}</div>
                @endforeach
            </div>
        @endif

        @if ($live)
            <div class="row">
                <div class="col-sm-6 mb-3">
                    <label class="text-muted small d-block">Shipment status</label>
                    <span class="badge bg-light-primary">{{ $live->adminStatusLabel() }}</span>
                    @if ($live->provider_status)
                        <small class="text-muted ms-1">({{ $live->provider_status }})</small>
                    @endif
                </div>
                <div class="col-sm-6 mb-3">
                    <label class="text-muted small d-block">Courier</label>
                    <strong>{{ $live->courier_name ?? '—' }}</strong>
                </div>
                <div class="col-sm-6 mb-3">
                    <label class="text-muted small d-block">AWB / tracking number</label>
                    <strong>{{ $live->awb_number ?? '—' }}</strong>
                </div>
                <div class="col-sm-6 mb-3">
                    <label class="text-muted small d-block">Payment type sent</label>
                    {{ strtoupper($live->payment_type) }}{{ $live->payment_type === 'cod' ? ' · collect ₹'.number_format((float) $live->cod_amount, 2) : '' }}
                </div>
                <div class="col-sm-6 mb-3">
                    <label class="text-muted small d-block">Pickup</label>
                    {{ $live->pickup_requested ? 'Auto pickup requested' : 'Pickup not requested automatically — schedule it in NimbusPost' }}
                </div>
                <div class="col-sm-6 mb-3">
                    <label class="text-muted small d-block">Booked / last synced</label>
                    {{ $live->booked_at?->format('d M Y, h:i A') ?? '—' }} / {{ $live->last_synced_at?->format('d M Y, h:i A') ?? 'never' }}
                </div>
                @if ($live->ndr_reason)
                    <div class="col-12 mb-3">
                        <div class="alert alert-warning mb-0"><strong>Delivery exception (NDR):</strong> {{ $live->ndr_reason }}</div>
                    </div>
                @endif
                @if ($live->rto_awb)
                    <div class="col-12 mb-3"><span class="text-muted small">Return (RTO) AWB:</span> {{ $live->rto_awb }}</div>
                @endif
                @if ($live->failure_reason && $live->status === \App\Models\Shipment::CREATION_UNCONFIRMED)
                    <div class="col-12 mb-3">
                        <div class="alert alert-warning mb-0">
                            NimbusPost did not confirm this booking. Check the NimbusPost panel for order <strong>{{ $order->order_number }}</strong>.
                            If it was <em>not</em> created there, release it below and try again. If it <em>was</em> created, cancel it in NimbusPost first.
                        </div>
                    </div>
                @endif
            </div>

            <div class="d-flex flex-wrap gap-2">
                @if ($live->isTrackable())
                    <form action="{{ route('admin.sales.orders.shipments.refresh', [$order, $live]) }}" method="POST" data-once>
                        @csrf
                        <button type="submit" class="btn btn-light-primary"><i class="ph ph-arrows-clockwise me-1"></i>Track / refresh status</button>
                    </form>
                @endif
                @if ($live->label_url)
                    <a href="{{ $live->label_url }}" class="btn btn-light-secondary" target="_blank" rel="noopener noreferrer"><i class="ph ph-printer me-1"></i>Shipping label</a>
                @endif
                @if ($live->isCancellable())
                    <form action="{{ route('admin.sales.orders.shipments.cancel', [$order, $live]) }}" method="POST" data-once data-confirm-text="Cancel this shipment with NimbusPost?">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger"><i class="ph ph-x-circle me-1"></i>Cancel shipment</button>
                    </form>
                @endif
                @if ($live->status === \App\Models\Shipment::CREATION_UNCONFIRMED)
                    <form action="{{ route('admin.sales.orders.shipments.release', [$order, $live]) }}" method="POST" data-once>
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary">Not booked in NimbusPost — allow retry</button>
                    </form>
                @endif
            </div>

            @if (! empty($live->tracking_history))
                <div class="table-responsive mt-3">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Time</th><th>Status</th><th>Location</th><th>Update</th></tr></thead>
                        <tbody>
                            @foreach (array_reverse($live->tracking_history) as $event)
                                <tr>
                                    <td class="text-nowrap">{{ $event['event_time'] ?? '' }}</td>
                                    <td>{{ $event['status_code'] ?? '' }}</td>
                                    <td>{{ $event['location'] ?? '' }}</td>
                                    <td>{{ $event['message'] ?? '' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @else
            @if ($shipmentBlockers === [])
                <p class="mb-2">This order is eligible. Enter the packed parcel's weight and size, then book it with NimbusPost.</p>
            @else
                <ul class="text-muted small mb-3">
                    @foreach ($shipmentBlockers as $blocker)
                        <li>{{ $blocker }}</li>
                    @endforeach
                </ul>
            @endif

            @foreach ($packageSuggestion['warnings'] as $warning)
                <p class="small text-warning mb-1">{{ $warning }}</p>
            @endforeach

            <form action="{{ route('admin.sales.orders.shipments.store', $order) }}" method="POST" class="row g-2 align-items-end mt-1" data-once>
                @csrf
                <div class="col-sm-3">
                    <label class="form-label small">Weight (g) *</label>
                    <input type="number" name="package_weight_grams" min="1" step="1" class="form-control" required value="{{ old('package_weight_grams', $packageSuggestion['weight_grams']) }}">
                </div>
                <div class="col-sm-2">
                    <label class="form-label small">Length (cm)</label>
                    <input type="number" name="package_length_cm" min="0.1" step="0.1" class="form-control" value="{{ old('package_length_cm', $packageSuggestion['length_cm']) }}">
                </div>
                <div class="col-sm-2">
                    <label class="form-label small">Width (cm)</label>
                    <input type="number" name="package_width_cm" min="0.1" step="0.1" class="form-control" value="{{ old('package_width_cm', $packageSuggestion['width_cm']) }}">
                </div>
                <div class="col-sm-2">
                    <label class="form-label small">Height (cm)</label>
                    <input type="number" name="package_height_cm" min="0.1" step="0.1" class="form-control" value="{{ old('package_height_cm', $packageSuggestion['height_cm']) }}">
                </div>
                <div class="col-sm-3 d-grid">
                    <button type="submit" class="btn btn-primary" @if ($shipmentBlockers !== []) disabled aria-disabled="true" title="{{ implode(' ', $shipmentBlockers) }}" @endif>
                        <i class="ph ph-truck me-1"></i>{{ $order->shipments->isNotEmpty() ? 'Retry shipment' : 'Create shipment' }}
                    </button>
                </div>
            </form>
        @endif

        @php($history = $order->shipments->reject(fn ($s) => $live && $s->id === $live->id))
        @if ($history->isNotEmpty())
            <details class="mt-3">
                <summary class="small text-muted">Earlier attempts ({{ $history->count() }})</summary>
                <ul class="small mb-0 mt-2">
                    @foreach ($history as $past)
                        <li>{{ $past->created_at->format('d M Y, h:i A') }} — {{ $past->adminStatusLabel() }}{{ $past->awb_number ? ' · AWB '.$past->awb_number : '' }}{{ $past->failure_reason ? ' · '.$past->failure_reason : '' }}</li>
                    @endforeach
                </ul>
            </details>
        @endif
    </div>
</div>

<script>
    // Every fulfilment button submits once: disabled immediately so a double click can't fire a second request
    // (the server is duplicate-safe as well). Optional confirmation for destructive actions.
    document.querySelectorAll('#fulfilment form[data-once]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            var text = form.getAttribute('data-confirm-text');
            if (form.dataset.submitting === '1' || (text && !window.confirm(text))) { event.preventDefault(); return; }
            form.dataset.submitting = '1';
            form.querySelectorAll('button[type="submit"]').forEach(function (button) { button.disabled = true; });
        });
    });
</script>
