<?php

namespace App\Http\Controllers\Admin;

use App\Models\BookingAssignee;
use App\Models\Booking;
use App\Models\User;
use App\Models\City;
use App\Models\State;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class BookingAssigneeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get filter options for view
        $states = State::all();
        $cities = City::all();
        $users = User::all();

        if ($request->ajax()) {
            $query = Booking::query()
                ->with(['user', 'propertyType', 'propertySubType', 'bhk', 'city', 'state']);

            // Apply filters
            if ($request->filled('state_id')) {
                $query->where('state_id', $request->state_id);
            }

            if ($request->filled('city_id')) {
                $query->where('city_id', $request->city_id);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            } else {
                $query->whereIn('status', ['Schedul_accepted', 'Reschedul_accepted']);
            }

            if ($request->filled('date_from') && $request->filled('date_to')) {
                $query->whereBetween('booking_date', [
                    $request->date_from,
                    $request->date_to
                ]);
            }

            return DataTables::of($query)
                ->addColumn('id', function (Booking $booking) {
                    return '<span class="badge bg-primary">#' . $booking->id . '</span>';
                })
                ->addColumn('user', function (Booking $booking) {
                    if ($booking->user) {
                        return $booking->user->name;
                    }
                    return '<span class="text-muted">-</span>';
                })
                ->addColumn('property', function (Booking $booking) {
                    $parts = [];
                    if ($booking->bhk) {
                        $parts[] = $booking->bhk->name;
                    }
                    if ($booking->propertyType) {
                        $parts[] = $booking->propertyType->name;
                    }
                    return count($parts) > 0 ? implode(' ', $parts) : '-';
                })
                ->addColumn('location', function (Booking $booking) {
                    $parts = [];
                    if ($booking->city) {
                        $parts[] = $booking->city->name;
                    }
                    if ($booking->state) {
                        $parts[] = $booking->state->name;
                    }
                    return count($parts) > 0 ? implode(', ', $parts) : '-';
                })
                ->editColumn('booking_date', function (Booking $booking) {
                    return $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') : '-';
                })
                ->editColumn('status', function (Booking $booking) {
                    $statusColors = [
                        'pending' => 'warning',
                        'confirmed' => 'success',
                        'cancelled' => 'danger',
                        'completed' => 'info',
                    ];
                    $color = $statusColors[$booking->status] ?? 'secondary';
                    return '<span class="badge bg-' . $color . '">' . ucfirst($booking->status) . '</span>';
                })
                ->editColumn('payment_status', function (Booking $booking) {
                    $statusColors = [
                        'pending' => 'warning',
                        'paid' => 'success',
                        'failed' => 'danger',
                        'refunded' => 'info',
                    ];
                    $color = $statusColors[$booking->payment_status] ?? 'secondary';
                    return '<span class="badge bg-' . $color . '">' . ucfirst($booking->payment_status) . '</span>';
                })
                ->addColumn('created_by', function (Booking $booking) {
                    return $booking->creator ? $booking->creator->name : '-';
                })
                ->editColumn('created_at', function (Booking $booking) {
                    return $booking->created_at->format('d M Y H:i');
                })
                ->addColumn('assign_action', function (Booking $booking) {
                    $date = $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('Y-m-d') : '';
                    return '<button class="btn btn-sm btn-primary assign-btn" data-booking-id="' . $booking->id . '" data-booking-address="' . htmlspecialchars($booking->full_address ?? '') . '" data-booking-date="' . $date . '">
                        <i class="ri-add-line me-1"></i>Assign
                    </button>';
                })
                ->addColumn('view_action', function (Booking $booking) {
                    return '<a href="' . route('admin.bookings.show', $booking->id) . '" class="btn btn-sm btn-info" target="_blank">
                        <i class="ri-eye-line"></i>
                    </a>';
                })
                ->rawColumns(['id', 'status', 'payment_status', 'assign_action', 'view_action'])
                ->toJson();
        }

        return view('admin.booking-assignees.index', compact('states', 'cities', 'users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $bookings = Booking::all();
        $users = User::all();
        return view('admin.booking-assignees.create', compact('bookings', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'user_id' => 'required|exists:users,id',
            'date' => 'nullable|date',
            'time' => 'nullable|date_format:H:i',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();

        $assignee = BookingAssignee::create($validated);

        activity('booking_assignees')
            ->performedOn($assignee)
            ->causedBy($request->user())
            ->withProperties([
                'event' => 'created',
                'after' => [
                    'booking_id' => $assignee->booking_id,
                    'user_id' => $assignee->user_id,
                    'date' => $assignee->date,
                    'time' => $assignee->time,
                ]
            ])
            ->log('Booking assignment created');

        // If AJAX request, return JSON response
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Booking assigned successfully'
            ]);
        }

        return redirect()->route('admin.booking-assignees.index')
            ->with('success', 'Booking assigned successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(BookingAssignee $bookingAssignee)
    {
        $bookingAssignee->load(['booking', 'user', 'createdBy', 'updatedBy', 'deletedBy']);
        return view('admin.booking-assignees.show', compact('bookingAssignee'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BookingAssignee $bookingAssignee)
    {
        $bookings = Booking::all();
        $users = User::all();
        return view('admin.booking-assignees.edit', compact('bookingAssignee', 'bookings', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BookingAssignee $bookingAssignee)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'user_id' => 'required|exists:users,id',
            'date' => 'nullable|date',
            'time' => 'nullable|date_format:H:i',
        ]);

        $old = [
            'booking_id' => $bookingAssignee->booking_id,
            'user_id' => $bookingAssignee->user_id,
            'date' => $bookingAssignee->date,
            'time' => $bookingAssignee->time,
        ];

        $validated['updated_by'] = auth()->id();

        $bookingAssignee->update($validated);

        activity('booking_assignees')
            ->performedOn($bookingAssignee)
            ->causedBy($request->user())
            ->withProperties([
                'event' => 'updated',
                'before' => $old,
                'after' => [
                    'booking_id' => $bookingAssignee->booking_id,
                    'user_id' => $bookingAssignee->user_id,
                    'date' => $bookingAssignee->date,
                    'time' => $bookingAssignee->time,
                ]
            ])
            ->log('Booking assignment updated');

        return redirect()->route('admin.booking-assignees.index')
            ->with('success', 'Booking assignment updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BookingAssignee $bookingAssignee)
    {
        $bookingAssignee->update(['deleted_by' => auth()->id()]);
        $bookingAssignee->delete();

        activity('booking_assignees')
            ->performedOn($bookingAssignee)
            ->causedBy(auth()->user())
            ->withProperties([
                'event' => 'deleted',
                'before' => [
                    'booking_id' => $bookingAssignee->booking_id,
                    'user_id' => $bookingAssignee->user_id,
                    'date' => $bookingAssignee->date,
                    'time' => $bookingAssignee->time,
                ]
            ])
            ->log('Booking assignment deleted');

        return redirect()->route('admin.booking-assignees.index')
            ->with('success', 'Booking assignment deleted successfully.');
    }
}

