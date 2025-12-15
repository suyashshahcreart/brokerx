<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $title = 'Dashboard';
        if (auth()->user()->hasRole('photographer')) {
            
            return view('admin.photographer.index', ['title' => $title]);
        }
        return view('admin.dashboard',['title' => $title]);
    }
}
