<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Tour;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        $totalRevenue = Booking::sum('cashfree_payment_amount');
        if ($totalRevenue === 0) {
            $totalRevenue = Booking::sum('price');
        }

        $totalBookings = Booking::count();
        $totalCustomers = User::count();
        $totalTours = Tour::count();

        $recentSales = Booking::select(['id', 'user_id', 'cashfree_payment_amount', 'price', 'status', 'created_at'])
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.reports.index', [
            'totalRevenue' => $totalRevenue,
            'totalBookings' => $totalBookings,
            'totalCustomers' => $totalCustomers,
            'totalTours' => $totalTours,
            'recentSales' => $recentSales,
        ]);
    }

    public function sales(Request $request)
    {
        $from = Carbon::parse($request->get('from', now()->subDays(6)->toDateString()))->startOfDay();
        $to = Carbon::parse($request->get('to', now()->toDateString()))->endOfDay();

        $dailySales = Booking::selectRaw('DATE(created_at) as sale_date, SUM(COALESCE(cashfree_payment_amount, price, 0)) as total_amount, COUNT(*) as booking_count')
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('sale_date')
            ->orderBy('sale_date')
            ->get();

        $totalSales = (float) $dailySales->sum('total_amount');
        $totalBookings = (int) $dailySales->sum('booking_count');

        return view('admin.reports.sales', [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'dailySales' => $dailySales,
            'totalSales' => $totalSales,
            'totalBookings' => $totalBookings,
        ]);
    }

    public function bookings()
    {
        $statusBreakdown = Booking::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->orderByDesc('total')
            ->get();

        $recentBookings = Booking::with('user')
            ->latest()
            ->limit(20)
            ->get();

        return view('admin.reports.bookings', [
            'statusBreakdown' => $statusBreakdown,
            'recentBookings' => $recentBookings,
        ]);
    }

    public function customers()
    {
        $topCustomers = Booking::selectRaw('user_id, COUNT(*) as bookings, SUM(COALESCE(cashfree_payment_amount, price, 0)) as revenue')
            ->with('user')
            ->groupBy('user_id')
            ->orderByDesc('bookings')
            ->limit(15)
            ->get();

        $totalCustomers = User::count();

        return view('admin.reports.customers', [
            'topCustomers' => $topCustomers,
            'totalCustomers' => $totalCustomers,
        ]);
    }
}
