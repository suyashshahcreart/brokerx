<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BHK;
use App\Models\Booking;
use App\Models\BookingHistory;
use App\Models\City;
use App\Models\PropertySubType;
use App\Models\PropertyType;
use App\Models\State;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class BookingController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:booking_view')->only(['index', 'show']);
        $this->middleware('permission:booking_create')->only(['create', 'store']);
        $this->middleware('permission:booking_edit')->only(['edit', 'update']);
        $this->middleware('permission:booking_delete')->only(['destroy']);
    }
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Booking::with(['user', 'propertyType', 'propertySubType', 'bhk', 'city', 'state']);
            return \Yajra\DataTables\Facades\DataTables::of($query)
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
                ->addColumn('booking_notes', fn(Booking $booking) => $booking->booking_notes ?? '')
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
                    $edit = route('admin.bookings.edit', $booking);
                    $delete = route('admin.bookings.destroy', $booking);
                    $csrf = csrf_field();
                    $method = method_field('DELETE');
                    
                    $actions = '<div class="btn-group btn-group-sm" role="group">';
                    
                    // Add accept/decline buttons for pending schedules
                    if (in_array($booking->status, ['schedul_pending', 'reschedul_pending']) && auth()->user()->can('booking_edit')) {
                        $actions .= '
                            <button onclick="acceptScheduleQuick(' . $booking->id . ')" class="btn btn-success" title="Accept Schedule">
                                <i class="ri-check-line"></i>
                            </button>
                            <button onclick="declineScheduleQuick(' . $booking->id . ')" class="btn btn-danger" title="Decline Schedule">
                                <i class="ri-close-line"></i>
                            </button>
                        ';
                    }
                    
                    $actions .= '
                        <a href="' . $view . '" class="btn btn-light border" title="View"><i class="ri-eye-line"></i></a>
                        <a href="' . $edit . '" class="btn btn-soft-primary border" title="Edit"><i class="ri-edit-line"></i></a>
                    ';
                    
                    if (auth()->user()->can('booking_delete')) {
                        $actions .= '
                            <button onclick="deleteBooking(' . $booking->id . ')" class="btn btn-soft-danger border" title="Delete">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        ';
                    }
                    
                    $actions .= '</div>';
                    
                    return $actions;
                })
                ->rawColumns(['type_subtype', 'city_state', 'status', 'payment_status', 'actions', 'schedule'])
                ->toJson();
        }
        $canCreate = $request->user()->can('booking_create');
        $canEdit = $request->user()->can('booking_edit');
        $canDelete = $request->user()->can('booking_delete');
        return view('admin.bookings.index', compact('canCreate', 'canEdit', 'canDelete'));
    }

    /**
     * API: Return bookings with filters (for modal, returns JSON)
     */
    public function apiList(Request $request)
    {
        $query = Booking::query()
            ->with(['user', 'propertyType', 'propertySubType'])
            ->whereDoesntHave('qr');
        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }
        if ($request->filled('property_type_id')) {
            $query->where('property_type_id', $request->input('property_type_id'));
        }
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%$search%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('firstname', 'like', "%$search%")
                            ->orWhere('lastname', 'like', "%$search%")
                            ->orWhere('email', 'like', "%$search%")
                            ->orWhere('mobile', 'like', "%$search%")
                        ;
                    })
                    ->orWhereHas('propertyType', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%$search%");
                    });
            });
        }
        $bookings = $query->with(['city', 'state', 'bhk'])->orderByDesc('created_at')->limit(50)->get();
        $result = $bookings->map(function ($booking) {
            return [
                'id' => $booking->id,
                'customer' => $booking->user ? $booking->user->firstname . ' ' . $booking->user->lastname : null,
                'customer_mobile' => $booking->user?->mobile,
                'property_type' => $booking->propertyType?->name,
                'property_sub_type' => $booking->propertySubType?->name,
                'bhk' => $booking->bhk?->name,
                'city' => $booking->city?->name,
                'state' => $booking->state?->name,
                'address' => $booking->full_address,
                'pin_code' => $booking->pin_code,
                'area' => $booking->area,
                'price' => $booking->price,
                'booking_date' => optional($booking->booking_date)->format('d M Y'),
                'status' => $booking->status,
            ];
        });
        return response()->json(['data' => $result]);
    }

    /**
     * API: Get booking details by ID (for QR modal)
     */
    public function getBookingDetails(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
        ]);

        $booking = Booking::with(['user', 'propertyType', 'propertySubType', 'bhk', 'city', 'state'])
            ->findOrFail($request->booking_id);

        $bookingData = [
            'id' => $booking->id,
            'customer' => $booking->user ? $booking->user->firstname . ' ' . $booking->user->lastname : null,
            'property_type' => $booking->propertyType?->name,
            'property_sub_type' => $booking->propertySubType?->name,
            'bhk' => $booking->bhk?->name,
            'city' => $booking->city?->name,
            'state' => $booking->state?->name,
            'area' => $booking->area ? number_format($booking->area) : null,
            'price' => $booking->price ? '₹ ' . number_format($booking->price) : null,
            'booking_date' => optional($booking->booking_date)->format('Y-m-d'),
            'status' => $booking->status,
            'payment_status' => $booking->payment_status,
            'address' => $booking->full_address,
        ];

        return response()->json(['booking' => $bookingData]);
    }
    public function create()
    {
        $users = User::orderBy('firstname')->get();
        $propertyTypes = PropertyType::orderBy('name')->get();
        $propertySubTypes = PropertySubType::orderBy('name')->get();
        $bhks = BHK::orderBy('name')->get();
        $cities = City::orderBy('name')->get();
        $states = State::orderBy('name')->get();

        $paymentStatuses = ['pending', 'paid', 'failed', 'refunded'];
        $statuses = Booking::getAvailableStatuses();

        return view('admin.bookings.create', compact(
            'users',
            'propertyTypes',
            'propertySubTypes',
            'bhks',
            'cities',
            'states',
            'paymentStatuses',
            'statuses'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'owner_type' => ['nullable', 'string', 'in:Owner,Broker'],
            'property_type_id' => ['required', 'exists:property_types,id'],
            'property_sub_type_id' => ['required', 'exists:property_sub_types,id'],
            'bhk_id' => ['nullable', 'exists:b_h_k_s,id'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'state_id' => ['nullable', 'exists:states,id'],
            'furniture_type' => ['nullable', 'string', 'max:255'],
            'area' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'integer', 'min:0'],
            'firm_name' => ['nullable', 'string', 'max:255'],
            'gst_no' => ['nullable', 'string', 'max:50'],
            'other_option_details' => ['nullable', 'string'],
            'house_no' => ['required', 'string', 'max:255'],
            'building' => ['required', 'string', 'max:255'],
            'society_name' => ['nullable', 'string', 'max:255'],
            'address_area' => ['nullable', 'string', 'max:255'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'full_address' => ['required', 'string'],
            'pin_code' => ['required', 'string', 'size:6', 'regex:/^[0-9]{6}$/'],
            'booking_date' => ['nullable', 'date'],
            'payment_status' => ['nullable', 'in:unpaid,pending,paid,failed,refunded'],
            'status' => ['nullable', 'in:inquiry,pending,confirmed,schedul_pending,schedul_accepted,schedul_decline,reschedul_pending,reschedul_accepted,reschedul_decline,reschedul_blocked,schedul_assign,schedul_completed,tour_pending,tour_completed,tour_live,completed,maintenance,cancelled,expired'],
        ]);

        $validated['created_by'] = $request->user()->id ?? null;

        $booking = Booking::create($validated);

        // Create initial booking history entry
        \App\Models\BookingHistory::create([
            'booking_id' => $booking->id,
            'from_status' => null,
            'to_status' => $booking->status ?? 'pending',
            'changed_by' => $request->user()->id,
            'notes' => 'Booking created',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Create a tour for this booking
        $tour = Tour::create([
            'booking_id' => $booking->id,
            'name' => 'Tour for Booking #' . $booking->id,
            'title' => 'Property Tour - ' . ($booking->propertyType?->name ?? 'Property'),
            'status' => 'draft',
            'revision' => 1,
        ]);

        activity('bookings')
            ->performedOn($booking)
            ->causedBy($request->user())
            ->withProperties([
                'event' => 'created',
                'after' => $booking->toArray()
            ])
            ->log('Booking created');

        activity('tours')
            ->performedOn($tour)
            ->causedBy($request->user())
            ->withProperties([
                'event' => 'created',
                'after' => $tour->toArray(),
                'booking_id' => $booking->id
            ])
            ->log('Tour created for booking');

        return redirect()->route('admin.bookings.index')->with('success', 'Booking and tour created successfully.');
    }

    public function show(Booking $booking)
    {
        $booking->load(['user', 'propertyType', 'propertySubType', 'bhk', 'city', 'state', 'creator', 'histories.changedBy.roles']);
        return view('admin.bookings.show', compact('booking'));
    }

    public function edit(Booking $booking)
    {
        $users = User::orderBy('firstname')->get();
        $propertyTypes = PropertyType::orderBy('name')->get();
        $propertySubTypes = PropertySubType::orderBy('name')->get();
        $bhks = BHK::orderBy('name')->get();
        $cities = City::orderBy('name')->get();
        $states = State::orderBy('name')->get();

        $paymentStatuses = ['pending', 'paid', 'failed', 'refunded'];
        $statuses = Booking::getAvailableStatuses();

        // Load tour if linked
        $tour = Tour::where('booking_id', $booking->id)->first();

        return view('admin.bookings.edit', compact(
            'booking',
            'tour',
            'users',
            'propertyTypes',
            'propertySubTypes',
            'bhks',
            'cities',
            'states',
            'paymentStatuses',
            'statuses'
        ));
    }

    public function update(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'owner_type' => ['nullable', 'string', 'in:Owner,Broker'],
            'property_type_id' => ['required', 'exists:property_types,id'],
            'property_sub_type_id' => ['required', 'exists:property_sub_types,id'],
            'bhk_id' => ['nullable', 'exists:b_h_k_s,id'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'state_id' => ['nullable', 'exists:states,id'],
            'furniture_type' => ['nullable', 'string', 'max:255'],
            'area' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'integer', 'min:0'],
            'firm_name' => ['nullable', 'string', 'max:255'],
            'gst_no' => ['nullable', 'string', 'max:50'],
            'other_option_details' => ['nullable', 'string'],
            'house_no' => ['required', 'string', 'max:255'],
            'building' => ['required', 'string', 'max:255'],
            'society_name' => ['nullable', 'string', 'max:255'],
            'address_area' => ['nullable', 'string', 'max:255'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'full_address' => ['required', 'string'],
            'pin_code' => ['required', 'string', 'size:6', 'regex:/^[0-9]{6}$/'],
            'booking_date' => ['nullable', 'date'],
            'payment_status' => ['nullable', 'in:unpaid,pending,paid,failed,refunded'],
            'status' => ['nullable', 'in:inquiry,pending,confirmed,schedul_pending,schedul_accepted,schedul_decline,reschedul_pending,reschedul_accepted,reschedul_decline,reschedul_blocked,schedul_assign,schedul_completed,tour_pending,tour_completed,tour_live,completed,maintenance,cancelled,expired'],
        ]);

        $validated['updated_by'] = $request->user()->id ?? null;

        $before = $booking->getOriginal();
        
        // Check if status is being changed
        $statusChanged = isset($validated['status']) && $validated['status'] !== $booking->status;
        $oldStatus = $booking->status;
        $newStatus = $validated['status'] ?? null;
        
        // Update booking (but we'll handle status separately if changed)
        if ($statusChanged) {
            // Remove status from validated array temporarily
            $statusToSet = $validated['status'];
            unset($validated['status']);
            $booking->update($validated);
            
            // Use changeStatus method to update status and create history
            $booking->changeStatus($statusToSet, $request->user()->id, 'Status updated via booking edit');
        } else {
            $booking->update($validated);
        }
        
        $after = $booking->fresh()->toArray();
        $changes = [];
        foreach ($after as $key => $value) {
            if (!isset($before[$key]) || $before[$key] !== $value) {
                $changes[$key] = [
                    'old' => $before[$key] ?? null,
                    'new' => $value
                ];
            }
        }
        activity('bookings')
            ->performedOn($booking)
            ->causedBy($request->user())
            ->withProperties([
                'event' => 'updated',
                'before' => $before,
                'after' => $after,
                'changes' => $changes
            ])
            ->log('Booking updated');

        return redirect()->route('admin.bookings.index')->with('success', 'Booking updated successfully.');
    }

    public function destroy(Booking $booking)
    {
        $before = $booking->toArray();
        $bookingId = $booking->id;
        $bookingType = get_class($booking);
        $booking->delete();

        Activity::create([
            'log_name' => 'bookings',
            'description' => 'Booking deleted',
            'subject_type' => $bookingType,
            'subject_id' => $bookingId,
            'causer_type' => get_class(auth()->user()),
            'causer_id' => auth()->id(),
            'properties' => [
                'event' => 'deleted',
                'before' => $before,
                'deleted_id' => $bookingId,
            ]
        ]);
        return redirect()->route('admin.bookings.index')->with('success', 'Booking deleted successfully.');
    }

    public function reschedule(Request $request, Booking $booking)
    {
        $request->validate([
            'schedule_date' => ['required', 'date'],
        ]);
        $oldDate = $booking->booking_date;
        $booking->booking_date = $request->input('schedule_date');
        $booking->save();

        activity('bookings')
            ->performedOn($booking)
            ->causedBy($request->user())
            ->withProperties([
                'event' => 'rescheduled',
                'old_date' => $oldDate,
                'new_date' => $booking->booking_date,
            ])
            ->log('Booking rescheduled');

        return response()->json(['success' => true, 'new_date' => $booking->booking_date->format('Y-m-d')]);
    }

    /**

     * API: Assign a booking to a QR code
     * POST: /api/qr/assign-booking
     * Params: qr_id, booking_id
     */
    public function assignBookingToQr(Request $request)
    {
        $request->validate([
            'qr_id' => 'required|exists:qr_code,id',
            'booking_id' => 'required|exists:bookings,id',
        ]);

        $qr = \App\Models\QR::findOrFail($request->qr_id);
        $booking = Booking::findOrFail($request->booking_id);

        // Only allow assignment if QR is not already assigned and booking is not already assigned
        if ($qr->booking_id) {
            return response()->json(['success' => false, 'message' => 'QR already assigned to a booking.'], 422);
        }
        if ($booking->qr) {
            return response()->json(['success' => false, 'message' => 'Booking already assigned to a QR.'], 422);
        }

        $qr->booking_id = $booking->id;
        $qr->save();

        $booking->tour_code = $qr->code;
        $booking->save();

        return response()->json(['success' => true, 'message' => 'Booking assigned to QR successfully.']);
    }
    /**
     * Update booking via AJAX
     */
    public function updateAjax(Request $request, Booking $booking)
    {
        // Allow partial updates - only validate fields that are present in request
        $rules = [
            'user_id' => ['sometimes', 'required', 'exists:users,id'],
            'owner_type' => ['sometimes', 'nullable', 'string', 'in:Owner,Broker'],
            'property_type_id' => ['sometimes', 'required', 'exists:property_types,id'],
            'property_sub_type_id' => ['sometimes', 'required', 'exists:property_sub_types,id'],
            'area' => ['sometimes', 'required', 'numeric', 'min:0'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'payment_status' => ['sometimes', 'required', 'in:unpaid,pending,paid,failed,refunded'],
            'status' => ['sometimes', 'required', 'in:inquiry,pending,confirmed,schedul_pending,schedul_accepted,schedul_decline,reschedul_pending,reschedul_accepted,reschedul_decline,reschedul_blocked,schedul_assign,schedul_completed,tour_pending,tour_completed,tour_live,completed,maintenance,cancelled,expired'],
            'bhk_id' => ['sometimes', 'nullable', 'exists:b_h_k_s,id'],
            'city_id' => ['sometimes', 'nullable', 'exists:cities,id'],
            'state_id' => ['sometimes', 'nullable', 'exists:states,id'],
            'furniture_type' => ['sometimes', 'nullable', 'string'],
            'booking_date' => ['sometimes', 'nullable', 'date'],
            'house_no' => ['sometimes', 'required', 'string', 'max:255'],
            'building' => ['sometimes', 'required', 'string', 'max:255'],
            'society_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'address_area' => ['sometimes', 'nullable', 'string', 'max:255'],
            'landmark' => ['sometimes', 'nullable', 'string', 'max:255'],
            'pin_code' => ['sometimes', 'required', 'string', 'size:6', 'regex:/^[0-9]{6}$/'],
            'full_address' => ['sometimes', 'required', 'string'],
            'firm_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'gst_no' => ['sometimes', 'nullable', 'string', 'max:50'],
            'other_option_details' => ['sometimes', 'nullable', 'string'],
            'notes' => ['sometimes', 'nullable', 'string'],
        ];

        $validated = $request->validate($rules);

        // Get only the changed fields
        $oldData = $booking->only(array_keys($validated));
        
        // Extract notes for history
        $notes = $validated['notes'] ?? null;
        unset($validated['notes']);
        
        // Check if status is being changed
        if (isset($validated['status']) && $validated['status'] !== $booking->status) {
            $statusNotes = $notes ?? 'Status changed via Quick Actions';
            
            // Use changeStatus method which creates history automatically
            $booking->changeStatus(
                $validated['status'], 
                $request->user()->id,
                $statusNotes,
                [
                    'source' => 'admin_quick_action',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]
            );
            
            // Remove status from validated to avoid double update
            unset($validated['status']);
        }
        
        // Check if payment status is being changed
        if (isset($validated['payment_status']) && $validated['payment_status'] !== $booking->payment_status) {
            $oldPaymentStatus = $booking->payment_status;
            $newPaymentStatus = $validated['payment_status'];
            
            // Update payment status
            $booking->payment_status = $newPaymentStatus;
            $booking->save();
            
            // Create booking history entry for payment status change
            \App\Models\BookingHistory::create([
                'booking_id' => $booking->id,
                'from_status' => $booking->status, // Booking status stays same
                'to_status' => $booking->status,
                'changed_by' => $request->user()->id,
                'notes' => $notes ?? "Payment status changed from {$oldPaymentStatus} to {$newPaymentStatus}",
                'metadata' => array_filter([
                    'source' => 'admin_quick_action',
                    'change_type' => 'payment_status',
                    'old_payment_status' => $oldPaymentStatus,
                    'new_payment_status' => $newPaymentStatus,
                    'changed_by_name' => $request->user()->firstname . ' ' . $request->user()->lastname,
                    'admin_notes' => $notes,
                ], function($value) {
                    return !is_null($value) && $value !== '';
                }),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            
            // Remove payment_status from validated to avoid double update
            unset($validated['payment_status']);
        }
        
        // Update only the remaining provided fields
        if (!empty($validated)) {
            $booking->update($validated);
        }

        // Determine what was updated for logging
        $updatedFields = array_keys($request->except('notes'));
        $logMessage = 'Booking updated via AJAX: ' . implode(', ', $updatedFields);

        activity('bookings')
            ->performedOn($booking)
            ->causedBy($request->user())
            ->withProperties([
                'old' => $oldData,
                'attributes' => $booking->fresh()->only($updatedFields),
                'updated_fields' => $updatedFields,
            ])
            ->log($logMessage);

        return response()->json([
            'success' => true,
            'message' => 'Booking updated successfully',
            'booking' => $booking->fresh()->load('histories.changedBy.roles'),
            'updated_fields' => $updatedFields,
        ]);
    }
}