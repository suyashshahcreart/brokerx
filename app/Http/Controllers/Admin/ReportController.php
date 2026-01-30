<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\City;
use App\Models\State;
use App\Models\PropertyType;
use App\Models\PropertySubType;
use App\Models\Tour;
use App\Models\User;
use App\Exports\BookingsExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index()
    {
        $totalRevenue = Booking::sum('cashfree_payment_amount');
        if ($totalRevenue === 0) {
            $totalRevenue = Booking::sum('price');
        }

        $totalBookings = Booking::count();
        $totalCustomers = User::count();
        $totalTours = Tour::count();

        $recentSales = Booking::select(['id', 'user_id', 'cashfree_payment_amount', 'price', 'status', 'created_at'])
            ->latest()
            ->limit(5)
            ->get();

        $ownerTypes = Booking::select('owner_type')
            ->whereNotNull('owner_type')
            ->where('owner_type', '!=', '')
            ->distinct()
            ->orderBy('owner_type')
            ->pluck('owner_type');

        $propertyTypes = PropertyType::whereIn('id', Booking::query()
                ->whereNotNull('property_type_id')
                ->select('property_type_id')
            )
            ->orderBy('name')
            ->get();
        $propertySubTypes = PropertySubType::whereIn('id', Booking::query()
                ->whereNotNull('property_sub_type_id')
                ->select('property_sub_type_id')
            )
            ->orderBy('name')
            ->get();
        $customers = User::orderBy('firstname')
            ->orderBy('lastname')
            ->select(['id', 'firstname', 'lastname', 'email','mobile'])
            ->get();
        $states = State::whereIn('id', Booking::query()
                ->whereNotNull('state_id')
                ->select('state_id')
            )
            ->orderBy('name')
            ->get();
        $cities = City::whereIn('id', Booking::query()
                ->whereNotNull('city_id')
                ->select('city_id')
            )
            ->orderBy('name')
            ->get();

        return view('admin.reports.index', [
            'totalRevenue' => $totalRevenue,
            'totalBookings' => $totalBookings,
            'totalCustomers' => $totalCustomers,
            'totalTours' => $totalTours,
            'recentSales' => $recentSales,
            'ownerTypes' => $ownerTypes,
            'propertyTypes' => $propertyTypes,
            'propertySubTypes' => $propertySubTypes,
            'customers' => $customers,
            'states' => $states,
            'cities' => $cities,
        ]);
    }   

    public function sales(Request $request)
    {
        $from = Carbon::parse($request->get('from', now()->subDays(6)->toDateString()))->startOfDay();
        $to = Carbon::parse($request->get('to', now()->toDateString()))->endOfDay();

        $dailySales = Booking::selectRaw('DATE(created_at) as sale_date, SUM(COALESCE(cashfree_payment_amount, price, 0)) as total_amount, COUNT(*) as booking_count')
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('sale_date')
            ->orderBy('sale_date')
            ->get();

        $totalSales = (float) $dailySales->sum('total_amount');
        $totalBookings = (int) $dailySales->sum('booking_count');

        return view('admin.reports.sales', [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'dailySales' => $dailySales,
            'totalSales' => $totalSales,
            'totalBookings' => $totalBookings,
        ]);
    }

    public function bookings(Request $request)
    {
        $statusBreakdown = Booking::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->orderByDesc('total')
            ->get();

        $totalBookings = Booking::count();
        $states = State::orderBy('name')->get();
        $cities = City::orderBy('name')->get();

        if ($request->ajax()) {
            $query = Booking::with(['user', 'propertyType', 'propertySubType', 'bhk', 'city', 'state', 'assignees']);

            // Filter bookings based on user role
            if (auth()->user()->hasRole('admin')) {
                // Admin can see all bookings
            } elseif (auth()->user()->hasRole('photographer')) {
                // Photographer can see only assigned bookings
                $query->whereHas('assignees', function ($subQuery) {
                    $subQuery->where('user_id', auth()->id());
                });
            }

            // Apply filters
            if ($request->filled('state_id')) {
                $query->where('state_id', $request->state_id);
            }

            if ($request->filled('city_id')) {
                $query->where('city_id', $request->city_id);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('date_from') && $request->filled('date_to')) {
                $query->whereBetween('booking_date', [
                    $request->date_from,
                    $request->date_to
                ]);
            }

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
                    if (auth()->user()->can('booking_schedule')) {
                        return '<a href="#" class="btn btn-soft-warning btn-sm schedule-booking-btn" data-booking-id="' . $booking->id . '" data-booking-date="' . ($booking->booking_date ? $booking->booking_date->format('Y-m-d') : '') . '" title="Schedule"><i class="ri-calendar-line"></i></a>';
                    }
                    return '';
                })
                ->addColumn('actions', function (Booking $booking) {
                    $view = route('admin.bookings.show', $booking);
                    $edit = route('admin.bookings.edit', $booking);
                    $delete = route('admin.bookings.destroy', $booking);
                    $csrf = csrf_field();
                    $method = method_field('DELETE');
                    $schedule = '';
                    return '<div class="d-flex gap-1">' . $schedule .
                        '<a href="' . $view . '" class="btn btn-soft-primary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="View Booking Details"><iconify-icon icon="solar:eye-broken" class="align-middle fs-18"></iconify-icon></a>' .
                        '<a href="' . $edit . '" class="btn btn-soft-info btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Booking Info"><iconify-icon icon="solar:pen-new-square-broken" class="align-middle fs-18"></iconify-icon></a>' .
                        '<form action="' . $delete . '" method="POST" class="d-inline">' . $csrf . $method .
                        '<button type="submit" class="btn btn-soft-danger btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete Booking" onclick="return confirm(\'Delete this booking?\')"><iconify-icon icon="solar:trash-bin-minimalistic-broken" class="align-middle fs-18"></iconify-icon></button></form></div>';
                })
                ->rawColumns(['type_subtype', 'city_state', 'status', 'payment_status', 'actions', 'schedule'])
                ->toJson();
        }

        return view('admin.reports.bookings', [
            'statusBreakdown' => $statusBreakdown,
            'totalBookings' => $totalBookings,
            'states' => $states,
            'cities' => $cities,
        ]);
    }

    /**
     * Export bookings report to Excel with all details
     */
    public function exportBookings(Request $request)
    {
        $filters = [
            'state_id' => $request->state_id,
            'city_id' => $request->city_id,
            'owner_type' => $request->owner_type,
            'user_id' => $request->user_id,
            'property_type_id' => $request->property_type_id,
            'property_sub_type_id' => $request->property_sub_type_id,
            'pin_code' => $request->pin_code,
            'status' => $request->status,
            'from' => $request->from,
            'to' => $request->to,
        ];

        $filename = 'bookings-report-' . now()->format('Y-m-d-His') . '.xlsx';

        return Excel::download(new BookingsExport($filters, auth()->user()), $filename);
    }

    /**
     * Export sales report to Excel
     */
    public function exportSales(Request $request)
    {
        $from = $request->from ?? now()->subDays(30)->toDateString();
        $to = $request->to ?? now()->toDateString();

        // For now, redirect to bookings export
        // You can create a separate SalesExport class later
        return $this->exportBookings($request);
    }
}
