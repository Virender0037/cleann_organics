<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateReturnStatusRequest;
use App\Models\ReturnRequest;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReturnController extends Controller
{
    public function index(Request $request): View
    {
        $returns = $this->baseQuery($request)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.sales.returns.index', compact('returns'));
    }

    public function report(Request $request): View
    {
        $returns = $this->baseQuery($request)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => ReturnRequest::count(),
            'approved' => ReturnRequest::where('status', 'approved')->count(),
            'pending' => ReturnRequest::where('status', 'requested')->count(),
            'rejected' => ReturnRequest::where('status', 'rejected')->count(),
        ];

        return view('admin.reports.returns.index', compact('returns', 'stats'));
    }

    private function baseQuery(Request $request): Builder
    {
        return ReturnRequest::with(['order', 'user'])
            ->withCount('items')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where(function ($query) use ($search) {
                    $query->where('return_number', 'like', "%{$search}%")
                        ->orWhereHas('order', fn ($query) => $query->where('order_number', 'like', "%{$search}%"))
                        ->orWhereHas('user', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')));
    }

    public function show(ReturnRequest $return): View
    {
        $return->load(['order', 'user', 'items.orderItem']);

        return view('admin.sales.returns.show', compact('return'));
    }

    public function updateStatus(UpdateReturnStatusRequest $request, ReturnRequest $return): RedirectResponse
    {
        $data = ['status' => $request->status];

        if ($request->status === 'approved') {
            $data['approved_at'] = now();
        }

        $return->update($data);

        return back()->with('success', 'Return marked as '.$request->status.'.');
    }
}
