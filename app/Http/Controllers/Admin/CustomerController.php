<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateCustomerRequest;
use App\Models\Order;
use App\Models\User;
use App\Services\CsvExporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $customers = User::where('role', 'customer')
            ->withCount('orders')
            ->withSum(['orders as total_spent' => fn ($query) => $query->where('payment_status', 'paid')], 'grand_total')
            ->addSelect(['last_order_at' => Order::select('created_at')
                ->whereColumn('user_id', 'users.id')
                ->latest()
                ->limit(1),
            ])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function export(Request $request, CsvExporter $exporter): StreamedResponse
    {
        $customers = User::where('role', 'customer')
            ->withCount('orders')
            ->withSum(['orders as total_spent' => fn ($query) => $query->where('payment_status', 'paid')], 'grand_total')
            ->addSelect(['last_order_at' => Order::select('created_at')
                ->whereColumn('user_id', 'users.id')
                ->latest()
                ->limit(1),
            ])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->lazy(200);

        $headers = ['id', 'name', 'email', 'phone', 'orders_count', 'total_spent', 'last_order_at', 'status'];

        $rows = $customers->map(fn (User $customer) => [
            $customer->id,
            $customer->name,
            $customer->email,
            $customer->phone,
            $customer->orders_count,
            $customer->total_spent,
            $customer->last_order_at ? \Illuminate\Support\Carbon::parse($customer->last_order_at)->format('d M Y') : null,
            $customer->status,
        ]);

        return $exporter->stream('customers.csv', $headers, $rows);
    }

    public function report(Request $request): View
    {
        $customers = $this->baseReportQuery($request)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => User::where('role', 'customer')->count(),
            'new_this_month' => User::where('role', 'customer')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'returning' => User::where('role', 'customer')
                ->has('orders', '>=', 2)
                ->count(),
            'inactive' => User::where('role', 'customer')->where('status', 'inactive')->count(),
        ];

        return view('admin.reports.customers.index', compact('customers', 'stats'));
    }

    public function exportReport(Request $request, CsvExporter $exporter): StreamedResponse
    {
        $customers = $this->baseReportQuery($request)
            ->latest()
            ->lazy(200);

        $headers = ['id', 'name', 'email', 'orders_count', 'total_spent', 'joined'];

        $rows = $customers->map(fn (User $customer) => [
            $customer->id,
            $customer->name,
            $customer->email,
            $customer->orders_count,
            $customer->total_spent,
            $customer->created_at->format('d M Y'),
        ]);

        return $exporter->stream('customers-report.csv', $headers, $rows);
    }

    private function baseReportQuery(Request $request)
    {
        return User::where('role', 'customer')
            ->withCount('orders')
            ->withSum(['orders as total_spent' => fn ($query) => $query->where('payment_status', 'paid')], 'grand_total')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')));
    }

    public function show(User $customer): View
    {
        $customer->loadCount('orders');
        $customer->loadSum(['orders as total_spent' => fn ($query) => $query->where('payment_status', 'paid')], 'grand_total');
        $customer->load([
            'orders' => fn ($query) => $query->latest(),
            'wishlists.product.category',
            'reviews.product',
        ]);

        $defaultAddress = $customer->addresses()->where('is_default', true)->first();

        return view('admin.customers.show', compact('customer', 'defaultAddress'));
    }

    public function edit(User $customer): View
    {
        return view('admin.customers.edit', compact('customer'));
    }

    public function update(UpdateCustomerRequest $request, User $customer): RedirectResponse
    {
        $data = $request->safe()->except('avatar');

        if ($request->hasFile('avatar')) {
            if ($customer->avatar) {
                Storage::disk('public')->delete($customer->avatar);
            }

            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $customer->update($data);

        return redirect()->route('admin.customers.index')->with('success', 'Customer updated.');
    }

    public function destroy(User $customer): RedirectResponse
    {
        if ($customer->orders()->exists()) {
            return back()->with('error', 'Cannot delete a customer that has orders.');
        }

        $customer->delete();

        return back()->with('success', 'Customer deleted.');
    }
}
