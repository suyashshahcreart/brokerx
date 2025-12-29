<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:user_view')->only(['index']);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Filter only users with 'customer' role and load bookings count
            $query = User::role('customer')
                ->withCount('bookings');
            $canEdit = $request->user()->can('user_edit');
            $canDelete = $request->user()->can('user_delete');

            return DataTables::of($query)
                ->addColumn('name', function (User $user) {
                    return e($user->name);
                })
                ->filterColumn('name', function ($query, $keyword) {
                    $query->where(function ($subQuery) use ($keyword) {
                        $subQuery
                            ->where('firstname', 'like', "%{$keyword}%")
                            ->orWhere('lastname', 'like', "%{$keyword}%");
                    });
                })
                ->orderColumn('name', function ($query, $direction) {
                    $query
                        ->orderBy('firstname', $direction)
                        ->orderBy('lastname', $direction);
                })
                ->editColumn('mobile', fn(User $user) => e($user->mobile))
                ->addColumn('bookings_count', function (User $user) {
                    $count = $user->bookings_count ?? 0;
                    return '<span class="badge bg-primary">' . $count . '</span>';
                })
                ->addColumn('actions', function (User $user) use ($canEdit, $canDelete) {
                    return view('admin.users.partials.actions', compact('user', 'canEdit', 'canDelete'))->render();
                })
                ->editColumn('email', fn(User $user) => e($user->email))
                ->rawColumns(['bookings_count', 'actions'])
                ->toJson();
        }

        $canEdit = $request->user()->can('user_edit');
        $canDelete = $request->user()->can('user_delete');

        return view('admin.customers.index', compact('canEdit', 'canDelete'));
    }
}

