<?php

namespace App\Http\Controllers\Admin\ajax;

use App\Http\Controllers\Controller;
use App\Models\BookingAssignee;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingAssigneController extends Controller
{
    /**
     * Get all bookings for date range with sorting (Admin sees all, Photographer sees assigned)
     * 
     * Request params:
     * - from_date (required)
     * - to_date (required)
     * - sort_by (optional): booking_date, booking_time, status, price, id (default: booking_date)
     * - sort_order (optional): asc, desc (default: asc)
     * - photographer (optional): filter by photographer user_id
     * - status (optional): filter by booking status
     */
    public function getAllBookings(Request $request)
    {
        $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'sort_by' => 'sometimes|in:booking_date,booking_time,status,price,id,created_at',
            'sort_order' => 'sometimes|in:asc,desc',
        ]);

        $auth = Auth::user();
        $sortBy = $request->input('sort_by', 'booking_date');
        $sortOrder = $request->input('sort_order', 'desc');

        if ($auth->hasRole('admin')) {
            // Admin: Get ALL bookings in date range
            $query = Booking::with([
                'user:id,firstname,lastname,email,mobile',
                'propertyType:id,name',
                'propertySubType:id,name',
                'bhk:id,name',
                'city:id,name',
                'state:id,name',
                'assignees' => function($q) {
                    $q->with('user:id,firstname,lastname,email,mobile');
                }
            ]);

            // Date range filter
            $query->whereBetween('booking_date', [
                $request->from_date,
                $request->to_date
            ]);

            // Filter by photographer if specified
            if ($request->filled('photographer')) {
                $query->whereHas('assignees', function($q) use ($request) {
                    $q->where('user_id', $request->photographer);
                });
            }

            // Filter by status if specified
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Apply sorting
            // $query->orderBy($sortBy, $sortOrder)
            //       ->orderBy('booking_time', 'asc');

            $bookings = $query->get();

            // Transform to return booking with assignees
            $data = $bookings->map(function($booking) {
                return [
                    'booking' => [
                        'id' => $booking->id,
                        'booking_date' => $booking->booking_date,
                        'booking_time' => $booking->booking_time,
                        'status' => $booking->status,
                        'price' => $booking->price,
                        'full_address' => $booking->full_address,
                        'pin_code' => $booking->pin_code,
                        'user' => $booking->user, // Customer data
                        'propertyType' => $booking->propertyType,
                        'propertySubType' => $booking->propertySubType,
                        'bhk' => $booking->bhk,
                        'city' => $booking->city,
                        'state' => $booking->state,
                    ],
                    'assignees' => $booking->assignees->map(function($assignee) {
                        return [
                            'id' => $assignee->id,
                            'date' => $assignee->date,
                            'time' => $assignee->time,
                            'user' => $assignee->user // Photographer data
                        ];
                    })->toArray()
                ];
            });

            return response()->json([
                'success' => true,
                'count' => $data->count(),
                'data' => $data,
            ]);

        } else {
            // Photographer: Get only assigned bookings for this user
            $query = Booking::with([
                'user:id,firstname,lastname,email',
                'propertyType:id,name',
                'propertySubType:id,name',
                'bhk:id,name',
                'city:id,name',
                'state:id,name',
                'assignees' => function($q) use ($auth) {
                    $q->where('user_id', $auth->id)
                      ->with('user:id,firstname,lastname,email');
                }
            ]);

            // Date range filter
            $query->whereBetween('booking_date', [
                $request->from_date,
                $request->to_date
            ]);

            // Filter: Only bookings assigned to this photographer
            $query->whereHas('assignees', function($q) use ($auth) {
                $q->where('user_id', $auth->id);
            });

            // Restrict to schedule-related statuses for photographers
            $query->whereIn('status', ['schedul_assign', 'reschedul_assign', 'schedul_inprogress', 'schedul_completed']);

            // Filter by status if specified
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Apply sorting
            $query->orderBy($sortBy, $sortOrder)
                  ->orderBy('booking_time', 'asc');

            $bookings = $query->get();

            // Transform to return booking with assignees (filtered to this photographer)
            $data = $bookings->map(function($booking) {
                return [
                    'booking' => [
                        'id' => $booking->id,
                        'booking_date' => $booking->booking_date,
                        'booking_time' => $booking->booking_time,
                        'status' => $booking->status,
                        'price' => $booking->price,
                        'full_address' => $booking->full_address,
                        'pin_code' => $booking->pin_code,
                        'user' => $booking->user, // Customer data
                        'propertyType' => $booking->propertyType,
                        'propertySubType' => $booking->propertySubType,
                        'bhk' => $booking->bhk,
                        'city' => $booking->city,
                        'state' => $booking->state,
                    ],
                    'assignees' => $booking->assignees->map(function($assignee) {
                        return [
                            'id' => $assignee->id,
                            'date' => $assignee->date,
                            'time' => $assignee->time,
                            'user' => $assignee->user // Photographer data
                        ];
                    })->toArray()
                ];
            });

            return response()->json([
                'success' => true,
                'count' => $data->count(),
                'data' => $data,
            ]);
        }
    }

    /**
     * Return booking slots for a given date and photographer.
     *
     * Request params:
     * - date (required, single date string)
     * - user_id (required)
     */
    public function slots(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'user_id' => 'required|exists:users,id',
        ]);

        $auth = Auth::user();

        $query = BookingAssignee::with([
            'booking:id,user_id,booking_date,booking_time,status',
            'user:id,firstname,lastname,email,mobile'
        ]);

        // Filter by photographer (user) - require provided user_id
        if (!$auth->hasRole('admin') && (int) $request->user_id !== $auth->id) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $query->where('user_id', $request->user_id);

        // Date filtering - required single date
        $query->whereDate('date', $request->date);

        // Only include bookings with relevant statuses
        $query->whereHas('booking', function ($q) {
            $q->whereIn('status', ['schedul_assign', 'reschedul_assign', 'schedul_inprogress', 'schedul_completed']);
        });

        $slots = $query->orderBy('date')->orderBy('time')->get();

        // Simplify response data
        $data = $slots->map(function ($assignee) {
            return [
                'assignee_id' => $assignee->id,
                'user_id' => $assignee->user_id,
                'user' => $assignee->user,
                'booking_id' => $assignee->booking_id,
                'booking' => $assignee->booking,
                'date' => optional($assignee->date)->toDateString(),
                'time' => optional($assignee->time)->format('H:i'),
            ];
        });

        return response()->json([
            'success' => true,
            'count' => $data->count(),
            'data' => $data,
        ]);
    }
}
