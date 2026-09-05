<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Customer account area (Phase J). Every query is scoped to
 * $request->user() — no order or address id is ever taken from request
 * input here. Thin by design: profile/password updates still go through
 * Breeze's ProfileController / Auth\PasswordController unchanged; this
 * controller only renders the account pages with real, owned data.
 */
class AccountController extends Controller
{
    public function dashboard(Request $request): View
    {
        $user = $request->user();
        $orders = $user->orders();

        return view('user-dashboard', [
            'user' => $user,
            'metrics' => [
                'total' => (clone $orders)->count(),
                // "Active" = anything not yet finished — using only the real
                // order_status enum values, no invented "processing" state.
                'active' => (clone $orders)->whereNotIn('order_status', ['delivered', 'cancelled'])->count(),
                'delivered' => (clone $orders)->where('order_status', 'delivered')->count(),
                'cancelled' => (clone $orders)->where('order_status', 'cancelled')->count(),
                'addresses' => $user->addresses()->count(),
            ],
            'recentOrders' => $user->orders()->latest()->take(5)->get(),
        ]);
    }

    public function orderHistory(Request $request): View
    {
        return view('order-history', [
            'orders' => $request->user()->orders()->latest()->paginate(10),
        ]);
    }

    public function settings(Request $request): View
    {
        return view('account-setting', [
            'user' => $request->user(),
            'addresses' => $request->user()->addresses()->latest()->get(),
        ]);
    }
}
