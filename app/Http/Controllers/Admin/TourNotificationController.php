<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TourNotification;
use App\Models\Booking;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class TourNotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:tour_notification_view')->only(['index', 'show']);
    }

    /**
     * Display a listing of tour notifications
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = TourNotification::with(['booking'])
                ->select('tour_notifications.*');

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('tour_code', function (TourNotification $notification) {
                    return '<span class="fw-semibold">' . htmlspecialchars($notification->tour_code) . '</span>';
                })
                ->editColumn('phone_number', function (TourNotification $notification) {
                    return '<span class="text-muted">' . htmlspecialchars($notification->phone_number) . '</span>';
                })
                ->editColumn('status', function (TourNotification $notification) {
                    $badges = [
                        'pending' => 'bg-warning',
                        'notified' => 'bg-success',
                        'failed' => 'bg-danger'
                    ];
                    $class = $badges[$notification->status] ?? 'bg-secondary';
                    return '<span class="badge ' . $class . ' text-uppercase">' . $notification->status . '</span>';
                })
                ->editColumn('booking_id', function (TourNotification $notification) {
                    if ($notification->booking) {
                        return '<a href="' . route('admin.bookings.show', $notification->booking_id) . '" class="text-primary" target="_blank">Booking #' . $notification->booking_id . '</a>';
                    }
                    return '<span class="text-muted">-</span>';
                })
                ->editColumn('created_at', function (TourNotification $notification) {
                    return $notification->created_at ? $notification->created_at->format('d M Y H:i') : '-';
                })
                ->editColumn('notified_at', function (TourNotification $notification) {
                    return $notification->notified_at ? $notification->notified_at->format('d M Y H:i') : '<span class="text-muted">-</span>';
                })
                ->addColumn('actions', function (TourNotification $notification) {
                    return '<button type="button" class="btn btn-soft-primary btn-sm view-notification" 
                        data-id="' . $notification->id . '" 
                        data-bs-toggle="tooltip" data-bs-placement="top" title="View Notification Details">
                        <iconify-icon icon="solar:eye-broken" class="align-middle fs-18"></iconify-icon>
                    </button>';
                })
                ->filter(function ($query) use ($request) {
                    // Filter by tour_code
                    if ($request->has('tour_code') && $request->tour_code != '') {
                        $query->where('tour_code', 'like', '%' . $request->tour_code . '%');
                    }
                    
                    // Filter by phone_number
                    if ($request->has('phone_number') && $request->phone_number != '') {
                        $query->where('phone_number', 'like', '%' . $request->phone_number . '%');
                    }
                    
                    // Filter by status
                    if ($request->has('status') && $request->status != '') {
                        $query->where('status', $request->status);
                    }
                    
                    // Filter by booking_id
                    if ($request->has('booking_id') && $request->booking_id != '') {
                        $query->where('booking_id', $request->booking_id);
                    }
                    
                    // Filter by date range
                    if ($request->has('date_from') && $request->date_from != '') {
                        $query->whereDate('created_at', '>=', $request->date_from);
                    }
                    
                    if ($request->has('date_to') && $request->date_to != '') {
                        $query->whereDate('created_at', '<=', $request->date_to);
                    }
                })
                ->rawColumns(['tour_code', 'phone_number', 'status', 'booking_id', 'notified_at', 'actions'])
                ->toJson();
        }

        // Get filter options
        $bookings = Booking::select('id', 'tour_code')
            ->whereNotNull('tour_code')
            ->orderBy('id', 'desc')
            ->limit(100)
            ->get();

        return view('admin.tour-notifications.index', compact('bookings'));
    }

    /**
     * Get notification details for modal
     */
    public function show($id)
    {
        $notification = TourNotification::with(['booking'])->findOrFail($id);
        
        // Format dates for display
        $notification->formatted_created_at = $notification->created_at ? $notification->created_at->format('d M Y, h:i A') : '-';
        $notification->formatted_notified_at = $notification->notified_at ? $notification->notified_at->format('d M Y, h:i A') : null;
        
        return response()->json([
            'success' => true,
            'notification' => $notification
        ]);
    }
}

