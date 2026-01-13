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
    public function SalesAnalyticChartData(Request $request)
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
}
