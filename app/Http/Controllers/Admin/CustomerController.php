<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
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
            $canShow = true;

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

    /* 
    show function of a customer show all the booking and tour details of the custoner
    @paramer User $customer
    */
    public function show(Request $request, User $customer)
    {
        if ($request->ajax()) {
            // DataTable AJAX for bookings
            $query = $customer->bookings()->latest();
            return DataTables::of($query)
                ->addColumn('user', function (Booking $booking) {
                    return $booking->user ? $booking->user->firstname . ' ' . $booking->user->lastname : '-';
                })
                ->addColumn('type_subtype', function (Booking $booking) {
                    return $booking->propertyType?->name . '<div class="text-muted small">' . ($booking->propertySubType?->name ?? '-') . '</div>';
                })
                ->addColumn('bhk', fn(Booking $booking) => $booking->bhk?->name ?? '-')
                ->addColumn('city_state', function (Booking $booking) {
                    return ($booking->city?->name ?? '-') . '<div class="text-muted small">' . ($booking->state?->name ?? '-') . '</div>';
                })
                ->editColumn('area', fn(Booking $booking) => number_format($booking->area))
                ->editColumn('price', fn(Booking $booking) => '₹ ' . number_format($booking->price))
                ->editColumn('booking_date', fn(Booking $booking) => optional($booking->booking_date)->format('Y-m-d') ?? '-')
                ->editColumn('status', fn(Booking $booking) => '<span class="badge bg-secondary text-uppercase">' . $booking->status . '</span>')
                ->editColumn('payment_status', fn(Booking $booking) => '<span class="badge bg-info text-uppercase">' . $booking->payment_status . '</span>')
                ->addColumn('schedule', function (Booking $booking) {
                    if (auth()->user()->can('booking_delete')) {
                        return '<a href="#" class="btn btn-soft-warning btn-sm" title="Schedule"><i class="ri-calendar-line"></i></a>';
                    }
                    return '';
                })
                ->addColumn('actions', function (Booking $booking) {
                    $view = route('admin.bookings.show', $booking);
                    $schedule = '';
                    return $schedule .
                        '<a href="' . $view . '" class="btn btn-light btn-sm border" title="View"><i class="ri-eye-line"></i></a>' ;
                       
                })
                ->rawColumns(['type_subtype', 'city_state', 'status', 'payment_status', 'actions', 'schedule'])
                ->toJson();
        }

        $totalBookings = $customer->bookings()->count();
        $totalTours = $totalBookings;
        return view('admin.customers.show', compact('customer', 'totalBookings', 'totalTours'));
    }
}

