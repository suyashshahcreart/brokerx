<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use App\Models\Tour;
use App\Models\Portfolio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboards.analytics');
    }

    public function getAnalytics(Request $request)
    {
        $period = $request->get('period', 'month'); // day, week, month, year
        
        // Get date range based on period
        $dateRange = $this->getDateRange($period);
        
        // Statistics
        $stats = [
            'total_bookings' => Booking::whereBetween('created_at', $dateRange)->count(),
            'total_revenue' => Booking::whereBetween('created_at', $dateRange)
                ->where('payment_status', 'paid')
                ->sum('price'),
            'total_users' => User::whereBetween('created_at', $dateRange)->count(),
            'total_tours' => Tour::whereBetween('created_at', $dateRange)->count(),
            'pending_bookings' => Booking::whereBetween('created_at', $dateRange)
                ->where('status', 'pending')
                ->count(),
            'completed_bookings' => Booking::whereBetween('created_at', $dateRange)
                ->where('status', 'completed')
                ->count(),
        ];

        // Booking trends by date
        $bookingTrends = Booking::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->whereBetween('created_at', $dateRange)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Revenue trends
        $revenueTrends = Booking::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(price) as revenue')
            )
            ->whereBetween('created_at', $dateRange)
            ->where('payment_status', 'paid')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Bookings by status
        $bookingsByStatus = Booking::select('status', DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', $dateRange)
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        // Top properties by bookings
        $topProperties = Booking::select('property_type_id', DB::raw('COUNT(*) as count'))
            ->with('propertyType')
            ->whereBetween('created_at', $dateRange)
            ->groupBy('property_type_id')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        // Recent bookings
        $recentBookings = Booking::with(['user', 'propertyType', 'city'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // User registrations trend
        $userTrends = User::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->whereBetween('created_at', $dateRange)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'stats' => $stats,
            'bookingTrends' => $bookingTrends,
            'revenueTrends' => $revenueTrends,
            'bookingsByStatus' => $bookingsByStatus,
            'topProperties' => $topProperties,
            'recentBookings' => $recentBookings,
            'userTrends' => $userTrends,
        ]);
    }

    public function getRevenueStats(Request $request)
    {
        $period = $request->get('period', 'month');
        $dateRange = $this->getDateRange($period);

        $revenueData = Booking::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(price) as total'),
                DB::raw('COUNT(*) as bookings')
            )
            ->whereBetween('created_at', $dateRange)
            ->where('payment_status', 'paid')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $totalRevenue = $revenueData->sum('total');
        $averageBookingValue = $revenueData->sum('bookings') > 0 
            ? $totalRevenue / $revenueData->sum('bookings') 
            : 0;

        return response()->json([
            'revenueData' => $revenueData,
            'totalRevenue' => $totalRevenue,
            'averageBookingValue' => $averageBookingValue,
        ]);
    }

    public function getUserStats(Request $request)
    {
        $period = $request->get('period', 'month');
        $dateRange = $this->getDateRange($period);

        $userStats = [
            'total_users' => User::whereBetween('created_at', $dateRange)->count(),
            'active_users' => User::whereBetween('last_login_at', $dateRange)->count(),
            'brokers' => User::role('broker')->whereBetween('created_at', $dateRange)->count(),
            'customers' => User::role('customer')->whereBetween('created_at', $dateRange)->count(),
        ];

        $userGrowth = User::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->whereBetween('created_at', $dateRange)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'userStats' => $userStats,
            'userGrowth' => $userGrowth,
        ]);
    }

    public function getBookingStats(Request $request)
    {
        $period = $request->get('period', 'month');
        $dateRange = $this->getDateRange($period);

        $bookingStats = [
            'total' => Booking::whereBetween('created_at', $dateRange)->count(),
            'pending' => Booking::whereBetween('created_at', $dateRange)->where('status', 'pending')->count(),
            'confirmed' => Booking::whereBetween('created_at', $dateRange)->where('status', 'confirmed')->count(),
            'completed' => Booking::whereBetween('created_at', $dateRange)->where('status', 'completed')->count(),
            'cancelled' => Booking::whereBetween('created_at', $dateRange)->where('status', 'cancelled')->count(),
        ];

        $statusDistribution = Booking::select('status', DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', $dateRange)
            ->groupBy('status')
            ->get();

        return response()->json([
            'bookingStats' => $bookingStats,
            'statusDistribution' => $statusDistribution,
        ]);
    }

    public function getPropertyStats(Request $request)
    {
        $period = $request->get('period', 'month');
        $dateRange = $this->getDateRange($period);

        $propertyTypeStats = Booking::select(
                'property_type_id',
                DB::raw('COUNT(*) as bookings'),
                DB::raw('SUM(price) as revenue')
            )
            ->with('propertyType')
            ->whereBetween('created_at', $dateRange)
            ->groupBy('property_type_id')
            ->orderByDesc('bookings')
            ->get();

        $cityStats = Booking::select(
                'city_id',
                DB::raw('COUNT(*) as bookings')
            )
            ->with('city')
            ->whereBetween('created_at', $dateRange)
            ->groupBy('city_id')
            ->orderByDesc('bookings')
            ->limit(10)
            ->get();

        return response()->json([
            'propertyTypeStats' => $propertyTypeStats,
            'cityStats' => $cityStats,
        ]);
    }

    private function getDateRange($period)
    {
        $now = Carbon::now();
        
        switch ($period) {
            case 'today':
                return [$now->startOfDay(), $now->endOfDay()];
            case 'week':
                return [$now->startOfWeek(), $now->endOfWeek()];
            case 'month':
                return [$now->startOfMonth(), $now->endOfMonth()];
            case 'year':
                return [$now->startOfYear(), $now->endOfYear()];
            case 'last_7_days':
                return [$now->subDays(7), Carbon::now()];
            case 'last_30_days':
                return [$now->subDays(30), Carbon::now()];
            case 'last_90_days':
                return [$now->subDays(90), Carbon::now()];
            default:
                return [$now->startOfMonth(), $now->endOfMonth()];
        }
    }
}
