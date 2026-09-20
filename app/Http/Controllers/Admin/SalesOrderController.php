<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Services\CsvExporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesOrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = Order::with('user')
            ->withCount('items')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where(function ($query) use ($search) {
                    $query->where('order_number', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('order_status'), fn ($query) => $query->where('order_status', $request->string('order_status')))
            ->when($request->filled('payment_status'), fn ($query) => $query->where('payment_status', $request->string('payment_status')))
            ->when($request->filled('from'), fn ($query) => $query->whereDate('created_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('created_at', '<=', $request->date('to')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.sales.orders.index', compact('orders'));
    }

    public function export(Request $request, CsvExporter $exporter): StreamedResponse
    {
        $orders = Order::with('user')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where(function ($query) use ($search) {
                    $query->where('order_number', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('order_status'), fn ($query) => $query->where('order_status', $request->string('order_status')))
            ->when($request->filled('payment_status'), fn ($query) => $query->where('payment_status', $request->string('payment_status')))
            ->when($request->filled('from'), fn ($query) => $query->whereDate('created_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('created_at', '<=', $request->date('to')))
            ->latest()
            ->lazy(200);

        $headers = ['id', 'order_number', 'customer_name', 'customer_email', 'total', 'payment_status', 'payment_method', 'order_status', 'date'];

        $rows = $orders->map(fn (Order $order) => [
            $order->id,
            $order->order_number,
            $order->user->name ?? null,
            $order->user->email ?? null,
            $order->grand_total,
            $order->payment_status,
            $order->payment_method,
            $order->order_status,
            $order->created_at->format('d M Y'),
        ]);

        return $exporter->stream('sales-orders.csv', $headers, $rows);
    }

    public function show(Order $order): View
    {
        $order->load(['user', 'address', 'items', 'payment', 'returns']);

        $order->load('earnedVoucher');

        return view('admin.sales.orders.show', compact('order'));
    }

    /**
     * Moves an order forward through fulfilment (confirmed → packed →
     * shipped → delivered). Saved through the model (not a bulk query) so
     * the Order::updated hook fires: reaching "delivered" is what issues
     * the earned ₹ voucher (idempotently) and unlocks product reviews.
     */
    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        $sequence = ['pending', 'confirmed', 'packed', 'shipped', 'delivered'];
        $target = $request->validated('status');

        $currentIndex = array_search($order->order_status, $sequence, true);

        if ($currentIndex === false || array_search($target, $sequence, true) <= $currentIndex) {
            return back()->with('error', 'That order cannot be moved to "'.$target.'" from "'.$order->order_status.'".');
        }

        $order->update([
            'order_status' => $target,
            $target.'_at' => $order->{$target.'_at'} ?? now(),
        ]);

        return back()->with('success', 'Order marked as '.$target.'.');
    }
}
