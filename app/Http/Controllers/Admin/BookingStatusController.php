<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingHistory;
use App\Models\User;
use Illuminate\Http\Request;

class BookingStatusController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:booking_edit')->except(['getBookingHistory', 'getStatusStatistics']);
        $this->middleware('permission:booking_view')->only(['getBookingHistory', 'getStatusStatistics']);
    }

    /**
     * Approve a schedule request
     */
    public function approveSchedule(Request $request, Booking $booking)
    {
        // Check permission
        if (!$request->user()->can('booking_approval')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to approve schedules.'
            ], 403);
        }

        // Validate that booking is in correct state
        if ($booking->status !== 'schedul_pending') {
            return response()->json([
                'success' => false,
                'message' => 'Booking is not pending schedule approval'
            ], 422);
        }

        $request->validate([
            'scheduled_date' => 'nullable|date|after:today',
            'notes' => 'nullable|string|max:500'
        ]);

        $oldStatus = $booking->status;
        $oldDate = $booking->booking_date;

        // Update scheduled date if provided
        if ($request->scheduled_date) {
            $booking->booking_date = $request->scheduled_date;
            $booking->save();

            // Log date update separately
            BookingHistory::create([
                'booking_id' => $booking->id,
                'from_status' => $oldStatus,
                'to_status' => $oldStatus,
                'changed_by' => auth()->id(),
                'notes' => 'Scheduled date updated during approval',
                'metadata' => [
                    'old_date' => $oldDate ? $oldDate->format('Y-m-d') : null,
                    'new_date' => $request->scheduled_date,
                    'updated_during' => 'schedule_approval'
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        // Change status with history tracking
        $booking->changeStatus(
            'schedul_accepted',
            auth()->id(),
            $request->notes ?? 'Schedule approved by ' . auth()->user()->name,
            [
                'approved_at' => now(),
                'approved_by' => auth()->user()->name,
                'scheduled_date' => $request->scheduled_date
            ]
        );

        activity('bookings')
            ->performedOn($booking)
            ->causedBy(auth()->user())
            ->withProperties([
                'event' => 'schedule_approved',
                'old_status' => $oldStatus,
                'new_status' => 'schedul_accepted',
                'scheduled_date' => $request->scheduled_date,
                'notes' => $request->notes
            ])
            ->log('Schedule approved for booking');

        return response()->json([
            'success' => true,
            'message' => 'Schedule approved successfully',
            'booking' => $booking->fresh()
        ]);
    }

    /**
     * Decline a schedule request with reason
     */
    public function declineSchedule(Request $request, Booking $booking)
    {
        // Check permission
        if (!$request->user()->can('booking_approval')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to decline schedules.'
            ], 403);
        }

        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $oldStatus = $booking->status;
        $requestedDate = $booking->booking_date?->format('Y-m-d');
        
        // Clear booking date when declined
        $booking->booking_date = null;
        $booking->booking_notes = null;
        $booking->save();

        // Log date clearing
        BookingHistory::create([
            'booking_id' => $booking->id,
            'from_status' => $oldStatus,
            'to_status' => $oldStatus,
            'changed_by' => auth()->id(),
            'notes' => 'Booking date and notes cleared due to schedule decline',
            'metadata' => [
                'cleared_date' => $requestedDate,
                'reason' => $request->reason,
                'action' => 'date_cleared'
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $booking->changeStatus(
            'schedul_decline',
            auth()->id(),
            'Schedule declined: ' . $request->reason,
            [
                'declined_at' => now(),
                'declined_by' => auth()->user()->name,
                'reason' => $request->reason,
                'scheduled_date_requested' => $requestedDate
            ]
        );

        activity('bookings')
            ->performedOn($booking)
            ->causedBy(auth()->user())
            ->withProperties([
                'event' => 'schedule_declined',
                'old_status' => $oldStatus,
                'new_status' => 'schedul_decline',
                'reason' => $request->reason,
                'requested_date' => $requestedDate
            ])
            ->log('Schedule declined for booking');

        return response()->json([
            'success' => true,
            'message' => 'Schedule declined',
            'booking' => $booking->fresh()
        ]);
    }

    /**
     * Request reschedule
     */
    public function requestReschedule(Request $request, Booking $booking)
    {
        $request->validate([
            'new_date' => 'required|date|after:today',
            'reason' => 'required|string|max:500'
        ]);

        // Check if booking can be rescheduled
        if ($booking->status === 'reschedul_blocked') {
            return response()->json([
                'success' => false,
                'message' => 'This booking is blocked from rescheduling'
            ], 422);
        }

        // Count previous ACCEPTED schedule attempts (not all reschedule statuses)
        $rescheduleCount = BookingHistory::where('booking_id', $booking->id)
            ->whereIn('to_status', ['schedul_accepted', 'reschedul_accepted'])
            ->count();

        // Log reschedule attempt tracking
        BookingHistory::create([
            'booking_id' => $booking->id,
            'from_status' => $booking->status,
            'to_status' => $booking->status,
            'changed_by' => auth()->id(),
            'notes' => 'Reschedule attempt ' . ($rescheduleCount + 1) . ' requested',
            'metadata' => [
                'attempt_number' => $rescheduleCount + 1,
                'requested_date' => $request->new_date,
                'reason' => $request->reason,
                'action' => 'reschedule_attempt_tracked'
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Get max attempts from settings
        $maxAttemptsSetting = \App\Models\Setting::where('name', 'customer_attempt')->first();
        $maxAttempts = $maxAttemptsSetting ? (int) $maxAttemptsSetting->value : 3;

        // Block if too many attempts
        if ($rescheduleCount >= $maxAttempts) {
            $oldStatus = $booking->status;

            $booking->changeStatus(
                'reschedul_blocked',
                auth()->id(),
                'Maximum schedule attempts reached - Booking blocked',
                [
                    'reschedule_count' => $rescheduleCount,
                    'max_attempts' => $maxAttempts,
                    'blocked_by' => 'system',
                    'blocked_at' => now()->toDateTimeString()
                ]
            );

            activity('bookings')
                ->performedOn($booking)
                ->causedBy(auth()->user())
                ->withProperties([
                    'event' => 'reschedule_blocked',
                    'old_status' => $oldStatus,
                    'new_status' => 'reschedul_blocked',
                    'reschedule_count' => $rescheduleCount,
                    'max_attempts' => $maxAttempts
                ])
                ->log('Booking blocked due to maximum reschedule attempts');

            $blockedMessage = \App\Models\Setting::where('name', 'customer_attempt_note')->first();
            return response()->json([
                'success' => false,
                'message' => $blockedMessage?->value ?? 'Maximum schedule attempts reached. Booking has been blocked.',
                'blocked' => true,
                'attempts' => $rescheduleCount,
                'max_attempts' => $maxAttempts
            ], 422);
        }

        $oldStatus = $booking->status;

        $booking->changeStatus(
            'reschedul_pending',
            auth()->id(),
            'Reschedule requested: ' . $request->reason,
            [
                'requested_date' => $request->new_date,
                'reason' => $request->reason,
                'attempt_number' => $rescheduleCount + 1
            ]
        );

        activity('bookings')
            ->performedOn($booking)
            ->causedBy(auth()->user())
            ->withProperties([
                'event' => 'reschedule_requested',
                'old_status' => $oldStatus,
                'new_status' => 'reschedul_pending',
                'requested_date' => $request->new_date,
                'reason' => $request->reason,
                'attempt_number' => $rescheduleCount + 1
            ])
            ->log('Reschedule requested for booking');

        return response()->json([
            'success' => true,
            'message' => 'Reschedule request submitted successfully',
            'booking' => $booking->fresh()
        ]);
    }

    /**
     * Approve a reschedule request
     */
    public function approveReschedule(Request $request, Booking $booking)
    {
        if ($booking->status !== 'reschedul_pending') {
            return response()->json([
                'success' => false,
                'message' => 'Booking is not pending reschedule approval'
            ], 422);
        }

        $request->validate([
            'scheduled_date' => 'nullable|date|after:today',
            'notes' => 'nullable|string|max:500'
        ]);

        $oldStatus = $booking->status;
        $oldDate = $booking->booking_date;

        // Update booking date if provided
        if ($request->scheduled_date) {
            $booking->booking_date = $request->scheduled_date;
            $booking->save();

            // Log date update
            BookingHistory::create([
                'booking_id' => $booking->id,
                'from_status' => $oldStatus,
                'to_status' => $oldStatus,
                'changed_by' => auth()->id(),
                'notes' => 'Booking date updated during reschedule approval',
                'metadata' => [
                    'old_date' => $oldDate ? $oldDate->format('Y-m-d') : null,
                    'new_date' => $request->scheduled_date,
                    'action' => 'date_updated'
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        $booking->changeStatus(
            'reschedul_accepted',
            auth()->id(),
            $request->notes ?? 'Reschedule approved',
            [
                'approved_at' => now(),
                'new_scheduled_date' => $request->scheduled_date
            ]
        );

        activity('bookings')
            ->performedOn($booking)
            ->causedBy(auth()->user())
            ->withProperties([
                'event' => 'reschedule_approved',
                'old_status' => $oldStatus,
                'new_status' => 'reschedul_accepted',
                'old_date' => $oldDate,
                'new_scheduled_date' => $request->scheduled_date,
                'notes' => $request->notes
            ])
            ->log('Reschedule approved for booking');

        return response()->json([
            'success' => true,
            'message' => 'Reschedule approved successfully',
            'booking' => $booking->fresh()
        ]);
    }

    /**
     * Decline a reschedule request
     */
    public function declineReschedule(Request $request, Booking $booking)
    {
        if ($booking->status !== 'reschedul_pending') {
            return response()->json([
                'success' => false,
                'message' => 'Booking is not pending reschedule'
            ], 422);
        }

        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $oldStatus = $booking->status;
        $requestedDate = $booking->booking_date?->format('Y-m-d');
        
        // Clear booking date when reschedule is declined
        $booking->booking_date = null;
        $booking->booking_notes = null;
        $booking->save();

        // Log date clearing
        BookingHistory::create([
            'booking_id' => $booking->id,
            'from_status' => $oldStatus,
            'to_status' => $oldStatus,
            'changed_by' => auth()->id(),
            'notes' => 'Booking date cleared due to reschedule decline',
            'metadata' => [
                'cleared_date' => $requestedDate,
                'reason' => $request->reason,
                'action' => 'date_cleared'
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $booking->changeStatus(
            'reschedul_decline',
            auth()->id(),
            'Reschedule declined: ' . $request->reason,
            [
                'declined_at' => now(),
                'declined_by' => auth()->user()->name,
                'reason' => $request->reason,
                'scheduled_date_requested' => $requestedDate
            ]
        );

        activity('bookings')
            ->performedOn($booking)
            ->causedBy(auth()->user())
            ->withProperties([
                'event' => 'reschedule_declined',
                'old_status' => $oldStatus,
                'new_status' => 'reschedul_decline',
                'reason' => $request->reason,
                'requested_date' => $requestedDate
            ])
            ->log('Reschedule declined for booking');

        return response()->json([
            'success' => true,
            'message' => 'Reschedule declined',
            'booking' => $booking->fresh()
        ]);
    }

    /**
     * Assign booking to team member
     */
    public function assignToTeamMember(Request $request, Booking $booking)
    {
        $request->validate([
            'team_member_id' => 'required|exists:users,id',
            'scheduled_date' => 'required|date',
            'notes' => 'nullable|string|max:500'
        ]);

        $oldStatus = $booking->status;
        $oldDate = $booking->booking_date;

        // Update booking with assignment details
        $booking->update([
            'booking_date' => $request->scheduled_date
        ]);

        // Log date assignment
        BookingHistory::create([
            'booking_id' => $booking->id,
            'from_status' => $oldStatus,
            'to_status' => $oldStatus,
            'changed_by' => auth()->id(),
            'notes' => 'Booking date updated for team member assignment',
            'metadata' => [
                'old_date' => $oldDate ? $oldDate->format('Y-m-d') : null,
                'new_date' => $request->scheduled_date,
                'team_member_id' => $request->team_member_id,
                'action' => 'date_updated_for_assignment'
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $teamMember = User::find($request->team_member_id);

        $booking->changeStatus(
            'schedul_assign',
            auth()->id(),
            $request->notes ?? 'Assigned to ' . $teamMember->firstname . ' ' . $teamMember->lastname,
            [
                'assigned_to' => $request->team_member_id,
                'assigned_to_name' => $teamMember->firstname . ' ' . $teamMember->lastname,
                'assigned_by' => auth()->id(),
                'scheduled_date' => $request->scheduled_date
            ]
        );

        activity('bookings')
            ->performedOn($booking)
            ->causedBy(auth()->user())
            ->withProperties([
                'event' => 'assigned_to_team_member',
                'old_status' => $oldStatus,
                'new_status' => 'schedul_assign',
                'old_date' => $oldDate,
                'scheduled_date' => $request->scheduled_date,
                'team_member_id' => $request->team_member_id,
                'team_member_name' => $teamMember->firstname . ' ' . $teamMember->lastname,
                'notes' => $request->notes
            ])
            ->log('Booking assigned to team member');

        return response()->json([
            'success' => true,
            'message' => 'Booking assigned successfully',
            'booking' => $booking->fresh()
        ]);
    }

    /**
     * Mark tour as completed
     */
    public function completeTour(Request $request, Booking $booking)
    {
        if (!in_array($booking->status, ['schedul_assign'])) {
            return response()->json([
                'success' => false,
                'message' => 'Booking must be assigned before it can be completed'
            ], 422);
        }

        $request->validate([
            'completion_notes' => 'nullable|string|max:1000',
            'photos_count' => 'nullable|integer|min:0',
            'videos_count' => 'nullable|integer|min:0'
        ]);

        $oldStatus = $booking->status;

        // Log media counts if provided
        if ($request->photos_count || $request->videos_count) {
            BookingHistory::create([
                'booking_id' => $booking->id,
                'from_status' => $oldStatus,
                'to_status' => $oldStatus,
                'changed_by' => auth()->id(),
                'notes' => 'Media counts recorded for tour completion',
                'metadata' => [
                    'photos_count' => $request->photos_count ?? 0,
                    'videos_count' => $request->videos_count ?? 0,
                    'action' => 'media_counts_recorded'
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        $booking->changeStatus(
            'schedul_completed',
            auth()->id(),
            $request->completion_notes ?? 'Tour completed',
            [
                'completed_at' => now(),
                'photos_count' => $request->photos_count,
                'videos_count' => $request->videos_count
            ]
        );

        activity('bookings')
            ->performedOn($booking)
            ->causedBy(auth()->user())
            ->withProperties([
                'event' => 'tour_completed',
                'old_status' => $oldStatus,
                'new_status' => 'schedul_completed',
                'photos_count' => $request->photos_count,
                'videos_count' => $request->videos_count,
                'completion_notes' => $request->completion_notes
            ])
            ->log('Tour marked as completed');

        return response()->json([
            'success' => true,
            'message' => 'Tour marked as completed',
            'booking' => $booking->fresh()
        ]);
    }

    /**
     * Start tour processing
     */
    public function startTourProcessing(Request $request, Booking $booking)
    {
        if ($booking->status !== 'schedul_completed') {
            return response()->json([
                'success' => false,
                'message' => 'Tour must be completed before processing'
            ], 422);
        }

        $oldStatus = $booking->status;

        $booking->changeStatus(
            'tour_pending',
            auth()->id(),
            $request->notes ?? 'Tour processing started',
            ['processing_started_at' => now()]
        );

        activity('bookings')
            ->performedOn($booking)
            ->causedBy(auth()->user())
            ->withProperties([
                'event' => 'tour_processing_started',
                'old_status' => $oldStatus,
                'new_status' => 'tour_pending',
                'notes' => $request->notes
            ])
            ->log('Tour processing started');

        return response()->json([
            'success' => true,
            'message' => 'Tour processing started',
            'booking' => $booking->fresh()
        ]);
    }

    /**
     * Mark tour as ready/completed
     */
    public function completeTourProcessing(Request $request, Booking $booking)
    {
        if ($booking->status !== 'tour_pending') {
            return response()->json([
                'success' => false,
                'message' => 'Tour must be in processing state'
            ], 422);
        }

        $request->validate([
            'tour_url' => 'required|url',
            'notes' => 'nullable|string|max:500'
        ]);

        $oldStatus = $booking->status;
        $oldTourUrl = $booking->tour_final_link;

        $booking->update([
            'tour_final_link' => $request->tour_url
        ]);

        // Log tour URL update
        BookingHistory::create([
            'booking_id' => $booking->id,
            'from_status' => $oldStatus,
            'to_status' => $oldStatus,
            'changed_by' => auth()->id(),
            'notes' => 'Tour final link updated',
            'metadata' => [
                'old_tour_url' => $oldTourUrl,
                'new_tour_url' => $request->tour_url,
                'action' => 'tour_url_updated'
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $booking->changeStatus(
            'tour_completed',
            auth()->id(),
            $request->notes ?? 'Tour processing completed',
            [
                'tour_url' => $request->tour_url,
                'processing_completed_at' => now()
            ]
        );

        activity('bookings')
            ->performedOn($booking)
            ->causedBy(auth()->user())
            ->withProperties([
                'event' => 'tour_processing_completed',
                'old_status' => $oldStatus,
                'new_status' => 'tour_completed',
                'tour_url' => $request->tour_url,
                'notes' => $request->notes
            ])
            ->log('Tour processing completed');

        return response()->json([
            'success' => true,
            'message' => 'Tour processing completed',
            'booking' => $booking->fresh()
        ]);
    }

    /**
     * Publish tour (make it live)
     */
    public function publishTour(Request $request, Booking $booking)
    {
        if ($booking->status !== 'tour_completed') {
            return response()->json([
                'success' => false,
                'message' => 'Tour must be completed before publishing'
            ], 422);
        }

        $oldStatus = $booking->status;

        $booking->changeStatus(
            'tour_live',
            auth()->id(),
            $request->notes ?? 'Tour published and live',
            ['published_at' => now()]
        );

        activity('bookings')
            ->performedOn($booking)
            ->causedBy(auth()->user())
            ->withProperties([
                'event' => 'tour_published',
                'old_status' => $oldStatus,
                'new_status' => 'tour_live',
                'notes' => $request->notes
            ])
            ->log('Tour published and made live');

        return response()->json([
            'success' => true,
            'message' => 'Tour is now live',
            'booking' => $booking->fresh()
        ]);
    }

    /**
     * Put booking under maintenance
     */
    public function putUnderMaintenance(Request $request, Booking $booking)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $oldStatus = $booking->status;

        // Store previous status for restoration tracking
        $previousStatus = $booking->status;
        
        BookingHistory::create([
            'booking_id' => $booking->id,
            'from_status' => $oldStatus,
            'to_status' => $oldStatus,
            'changed_by' => auth()->id(),
            'notes' => 'Maintenance started - Previous status stored for restoration',
            'metadata' => [
                'previous_status' => $previousStatus,
                'reason' => $request->reason,
                'action' => 'maintenance_initiated'
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $booking->changeStatus(
            'maintenance',
            auth()->id(),
            'Booking under maintenance: ' . $request->reason,
            [
                'maintenance_started_at' => now(),
                'reason' => $request->reason,
                'previous_status' => $booking->status
            ]
        );

        activity('bookings')
            ->performedOn($booking)
            ->causedBy(auth()->user())
            ->withProperties([
                'event' => 'put_under_maintenance',
                'old_status' => $oldStatus,
                'new_status' => 'maintenance',
                'reason' => $request->reason
            ])
            ->log('Booking put under maintenance');

        return response()->json([
            'success' => true,
            'message' => 'Booking put under maintenance',
            'booking' => $booking->fresh()
        ]);
    }

    /**
     * Remove from maintenance (restore to previous processing state)
     */
    public function removeFromMaintenance(Request $request, Booking $booking)
    {
        if ($booking->status !== 'maintenance') {
            return response()->json([
                'success' => false,
                'message' => 'Booking is not under maintenance'
            ], 422);
        }

        $request->validate([
            'target_status' => 'required|in:tour_pending,tour_completed,tour_live',
            'notes' => 'nullable|string|max:500'
        ]);

        $oldStatus = $booking->status;

        // Log restoration details
        BookingHistory::create([
            'booking_id' => $booking->id,
            'from_status' => $oldStatus,
            'to_status' => $oldStatus,
            'changed_by' => auth()->id(),
            'notes' => 'Booking restored from maintenance to ' . $request->target_status,
            'metadata' => [
                'maintenance_status' => $oldStatus,
                'restored_to' => $request->target_status,
                'action' => 'maintenance_restoration'
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $booking->changeStatus(
            $request->target_status,
            auth()->id(),
            $request->notes ?? 'Maintenance completed',
            ['maintenance_completed_at' => now()]
        );

        activity('bookings')
            ->performedOn($booking)
            ->causedBy(auth()->user())
            ->withProperties([
                'event' => 'removed_from_maintenance',
                'old_status' => $oldStatus,
                'new_status' => $request->target_status,
                'notes' => $request->notes
            ])
            ->log('Booking removed from maintenance');

        return response()->json([
            'success' => true,
            'message' => 'Booking removed from maintenance',
            'booking' => $booking->fresh()
        ]);
    }

    /**
     * Expire a booking
     */
    public function expireBooking(Request $request, Booking $booking)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500'
        ]);

        $oldStatus = $booking->status;

        $booking->changeStatus(
            'expired',
            auth()->id(),
            $request->reason ?? 'Booking expired',
            [
                'expired_at' => now(),
                'reason' => $request->reason
            ]
        );

        activity('bookings')
            ->performedOn($booking)
            ->causedBy(auth()->user())
            ->withProperties([
                'event' => 'booking_expired',
                'old_status' => $oldStatus,
                'new_status' => 'expired',
                'reason' => $request->reason
            ])
            ->log('Booking expired');

        return response()->json([
            'success' => true,
            'message' => 'Booking expired',
            'booking' => $booking->fresh()
        ]);
    }

    /**
     * Get booking history timeline
     */
    public function getBookingHistory(Booking $booking)
    {
        $history = $booking->histories()
            ->with('changedBy:id,firstname,lastname,email')
            ->get()
            ->map(function ($entry) {
                return [
                    'id' => $entry->id,
                    'from_status' => $entry->from_status,
                    'from_status_label' => $entry->from_status ? ucwords(str_replace('_', ' ', $entry->from_status)) : null,
                    'to_status' => $entry->to_status,
                    'to_status_label' => ucwords(str_replace('_', ' ', $entry->to_status)),
                    'changed_by' => $entry->changedBy ? 
                        $entry->changedBy->firstname . ' ' . $entry->changedBy->lastname : 
                        'System',
                    'changed_by_email' => $entry->changedBy?->email,
                    'notes' => $entry->notes,
                    'metadata' => $entry->metadata,
                    'created_at' => $entry->created_at->format('Y-m-d H:i:s'),
                    'created_at_human' => $entry->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'success' => true,
            'history' => $history,
            'count' => $history->count()
        ]);
    }

    /**
     * Get status statistics
     */
    public function getStatusStatistics()
    {
        $statistics = Booking::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->status => [
                        'count' => $item->count,
                        'label' => ucwords(str_replace('_', ' ', $item->status))
                    ]
                ];
            });

        $recentChanges = BookingHistory::with('booking:id', 'changedBy:id,firstname,lastname')
            ->recent(20)
            ->get()
            ->map(function ($entry) {
                return [
                    'booking_id' => $entry->booking_id,
                    'from_status' => $entry->from_status,
                    'to_status' => $entry->to_status,
                    'to_status_label' => ucwords(str_replace('_', ' ', $entry->to_status)),
                    'changed_by' => $entry->changedBy ? 
                        $entry->changedBy->firstname . ' ' . $entry->changedBy->lastname : 
                        'System',
                    'notes' => $entry->notes,
                    'created_at' => $entry->created_at->format('Y-m-d H:i:s'),
                    'created_at_human' => $entry->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'success' => true,
            'statistics' => $statistics,
            'recent_changes' => $recentChanges,
            'total_bookings' => Booking::count()
        ]);
    }

    /**
     * Bulk status update
     */
    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'booking_ids' => 'required|array|min:1',
            'booking_ids.*' => 'exists:bookings,id',
            'status' => 'required|in:' . implode(',', Booking::getAvailableStatuses()),
            'notes' => 'nullable|string|max:500'
        ]);

        $bookings = Booking::whereIn('id', $request->booking_ids)->get();
        $updated = 0;
        $failed = 0;
        $errors = [];

        foreach ($bookings as $booking) {
            try {
                $oldStatus = $booking->status;

                // Log that this booking is part of bulk operation
                BookingHistory::create([
                    'booking_id' => $booking->id,
                    'from_status' => $oldStatus,
                    'to_status' => $oldStatus,
                    'changed_by' => auth()->id(),
                    'notes' => 'Included in bulk status update operation',
                    'metadata' => [
                        'total_bookings' => count($request->booking_ids),
                        'target_status' => $request->status,
                        'bulk_operation_id' => uniqid('bulk_'),
                        'action' => 'bulk_operation_started'
                    ],
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                $booking->changeStatus(
                    $request->status,
                    auth()->id(),
                    $request->notes ?? 'Bulk status update',
                    ['bulk_update' => true]
                );

                activity('bookings')
                    ->performedOn($booking)
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'event' => 'bulk_status_update',
                        'old_status' => $oldStatus,
                        'new_status' => $request->status,
                        'notes' => $request->notes,
                        'booking_ids' => $request->booking_ids
                    ])
                    ->log('Booking status updated via bulk operation');

                $updated++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = "Booking #{$booking->id}: {$e->getMessage()}";
            }
        }

        return response()->json([
            'success' => $failed === 0,
            'message' => "Updated {$updated} bookings to status: {$request->status}" . 
                         ($failed > 0 ? ". {$failed} failed." : ""),
            'updated' => $updated,
            'failed' => $failed,
            'errors' => $errors
        ]);
    }

    /**
     * Change booking status (generic method)
     */
    public function changeStatus(Request $request, Booking $booking)
    {
        // Check permission
        if (!$request->user()->can('booking_update_status')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to update booking status.'
            ], 403);
        }

        $request->validate([
            'status' => 'required|in:' . implode(',', Booking::getAvailableStatuses()),
            'notes' => 'nullable|string|max:500',
            'metadata' => 'nullable|array'
        ]);

        $oldStatus = $booking->status;

        $booking->changeStatus(
            $request->status,
            auth()->id(),
            $request->notes,
            $request->metadata
        );

        activity('bookings')
            ->performedOn($booking)
            ->causedBy(auth()->user())
            ->withProperties([
                'event' => 'status_changed',
                'old_status' => $oldStatus,
                'new_status' => $request->status,
                'notes' => $request->notes,
                'metadata' => $request->metadata
            ])
            ->log('Booking status changed');

        return response()->json([
            'success' => true,
            'message' => 'Booking status updated successfully',
            'booking' => $booking->fresh(),
            'latest_history' => $booking->latestHistory
        ]);
    }
}
