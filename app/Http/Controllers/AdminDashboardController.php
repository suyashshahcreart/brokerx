<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $title = 'Dashboard';
        
        // Show photographer dashboard if user is photographer
        if(auth()->user()->hasRole('admin')){
            return view('admin.dashboard',['title' => $title]);
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
        
        // All other roles (admin, tour_manager, seo_manager, etc.) can access admin dashboard
        return view('admin.dashboard',['title' => $title]);
    }
}

