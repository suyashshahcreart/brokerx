<?php

namespace App\Http\Controllers\Admin;

use App\Models\BookingAssignee;
use App\Models\Booking;
use App\Models\BookingHistory;
use App\Models\PhotographerVisit;
use App\Models\Setting;
use App\Models\User;
use App\Models\City;
use App\Models\State;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class BookingAssigneeController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:booking_manage_assignees')->only(['store', 'update', 'destroy', 'checkIn', 'checkOut']);
        $this->middleware('permission:booking_view')->only(['index', 'show', 'checkInForm', 'checkOutForm']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get filter options for view
        $states = State::all();
        $cities = City::all();
        // Get only photographers (filter by role)
        $users = User::whereHas('roles', function ($q) {
            $q->where('name', 'photographer');
        })->get();

        if ($request->ajax()) {
            // Add joins for searchable columns to avoid "Column not found" errors
            $query = Booking::query()
                ->leftJoin('customers', 'bookings.customer_id', '=', 'customers.id')
                ->leftJoin('property_types', 'bookings.property_type_id', '=', 'property_types.id')
                ->leftJoin('cities', 'bookings.city_id', '=', 'cities.id')
                ->leftJoin('users as creator', 'bookings.created_by', '=', 'creator.id')
                ->select('bookings.*')
                ->with(['propertySubType', 'bhk', 'state']);

            // Apply filters
            if ($request->filled('state_id')) {
                $query->where('bookings.state_id', $request->state_id);
            }

            if ($request->filled('city_id')) {
                $query->where('bookings.city_id', $request->city_id);
            }

            if ($request->filled('status')) {
                $query->where('bookings.status', $request->status);
            } else {
                $query->whereIn('bookings.status', ['Schedul_accepted', 'Reschedul_accepted', 'Schedul_assign', 'Reschedul_assigned']);
            }

            if ($request->filled('date_from') && $request->filled('date_to')) {
                $query->whereBetween('bookings.booking_date', [
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
                    $date = $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('Y-m-d') : '';
                    $address = htmlspecialchars($booking->full_address ?? '');
                    $city = htmlspecialchars($booking->city ? $booking->city->name : '');
                    $state = htmlspecialchars($booking->state ? $booking->state->name : '');
                    $pincode = htmlspecialchars($booking->pin_code ?? '');
                    $userName = htmlspecialchars($booking->user ? $booking->user->name : '');

                    // Check if already assigned
                    if ($booking->status === 'schedul_assign') {
                        $assignee = BookingAssignee::where('booking_id', $booking->id)->first();
                        if ($assignee) {
                            $photographerName = $assignee->user ? $assignee->user->name : 'Unknown';
                            $assignedTime = $assignee->time ? \Carbon\Carbon::parse($assignee->time)->format('H:i') : '-';
                            return '<div class="d-flex justify-content-center gap-1">
                                <button class="btn btn-sm btn-soft-warning reassign-btn" 
                                    data-booking-id="' . $booking->id . '" 
                                    data-assignee-id="' . $assignee->id . '"
                                    data-current-photographer-id="' . $assignee->user_id . '"
                                    data-current-time="' . $assignedTime . '"
                                    data-booking-address="' . $address . '"
                                    data-booking-city="' . $city . '"
                                    data-booking-state="' . $state . '"
                                    data-booking-pincode="' . $pincode . '"
                                    data-booking-customer="' . $userName . '"
                                    data-booking-date="' . $date . '"
                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Reassign to ' . $photographerName . ' at ' . $assignedTime . '">
                                    <iconify-icon icon="solar:transfer-horizontal-broken" class="align-middle fs-18"></iconify-icon>
                                </button>
                                <button class="btn btn-sm btn-soft-danger cancel-assignment-btn" 
                                    data-assignee-id="' . $assignee->id . '"
                                    data-booking-id="' . $booking->id . '"
                                    data-photographer-name="' . $photographerName . '"
                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Cancel Assignment">
                                    <iconify-icon icon="solar:close-circle-broken" class="align-middle fs-18"></iconify-icon>
                                </button>
                            </div>';
                        }
                    }

                    // Show assign button for unassigned bookings
                    return '<div class="d-flex justify-content-center">
                        <button class="btn btn-sm btn-soft-success assign-btn" 
                            data-booking-id="' . $booking->id . '" 
                            data-booking-address="' . $address . '"
                            data-booking-city="' . $city . '"
                            data-booking-state="' . $state . '"
                            data-booking-pincode="' . $pincode . '"
                            data-booking-customer="' . $userName . '"
                            data-booking-date="' . $date . '"
                            data-bs-toggle="tooltip" data-bs-placement="top" title="Assign Photographer">
                            <iconify-icon icon="solar:user-check-rounded-broken" class="align-middle fs-18"></iconify-icon>
                        </button>
                    </div>';
                })
                ->addColumn('view_action', function (Booking $booking) {
                    return '<div class="d-flex justify-content-center">
                        <a href="' . route('admin.bookings.show', $booking->id) . '" class="btn btn-sm btn-soft-primary"
                        data-bs-toggle="tooltip" data-bs-placement="top" title="View Booking Detail Page"
                        target="_blank">
                           <iconify-icon icon="solar:eye-broken" class="align-middle fs-18"></iconify-icon>
                        </a>
                    </div>';
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

        // Validate assigned time against photographer availability settings
        $from = Setting::where('name', 'photographer_available_from')->value('value') ?? '08:00';
        $to = Setting::where('name', 'photographer_available_to')->value('value') ?? '21:00';
        $duration = (int) (Setting::where('name', 'photographer_working_duration')->value('value') ?? 60);

        $toMinutes = function ($t) {
            $parts = explode(':', $t);
            if (count($parts) < 2)
                return null;
            return (int) $parts[0] * 60 + (int) $parts[1];
        };

        $timeMins = $toMinutes($validated['time']);
        $fromMins = $toMinutes($from);
        $toMins = $toMinutes($to);
        // Per user request: allow start times up to the configured 'to' (do not subtract duration)

        if ($fromMins === null || $toMins === null || $timeMins === null) {
            $error = 'Invalid time format for photographer availability or selected time.';
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $error], 422);
            }
            return redirect()->back()->with('error', $error)->withInput();
        }

        if ($toMins < $fromMins) {
            $error = 'Invalid photographer availability: end time is before start time.';
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $error], 422);
            }
            return redirect()->back()->with('error', $error)->withInput();
        }

        if ($timeMins < $fromMins || $timeMins > $toMins) {
            $error = 'Selected time is outside allowed photographer availability.';
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $error], 422);
            }
            return redirect()->back()->with('error', $error)->withInput();
        }

        // Check for overlapping assignments for the selected photographer on the same date
        // We treat assignments as occupying a [start, start + duration) interval and reject overlapping intervals
        $duration = (int) $duration; // ensure integer minutes
        $newStart = $timeMins;
        $newEnd = $timeMins + $duration;

        $existingAssignments = BookingAssignee::where('user_id', $validated['user_id'])
            ->whereDate('date', $booking->booking_date)
            ->get();

        foreach ($existingAssignments as $assignment) {
            $existingTime = $assignment->time;
            // time may be stored as datetime or string; normalize to H:i
            if ($existingTime instanceof \DateTime) {
                $existingTimeStr = $existingTime->format('H:i');
            } elseif (is_object($existingTime) && method_exists($existingTime, 'format')) {
                $existingTimeStr = $existingTime->format('H:i');
            } else {
                $existingTimeStr = (string) $existingTime;
            }

            $existingStart = $toMinutes($existingTimeStr);
            if ($existingStart === null)
                continue;
            $existingEnd = $existingStart + $duration;

            // Overlap check
            if ($newStart < $existingEnd && $newEnd > $existingStart) {
                $error = 'Selected photographer already has an assignment that overlaps the chosen time.';
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => $error], 422);
                }
                return redirect()->back()->with('error', $error)->withInput();
            }
        }

        // Set date from booking_date if available
        $validated['date'] = $booking->booking_date;
        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();

        $assignee = BookingAssignee::create($validated);

        // Get photographer details for history
        $photographer = \App\Models\User::find($validated['user_id']);
        $oldStatus = $booking->status;

        // Update booking status to schedul_assign and set booking_time
        $booking->update([
            'status' => 'schedul_assign',
            'booking_time' => $validated['time'],
            'updated_by' => auth()->id(),
        ]);

        // Create booking history entry
        BookingHistory::create([
            'booking_id' => $booking->id,
            'from_status' => $oldStatus,
            'to_status' => 'schedul_assign',
            'changed_by' => auth()->id(),
            'notes' => 'Photographer assigned: ' . ($photographer->name ?? 'Unknown'),
            'metadata' => [
                'photographer_id' => $photographer->id ?? null,
                'photographer_name' => $photographer->name ?? null,
                'photographer_phone' => $photographer->mobile ?? null,
                'assigned_date' => $assignee->date ? \Carbon\Carbon::parse($assignee->date)->format('Y-m-d') : null,
                'assigned_time' => $assignee->time ? \Carbon\Carbon::parse($assignee->time)->format('H:i') : null,
                'assignee_id' => $assignee->id,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
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
                'booking_status_updated' => 'schedul_assign',
            ])
            ->log('Booking assignment created and booking status updated to schedul_assign');

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
     * Cancel assignment and revert booking status
     */
    public function cancel(Request $request, BookingAssignee $bookingAssignee)
    {
        try {
            $booking = $bookingAssignee->booking;
            $photographer = $bookingAssignee->user;
            $oldStatus = $booking->status;

            // Store data for history before deletion
            $assigneeData = [
                'id' => $bookingAssignee->id,
                'booking_id' => $bookingAssignee->booking_id,
                'user_id' => $bookingAssignee->user_id,
                'date' => $bookingAssignee->date,
                'time' => $bookingAssignee->time,
            ];

            // Revert booking status to Schedul_accepted and clear booking_time
            $previousStatus = 'Schedul_accepted'; // Default revert status
            $booking->update([
                'status' => $previousStatus,
                'booking_time' => null,
                'updated_by' => auth()->id(),
            ]);

            // Create booking history entry BEFORE deleting
            BookingHistory::create([
                'booking_id' => $booking->id,
                'from_status' => $oldStatus,
                'to_status' => $previousStatus,
                'changed_by' => auth()->id(),
                'notes' => 'Assignment cancelled. Photographer: ' . ($photographer->name ?? 'Unknown') . ' was unassigned.',
                'metadata' => [
                    'cancelled_photographer_id' => $photographer->id ?? null,
                    'cancelled_photographer_name' => $photographer->name ?? null,
                    'cancelled_assignee_id' => $assigneeData['id'],
                    'cancelled_time' => $assigneeData['time'] ? \Carbon\Carbon::parse($assigneeData['time'])->format('H:i') : null,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Log activity BEFORE deleting
            activity('booking_assignees')
                ->performedOn($bookingAssignee)
                ->causedBy(auth()->user())
                ->withProperties([
                    'event' => 'cancelled_and_deleted',
                    'before' => $assigneeData,
                    'booking_status_reverted' => $previousStatus,
                ])
                ->log('Booking assignment cancelled, entry deleted, and booking status reverted');

            // HARD DELETE the assignment record (IMPORTANT: slot calculation depends on this table)
            // Removing this entry frees up the photographer's schedule slot
            $bookingAssignee->forceDelete();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Assignment cancelled successfully. Booking status reverted to ' . $previousStatus . '.'
                ]);
            }

            return redirect()->route('admin.booking-assignees.index')
                ->with('success', 'Assignment cancelled successfully.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to cancel assignment: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to cancel assignment.');
        }
    }

    /**
     * Reassign booking to a different photographer or time
     */
    public function reassign(Request $request, BookingAssignee $bookingAssignee)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'time' => 'required|date_format:H:i',
        ]);

        try {
            $booking = $bookingAssignee->booking;
            $oldPhotographer = $bookingAssignee->user;
            $newPhotographer = User::findOrFail($validated['user_id']);

            // Validate time against photographer availability
            $from = Setting::where('name', 'photographer_available_from')->value('value') ?? '08:00';
            $to = Setting::where('name', 'photographer_available_to')->value('value') ?? '21:00';
            $duration = (int) (Setting::where('name', 'photographer_working_duration')->value('value') ?? 60);

            $toMinutes = function ($t) {
                $parts = explode(':', $t);
                if (count($parts) < 2) return null;
                return (int) $parts[0] * 60 + (int) $parts[1];
            };

            $timeMins = $toMinutes($validated['time']);
            $fromMins = $toMinutes($from);
            $toMins = $toMinutes($to);

            if ($timeMins === null || $timeMins < $fromMins || $timeMins > $toMins) {
                $error = 'Selected time is outside allowed photographer availability.';
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => $error], 422);
                }
                return redirect()->back()->with('error', $error);
            }

            // Check for overlapping assignments (excluding current assignment)
            $newStart = $timeMins;
            $newEnd = $timeMins + $duration;

            $existingAssignments = BookingAssignee::where('user_id', $validated['user_id'])
                ->where('id', '!=', $bookingAssignee->id)
                ->whereDate('date', $booking->booking_date)
                ->get();

            foreach ($existingAssignments as $assignment) {
                $existingTime = $assignment->time;
                if ($existingTime instanceof \DateTime) {
                    $existingTimeStr = $existingTime->format('H:i');
                } elseif (is_object($existingTime) && method_exists($existingTime, 'format')) {
                    $existingTimeStr = $existingTime->format('H:i');
                } else {
                    $existingTimeStr = (string) $existingTime;
                }

                $existingStart = $toMinutes($existingTimeStr);
                if ($existingStart === null) continue;
                // Add buffer time
                $bufferTime = $duration;
                $existingEnd = $existingStart + $duration + $bufferTime;

                if ($newStart < $existingEnd && $newEnd > $existingStart) {
                    $error = 'Selected photographer already has an assignment that overlaps the chosen time.';
                    if ($request->ajax()) {
                        return response()->json(['success' => false, 'message' => $error], 422);
                    }
                    return redirect()->back()->with('error', $error);
                }
            }

            // Store old values for history
            $oldUserId = $bookingAssignee->user_id;
            $oldTime = $bookingAssignee->time;

            // UPDATE the existing assignment record (IMPORTANT: slot calculation depends on this table)
            // This ensures the booking_assignee table accurately reflects current assignments
            $bookingAssignee->update([
                'user_id' => $validated['user_id'],
                'time' => $validated['time'],
                'updated_by' => auth()->id(),
            ]);

            // Update booking time
            $booking->update([
                'booking_time' => $validated['time'],
                'updated_by' => auth()->id(),
            ]);

            // Create booking history entry
            BookingHistory::create([
                'booking_id' => $booking->id,
                'from_status' => $booking->status,
                'to_status' => $booking->status,
                'changed_by' => auth()->id(),
                'notes' => 'Assignment reassigned from ' . ($oldPhotographer->name ?? 'Unknown') . ' to ' . ($newPhotographer->name ?? 'Unknown'),
                'metadata' => [
                    'old_photographer_id' => $oldUserId,
                    'old_photographer_name' => $oldPhotographer->name ?? null,
                    'old_time' => $oldTime ? \Carbon\Carbon::parse($oldTime)->format('H:i') : null,
                    'new_photographer_id' => $validated['user_id'],
                    'new_photographer_name' => $newPhotographer->name ?? null,
                    'new_time' => $validated['time'],
                    'assignee_id' => $bookingAssignee->id,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Log activity
            activity('booking_assignees')
                ->performedOn($bookingAssignee)
                ->causedBy(auth()->user())
                ->withProperties([
                    'event' => 'reassigned',
                    'before' => [
                        'user_id' => $oldUserId,
                        'time' => $oldTime,
                    ],
                    'after' => [
                        'user_id' => $validated['user_id'],
                        'time' => $validated['time'],
                    ],
                ])
                ->log('Booking assignment reassigned to different photographer or time');

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Assignment reassigned successfully to ' . ($newPhotographer->name ?? 'photographer') . '.',
                    'data' => [
                        'photographer_name' => $newPhotographer->name ?? '',
                        'time' => $validated['time'],
                    ]
                ]);
            }

            return redirect()->route('admin.booking-assignees.index')
                ->with('success', 'Assignment reassigned successfully.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to reassign: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to reassign assignment.');
        }
    }

    /**
     * Show check-in form
     */
    public function checkInForm(BookingAssignee $bookingAssignee)
    {
        // Ensure the authenticated user is the assigned photographer
        if ((int) $bookingAssignee->user_id !== (int) auth()->id()) {
            return redirect()->back()->with('error', 'You are not assigned to this booking.');
        }

        // Prevent double check-in: if a visit for this booking is already checked in, block
        $activeVisit = PhotographerVisit::where('booking_id', $bookingAssignee->booking_id)
            ->where('status', 'checked_in')
            ->orderByDesc('id')
            ->first();



        if ($activeVisit) {
            return redirect()->route('admin.photographer-visits.index')->with('error', 'This booking is already checked in. Please check out the current visit before starting a new one.');
        }

        $bookingAssignee->load(['booking.city', 'booking.state', 'booking.propertyType', 'booking.propertySubType', 'user']);
        $booking = $bookingAssignee->booking;
        $photographer = $bookingAssignee->user;
        return view('admin.photographer.check-in', compact('booking', 'photographer', 'bookingAssignee'));
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

            // Update booking status to shedul_inproccess (as requested)
            $booking = $bookingAssignee->booking;
            if ($booking) {
                $oldStatus = $booking->status;

                $booking->update([
                    'status' => 'schedul_inprogress',
                    'updated_by' => auth()->id(),
                ]);
                // Create booking history entry for check-in
                BookingHistory::create([
                    'booking_id' => $booking->id,
                    'from_status' => $oldStatus,
                    'to_status' => 'schedul_inprogress',
                    'changed_by' => auth()->id(),
                    'notes' => 'Photographer checked in for the booking',
                    'metadata' => [
                        'photographer_id' => auth()->id(),
                        'photographer_name' => auth()->user()->name ?? null,
                        'assignee_id' => $bookingAssignee->id,
                        'visit_id' => $photographerVisit->id,
                        'check_in_location' => $validated['location'] ?? null,
                        'check_in_time' => $checkedAt->toDateTimeString(),
                        'check_in_photo' => $photoPath,
                        'check_in_remarks' => $validated['remarks'] ?? null,
                        'location_accuracy' => $validated['location_accuracy'] ?? null,
                        'location_source' => $validated['location_source'] ?? null,
                    ],
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            }

            activity('booking_assignees')
                ->performedOn($bookingAssignee)
                ->causedBy(auth()->user())
                ->event('photographer_checked_in')
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
        if ((int) $bookingAssignee->user_id !== (int) auth()->id()) {
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
        return view('admin.photographer.check-out', compact('booking', 'photographer', 'bookingAssignee'));
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
            if ((int) $bookingAssignee->user_id !== (int) auth()->id()) {
                return redirect()->back()->with('error', 'You are not assigned to this booking.');
            }

            // Require an active check-in before check-out
            $activeVisit = PhotographerVisit::where('booking_id', $bookingAssignee->booking_id)
                ->where('status', 'checked_in')
                ->first();

            if (!$activeVisit) {
                dd($validated, $activeVisit);
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

            // Update booking status to shedule_complete
            $booking = $bookingAssignee->booking;

            if ($booking) {
                $oldStatus = $booking->status;
                $booking->update([
                    'status' => 'schedul_completed',
                    'updated_by' => auth()->id(),
                ]);
                // Create booking history entry for check-out
                BookingHistory::create([
                    'booking_id' => $booking->id,
                    'from_status' => $oldStatus,
                    'to_status' => 'schedul_completed',
                    'changed_by' => auth()->id(),
                    'notes' => 'Photographer checked out from the booking',
                    'metadata' => [
                        'photographer_id' => auth()->id(),
                        'photographer_name' => auth()->user()->name ?? null,
                        'assignee_id' => $bookingAssignee->id,
                        'visit_id' => $visit->id ?? null,
                        'check_out_location' => $validated['location'] ?? null,
                        'check_out_time' => $checkedAt->toDateTimeString(),
                        'check_out_photo' => $photoPath,
                        'check_out_remarks' => $validated['remarks'] ?? null,
                        'photos_taken' => $validated['photos_taken'] ?? 0,
                        'work_summary' => $validated['work_summary'] ?? null,
                        'location_accuracy' => $validated['location_accuracy'] ?? null,
                        'location_source' => $validated['location_source'] ?? null,
                    ],
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            }

            activity('booking_assignees')
                ->performedOn($bookingAssignee)
                ->causedBy(auth()->user())
                ->withProperties(['event' => 'checked_out'])
                ->log('Photographer checked out from booking assignee');

            return redirect()->route('admin.photographer-visits.show', $visit->id ?? null)
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