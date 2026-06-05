<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TourNotification;
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
            $query = TourNotification::query()
                ->select([
                    'tour_notifications.id',
                    'tour_notifications.tour_code',
                    'tour_notifications.phone_number',
                    'tour_notifications.booking_id',
                    'tour_notifications.status',
                    'tour_notifications.created_at',
                    'tour_notifications.notified_at',
                ]);

            if ($request->filled('tour_code')) {
                $query->where('tour_notifications.tour_code', 'like', '%' . $request->tour_code . '%');
            }

            if ($request->filled('phone_number')) {
                $query->where('tour_notifications.phone_number', 'like', '%' . $request->phone_number . '%');
            }

            if ($request->filled('status')) {
                $query->where('tour_notifications.status', $request->status);
            }

            if ($request->filled('booking_id')) {
                $query->where('tour_notifications.booking_id', $request->booking_id);
            }

            if ($request->filled('date_from') && $request->filled('date_to')) {
                $query->whereBetween('tour_notifications.created_at', [
                    $request->date_from . ' 00:00:00',
                    $request->date_to . ' 23:59:59',
                ]);
            } elseif ($request->filled('date_from')) {
                $query->whereDate('tour_notifications.created_at', '>=', $request->date_from);
            } elseif ($request->filled('date_to')) {
                $query->whereDate('tour_notifications.created_at', '<=', $request->date_to);
            }

            return DataTables::of($query)
                ->editColumn('tour_code', function (TourNotification $notification) {
                    return '<span class="fw-semibold">' . e($notification->tour_code) . '</span>';
                })
                ->editColumn('phone_number', function (TourNotification $notification) {
                    return '<span class="text-muted">' . e($notification->phone_number) . '</span>';
                })
                ->editColumn('status', function (TourNotification $notification) {
                    $badges = [
                        'pending' => 'bg-warning',
                        'notified' => 'bg-success',
                        'failed' => 'bg-danger',
                    ];
                    $class = $badges[$notification->status] ?? 'bg-secondary';

                    return '<span class="badge ' . $class . ' text-uppercase">' . e($notification->status) . '</span>';
                })
                ->editColumn('booking_id', function (TourNotification $notification) {
                    if ($notification->booking_id) {
                        return '<a href="' . route('admin.bookings.show', $notification->booking_id) . '" class="text-primary" target="_blank">Booking #' . $notification->booking_id . '</a>';
                    }

                    return '<span class="text-muted">-</span>';
                })
                ->editColumn('created_at', fn (TourNotification $notification) => $notification->created_at ? $notification->created_at->format('d M Y H:i') : '-')
                ->editColumn('notified_at', function (TourNotification $notification) {
                    return $notification->notified_at
                        ? $notification->notified_at->format('d M Y H:i')
                        : '<span class="text-muted">-</span>';
                })
                ->addColumn('actions', function (TourNotification $notification) {
                    return '<button type="button" class="btn btn-soft-primary btn-sm view-notification" data-id="' . $notification->id . '" title="View Notification Details">'
                        . '<iconify-icon icon="solar:eye-broken" class="align-middle fs-18"></iconify-icon>'
                        . '</button>';
                })
                ->rawColumns(['tour_code', 'phone_number', 'status', 'booking_id', 'notified_at', 'actions'])
                ->only([
                    'id',
                    'tour_code',
                    'phone_number',
                    'booking_id',
                    'status',
                    'created_at',
                    'notified_at',
                    'actions',
                ])
                ->make(true);
        }

        return view('admin.tour-notifications.index');
    }

    /**
     * Get notification details for modal
     */
    public function show($id)
    {
        $notification = TourNotification::query()
            ->select([
                'id',
                'tour_code',
                'booking_id',
                'phone_number',
                'status',
                'notified_at',
                'ip_address',
                'user_agent',
                'metadata',
                'created_at',
            ])
            ->with(['booking:id'])
            ->findOrFail($id);

        $notification->formatted_created_at = $notification->created_at ? $notification->created_at->format('d M Y, h:i A') : '-';
        $notification->formatted_notified_at = $notification->notified_at ? $notification->notified_at->format('d M Y, h:i A') : null;

        return response()->json([
            'success' => true,
            'notification' => $notification,
        ]);
    }
}
