<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QRAnalytics;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class QRAnalyticsController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:qr_analytics_view')->only(['index', 'show']);
    }

    /**
     * Display a listing of QR analytics
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = QRAnalytics::query()
                ->select([
                    'qr_analytics.id',
                    'qr_analytics.tour_code',
                    'qr_analytics.booking_id',
                    'qr_analytics.city',
                    'qr_analytics.region',
                    'qr_analytics.country',
                    'qr_analytics.device_type',
                    'qr_analytics.browser_name',
                    'qr_analytics.os_name',
                    'qr_analytics.location_source',
                    'qr_analytics.tracking_status',
                    'qr_analytics.scan_date',
                ]);

            if ($request->filled('tour_code')) {
                $query->where('qr_analytics.tour_code', 'like', '%' . $request->tour_code . '%');
            }

            if ($request->filled('booking_id')) {
                $query->where('qr_analytics.booking_id', $request->booking_id);
            }

            if ($request->filled('country')) {
                $query->where('qr_analytics.country', 'like', '%' . $request->country . '%');
            }

            if ($request->filled('city')) {
                $query->where('qr_analytics.city', 'like', '%' . $request->city . '%');
            }

            if ($request->filled('device_type')) {
                $query->where('qr_analytics.device_type', $request->device_type);
            }

            if ($request->filled('location_source')) {
                $query->where('qr_analytics.location_source', $request->location_source);
            }

            if ($request->filled('tracking_status')) {
                $query->where('qr_analytics.tracking_status', $request->tracking_status);
            }

            if ($request->filled('page_type')) {
                $query->where('qr_analytics.page_type', $request->page_type);
            }

            if ($request->filled('date_from') && $request->filled('date_to')) {
                $query->whereBetween('qr_analytics.scan_date', [
                    $request->date_from . ' 00:00:00',
                    $request->date_to . ' 23:59:59',
                ]);
            } elseif ($request->filled('date_from')) {
                $query->whereDate('qr_analytics.scan_date', '>=', $request->date_from);
            } elseif ($request->filled('date_to')) {
                $query->whereDate('qr_analytics.scan_date', '<=', $request->date_to);
            }

            $query->orderByDesc('qr_analytics.scan_date')->orderByDesc('qr_analytics.id');

            return DataTables::of($query)
                ->filterColumn('location', function ($query, $keyword) {
                    $query->where(function ($subQuery) use ($keyword) {
                        $subQuery
                            ->where('qr_analytics.city', 'like', "%{$keyword}%")
                            ->orWhere('qr_analytics.region', 'like', "%{$keyword}%")
                            ->orWhere('qr_analytics.country', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('device_info', function ($query, $keyword) {
                    $query->where(function ($subQuery) use ($keyword) {
                        $subQuery
                            ->where('qr_analytics.device_type', 'like', "%{$keyword}%")
                            ->orWhere('qr_analytics.browser_name', 'like', "%{$keyword}%")
                            ->orWhere('qr_analytics.os_name', 'like', "%{$keyword}%");
                    });
                })
                ->editColumn('tour_code', function (QRAnalytics $analytics) {
                    return $analytics->tour_code
                        ? '<span class="fw-semibold">' . e($analytics->tour_code) . '</span>'
                        : '<span class="text-muted">-</span>';
                })
                ->editColumn('booking_id', function (QRAnalytics $analytics) {
                    if ($analytics->booking_id) {
                        return '<a href="' . route('admin.bookings.show', $analytics->booking_id) . '" class="text-primary" target="_blank">Booking #' . $analytics->booking_id . '</a>';
                    }

                    return '<span class="text-muted">-</span>';
                })
                ->addColumn('location', function (QRAnalytics $analytics) {
                    $location = array_filter([
                        $analytics->city,
                        $analytics->region,
                        $analytics->country,
                    ]);

                    return $location !== []
                        ? e(implode(', ', $location))
                        : '<span class="text-muted">-</span>';
                })
                ->addColumn('device_info', function (QRAnalytics $analytics) {
                    $device = array_filter([
                        $analytics->device_type ? ucfirst($analytics->device_type) : null,
                        $analytics->browser_name,
                        $analytics->os_name,
                    ]);

                    return $device !== []
                        ? e(implode(' / ', $device))
                        : '<span class="text-muted">-</span>';
                })
                ->editColumn('location_source', function (QRAnalytics $analytics) {
                    if (! $analytics->location_source) {
                        return '<span class="text-muted">-</span>';
                    }

                    $badges = [
                        'GPS' => 'bg-success',
                        'IP' => 'bg-info',
                        'UNAVAILABLE' => 'bg-secondary',
                    ];
                    $class = $badges[$analytics->location_source] ?? 'bg-secondary';

                    return '<span class="badge ' . $class . '">' . e($analytics->location_source) . '</span>';
                })
                ->editColumn('tracking_status', function (QRAnalytics $analytics) {
                    $badges = [
                        'success' => 'bg-success',
                        'error' => 'bg-danger',
                        'invalid_tour_code' => 'bg-warning',
                    ];
                    $class = $badges[$analytics->tracking_status] ?? 'bg-secondary';

                    return '<span class="badge ' . $class . ' text-uppercase">' . e($analytics->tracking_status) . '</span>';
                })
                ->editColumn('scan_date', fn (QRAnalytics $analytics) => $analytics->scan_date ? $analytics->scan_date->format('d M Y H:i') : '-')
                ->addColumn('actions', function (QRAnalytics $analytics) {
                    return '<button type="button" class="btn btn-soft-primary btn-sm view-analytics" data-id="' . $analytics->id . '" title="View Full Scan Details">'
                        . '<iconify-icon icon="solar:eye-broken" class="align-middle fs-18"></iconify-icon>'
                        . '</button>';
                })
                ->rawColumns(['tour_code', 'booking_id', 'location', 'device_info', 'location_source', 'tracking_status', 'actions'])
                ->only([
                    'id',
                    'tour_code',
                    'booking_id',
                    'location',
                    'device_info',
                    'location_source',
                    'tracking_status',
                    'scan_date',
                    'actions',
                ])
                ->make(true);
        }

        return view('admin.qr-analytics.index');
    }

    /**
     * Get analytics details for modal
     */
    public function show($id)
    {
        $analytics = QRAnalytics::query()
            ->select([
                'id',
                'tour_code',
                'booking_id',
                'page_url',
                'page_type',
                'user_ip',
                'user_agent',
                'browser_name',
                'browser_version',
                'os_name',
                'os_version',
                'device_type',
                'screen_resolution',
                'language',
                'country',
                'city',
                'region',
                'full_address',
                'pincode',
                'latitude',
                'longitude',
                'timezone',
                'location_source',
                'referrer',
                'utm_source',
                'utm_medium',
                'utm_campaign',
                'utm_term',
                'utm_content',
                'session_id',
                'scan_date',
                'tracking_status',
                'error_message',
                'load_time',
                'metadata',
                'created_at',
            ])
            ->with(['booking:id'])
            ->findOrFail($id);

        $analytics->formatted_scan_date = $analytics->scan_date ? $analytics->scan_date->format('d M Y, h:i A') : '-';
        $analytics->formatted_created_at = $analytics->created_at ? $analytics->created_at->format('d M Y, h:i A') : '-';

        return response()->json([
            'success' => true,
            'analytics' => $analytics,
        ]);
    }
}
