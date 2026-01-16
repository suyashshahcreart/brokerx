<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\User; // Assuming you have a Customer model
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AdminDashboardController extends Controller
{
    /**
     * Get booking and customer data for dashboard charts.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
public function BookingsAnalyticChartData(Request $request)
    {
        $type = $request->get('type', 'week');

        if (!in_array($type, ['week', 'month', 'year'])) {
            return response()->json(['success' => false, 'message' => 'Invalid type'], 400);
        }

        $data = match ($type) {
            'week' => $this->weekData(),
            'month' => $this->monthData(),
            'year' => $this->yearData(),
        };

        return response()->json(['success'=>true,'message'=>'Data fetched successfully','date'=>$data]);
    }

    /**
     * Get sales data for dashboard sales chart.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function SalesAnalyticChartData(Request $request)
    {
        $type = $request->get('type', 'week');

        if (!in_array($type, ['week', 'month', 'year'])) {
            return response()->json(['success' => false, 'message' => 'Invalid type'], 400);
        }

        $data = match ($type) {
            'week' => $this->weekSalesData(),
            'month' => $this->monthSalesData(),
            'year' => $this->yearSalesData(),
        };

        return response()->json(['success'=>true,'message'=>'Data fetched successfully','date'=>$data]);
    }

    /**
     * WEEK → group by day
     */
    private function weekData()
    {
        $start = now()->startOfWeek();
        $end = now()->endOfWeek();

        $labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

        $customers = User::whereBetween('created_at', [$start, $end])
            ->selectRaw('DAYOFWEEK(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $bookings = Booking::whereBetween('created_at', [$start, $end])
            ->selectRaw('DAYOFWEEK(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        return $this->normalizeWeek($labels, $customers, $bookings);
    }

    /**
     * MONTH → group by week
     */
    private function monthData()
    {
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();

        $labels = ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5'];

        $customers = User::whereBetween('created_at', [$start, $end])
            ->selectRaw('
                WEEK(created_at, 1) - 
                WEEK(DATE_SUB(created_at, INTERVAL DAYOFMONTH(created_at)-1 DAY), 1) + 1 as week,
                COUNT(*) as total
            ')
            ->groupBy('week')
            ->pluck('total', 'week');

        $bookings = Booking::whereBetween('created_at', [$start, $end])
            ->selectRaw('
                WEEK(created_at, 1) - 
                WEEK(DATE_SUB(created_at, INTERVAL DAYOFMONTH(created_at)-1 DAY), 1) + 1 as week,
                COUNT(*) as total
            ')
            ->groupBy('week')
            ->pluck('total', 'week');

        return $this->normalizeRange($labels, $customers, $bookings);
    }

    /**
     * YEAR → group by month
     */
    private function yearData()
    {
        $year = now()->year;

        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        $customers = User::whereYear('created_at', $year)
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->groupBy('month')
            ->pluck('total', 'month');

        $bookings = Booking::whereYear('created_at', $year)
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->groupBy('month')
            ->pluck('total', 'month');

        return $this->normalizeRange($labels, $customers, $bookings, 12);
    }

    /**
     * Helpers
     */

    private function normalizeWeek($labels, $customers, $bookings)
    {
        $customerData = [];
        $bookingData = [];

        // MySQL: Sunday=1 ... Saturday=7
        // We want Monday start
        $order = [2, 3, 4, 5, 6, 7, 1];

        foreach ($order as $day) {
            $customerData[] = $customers[$day] ?? 0;
            $bookingData[] = $bookings[$day] ?? 0;
        }

        return [
            'categories' => $labels,
            'series' => [
                ['name' => 'Customers', 'data' => $customerData],
                ['name' => 'Bookings', 'data' => $bookingData],
            ]
        ];
    }

    private function normalizeRange($labels, $customers, $bookings, $limit = 5)
    {
        $customerData = [];
        $bookingData = [];

        for ($i = 1; $i <= $limit; $i++) {
            $customerData[] = $customers[$i] ?? 0;
            $bookingData[] = $bookings[$i] ?? 0;
        }

        return [
            'categories' => $labels,
            'series' => [
                ['name' => 'Customers', 'data' => $customerData],
                ['name' => 'Bookings', 'data' => $bookingData],
            ]
        ];
    }

    /**
     * WEEK → last 6 days + today for sales (with previous week comparison)
     */
    private function weekSalesData()
    {
        // Current week: last 6 days + today
        $currentStart = now()->subDays(6)->startOfDay();
        $currentEnd = now()->endOfDay();

        // Previous week: 13 days ago to 7 days ago
        $previousStart = now()->subDays(13)->startOfDay();
        $previousEnd = now()->subDays(7)->endOfDay();

        // Generate labels for the last 7 days
        $labels = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('D');
        }

        // Get current week data
        $currentSales = Booking::whereBetween('created_at', [$currentStart, $currentEnd])
            ->selectRaw('DATE(created_at) as date, SUM(COALESCE(price, 0)) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        // Get previous week data
        $previousSales = Booking::whereBetween('created_at', [$previousStart, $previousEnd])
            ->selectRaw('DATE(created_at) as date, SUM(COALESCE(price, 0)) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        return $this->normalizeSalesWeekComparison($labels, $currentSales, $previousSales);
    }

    /**
     * MONTH → group by week for sales (with previous month comparison)
     */
    private function monthSalesData()
    {
        $currentStart = now()->startOfMonth();
        $currentEnd = now()->endOfMonth();

        $previousStart = now()->subMonth()->startOfMonth();
        $previousEnd = now()->subMonth()->endOfMonth();

        $labels = ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5'];

        // Current month sales
        $currentSales = Booking::whereBetween('created_at', [$currentStart, $currentEnd])
            ->selectRaw('
                WEEK(created_at, 1) - 
                WEEK(DATE_SUB(created_at, INTERVAL DAYOFMONTH(created_at)-1 DAY), 1) + 1 as week,
                SUM(COALESCE(price, 0)) as total
            ')
            ->groupBy('week')
            ->pluck('total', 'week');

        // Previous month sales
        $previousSales = Booking::whereBetween('created_at', [$previousStart, $previousEnd])
            ->selectRaw('
                WEEK(created_at, 1) - 
                WEEK(DATE_SUB(created_at, INTERVAL DAYOFMONTH(created_at)-1 DAY), 1) + 1 as week,
                SUM(COALESCE(price, 0)) as total
            ')
            ->groupBy('week')
            ->pluck('total', 'week');

        return $this->normalizeSalesMonthComparison($labels, $currentSales, $previousSales);
    }

    /**
     * YEAR → group by month for sales (with previous year comparison)
     */
    private function yearSalesData()
    {
        $currentYear = now()->year;
        $previousYear = $currentYear - 1;

        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        // Current year sales
        $currentSales = Booking::whereYear('created_at', $currentYear)
            ->selectRaw('MONTH(created_at) as month, SUM(COALESCE(price, 0)) as total')
            ->groupBy('month')
            ->pluck('total', 'month');

        // Previous year sales
        $previousSales = Booking::whereYear('created_at', $previousYear)
            ->selectRaw('MONTH(created_at) as month, SUM(COALESCE(price, 0)) as total')
            ->groupBy('month')
            ->pluck('total', 'month');

        return $this->normalizeSalesYearComparison($labels, $currentSales, $previousSales);
    }

    /**
     * Normalize sales data for week comparison
     */
    private function normalizeSalesWeekComparison($labels, $currentSales, $previousSales)
    {
        $currentData = [];
        $previousData = [];

        // Get dates for the last 7 days
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $currentData[] = ($currentSales[$date] ?? 0);
            
            // Get previous week date
            $prevDate = now()->subDays($i + 7)->toDateString();
            $previousData[] = ($previousSales[$prevDate] ?? 0);
        }

        return [
            'categories' => $labels,
            'series' => [
                ['name' => 'This Week', 'data' => $currentData],
                ['name' => 'Last Week', 'data' => $previousData],
            ]
        ];
    }

    /**
     * Normalize sales data for month comparison
     */
    private function normalizeSalesMonthComparison($labels, $currentSales, $previousSales)
    {
        $currentData = [];
        $previousData = [];

        for ($i = 1; $i <= 5; $i++) {
            $currentData[] = ($currentSales[$i] ?? 0);
            $previousData[] = ($previousSales[$i] ?? 0);
        }

        return [
            'categories' => $labels,
            'series' => [
                ['name' => 'This Month', 'data' => $currentData],
                ['name' => 'Last Month', 'data' => $previousData],
            ]
        ];
    }

    /**
     * Normalize sales data for year comparison
     */
    private function normalizeSalesYearComparison($labels, $currentSales, $previousSales)
    {
        $currentData = [];
        $previousData = [];

        for ($i = 1; $i <= 12; $i++) {
            $currentData[] = ($currentSales[$i] ?? 0);
            $previousData[] = ($previousSales[$i] ?? 0);
        }

        return [
            'categories' => $labels,
            'series' => [
                ['name' => 'This Year', 'data' => $currentData],
                ['name' => 'Last Year', 'data' => $previousData],
            ]
        ];
    }
}
