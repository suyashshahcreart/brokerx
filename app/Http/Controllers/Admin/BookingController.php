<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BHK;
use App\Models\Booking;
use App\Models\BookingAssignee;
use App\Models\BookingHistory;
use App\Models\City;
use App\Models\PropertySubType;
use App\Models\PropertyType;
use App\Models\QR;
use App\Models\State;
use App\Models\Tour;
use App\Models\User;
use App\Models\PhotographerVisitJob;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\DataTables;

class BookingController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:booking_view')->only(['index', 'show']);
        $this->middleware('permission:booking_create')->only(['create', 'store']);
        $this->middleware('permission:booking_edit')->only(['edit', 'update']);
        $this->middleware('permission:booking_delete')->only(['destroy']);
    }

    //  calender view for assignments | ADMIN
    public function AssignementCalender()
    {
        // Provide photographer list and schedule-related statuses to the view
        $photographers = User::whereHas('roles', function ($q) {
            $q->where('name', 'photographer');
        })->orderBy('firstname')->get();

        $statuses = ['pending', 'schedul_accepted', 'confirmed', 'schedul_assign', 'reschedul_assign', 'schedul_pending', 'schedul_inprogress', 'schedul_completed'];

        return view('admin.photographer.index', [
            'title' => 'Booking Assignment Calendar',
            'photographers' => $photographers,
            'statuses' => $statuses
        ]);
    }

    public function index(Request $request)
    {
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
                    if (auth()->user()->can('booking_schedule')) {
                        if ($booking->status != 'tour_live') {
                            $schedule = '<a href="#" class="btn btn-soft-warning btn-sm me-1 schedule-booking-btn" data-booking-id="' . $booking->id . '" data-booking-date="' . ($booking->booking_date ? $booking->booking_date->format('Y-m-d') : '') . '" title="Schedule"><i class="ri-calendar-line"></i></a>';
                        }
                    }
                    return $schedule .
                        '<a href="' . $view . '" class="btn btn-light btn-sm border" title="View"><i class="ri-eye-line"></i></a>' .
                        ' <a href="' . $edit . '" class="btn btn-soft-primary btn-sm border" title="Edit"><i class="ri-edit-line"></i></a>' .
                        ' <form action="' . $delete . '" method="POST" class="d-inline">' . $csrf . $method .
                        '<button type="submit" class="btn btn-soft-danger btn-sm border" onclick="return confirm(\'Delete this booking?\')"><i class="ri-delete-bin-line"></i></button></form>';
                })
                ->rawColumns(['type_subtype', 'city_state', 'status', 'payment_status', 'actions', 'schedule'])
                ->toJson();
        }

        // Check permissions for actions
        $canSchedule = $request->user()->can('booking_schedule');
        $canCreate = $request->user()->can('booking_create');
        $canEdit = $request->user()->can('booking_edit');
        $canDelete = $request->user()->can('booking_delete');

        // Get filter options for view
        $states = State::all();
        $cities = City::all();

        $canCreate = $request->user()->can('booking_create');
        $canEdit = $request->user()->can('booking_edit');
        $canDelete = $request->user()->can('booking_delete');
        $canSchedule = $request->user()->can('booking_schedule');

        //dd($canCreate, $canEdit, $canDelete, $canSchedule, $states, $cities);
        
        return view('admin.bookings.index', compact('canCreate', 'canEdit', 'canDelete', 'canSchedule', 'states', 'cities'));
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
        $users = User::role('customer')->orderBy('firstname')->get();
        $propertyTypes = PropertyType::orderBy('name')->get();
        $propertySubTypes = PropertySubType::orderBy('name')->get();
        $bhks = BHK::orderBy('name')->get();
        $cities = City::orderBy('name')->get();
        $states = State::orderBy('name')->get();

        $paymentStatuses = ['pending', 'paid', 'failed', 'refunded'];
        $statuses = ['pending', 'confirmed', 'cancelled', 'completed'];

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
            'property_type_id' => ['required', 'exists:property_types,id'],
            'property_sub_type_id' => ['required', 'exists:property_sub_types,id'],
            'bhk_id' => ['nullable', 'exists:b_h_k_s,id'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'state_id' => ['nullable', 'exists:states,id'],
            'furniture_type' => ['nullable', 'string', 'max:255'],
            'area' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'integer', 'min:0'],
            'house_no' => ['nullable', 'string', 'max:255'],
            'building' => ['nullable', 'string', 'max:255'],
            'society_name' => ['nullable', 'string', 'max:255'],
            'address_area' => ['nullable', 'string', 'max:255'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'full_address' => ['nullable', 'string'],
            'pin_code' => ['nullable', 'string', 'max:20'],
            'booking_date' => ['nullable', 'date'],
            'payment_status' => ['required', 'in:pending,paid,failed,refunded'],
            'status' => ['required', 'in:pending,confirmed,cancelled,completed'],
        ]);

        $validated['created_by'] = $request->user()->id ?? null;

        $booking = Booking::create($validated);

        // Create initial booking history entry
        BookingHistory::create([
            'booking_id' => $booking->id,
            'from_status' => null,
            'to_status' => $validated['status'],
            'changed_by' => auth()->id(),
            'notes' => 'Booking created',
            'metadata' => [
                'initial_creation' => true,
                'payment_status' => $validated['payment_status'],
                'booking_date' => $validated['booking_date'] ?? null,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Generate unique QR code and create QR record for this booking
        $qrCode = $this->generateUniqueQrCode();

        // Get user name for QR code naming
        $user = User::find($validated['user_id']);
        $userName = $user ? ($user->firstname . ' ' . $user->lastname) : 'Customer';

        // Create QR code for this booking
        $qr = QR::create([
            'code' => $qrCode,
            'name' => 'Booking #' . $booking->id . ' - ' . $userName,
            'booking_id' => $booking->id,
            'qr_link' => 'https://qr.proppik.com/' . $qrCode,
            'created_by' => $request->user()->id ?? null,
            'updated_by' => $request->user()->id ?? null,
        ]);

        // Update booking's tour_code field with the QR code
        $booking->tour_code = $qrCode;
        $booking->save();

        activity('qr_code')
            ->performedOn($qr)
            ->causedBy($request->user())
            ->withProperties([
                'event' => 'created',
                'after' => $qr->toArray(),
                'booking_id' => $booking->id
            ])
            ->log('QR code auto-created for new booking');

        // Create a tour for this booking with unique slug
        $tour = Tour::create([
            'booking_id' => $booking->id,
            'name' => 'Tour for Booking #' . $booking->id,
            'title' => 'Property Tour - Booking #' . $booking->id,
            'slug' => 'tour-booking-' . $booking->id . '-' . time(),
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

        // Create a photographer visit job for this booking
        $job = PhotographerVisitJob::create([
            'booking_id' => $booking->id,
            'tour_id' => $tour->id,
            'photographer_id' => null, // Will be assigned later
            'status' => 'pending',
            'priority' => 'normal',
            'scheduled_date' => $booking->booking_date ?? now()->addDays(1),
            'instructions' => 'Complete photography for property booking #' . $booking->id,
            'created_by' => $request->user()->id ?? null,
        ]);

        // Generate and assign a unique job code
        $job->job_code = 'JOB-' . str_pad($job->id, 6, '0', STR_PAD_LEFT);
        $job->save();

        activity('photographer_visit_jobs')
            ->performedOn($job)
            ->causedBy($request->user())
            ->withProperties([
                'event' => 'created',
                'after' => $job->toArray(),
                'booking_id' => $booking->id
            ])
            ->log('Photographer visit job created for booking');

        return redirect()->route('admin.bookings.index')->with('success', 'Booking, QR code, tour, and photographer job created successfully.');
    }

    public function show(Request $request, Booking $booking)
    {
        $booking->load(['user', 'propertyType', 'propertySubType', 'bhk', 'city', 'state', 'creator', 'assignees.user']);

        // Get photographers for assignment modal
        $photographers = User::whereHas('roles', function ($q) {
            $q->where('name', 'photographer');
        })->get();

        // Check permissions for Quick Actions
        $canSchedule = $request->user()->can('booking_schedule');
        $canUpdatePaymentStatus = $request->user()->can('booking_update_payment_status');
        $canUpdateStatus = $request->user()->can('booking_update_status');
        $canAssignQR = $request->user()->can('booking_assign_qr');
        $canApproval = $request->user()->can('booking_approval');
        $canManageAssignees = $request->user()->can('booking_manage_assignees');
        $canEdit = $request->user()->can('booking_edit');
        $canDelete = $request->user()->can('booking_delete');

        // Check if user has ANY Quick Actions permission
        // Approval permission only counts if booking is pending
        // Manage assignees only counts if booking is accepted
        $hasApprovalPermission = $canApproval && in_array($booking->status, ['schedul_pending', 'reschedul_pending']);
        $hasManageAssigneesPermission = $canManageAssignees && in_array($booking->status, ['schedul_accepted', 'reschedul_accepted']);
        
        $hasAnyQuickActionPermission = $canSchedule || 
                                       $canUpdatePaymentStatus || 
                                       $canUpdateStatus || 
                                       $canAssignQR || 
                                       $hasApprovalPermission || 
                                       $hasManageAssigneesPermission || 
                                       $canEdit || 
                                       $canDelete;

        return view('admin.bookings.show', compact(
            'booking', 
            'photographers',
            'canSchedule',
            'canUpdatePaymentStatus',
            'canUpdateStatus',
            'canAssignQR',
            'canApproval',
            'canManageAssignees',
            'canEdit',
            'canDelete',
            'hasAnyQuickActionPermission'
        ));
    }

    public function edit(Booking $booking)
    {
        $users = User::role('customer')->orderBy('firstname')->get();
        $propertyTypes = PropertyType::orderBy('name')->get();
        $propertySubTypes = PropertySubType::orderBy('name')->get();
        $bhks = BHK::orderBy('name')->get();
        $cities = City::orderBy('name')->get();
        $states = State::orderBy('name')->get();

        $paymentStatuses = ['pending', 'paid', 'failed', 'refunded'];
        $statuses = ['pending', 'confirmed', 'cancelled', 'completed'];

        // Load tour if linked
        $tour = Tour::where('booking_id', $booking->id)->first();
        $qr_code = QR::where('booking_id', $booking->id)->value('code');

        // dd($booking,$qr_code);

        // Get photographers for assignment modal (needed for Quick Actions)
        $photographers = User::whereHas('roles', function ($q) {
            $q->where('name', 'photographer');
        })->get();

        // Check permissions for Quick Actions
        $request = request(); // Get request instance
        $canSchedule = $request->user()->can('booking_schedule');
        $canUpdatePaymentStatus = $request->user()->can('booking_update_payment_status');
        $canUpdateStatus = $request->user()->can('booking_update_status');
        $canAssignQR = $request->user()->can('booking_assign_qr');
        $canApproval = $request->user()->can('booking_approval');
        $canManageAssignees = $request->user()->can('booking_manage_assignees');
        $canEdit = $request->user()->can('booking_edit');
        $canDelete = $request->user()->can('booking_delete');

        // Check if user has ANY Quick Actions permission
        // Approval permission only counts if booking is pending
        // Manage assignees only counts if booking is accepted
        $hasApprovalPermission = $canApproval && in_array($booking->status, ['schedul_pending', 'reschedul_pending']);
        $hasManageAssigneesPermission = $canManageAssignees && in_array($booking->status, ['schedul_accepted', 'reschedul_accepted']);
        
        $hasAnyQuickActionPermission = $canSchedule || 
                                       $canUpdatePaymentStatus || 
                                       $canUpdateStatus || 
                                       $canAssignQR || 
                                       $hasApprovalPermission || 
                                       $hasManageAssigneesPermission || 
                                       $canEdit || 
                                       $canDelete;

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
            'statuses',
            'qr_code',
            'photographers',
            'canSchedule',
            'canUpdatePaymentStatus',
            'canUpdateStatus',
            'canAssignQR',
            'canApproval',
            'canManageAssignees',
            'canEdit',
            'canDelete',
            'hasAnyQuickActionPermission'
        ));
    }

    public function update(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'property_type_id' => ['required', 'exists:property_types,id'],
            'property_sub_type_id' => ['required', 'exists:property_sub_types,id'],
            'bhk_id' => ['nullable', 'exists:b_h_k_s,id'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'state_id' => ['nullable', 'exists:states,id'],
            'furniture_type' => ['nullable', 'string', 'max:255'],
            'area' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'integer', 'min:0'],
            'house_no' => ['nullable', 'string', 'max:255'],
            'building' => ['nullable', 'string', 'max:255'],
            'society_name' => ['nullable', 'string', 'max:255'],
            'address_area' => ['nullable', 'string', 'max:255'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'full_address' => ['nullable', 'string'],
            'pin_code' => ['nullable', 'string', 'max:20'],
            'booking_date' => ['nullable', 'date'],
            'payment_status' => ['nullable', 'in:pending,paid,failed,refunded'],
            'status' => ['nullable'],
        ]);

        $validated['updated_by'] = $request->user()->id ?? null;

        $before = $booking->getOriginal();
        $booking->update($validated);
        $after = $booking->toArray();
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

        return redirect()->route('admin.bookings.edit', $booking)->with(['success' => 'Booking updated successfully.', 'active_tab' => 'booking']);
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
        // Check permission
        if (!$request->user()->can('booking_schedule')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to schedule bookings.'
                ], 403);
            }
            return redirect()->route('admin.bookings.show', $booking)
                ->with('error', 'You do not have permission to schedule bookings.');
        }

        $request->validate([
            'schedule_date' => ['required', 'date'],
        ]);
        // Snapshot before changes so activity log can capture accurate diff
        $before = $booking->toArray();

        $oldDate = $booking->booking_date;
        $newDate = $request->input('schedule_date');
        $oldStatus = $booking->status;

        // Compare dates to check if date actually changed
        $oldDateStr = $oldDate ? Carbon::parse($oldDate)->format('Y-m-d') : null;
        $newDateStr = Carbon::parse($newDate)->format('Y-m-d');
        $dateChanged = $oldDateStr && $oldDateStr !== $newDateStr;

        // Determine new status based on current status
        // If status is schedul_assign or reschedul_assign, and date is changed, change to schedul_accepted or reschedul_accepted
        $newStatus = $oldStatus;
        $statusChanged = false;

        // If date changed, remove photographer assignments (same logic as frontend)
        if ($dateChanged) {
            // Check if status should change when date is updated
            if (in_array($oldStatus, ['schedul_assign', 'reschedul_assign', 'schedul_completed', 'pending', 'confirmed', 'completed'])) {

                // udpate status to reschedul_accepted or shedul_accepted
                if ($oldStatus === 'schedul_assign') {
                    $newStatus = 'schedul_accepted';
                } else {
                    $newStatus = 'reschedul_accepted';
                }

                // shedule_pending stuats update
                if ($oldStatus === 'pending' || $oldStatus === 'confirmed' || $oldStatus === 'completed' || $oldStatus === 'schedul_completed') {
                    $newStatus = 'schedul_pending';
                }

                $statusChanged = true;
            }
            // Check if there's an existing photographer assignment
            $existingAssignees = BookingAssignee::where('booking_id', $booking->id)
                ->with('user')
                ->get()
                ->filter(function ($assignee) {
                    return $assignee->user && $assignee->user->hasRole('photographer');
                });

            // Store old assignment info for history before deletion
            $oldAssignmentInfo = null;
            $assignmentRemoved = false;
            if ($existingAssignees->isNotEmpty()) {
                $oldAssignee = $existingAssignees->first();
                $oldPhotographer = $oldAssignee->user;
                $oldAssignmentInfo = [
                    'photographer_id' => $oldPhotographer->id ?? null,
                    'photographer_name' => $oldPhotographer->name ?? null,
                    'photographer_phone' => $oldPhotographer->mobile ?? null,
                    'old_assigned_date' => $oldAssignee->date ? Carbon::parse($oldAssignee->date)->format('Y-m-d') : null,
                    'old_assigned_time' => $oldAssignee->time ? Carbon::parse($oldAssignee->time)->format('H:i') : null,
                ];

                // Delete all photographer assignments for this booking
                // This removes the assignment but booking history remains intact
                foreach ($existingAssignees as $assignee) {
                    $assignee->delete(); // Soft delete
                }
                $assignmentRemoved = true;
            }

            // Also check and remove PhotographerVisitJob if exists
            $visitJob = PhotographerVisitJob::where('booking_id', $booking->id)->first();
            if ($visitJob) {
                $visitJob->delete(); // Soft delete
                $assignmentRemoved = true;
            }

            // Clear booking_time when rescheduling (photographer assignment removed)
            if ($dateChanged || $assignmentRemoved) {
                $booking->booking_time = null;
            }

            // Create booking history entry for date change
            if ($assignmentRemoved) {
                $photographerName = $oldAssignmentInfo['photographer_name'] ?? 'Unknown';
                BookingHistory::create([
                    'booking_id' => $booking->id,
                    'from_status' => $oldStatus,
                    'to_status' => $newStatus   , // Status will be updated separately if needed
                    'changed_by' => auth()->id(),
                    'notes' => 'Booking date changed by admin - Photographer assignment removed (Photographer: ' . $photographerName . ')',
                    'metadata' => [
                        'old_date' => $oldDateStr,
                        'new_date' => $newDateStr,
                        'old_assignment' => $oldAssignmentInfo,
                        'assignment_removed' => true,
                        'booking_time_cleared' => true,
                    ],
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            }
        }

        // Update booking date and status
        $booking->booking_date = $newDate;
        $booking->updated_by = auth()->id();

        // Update status if it needs to change (from schedul_assign to schedul_accepted)
        if ($statusChanged) {
            $booking->status = $newStatus;

            // Create booking history entry for status change
            $booking->changeStatus(
                $newStatus,
                auth()->id(),
                'Booking date updated by admin - Status changed from ' . $oldStatus . ' to ' . $newStatus,
                [
                    'old_date' => $oldDateStr,
                    'new_date' => $newDateStr,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'changed_by_admin' => true,
                ]
            );
        }

        $booking->save();
        $booking->refresh();

        $after = $booking->toArray();

        // Build a simple change set for the log
        $changes = [];
        foreach ($after as $key => $value) {
            $oldValue = $before[$key] ?? null;
            if ($oldValue !== $value) {
                $changes[$key] = ['old' => $oldValue, 'new' => $value];
            }
        }
        
        activity('bookings')
            ->performedOn($booking)
            ->causedBy($request->user())
            ->withProperties([
                'event' => 'rescheduled',
                'old_date' => $oldDate,
                'new_date' => $booking->booking_date,
                'old_status' => $oldStatus,
                'new_status' => $booking->status,
                'date_changed' => $dateChanged,
                'status_changed' => $statusChanged,
                'before' => $before,
                'after' => $after,
                'changes' => $changes,
                'assignment_removed' => $assignmentRemoved ?? false,
            ])
            ->log('Booking rescheduled' .
                ($statusChanged ? ' - Status changed from ' . $oldStatus . ' to ' . $booking->status : '') .
                ($assignmentRemoved ?? false ? ' - Photographer assignment removed' : ''));

        return response()->json([
            'success' => true,
            'new_date' => $booking->booking_date ? Carbon::parse($booking->booking_date)->format('Y-m-d') : null,
            'new_status' => $booking->status,
            'status_changed' => $statusChanged,
            'assignment_removed' => $assignmentRemoved ?? false,
        ]);
    }

    /**
     * API: Assign a booking to a QR code
     * POST: /api/qr/assign-booking
     * Params: qr_id, booking_id
     */
    public function assignBookingToQr(Request $request)
    {
        // Check permission
        if (!$request->user()->can('booking_assign_qr')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to assign QR codes to bookings.'
            ], 403);
        }

        $request->validate([
            'qr_id' => 'required|exists:qr_code,id',
            'booking_id' => 'required|exists:bookings,id',
        ]);

        $qr = QR::findOrFail($request->qr_id);
        $booking = Booking::findOrFail($request->booking_id);

        $oldBookingId = $qr->booking_id;

        // Check if booking is already assigned to another QR
        if ($booking->qr && $booking->qr->id !== $qr->id) {
            return response()->json(['success' => false, 'message' => 'Booking already assigned to another QR code.'], 422);
        }

        // If QR is already assigned to a different booking, clear the old booking's tour_code
        if ($oldBookingId && $oldBookingId != $booking->id) {
            $oldBooking = Booking::find($oldBookingId);
            if ($oldBooking && $oldBooking->tour_code === $qr->code) {
                $oldBooking->tour_code = null;
                $oldBooking->save();
            }
        }

        // Assign booking to QR
        $qr->booking_id = $booking->id;
        $qr->save();

        // Update booking's tour_code with QR code
        $oldTourCode = $booking->tour_code;
        $booking->tour_code = $qr->code;
        $booking->save();

        // Log QR assignment
        BookingHistory::create([
            'booking_id' => $booking->id,
            'from_status' => $booking->status,
            'to_status' => $booking->status,
            'changed_by' => auth()->id(),
            'notes' => 'QR code assigned to booking',
            'metadata' => [
                'qr_id' => $qr->id,
                'qr_code' => $qr->code,
                'old_qr_code' => $oldTourCode,
                'old_booking_id' => $oldBookingId,
                'action' => 'qr_assigned'
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        activity('bookings')
            ->performedOn($booking)
            ->causedBy(auth()->user())
            ->withProperties([
                'event' => 'qr_assigned',
                'qr_id' => $qr->id,
                'qr_code' => $qr->code,
                'old_tour_code' => $oldTourCode,
            ])
            ->log('QR code assigned to booking');

        activity('qr_code')
            ->performedOn($qr)
            ->causedBy(auth()->user())
            ->withProperties([
                'event' => 'assigned_to_booking',
                'booking_id' => $booking->id,
                'old_booking_id' => $oldBookingId,
            ])
            ->log('QR code assigned to booking #' . $booking->id);

        return response()->json([
            'success' => true,
            'message' => 'Booking assigned to QR successfully.',
            'qr' => $qr->fresh(),
            'booking' => $booking->fresh()
        ]);
    }

    /**
     * Update booking via AJAX
     */
    public function updateAjax(Request $request, Booking $booking)
    {
        // Check if this is a status-only or payment_status-only update
        if ($request->has('status') && !$request->has('user_id')) {
            // Check permission for status update
            if (!$request->user()->can('booking_update_status')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to update booking status.'
                ], 403);
            }
            
            // Status-only update - use changeStatus method
            $request->validate([
                'status' => ['required', 'in:' . implode(',', Booking::getAvailableStatuses())],
                'notes' => ['nullable', 'string', 'max:500'],
            ]);

            $oldStatus = $booking->status;

            $booking->changeStatus(
                $request->status,
                auth()->id(),
                $request->notes ?? 'Status updated via AJAX'
            );

            activity('bookings')
                ->performedOn($booking)
                ->causedBy(auth()->user())
                ->withProperties([
                    'event' => 'status_updated_ajax',
                    'old_status' => $oldStatus,
                    'new_status' => $request->status,
                    'notes' => $request->notes
                ])
                ->log('Booking status updated via AJAX');

            return response()->json([
                'success' => true,
                'message' => 'Booking status updated successfully',
                'booking' => $booking->fresh(),
                'latest_history' => $booking->latestHistory,
            ]);
        }

        // Check if this is a payment_status-only update
        if ($request->has('payment_status') && !$request->has('user_id')) {
            // Check permission for payment status update
            if (!$request->user()->can('booking_update_payment_status')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to update payment status.'
                ], 403);
            }
            
            $request->validate([
                'payment_status' => ['required', 'in:unpaid,pending,paid,failed,refunded'],
                'notes' => ['nullable', 'string', 'max:500'],
            ]);

            $oldPaymentStatus = $booking->payment_status;
            $booking->payment_status = $request->payment_status;
            $booking->save();

            // Create booking history entry for payment status change
            BookingHistory::create([
                'booking_id' => $booking->id,
                'from_status' => $booking->status,
                'to_status' => $booking->status, // Status doesn't change
                'changed_by' => auth()->id(),
                'notes' => $request->notes ?? "Payment status changed from {$oldPaymentStatus} to {$request->payment_status}",
                'metadata' => [
                    'payment_status_change' => true,
                    'old_payment_status' => $oldPaymentStatus,
                    'new_payment_status' => $request->payment_status,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            activity('bookings')
                ->performedOn($booking)
                ->causedBy($request->user())
                ->withProperties([
                    'old_payment_status' => $oldPaymentStatus,
                    'new_payment_status' => $request->payment_status,
                ])
                ->log('Payment status updated via AJAX');

            return response()->json([
                'success' => true,
                'message' => 'Payment status updated successfully',
                'booking' => $booking->fresh(),
            ]);
        }

        // Full update - requires all fields
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'property_type_id' => ['required', 'exists:property_types,id'],
            'property_sub_type_id' => ['required', 'exists:property_sub_types,id'],
            'area' => ['required', 'numeric', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'payment_status' => ['required', 'in:unpaid,pending,paid,failed,refunded'],
            'status' => ['required', 'in:' . implode(',', Booking::getAvailableStatuses())],
            'bhk_id' => ['nullable', 'exists:b_h_k_s,id'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'state_id' => ['nullable', 'exists:states,id'],
            'furniture_type' => ['nullable', 'string'],
            'booking_date' => ['nullable', 'date'],
            'house_no' => ['nullable', 'string', 'max:255'],
            'building' => ['nullable', 'string', 'max:255'],
            'society_name' => ['nullable', 'string', 'max:255'],
            'address_area' => ['nullable', 'string', 'max:255'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'pin_code' => ['nullable', 'string', 'max:20'],
            'full_address' => ['nullable', 'string'],
        ]);

        $oldData = $booking->toArray();
        $oldStatus = $booking->status;
        $oldPaymentStatus = $booking->payment_status;
        
        $booking->update($validated);

        // Create booking history for full update
        $changes = [];
        foreach ($validated as $key => $value) {
            if (isset($oldData[$key]) && $oldData[$key] != $value) {
                $changes[$key] = [
                    'old' => $oldData[$key],
                    'new' => $value
                ];
            }
        }

        BookingHistory::create([
            'booking_id' => $booking->id,
            'from_status' => $oldStatus,
            'to_status' => $validated['status'] ?? $oldStatus,
            'changed_by' => auth()->id(),
            'notes' => 'Booking updated via AJAX',
            'metadata' => [
                'changes' => $changes,
                'full_update' => true,
                'old_payment_status' => $oldPaymentStatus,
                'new_payment_status' => $validated['payment_status'] ?? $oldPaymentStatus,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        activity('bookings')
            ->performedOn($booking)
            ->causedBy($request->user())
            ->withProperties([
                'old' => $oldData,
                'attributes' => $booking->toArray(),
                'changes' => $changes,
            ])
            ->log('Booking updated via AJAX');

        return response()->json([
            'success' => true,
            'message' => 'Booking updated successfully',
            'booking' => $booking->fresh(),
        ]);
    }

    /**
     * Generate a random 8-character QR code (A-Za-z0-9)
     * 
     * @return string
     */
    private function generateRandomQrCode(): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $code = '';
        for ($i = 0; $i < 8; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $code;
    }

    /**
     * Generate a unique QR code that doesn't exist in the database
     * 
     * @return string
     */
    private function generateUniqueQrCode(): string
    {
        do {
            $code = $this->generateRandomQrCode();
        } while (QR::where('code', $code)->exists());
        return $code;
    }
}