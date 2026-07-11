<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_revenue' => Order::where('payment_status', 'paid')->sum('grand_total'),
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('order_status', 'pending')->count(),
            'total_customers' => User::where('role', 'customer')->count(),
            'total_products' => Product::count(),
            'low_stock_count' => ProductVariant::where('stock_quantity', '>', 0)
                ->whereColumn('stock_quantity', '<=', 'low_stock_quantity')
                ->where('stock_status', '!=', 'out_of_stock')
                ->count(),
            'out_of_stock_count' => ProductVariant::where(fn ($query) => $query->where('stock_quantity', 0)->orWhere('stock_status', 'out_of_stock'))
                ->count(),
        ];

        $recentOrders = Order::with('user')
            ->latest()
            ->limit(8)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentOrders'));
    }
}
