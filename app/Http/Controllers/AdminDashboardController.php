<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Booking;
use App\Models\Tour;
use App\Models\PaymentHistory;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $title = 'Dashboard';
        
        // Redirect photographers to their specific dashboard
        if (auth()->user()->hasRole('photographer')) {
            $photographers = User::whereHas('roles', function($q) {
                $q->where('name', 'photographer');
            })->orderBy('firstname')->get();

            $statuses = [
                'pending', 'schedul_accepted', 'confirmed', 'schedul_assign', 
                'reschedul_assign', 'schedul_pending', 'schedul_inprogress', 'schedul_completed'
            ];

            return view('admin.photographer.index', [
                'title' => $title,
                'photographers' => $photographers,
                'statuses' => $statuses
            ]);
        }

        // Admin Dashboard - For admin and other roles
        // Get statistics
        $totalProperties = Booking::count();
        $totalCustomers = User::whereHas('roles', function($q) {
            $q->where('name', 'customer');
        })->count();
        $liveTours = Booking::whereNotNull('tour_final_link')->count();
        $totalRevenue = PaymentHistory::where('status', 'completed')->sum('amount');

        // Get current date info
        $currentMonth = now()->month;
        $currentYear = now()->year;
        $daysInMonth = now()->daysInMonth;
        $startOfWeek = now()->startOfWeek();

        // Weekly data (M-S)
        $weekLabels = ['M', 'T', 'W', 'T', 'F', 'S', 'S'];
        $weeklyProperties = [];
        $weeklyCustomers = [];
        $weeklyTours = [];
        $weeklyRevenue = [];
        
        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i)->format('Y-m-d');
            $weeklyProperties[] = Booking::whereDate('created_at', $date)->count();
            $weeklyCustomers[] = User::whereHas('roles', function($q) {
                $q->where('name', 'customer');
            })->whereDate('created_at', $date)->count();
            $weeklyTours[] = Booking::whereNotNull('tour_final_link')
                ->whereDate('created_at', $date)
                ->count();
            $weeklyRevenue[] = PaymentHistory::where('status', 'completed')
                ->whereDate('created_at', $date)
                ->sum('amount');
        }

        // Monthly data (daily breakdown)
        $monthlyBookings = [];
        $monthlyCustomers = [];
        
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = sprintf('%04d-%02d-%02d', $currentYear, $currentMonth, $day);
            $monthlyBookings[] = Booking::whereDate('created_at', $date)->count();
            $monthlyCustomers[] = User::whereHas('roles', function($q) {
                $q->where('name', 'customer');
            })->whereDate('created_at', $date)->count();
        }

        // Monthly earnings
        $monthlyEarning = PaymentHistory::where('status', 'completed')
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->sum('amount');

        // Latest 10 bookings (all time)
        $latestTransactions = Booking::with(['user'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.dashboard', [
            'title' => $title,
            'totalProperties' => $totalProperties,
            'totalCustomers' => $totalCustomers,
            'liveTours' => $liveTours,
            'totalRevenue' => $totalRevenue,
            'weeklyLabels' => $weekLabels,
            'weeklyProperties' => $weeklyProperties,
            'weeklyCustomers' => $weeklyCustomers,
            'weeklyTours' => $weeklyTours,
            'weeklyRevenue' => $weeklyRevenue,
            'monthlyBookings' => $monthlyBookings,
            'monthlyCustomers' => $monthlyCustomers,
            'monthlyEarning' => $monthlyEarning,
            'daysInMonth' => $daysInMonth,
            'latestTransactions' => $latestTransactions
        ]);
    }
}

