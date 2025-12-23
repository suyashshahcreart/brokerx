<?php

namespace App\Http\Controllers\QR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\QR;
use App\Models\Booking;
use App\Services\QRTrackingService;

class QRManageController extends Controller
{
    protected $trackingService;
    
    public function __construct(QRTrackingService $trackingService)
    {
        $this->trackingService = $trackingService;
    }
    
    /**
     * Display welcome page for QR Proppik
     */
    public function index(Request $request)
    {
        // Note: Tracking happens via AJAX after GPS coordinates are captured
        return view('qr.welcome');
    }

    /**
     * Display QR analytics dashboard
     */
    public function analytics(Request $request)
    {
        // Note: Tracking happens via AJAX after GPS coordinates are captured
        
        $filter = $request->get('filter', 'all'); // all, active, inactive
        
        $query = QR::with(['booking', 'creator']);
        
        // Apply filters
        if ($filter === 'active') {
            $query->whereNotNull('booking_id');
        } elseif ($filter === 'inactive') {
            $query->whereNull('booking_id');
        }
        
        $qrs = $query->orderBy('id', 'desc')->paginate(20);
        
        return view('qr.analytics', compact('qrs', 'filter'));
    }

    /**
     * Handle dynamic tour_code parameter
     * Route: /{tour_code}
     * Example: /1234Aber
     */
    public function showByTourCode(Request $request, $tour_code)
    {
        // Check if tour_code exists in bookings table
        $booking = Booking::where('tour_code', $tour_code)->first();
        
        if ($booking) {
            // Tour code found - show booking details or tour
            // Note: Tracking will happen via AJAX after GPS coordinates are captured
            return view('qr.tour-found', compact('booking', 'tour_code'));
        } else {
            // Tour code not found - show not found page
            return view('qr.tour-not-found', compact('tour_code'));
        }
    }
    
    /**
     * AJAX endpoint to track visit with GPS coordinates
     */
    public function trackVisitAjax(Request $request)
    {
        try {
            $tour_code = $request->input('tour_code');
            $page_type = $request->input('page_type', 'welcome');
            
            // Store GPS coordinates in session if provided
            if ($request->has('gps_latitude') && $request->has('gps_longitude')) {
                $request->session()->put('qr_gps_latitude', $request->input('gps_latitude'));
                $request->session()->put('qr_gps_longitude', $request->input('gps_longitude'));
            }
            
            // Store screen resolution in session if provided
            if ($request->has('screen_resolution')) {
                $request->session()->put('qr_screen_resolution', $request->input('screen_resolution'));
            }
            
            // Track the visit with GPS coordinates
            $tracking = $this->trackingService->trackVisit($request, $tour_code, $page_type);
            
            if (!$tracking) {
                \Log::error('QR Tracking: Failed to create tracking record', [
                    'tour_code' => $tour_code,
                    'page_type' => $page_type
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create tracking record'
                ], 500);
            }
            
            return response()->json([
                'success' => true,
                'tracking_id' => $tracking->id,
                'location_source' => $tracking->location_source ?? 'IP',
                'latitude' => $tracking->latitude ? (float)$tracking->latitude : null,
                'longitude' => $tracking->longitude ? (float)$tracking->longitude : null,
                'city' => $tracking->city,
                'region' => $tracking->region,
                'country' => $tracking->country,
                'full_address' => $tracking->full_address,
                'pincode' => $tracking->pincode,
            ]);
        } catch (\Exception $e) {
            \Log::error('QR Tracking AJAX Error: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Tracking error: ' . $e->getMessage()
            ], 500);
        }
    }
}

