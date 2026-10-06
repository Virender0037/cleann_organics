<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateShipmentRequest;
use App\Models\Order;
use App\Models\Shipment;
use App\Services\Shipping\Exceptions\NimbusPostException;
use App\Services\Shipping\FulfilmentNotAllowed;
use App\Services\Shipping\FulfilmentService;
use Illuminate\Http\RedirectResponse;

/**
 * Admin → Sales → Orders → Order detail → Shipping / Fulfilment. Manual by design: an admin books the courier once
 * the order is eligible (FulfilmentService enforces the payment gate and the one-live-shipment rule). Lives inside
 * the superadmin-only admin route group; CSRF applies as on every admin form.
 */
class OrderShipmentController extends Controller
{
    public function __construct(private readonly FulfilmentService $fulfilment) {}

    public function store(CreateShipmentRequest $request, Order $order): RedirectResponse
    {
        try {
            $shipment = $this->fulfilment->createShipment($order, [
                'weight_grams' => (int) $request->validated('package_weight_grams'),
                'length_cm' => $request->filled('package_length_cm') ? (float) $request->validated('package_length_cm') : null,
                'width_cm' => $request->filled('package_width_cm') ? (float) $request->validated('package_width_cm') : null,
                'height_cm' => $request->filled('package_height_cm') ? (float) $request->validated('package_height_cm') : null,
            ]);
        } catch (FulfilmentNotAllowed $e) {
            return $this->back($order)->with('error', $e->getMessage());
        }

        return match ($shipment->status) {
            Shipment::BOOKED => $this->back($order)->with('success', 'Shipment booked with NimbusPost. AWB '.$shipment->awb_number.($shipment->courier_name ? ' ('.$shipment->courier_name.')' : '').'.'),
            Shipment::CREATION_UNCONFIRMED => $this->back($order)->with('error', 'NimbusPost did not respond in time, so it is unclear whether the shipment was booked. Check the NimbusPost panel for order '.$order->order_number.' before trying again.'),
            default => $this->back($order)->with('error', 'NimbusPost could not book this shipment: '.$shipment->failure_reason),
        };
    }

    public function refresh(Order $order, Shipment $shipment): RedirectResponse
    {
        $this->ensureBelongs($order, $shipment);

        try {
            $shipment = $this->fulfilment->refreshTracking($shipment);
        } catch (FulfilmentNotAllowed|NimbusPostException $e) {
            return $this->back($order)->with('error', 'Tracking could not be refreshed: '.$e->getMessage());
        }

        return $this->back($order)->with('success', 'Tracking updated: '.$shipment->adminStatusLabel().'.');
    }

    public function cancel(Order $order, Shipment $shipment): RedirectResponse
    {
        $this->ensureBelongs($order, $shipment);

        try {
            $this->fulfilment->cancel($shipment);
        } catch (FulfilmentNotAllowed|NimbusPostException $e) {
            return $this->back($order)->with('error', 'The shipment was not cancelled: '.$e->getMessage());
        }

        return $this->back($order)->with('success', 'Shipment cancelled with NimbusPost. A new shipment can be created.');
    }

    public function release(Order $order, Shipment $shipment): RedirectResponse
    {
        $this->ensureBelongs($order, $shipment);

        try {
            $this->fulfilment->releaseUnconfirmed($shipment);
        } catch (FulfilmentNotAllowed $e) {
            return $this->back($order)->with('error', $e->getMessage());
        }

        return $this->back($order)->with('success', 'Marked as not booked. You can create the shipment again.');
    }

    private function ensureBelongs(Order $order, Shipment $shipment): void
    {
        abort_unless($shipment->order_id === $order->id, 404);
    }

    private function back(Order $order): RedirectResponse
    {
        return redirect()->to(route('admin.sales.orders.show', $order).'#fulfilment');
    }
}
