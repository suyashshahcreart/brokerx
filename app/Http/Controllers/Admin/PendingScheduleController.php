<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingHistory;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class PendingScheduleController extends Controller
{
    protected SmsService $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
        $this->middleware('permission:booking_view')->only(['index']);
        $this->middleware('permission:booking_approval')->only(['accept', 'decline']);
    }

    /**
     * Display pending schedules (schedul_pending and reschedul_pending)
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Booking::with(['user', 'propertyType', 'propertySubType', 'bhk', 'city', 'state'])
                ->whereIn('status', ['schedul_pending', 'reschedul_pending']);

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
                ->editColumn('status', function (Booking $booking) {
                    $badges = [
                        'schedul_pending' => 'warning',
                        'reschedul_pending' => 'warning',
                    ];
                    $color = $badges[$booking->status] ?? 'secondary';
                    return '<span class="badge bg-' . $color . ' text-uppercase">' . str_replace('_', ' ', $booking->status) . '</span>';
                })
                ->editColumn('payment_status', fn(Booking $booking) => '<span class="badge bg-info text-uppercase">' . $booking->payment_status . '</span>')
                ->addColumn('actions', function (Booking $booking) {
                    $view = route('admin.bookings.show', $booking);
                    $accept = route('admin.pending-schedules.accept', $booking);
                    $decline = route('admin.pending-schedules.decline', $booking);

                    return '
                          <div class="d-flex gap-1 justify-content-end">
                            <a href="' . $view . '" class="btn btn-sm btn-soft-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="View Booking Details">
                                <iconify-icon icon="solar:eye-broken" class="align-middle fs-18"></iconify-icon>
                            </a>
                            <button onclick="acceptSchedule(' . $booking->id . ')" class="btn btn-sm btn-soft-success" data-bs-toggle="tooltip" data-bs-placement="top" title="Accept Schedule">
                                <iconify-icon icon="solar:check-circle-broken" class="align-middle fs-18"></iconify-icon>
                            </button>
                            <button onclick="declineSchedule(' . $booking->id . ')" class="btn btn-sm btn-soft-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="Decline Schedule">
                                <iconify-icon icon="solar:close-circle-broken" class="align-middle fs-18"></iconify-icon>
                            </button>
                        </div>
                    ';
                })
                ->rawColumns(['type_subtype', 'city_state', 'status', 'payment_status', 'actions'])
                ->toJson();
        }

        $canEdit = $request->user()->can('booking_edit');
        return view('admin.pending-schedules.index', compact('canEdit'));
    }

    /**
     * Accept a schedule request
     */
    public function accept(Request $request, Booking $booking)
    {
        // Check permission
        if (!$request->user()->can('booking_approval')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to approve schedules.'
            ], 403);
        }

        // Validate booking is in pending state
        if (!in_array($booking->status, ['schedul_pending', 'reschedul_pending'])) {
            return response()->json([
                'success' => false,
                'message' => 'Booking is not pending schedule approval'
            ], 422);
        }

        $request->validate([
            'notes' => 'nullable|string|max:500'
        ]);

        $isReschedule = $booking->status === 'reschedul_pending';
        $oldStatus = $booking->status;

        // Capture before state for activity log
        $before = $booking->toArray();

        // Change status using the booking model method
        $newStatus = $isReschedule ? 'reschedul_accepted' : 'schedul_accepted';

        $booking->changeStatus(
            $newStatus,
            auth()->id(),
            $request->notes ?? ($isReschedule ? 'Reschedule approved by admin' : 'Schedule approved by admin'),
            array_filter([
                'approved_by' => auth()->user()->name,
                'approved_at' => now()->toDateTimeString(),
                'scheduled_date' => $booking->booking_date?->format('Y-m-d'),
                'admin_notes' => $request->notes,
            ], function ($value) {
                return !is_null($value) && $value !== '';
            })
        );

        // Capture after state and calculate changes
        $booking->refresh();
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

        // Log activity
        activity('bookings')
            ->performedOn($booking)
            ->causedBy($request->user())
            ->withProperties([
                'event' => 'schedule_accepted',
                'before' => $before,
                'after' => $after,
                'changes' => $changes,
                'is_reschedule' => $isReschedule,
                'admin_notes' => $request->notes,
            ])
            ->log($isReschedule ? 'Reschedule approved' : 'Schedule approved');

        // Send SMS notification to customer when schedule is accepted
        if ($booking->user && $booking->user->mobile && $booking->booking_date) {
            try {
                // PROPPIK: Your appointment is scheduled on ##DATE##. Please ensure you are available. – CREART
                // Template ID: 69295d82a0f6627e122a0252

                $mobile = $booking->user->mobile;

                // Ensure mobile has country code (91 for India)
                if (!str_starts_with($mobile, '91')) {
                    $mobile = '91' . $mobile;
                }

                // Format booking date for SMS
                $formattedDate = $booking->booking_date->format('d M Y'); // e.g., "05 Dec 2025"

                // Prepare SMS parameters
                $smsParams = [
                    'DATE' => $formattedDate
                ];

                // Send SMS using MSG91 appointment_scheduled template
                $this->smsService->send(
                    $mobile,                        // Mobile number with country code
                    'appointment_scheduled',        // Template key from config/msg91.php
                    $smsParams,                     // Template parameters
                    [
                        'type' => 'manual',
                        'reference_type' => 'App\Models\Booking',
                        'reference_id' => $booking->id,
                        'notes' => 'Appointment scheduled SMS sent after admin approval'
                    ]
                );

                \Log::info('Appointment scheduled SMS sent successfully', [
                    'booking_id' => $booking->id,
                    'mobile' => $mobile,
                    'template' => 'appointment_scheduled',
                    'template_id' => '69295d82a0f6627e122a0252',
                    'scheduled_date' => $formattedDate,
                    'is_reschedule' => $isReschedule
                ]);
            } catch (\Exception $e) {
                // Log error but don't fail the schedule acceptance
                \Log::error('Failed to send appointment scheduled SMS', [
                    'booking_id' => $booking->id,
                    'mobile' => $booking->user->mobile ?? 'N/A',
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => $isReschedule ? 'Reschedule approved successfully' : 'Schedule approved successfully',
            'booking' => $booking->fresh()
        ]);
    }

    /**
     * Decline a schedule request
     */
    public function decline(Request $request, Booking $booking)
    {
        // Check permission
        if (!$request->user()->can('booking_approval')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to decline schedules.'
            ], 403);
        }

        // Validate booking is in pending state
        if (!in_array($booking->status, ['schedul_pending', 'reschedul_pending'])) {
            return response()->json([
                'success' => false,
                'message' => 'Booking is not pending schedule approval'
            ], 422);
        }

        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $isReschedule = $booking->status === 'reschedul_pending';
        $oldStatus = $booking->status;
        $requestedDate = $booking->booking_date?->format('Y-m-d');

        // Capture before state for activity log
        $before = $booking->toArray();

        // Clear booking date when declined
        $booking->booking_date = null;
        $booking->booking_notes = null;
        $booking->save();

        // Change status using the booking model method
        $newStatus = $isReschedule ? 'reschedul_decline' : 'schedul_decline';

        $booking->changeStatus(
            $newStatus,
            auth()->id(),
            ($isReschedule ? 'Reschedule declined: ' : 'Schedule declined: ') . $request->reason,
            [
                'declined_by' => auth()->user()->name,
                'declined_at' => now()->toDateTimeString(),
                'reason' => $request->reason,
                'scheduled_date_requested' => $requestedDate,
            ]
        );

        // Capture after state and calculate changes
        $booking->refresh();
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

        // Log activity
        activity('bookings')
            ->performedOn($booking)
            ->causedBy($request->user())
            ->withProperties([
                'event' => 'schedule_declined',
                'before' => $before,
                'after' => $after,
                'changes' => $changes,
                'is_reschedule' => $isReschedule,
                'decline_reason' => $request->reason,
                'requested_date' => $requestedDate,
            ])
            ->log($isReschedule ? 'Reschedule declined' : 'Schedule declined');

        return response()->json([
            'success' => true,
            'message' => $isReschedule ? 'Reschedule declined' : 'Schedule declined',
            'booking' => $booking->fresh()
        ]);
    }
}
