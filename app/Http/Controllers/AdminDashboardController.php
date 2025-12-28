<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $title = 'Dashboard';
        
        // Show photographer dashboard if user is photographer
        if (auth()->user()->hasRole('photographer')) {
            return view('admin.photographer.index', ['title' => $title]);
        }
        
        // All other roles (admin, tour_manager, seo_manager, etc.) can access admin dashboard
        return view('admin.dashboard',['title' => $title]);
    }
}

