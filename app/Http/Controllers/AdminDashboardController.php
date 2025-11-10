<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // $title = 'Dashboard demo';
        // dd($title);
        return view('admin.dashboard');
    }
}
