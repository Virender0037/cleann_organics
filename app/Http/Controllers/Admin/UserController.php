<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Read-only for now: only the admin-user list. Create/Edit/Delete and the
 * Roles/Permissions screens remain the pre-existing static placeholders —
 * building real CRUD for those is a separate, larger "Administration"
 * module task, not part of fixing the identity-mismatch this addresses.
 */
class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::where('role', 'superadmin')
            ->when($request->filled('search'), fn ($query) => $query->where(function ($query) use ($request) {
                $query->where('name', 'like', '%'.$request->string('search').'%')
                    ->orWhere('email', 'like', '%'.$request->string('search').'%');
            }))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.administration.users.index', compact('users'));
    }
}
