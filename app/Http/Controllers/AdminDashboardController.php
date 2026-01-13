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
        
        // Show photographer dashboard if user is photographer
        if(auth()->user()->hasRole('admin')){
        
        // Fetch actual statistics
        $totalProperties = Booking::count();
        $totalCustomers = User::whereHas('roles', function($q){
            $q->where('name', 'customer');
        })->count();
        $liveTours = Booking::whereNotNull('tour_final_link')->count();
        $totalRevenue = PaymentHistory::where('status', 'completed')->sum('amount');
        
        // Fetch monthly data for this month (current month)
        $currentMonth = now()->month;
        $currentYear = now()->year;
        $currentWeek = now()->weekOfMonth;
        $daysInMonth = now()->daysInMonth;
        
        // Get daily bookings for current month
        $monthlyBookings = [];
        $monthlyCustomers = [];
        
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = sprintf('%04d-%02d-%02d', $currentYear, $currentMonth, $day);
            
            $monthlyBookings[] = Booking::whereDate('created_at', $date)->count();
            $monthlyCustomers[] = User::whereHas('roles', function($q){
                $q->where('name', 'customer');
            })->whereDate('created_at', $date)->count();
        }
        
        // Calculate total earning for this month
        $monthlyEarning = PaymentHistory::where('status', 'completed')
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->sum('amount');
        
        // Fetch latest transactions
        $latestTransactions = PaymentHistory::with(['user', 'booking'])
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->orderBy('created_at', 'desc')
            ->limit(7)
            ->get();
        
        // response fofr admin user    
        return view('admin.dashboard',[
            'title' => $title,
            'totalProperties' => $totalProperties,
            'totalCustomers' => $totalCustomers,
            'liveTours' => $liveTours,
            'totalRevenue' => $totalRevenue,

            'monthlyBookings' => $monthlyBookings,
            'monthlyCustomers' => $monthlyCustomers,
            'monthlyEarning' => $monthlyEarning,
            'daysInMonth' => $daysInMonth,
            'latestTransactions' => $latestTransactions
        ]);

        }elseif(auth()->user()->hasRole('photographer')) {
            // Provide photographer list and schedule-related statuses to the view
            $photographers = User::whereHas('roles', function($q){
                $q->where('name', 'photographer');
            })->orderBy('firstname')->get();

            $statuses = [ 'pending' , 'schedul_accepted' ,'confirmed', 'schedul_assign', 'reschedul_assign', 'schedul_pending', 'schedul_inprogress', 'schedul_completed'];

            return view('admin.photographer.index', [
                'title' => $title,
                'photographers' => $photographers,
                'statuses' => $statuses
            ]);
        }
        
        // Fetch actual statistics for other roles
        $totalProperties = Booking::count();
        $totalCustomers = User::whereHas('roles', function($q){
            $q->where('name', 'customer');
        })->count();
        $liveTours = Booking::whereNotNull('tour_final_link')->count();
        $totalRevenue = PaymentHistory::where('status', 'success')->sum('amount');
        
        // Fetch monthly data for this month (current month)
        $currentMonth = now()->month;
        $currentYear = now()->year;
        $daysInMonth = now()->daysInMonth;
        
        // Get daily bookings for current month
        $monthlyBookings = [];
        $monthlyCustomers = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = sprintf('%04d-%02d-%02d', $currentYear, $currentMonth, $day);
            
            $monthlyBookings[] = Booking::whereDate('created_at', $date)->count();
            $monthlyCustomers[] = User::whereHas('roles', function($q){
                $q->where('name', 'customer');
            })->whereDate('created_at', $date)->count();
        }
        
        // Calculate total earning for this month
        $monthlyEarning = PaymentHistory::where('status', 'success')
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->sum('amount');
        
        // Fetch latest transactions
        $latestTransactions = PaymentHistory::with(['user', 'booking'])
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->orderBy('created_at', 'desc')
            ->limit(7)
            ->get();
        
        // All other roles (admin, tour_manager, seo_manager, etc.) can access admin dashboard
        return view('admin.dashboard',[
            'title' => $title,
            'totalProperties' => $totalProperties,
            'totalCustomers' => $totalCustomers,
            'liveTours' => $liveTours,
            'totalRevenue' => $totalRevenue,
            'monthlyBookings' => $monthlyBookings,
            'monthlyCustomers' => $monthlyCustomers,
            'monthlyEarning' => $monthlyEarning,
            'daysInMonth' => $daysInMonth,
            'latestTransactions' => $latestTransactions
        ]);
    }
}

