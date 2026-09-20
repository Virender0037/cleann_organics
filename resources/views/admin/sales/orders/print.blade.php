{{--
    Printer-friendly order / invoice sheet. Standalone document on purpose (no admin
    layout, sidebar or navbar) with all its CSS inline, so it depends on no public
    asset and prints identically wherever it is opened. Every figure is read from the
    stored Order / OrderItem / Payment rows — nothing is recalculated or hardcoded
    except arithmetic needed to show how the stored total was reached.
--}}
@php
    use Illuminate\Support\Facades\Storage;

    $money = fn ($n) => '₹'.number_format((float) $n, 2);
    $siteName = $company['site_name'] ?? 'Cleann Organics';
    $logo = storage_image_url($company['logo'] ?? null, asset('images/vertical-logo.jpeg'));

    $methodLabels = [
        'cod' => 'Cash on Delivery (COD)',
        'upi' => 'UPI',
        'manual_upi' => 'UPI (manual payment)',
        'razorpay' => 'Razorpay (online payment)',
        'bank_transfer' => 'Bank Transfer',
    ];
    $methodLabel = $methodLabels[$order->payment_method] ?? ucwords(str_replace('_', ' ', (string) $order->payment_method));

    $subtotal = (float) $order->subtotal;
    $discount = (float) $order->discount_amount;
    $shipping = (float) $order->shipping_amount;
    $grand = (float) $order->grand_total;
    // How the STORED grand total was reached. Orders placed under the tax-inclusive rule
    // satisfy grand = subtotal - discount + shipping (GST is only a breakup inside it);
    // older orders had GST added on top, which shows up as a positive remainder here.
    $taxAddedOnTop = round($grand - ($subtotal - $discount + $shipping), 2);
    $taxIncluded = $taxAddedOnTop <= 0.01;

    $payment = $order->payment;
    $paid = $order->payment_status === 'paid';

    $addressLines = function (array $a): array {
        return array_values(array_filter([
            $a['name'] ?? null,
            trim(($a['address_line_1'] ?? '').(($a['address_line_2'] ?? null) ? ', '.$a['address_line_2'] : ''), ', '),
            trim(($a['city'] ?? '').(($a['state'] ?? null) ? ', '.$a['state'] : ''), ', '),
            trim(($a['country'] ?? '').(($a['pincode'] ?? null) ? ' - '.$a['pincode'] : ''), ' -'),
            ($a['phone'] ?? null) ? 'Phone: '.$a['phone'] : null,
        ], fn ($line) => filled($line)));
    };
    $shipTo = $addressLines($order->shippingSnapshot());
    $billTo = $addressLines($order->billingSnapshot());
    $customerPhone = $order->shipping_phone ?: ($order->billing_phone ?: ($order->user->phone ?? null));
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Order {{ $order->order_number }} — {{ $siteName }}</title>
    <style>
        @page { size: A4; margin: 12mm; }
        * { box-sizing: border-box; }
        html { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        body { margin: 0; background: #eef0f2; color: #000; font: 13px/1.45 "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
        .toolbar { max-width: 210mm; margin: 12px auto 0; padding: 0 12px; display: flex; gap: 8px; justify-content: flex-end; }
        .toolbar button, .toolbar a { font: inherit; padding: 8px 16px; border-radius: 6px; border: 1px solid #1a1a1a; background: #fff; color: #000; cursor: pointer; text-decoration: none; }
        .toolbar button.primary { background: #00b207; border-color: #00b207; color: #fff; font-weight: 600; }
        .sheet { width: 210mm; max-width: 100%; margin: 12px auto 24px; padding: 14mm; background: #fff; box-shadow: 0 2px 12px rgba(0, 0, 0, .12); }
        header.doc { display: flex; justify-content: space-between; gap: 16px; align-items: flex-start; border-bottom: 2px solid #000; padding-bottom: 12px; }
        header.doc img { display: block; max-height: 64px; max-width: 220px; width: auto; height: auto; object-fit: contain; }
        header.doc .co { margin-top: 6px; font-size: 12px; color: #000; }
        header.doc .title { text-align: right; }
        header.doc .title h1 { margin: 0 0 4px; font-size: 22px; letter-spacing: .02em; }
        .meta { width: 100%; margin-top: 14px; border-collapse: collapse; }
        .meta td { padding: 3px 0; vertical-align: top; }
        .meta td:first-child { width: 34%; font-weight: 600; }
        .grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 14px; }
        .box { border: 1px solid #000; padding: 8px 10px; min-height: 84px; }
        .box h2 { margin: 0 0 4px; font-size: 12px; text-transform: uppercase; letter-spacing: .05em; }
        .box p { margin: 0; overflow-wrap: anywhere; }
        table.items { width: 100%; margin-top: 16px; border-collapse: collapse; }
        table.items th, table.items td { border: 1px solid #000; padding: 6px 8px; text-align: left; vertical-align: top; }
        table.items thead { display: table-header-group; }
        table.items th { background: #f0f0f0; font-size: 12px; }
        table.items tr { page-break-inside: avoid; break-inside: avoid; }
        table.items .num { text-align: right; white-space: nowrap; }
        table.items .sub { display: block; font-size: 11px; color: #333; overflow-wrap: anywhere; }
        .totals { width: 100%; max-width: 88mm; margin: 12px 0 0 auto; border-collapse: collapse; page-break-inside: avoid; break-inside: avoid; }
        .totals td { padding: 4px 8px; border-bottom: 1px solid #999; }
        .totals td:last-child { text-align: right; white-space: nowrap; }
        .totals tr.grand td { border-top: 2px solid #000; border-bottom: 2px solid #000; font-weight: 700; font-size: 15px; }
        .note { margin-top: 12px; font-size: 12px; page-break-inside: avoid; break-inside: avoid; }
        .payline { margin-top: 12px; border: 1px solid #000; padding: 8px 10px; page-break-inside: avoid; break-inside: avoid; }
        .payline strong.cod { font-size: 14px; }
        footer.doc { margin-top: 18px; padding-top: 8px; border-top: 1px solid #000; text-align: center; font-size: 11px; }
        .scroll { overflow-x: auto; }

        @media (max-width: 640px) {
            .sheet { padding: 12px; margin: 8px auto; }
            header.doc { flex-direction: column; }
            header.doc .title { text-align: left; }
            .grid2 { grid-template-columns: 1fr; }
            table.items { font-size: 12px; }
        }
        @media print {
            body { background: #fff; }
            .no-print, .toolbar { display: none !important; }
            .sheet { width: auto; max-width: none; margin: 0; padding: 0; box-shadow: none; }
            .scroll { overflow: visible; }
            a { color: #000; text-decoration: none; }
        }
    </style>
</head>
<body>
    <div class="toolbar no-print">
        <button type="button" class="primary" onclick="window.print()">Print</button>
        <button type="button" onclick="window.close()">Close</button>
        <a href="{{ route('admin.sales.orders.show', $order) }}">Back to order</a>
    </div>

    <main class="sheet">
        <header class="doc">
            <div>
                <img src="{{ $logo }}" alt="{{ $siteName }}">
                <div class="co">
                    <strong>{{ $company['company_name'] ?? $siteName }}</strong><br>
                    @if (! empty($company['company_address']))
                        {!! nl2br(e($company['company_address'])) !!}<br>
                    @endif
                    @if (! empty($company['company_phone']))Phone: {{ $company['company_phone'] }}@endif
                    @if (! empty($company['company_email']))@if (! empty($company['company_phone'])) &middot; @endif{{ $company['company_email'] }}@endif
                    @if (! empty($company['gst_number']))<br>GSTIN: {{ $company['gst_number'] }}@endif
                </div>
            </div>
            <div class="title">
                <h1>ORDER INVOICE</h1>
                <div>Order <strong>#{{ $order->order_number }}</strong></div>
                @if ($order->invoice_number)<div>Invoice No: {{ $order->invoice_number }}</div>@endif
            </div>
        </header>

        <table class="meta">
            <tr><td>Order number</td><td>{{ $order->order_number }}</td></tr>
            <tr><td>Order date</td><td>{{ $order->created_at->format('d M Y, h:i A') }}</td></tr>
            <tr><td>Order status</td><td>{{ ucfirst($order->order_status) }}</td></tr>
            <tr><td>Customer</td><td>{{ $order->user?->name ?? $order->shipping_name ?? '—' }}</td></tr>
            <tr><td>Email</td><td>{{ $order->user?->email ?? '—' }}</td></tr>
            <tr><td>Phone</td><td>{{ $customerPhone ?: '—' }}</td></tr>
        </table>

        <div class="grid2">
            <div class="box">
                <h2>Billing address</h2>
                <p>{!! $billTo ? implode('<br>', array_map('e', $billTo)) : '—' !!}</p>
            </div>
            <div class="box">
                <h2>Delivery address</h2>
                <p>{!! $shipTo ? implode('<br>', array_map('e', $shipTo)) : '—' !!}</p>
            </div>
        </div>

        <div class="scroll">
            <table class="items">
                <thead>
                    <tr>
                        <th style="width:5%">#</th>
                        <th>Item</th>
                        <th class="num" style="width:8%">Qty</th>
                        <th class="num" style="width:16%">Unit price</th>
                        <th class="num" style="width:17%">Line total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $i => $item)
                        @php
                            $details = array_filter([
                                $item->variant_sku ? 'SKU: '.$item->variant_sku : null,
                                $item->variant_size ? 'Size: '.$item->variant_size : null,
                                $item->variant_color ? 'Colour: '.$item->variant_color : null,
                                $item->variant_pack_quantity ? 'Pack of '.$item->variant_pack_quantity : null,
                                $item->weight ? 'Weight: '.rtrim(rtrim(number_format((float) $item->weight, 2, '.', ''), '0'), '.').($item->unit ? ' '.$item->unit : '') : null,
                            ]);
                        @endphp
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>
                                {{ $item->product_name }}
                                @if ($details)<span class="sub">{{ implode(' · ', $details) }}</span>@endif
                                @if ((float) $item->discount_amount > 0)<span class="sub">Item discount: {{ $money($item->discount_amount) }}</span>@endif
                            </td>
                            <td class="num">{{ $item->quantity }}</td>
                            <td class="num">{{ $money($item->unit_price) }}</td>
                            <td class="num">{{ $money($item->total_price) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <table class="totals">
            <tr><td>Subtotal</td><td>{{ $money($subtotal) }}</td></tr>
            @if ($discount > 0)
                <tr><td>Discount{{ $order->coupon ? ' ('.$order->coupon->code.')' : '' }}</td><td>− {{ $money($discount) }}</td></tr>
            @endif
            <tr><td>Shipping{{ $order->shipping_zone_name ? ' ('.$order->shipping_zone_name.')' : '' }}</td><td>{{ $shipping > 0 ? $money($shipping) : 'Free' }}</td></tr>
            @if (! $taxIncluded)
                <tr><td>Tax</td><td>{{ $money($taxAddedOnTop) }}</td></tr>
            @endif
            <tr class="grand"><td>Total</td><td>{{ $money($grand) }}</td></tr>
        </table>

        <p class="note">
            @if ($taxIncluded)
                Inclusive of all taxes{{ (float) $order->tax_amount > 0 ? ' (includes GST '.$money($order->tax_amount).')' : '' }}.
            @else
                Tax shown above was added to the item total when this order was placed.
            @endif
        </p>

        <div class="payline">
            <div><strong>Payment method:</strong> {{ $methodLabel }}</div>
            <div>
                <strong>Payment status:</strong>
                @if ($order->payment_method === 'cod' && ! $paid)
                    <strong class="cod">COD — To be paid on delivery</strong>
                @elseif ($order->payment_method === 'cod')
                    COD — Paid
                @else
                    {{ ucfirst($order->payment_status) }}
                @endif
            </div>
            @if ($payment)
                @if ($order->payment_method !== 'cod' && $payment->status && $payment->status !== $order->payment_status)
                    <div>Payment record: {{ ucfirst($payment->status) }}@if ($payment->admin_note) — {{ $payment->admin_note }}@endif</div>
                @endif
                @if ($payment->upi_reference)<div>UPI reference (UTR): {{ $payment->upi_reference }}</div>@endif
                @if ($payment->gateway_payment_id)<div>Gateway payment ID: {{ $payment->gateway_payment_id }}</div>@endif
                @if ($payment->transaction_id)<div>Transaction ID: {{ $payment->transaction_id }}</div>@endif
                @if ($payment->paid_at)<div>Paid on: {{ $payment->paid_at->format('d M Y, h:i A') }}</div>@endif
            @endif
            @if ($order->payment_method === 'bank_transfer' && ! $paid)
                <div>Awaiting manual bank-transfer verification.</div>
            @endif
        </div>

        @if (filled($order->notes))
            <div class="payline">
                <strong>Customer notes:</strong><br>
                {!! nl2br(e($order->notes)) !!}
            </div>
        @endif

        <footer class="doc">Thank you for shopping with {{ $siteName }}.</footer>
    </main>

    @if (request()->boolean('auto'))
        <script>
            // Opened from the Orders list: open the print dialog once the logo has loaded.
            // Cancelling the dialog simply leaves this tab open with Print / Close buttons.
            window.addEventListener('load', function () { window.setTimeout(function () { window.print(); }, 300); });
        </script>
    @endif
</body>
</html>
