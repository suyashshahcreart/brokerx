<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QRAnalytics;
use App\Models\Booking;
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
            $query = QRAnalytics::with(['booking', 'user'])
                ->select('qr_analytics.*');

            // Filter by tour_code
            if ($request->has('tour_code') && $request->tour_code != '') {
                $query->where('tour_code', 'like', '%' . $request->tour_code . '%');
            }

            // Filter by booking_id
            if ($request->has('booking_id') && $request->booking_id != '') {
                $query->where('booking_id', $request->booking_id);
            }

            // Filter by country
            if ($request->has('country') && $request->country != '') {
                $query->where('country', 'like', '%' . $request->country . '%');
            }

            // Filter by city
            if ($request->has('city') && $request->city != '') {
                $query->where('city', 'like', '%' . $request->city . '%');
            }

            // Filter by device_type
            if ($request->has('device_type') && $request->device_type != '') {
                $query->where('device_type', $request->device_type);
            }

            // Filter by location_source
            if ($request->has('location_source') && $request->location_source != '') {
                $query->where('location_source', $request->location_source);
            }

            // Filter by tracking_status
            if ($request->has('tracking_status') && $request->tracking_status != '') {
                $query->where('tracking_status', $request->tracking_status);
            }

            // Filter by page_type
            if ($request->has('page_type') && $request->page_type != '') {
                $query->where('page_type', $request->page_type);
            }

            // Filter by date range
            if ($request->has('date_from') && $request->date_from != '') {
                $query->whereDate('scan_date', '>=', $request->date_from);
            }

            if ($request->has('date_to') && $request->date_to != '') {
                $query->whereDate('scan_date', '<=', $request->date_to);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('tour_code', function (QRAnalytics $analytics) {
                    return $analytics->tour_code ? '<span class="fw-semibold">' . htmlspecialchars($analytics->tour_code) . '</span>' : '<span class="text-muted">-</span>';
                })
                ->editColumn('booking_id', function (QRAnalytics $analytics) {
                    if ($analytics->booking) {
                        return '<a href="' . route('admin.bookings.show', $analytics->booking_id) . '" class="text-primary" target="_blank">Booking #' . $analytics->booking_id . '</a>';
                    }
                    return '<span class="text-muted">-</span>';
                })
                ->editColumn('user_ip', function (QRAnalytics $analytics) {
                    return '<span class="text-muted small">' . htmlspecialchars($analytics->user_ip ?? '-') . '</span>';
                })
                ->editColumn('location', function (QRAnalytics $analytics) {
                    $location = [];
                    if ($analytics->city)
                        $location[] = $analytics->city;
                    if ($analytics->region)
                        $location[] = $analytics->region;
                    if ($analytics->country)
                        $location[] = $analytics->country;
                    return !empty($location) ? implode(', ', $location) : '<span class="text-muted">-</span>';
                })
                ->editColumn('device_info', function (QRAnalytics $analytics) {
                    $device = [];
                    if ($analytics->device_type)
                        $device[] = ucfirst($analytics->device_type);
                    if ($analytics->browser_name)
                        $device[] = $analytics->browser_name;
                    if ($analytics->os_name)
                        $device[] = $analytics->os_name;
                    return !empty($device) ? implode(' / ', $device) : '<span class="text-muted">-</span>';
                })
                ->editColumn('location_source', function (QRAnalytics $analytics) {
                    if (!$analytics->location_source)
                        return '<span class="text-muted">-</span>';
                    $badges = [
                        'GPS' => 'bg-success',
                        'IP' => 'bg-info',
                        'UNAVAILABLE' => 'bg-secondary'
                    ];
                    $class = $badges[$analytics->location_source] ?? 'bg-secondary';
                    return '<span class="badge ' . $class . '">' . $analytics->location_source . '</span>';
                })
                ->editColumn('tracking_status', function (QRAnalytics $analytics) {
                    $badges = [
                        'success' => 'bg-success',
                        'error' => 'bg-danger',
                        'invalid_tour_code' => 'bg-warning'
                    ];
                    $class = $badges[$analytics->tracking_status] ?? 'bg-secondary';
                    return '<span class="badge ' . $class . ' text-uppercase">' . $analytics->tracking_status . '</span>';
                })
                ->editColumn('scan_date', function (QRAnalytics $analytics) {
                    return $analytics->scan_date ? $analytics->scan_date->format('d M Y H:i') : '-';
                })
                ->editColumn('created_at', function (QRAnalytics $analytics) {
                    return $analytics->created_at ? $analytics->created_at->format('d M Y H:i') : '-';
                })
                ->addColumn('actions', function (QRAnalytics $analytics) {
                    return '<button type="button" class="btn btn-soft-primary btn-sm view-analytics" 
                        data-id="' . $analytics->id . '" 
                        data-bs-toggle="tooltip" data-bs-placement="top" title="View Full Scan Details">
                        <iconify-icon icon="solar:eye-broken" class="align-middle fs-18"></iconify-icon>
                    </button>';
                })
                ->rawColumns(['tour_code', 'booking_id', 'user_ip', 'location', 'device_info', 'location_source', 'tracking_status', 'actions'])
                ->toJson();
        }

        // Get filter options
        $bookings = Booking::select('id', 'tour_code')
            ->whereNotNull('tour_code')
            ->orderBy('id', 'desc')
            ->limit(100)
            ->get();

        // Get unique values for filters
        $countries = QRAnalytics::select('country')
            ->whereNotNull('country')
            ->distinct()
            ->orderBy('country')
            ->pluck('country')
            ->filter()
            ->unique();

        $cities = QRAnalytics::select('city')
            ->whereNotNull('city')
            ->distinct()
            ->orderBy('city')
            ->pluck('city')
            ->filter()
            ->unique()
            ->take(100);

        return view('admin.qr-analytics.index', compact('bookings', 'countries', 'cities'));
    }

    /**
     * Get analytics details for modal
     */
    public function show($id)
    {
        $analytics = QRAnalytics::with(['booking', 'user'])->findOrFail($id);

        // Format dates for display
        $analytics->formatted_scan_date = $analytics->scan_date ? $analytics->scan_date->format('d M Y, h:i A') : '-';
        $analytics->formatted_created_at = $analytics->created_at ? $analytics->created_at->format('d M Y, h:i A') : '-';

        return response()->json([
            'success' => true,
            'analytics' => $analytics
        ]);
    }
}

