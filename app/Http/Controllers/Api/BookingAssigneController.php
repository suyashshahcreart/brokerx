<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookingAssignee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingAssigneController extends Controller
{
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
