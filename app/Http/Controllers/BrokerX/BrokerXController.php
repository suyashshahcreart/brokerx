<?php

namespace App\Http\Controllers\BrokerX;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BrokerXController extends Controller{
    public function index(){
        return view('brokerx.index');
    }
}
