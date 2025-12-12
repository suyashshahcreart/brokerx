<?php

namespace App\Http\Controllers\Admin;

use App\Models\BookingAssignee;
use App\Models\Booking;
use App\Models\PhotographerVisit;
use App\Models\User;
use App\Models\City;
use App\Models\State;
use Carbon\Carbon;
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
        // Get only photographers (filter by role)
        $users = User::whereHas('roles', function($q) {
            $q->where('name', 'photographer');
        })->get();

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
                $query->whereIn('status', ['Schedul_accepted', 'Reschedul_accepted','Schedul_assign','Reschedul_assigned']);
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
                        'schedul_assign' => 'success',
                        'tour_pending' => 'info',
                    ];
                    $color = $statusColors[$booking->status] ?? 'secondary';
                    return '<span class="badge bg-' . $color . '">' . ucfirst(str_replace('_', ' ', $booking->status)) . '</span>';
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
                    // Don't show assign button if already assigned
                    if ($booking->status === 'schedul_assign') {
                        return '<button class="btn btn-sm btn-success" ><i class="ri-check-line me-1"></i>Assigned</button>';
                    }
                    
                    $date = $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('Y-m-d') : '';
                    $address = htmlspecialchars($booking->full_address ?? '');
                    $city = htmlspecialchars($booking->city ? $booking->city->name : '');
                    $state = htmlspecialchars($booking->state ? $booking->state->name : '');
                    $pincode = htmlspecialchars($booking->pin_code ?? '');
                    $userName = htmlspecialchars($booking->user ? $booking->user->name : '');
                    
                    return '<button class="btn btn-sm btn-primary assign-btn" 
                        data-booking-id="' . $booking->id . '" 
                        data-booking-address="' . $address . '"
                        data-booking-city="' . $city . '"
                        data-booking-state="' . $state . '"
                        data-booking-pincode="' . $pincode . '"
                        data-booking-customer="' . $userName . '"
                        data-booking-date="' . $date . '">
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
            'time' => 'required|date_format:H:i',
        ]);

        // Get booking to extract date
        $booking = Booking::findOrFail($validated['booking_id']);
        
        // Set date from booking_date if available
        $validated['date'] = $booking->booking_date;
        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();

        $assignee = BookingAssignee::create($validated);

        // Update booking status to schedul_assign and set booking_time
        $booking->update([
            'status' => 'schedul_assign',
            'booking_time' => $validated['time'],
            'updated_by' => auth()->id(),
        ]);

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
                ],
                'booking_status_updated' => 'tour_pending',
            ])
            ->log('Booking assignment created and booking status updated to tour_pending');

        // If AJAX request, return JSON response
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Booking assigned successfully',
                'data' => [
                    'id' => $assignee->id,
                    'booking_id' => $assignee->booking_id,
                    'user_id' => $assignee->user_id,
                    'photographer_name' => $assignee->user->name ?? '',
                ]
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

    /**
     * Show check-in form
     */
    public function checkInForm(BookingAssignee $bookingAssignee)
    {
        // Ensure the authenticated user is the assigned photographer
        if ((int)$bookingAssignee->user_id !== (int)auth()->id()) {
            return redirect()->back()->with('error', 'You are not assigned to this booking.');
        }

        // Prevent double check-in: if a visit for this booking is already checked in, block
        $activeVisit = PhotographerVisit::where('booking_id', $bookingAssignee->booking_id)
            ->where('status', 'checked_in')
            ->orderByDesc('id')
            ->first();

        if ($activeVisit) {
            return redirect()->back()->with('error', 'This booking is already checked in. Please check out the current visit before starting a new one.');
        }

        $bookingAssignee->load(['booking.city', 'booking.state', 'booking.propertyType', 'booking.propertySubType', 'user']);
        $booking = $bookingAssignee->booking;
        $photographer = $bookingAssignee->user;
        return view('admin.photographer-visit-jobs.check-in', compact('booking', 'photographer', 'bookingAssignee'));
    }

    /**
     * Process check-in
     */
    public function checkIn(Request $request, BookingAssignee $bookingAssignee)
    {
        $validated = $request->validate([
            'location' => 'required|string|max:255',
            'location_timestamp' => 'nullable|date',
            'location_accuracy' => 'nullable|numeric',
            'location_source' => 'nullable|string|max:50',
            'remarks' => 'nullable|string|max:500',
            'photo' => 'required|image|max:5120', // 5MB max
        ]);

        try {
            // Prevent double check-in: if a visit for this booking is already checked in, block
            $activeVisit = PhotographerVisit::where('booking_id', $bookingAssignee->booking_id)
                ->where('status', 'checked_in')
                ->orderByDesc('id')
                ->first();

            if ($activeVisit) {
                return redirect()->back()->with('error', 'This booking is already checked in. Please check out the current visit before starting a new one.');
            }
            
            // If needed, add status checks on BookingAssignee here

            // Handle photo upload
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('photographer-job-checkins', 'public');
            }

            $locationTimestamp = !empty($validated['location_timestamp'])
                ? Carbon::parse($validated['location_timestamp'])
                : null;
            $checkedAt = now();

            // Prepare metadata for check-in
            $checkInMetadata = [
                'location_timestamp' => $locationTimestamp?->toIso8601String(),
                'location_accuracy' => $validated['location_accuracy'] ?? null,
                'location_source' => $validated['location_source'] ?? null,
                'ip_address' => $request->ip(),
                'device_info' => $request->userAgent(),
            ];

            // Create photographer visit with merged check-in fields
            $photographerVisit = PhotographerVisit::create([
                'job_id' => null,
                'booking_id' => $bookingAssignee->booking_id,
                'tour_id' => null,
                'photographer_id' => auth()->id(),
                'visit_date' => $checkedAt,
                'status' => 'checked_in',
                'metadata' => $checkInMetadata,
                'check_in_photo' => $photoPath,
                'check_in_metadata' => $checkInMetadata,
                'checked_in_at' => $checkedAt,
                'check_in_location' => $validated['location'] ?? null,
                'check_in_ip_address' => $request->ip(),
                'check_in_device_info' => $request->userAgent(),
                'check_in_remarks' => $validated['remarks'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // Optionally update BookingAssignee fields if model supports status/timestamps
            $bookingAssignee->update([
                'updated_by' => auth()->id(),
            ]);

            activity('booking_assignees')
                ->performedOn($bookingAssignee)
                ->causedBy(auth()->user())
                ->withProperties(['event' => 'checked_in', 'visit_id' => $photographerVisit->id])
                ->log('Photographer checked in for booking assignee');

            return redirect()->route('admin.photographer-visits.show', $photographerVisit)
                ->with('success', 'Successfully checked in for the job.');

        } catch (\Exception $e) {
            \Log::error('Error during photographer assignee check-in', [
                'assignee_id' => $bookingAssignee->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->with('error', 'Failed to check in. Please try again.');
        }
    }

    /**
     * Show check-out form
     */
    public function checkOutForm(BookingAssignee $bookingAssignee)
    {
        // Ensure the authenticated user is the assigned photographer
        if ((int)$bookingAssignee->user_id !== (int)auth()->id()) {
            return redirect()->back()->with('error', 'You are not assigned to this booking.');
        }

        // Require an active check-in before check-out
        $activeVisit = PhotographerVisit::where('booking_id', $bookingAssignee->booking_id)
            ->where('status', 'checked_in')
            ->orderByDesc('id')
            ->first();

        if (!$activeVisit) {
            return redirect()->back()->with('error', 'No active check-in found for this booking. Please check in first.');
        }

        // Load related booking and assignee user
        $bookingAssignee->load(['booking.city', 'booking.state', 'booking.propertyType', 'booking.propertySubType', 'user']);
        $booking = $bookingAssignee->booking;
        $photographer = $bookingAssignee->user;
        return view('admin.photographer-visit-jobs.check-out', compact('booking', 'photographer', 'bookingAssignee'));
    }

    /**
     * Process check-out
     */
    public function checkOut(Request $request, BookingAssignee $bookingAssignee)
    {
        $validated = $request->validate([
            'location' => 'required|string|max:255',
            'location_timestamp' => 'nullable|date',
            'location_accuracy' => 'nullable|numeric',
            'location_source' => 'nullable|string|max:50',
            'remarks' => 'nullable|string|max:500',
            'photos_taken' => 'nullable|integer|min:0',
            'work_summary' => 'nullable|string|max:1000',
            'photo' => 'required|image|max:5120', // 5MB max
        ]);

        try {
            // Ensure the authenticated user is the assigned photographer
            if ((int)$bookingAssignee->user_id !== (int)auth()->id()) {
                return redirect()->back()->with('error', 'You are not assigned to this booking.');
            }

            // Require an active check-in before check-out
            $activeVisit = PhotographerVisit::where('booking_id', $bookingAssignee->booking_id)
                ->where('status', 'checked_in')
                ->orderByDesc('id')
                ->first();

            if (!$activeVisit) {
                return redirect()->back()->with('error', 'No active check-in found for this booking. Please check in first.');
            }
            // Check if job can be checked out
            // If needed, add checks based on BookingAssignee state

            // Handle photo upload
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('photographer-job-checkouts', 'public');
            }

            $locationTimestamp = !empty($validated['location_timestamp'])
                ? Carbon::parse($validated['location_timestamp'])
                : null;
            $checkedAt = now();

            // Prepare metadata for check-out
            $checkOutMetadata = [
                'location_timestamp' => $locationTimestamp?->toIso8601String(),
                'location_accuracy' => $validated['location_accuracy'] ?? null,
                'location_source' => $validated['location_source'] ?? null,
                'ip_address' => $request->ip(),
                'device_info' => $request->userAgent(),
            ];

            // Update the related photographer visit with merged check-out fields
            $visit = PhotographerVisit::where('booking_id', $bookingAssignee->booking_id)
                ->orderByDesc('id')
                ->first();

            if ($visit) {
                $visit->update([
                    'status' => 'completed',
                    'check_out_photo' => $photoPath,
                    'check_out_metadata' => $checkOutMetadata,
                    'checked_out_at' => $checkedAt,
                    'check_out_location' => $validated['location'] ?? null,
                    'check_out_ip_address' => $request->ip(),
                    'check_out_device_info' => $request->userAgent(),
                    'check_out_remarks' => $validated['remarks'] ?? null,
                    'photos_taken' => $validated['photos_taken'] ?? 0,
                    'work_summary' => $validated['work_summary'] ?? null,
                    'updated_by' => auth()->id(),
                ]);
            }

            // Update assignee metadata if stored on booking_assignees
            $metadata = $bookingAssignee->metadata ?? [];
            $metadata['check_out'] = [
                'location' => $validated['location'] ?? null,
                'location_timestamp' => $locationTimestamp?->toIso8601String(),
                'location_accuracy' => $validated['location_accuracy'] ?? null,
                'location_source' => $validated['location_source'] ?? null,
                'remarks' => $validated['remarks'] ?? null,
                'photos_taken' => $validated['photos_taken'] ?? 0,
                'work_summary' => $validated['work_summary'] ?? null,
                'photo' => $photoPath,
                'checked_out_at' => $checkedAt->toIso8601String(),
                'ip_address' => $request->ip(),
                'device_info' => $request->userAgent(),
            ];

            $bookingAssignee->update([
                'updated_by' => auth()->id(),
            ]);

            activity('booking_assignees')
                ->performedOn($bookingAssignee)
                ->causedBy(auth()->user())
                ->withProperties(['event' => 'checked_out'])
                ->log('Photographer checked out from booking assignee');

            return redirect()->route('admin.photographer-visits.show', $visit ?? null)
                ->with('success', 'Successfully checked out from the job.');

        } catch (\Exception $e) {
            \Log::error('Error during photographer assignee check-out', [
                'assignee_id' => $bookingAssignee->id,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()
                ->with('error', 'Failed to check out. Please try again.');
        }
    }
}

