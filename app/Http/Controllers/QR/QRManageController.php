<?php

namespace App\Http\Controllers\QR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\QR;
use App\Models\Booking;
use App\Models\Tour;
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
            // Get the tour associated with this booking
            $tour = $booking->tours()->first();
            
            // Build redirect URL based on tour location and slug (same logic as edit.blade.php)
            $redirectUrl = null;
            if ($tour) {
                if ($tour->location === 'creart_qr' && $tour->slug) {
                    $redirectUrl = 'http://creart.in/qr/' . $tour->slug . '/index.php';
                } elseif ($tour->location === 'tours' && $tour->slug) {
                    $redirectUrl = 'https://tour.proppik.in/' . $tour->slug . '/index.php';
                } elseif ($tour->location && $tour->slug) {
                    $redirectUrl = 'https://' . $tour->location . '.proppik.com/' . $tour->slug . '/index.php';
                }
            }
            
            // Check if booking status is 'tour_live'
            if ($booking->status === 'tour_live') {
                // Tour is live - show tour found page
                // Note: Tracking will happen via AJAX after GPS coordinates are captured
                return view('qr.tour-found', compact('booking', 'tour_code', 'tour', 'redirectUrl'));
            } else {
                // Tour code found but status is not 'tour_live' - show coming soon page
                // Note: Tracking will happen via AJAX after GPS coordinates are captured
                return view('qr.tour-coming-soon', compact('booking', 'tour_code', 'tour', 'redirectUrl'));
            }
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
            try {
                $tracking = $this->trackingService->trackVisit($request, $tour_code, $page_type);
                
                if (!$tracking) {
                    \Log::error('QR Tracking: Failed to create tracking record - trackVisit returned null', [
                        'tour_code' => $tour_code,
                        'page_type' => $page_type,
                        'request_data' => $request->all()
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to create tracking record. Please check server logs for details.'
                    ], 500);
                }
            } catch (\Exception $e) {
                \Log::error('QR Tracking: Exception in trackVisitAjax', [
                    'tour_code' => $tour_code,
                    'page_type' => $page_type,
                    'exception' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Tracking error: ' . $e->getMessage()
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
    
    /**
     * Save tour notification (phone number)
     */
    public function saveNotification(Request $request)
    {
        try {
            $validated = $request->validate([
                'tour_code' => 'required|string|max:255',
                'phone_number' => 'required|string|regex:/^[0-9]{10}$/',
            ]);
            
            // Clean phone number (remove any non-digit characters)
            $phoneNumber = preg_replace('/\D/', '', $validated['phone_number']);
            
            // Validate phone number is exactly 10 digits
            if (strlen($phoneNumber) !== 10) {
                return response()->json([
                    'success' => false,
                    'message' => 'Phone number must be exactly 10 digits.'
                ], 422);
            }
            
            // Find booking by tour_code
            $booking = Booking::where('tour_code', $validated['tour_code'])->first();
            
            // Check if notification already exists for this phone and tour_code
            $existingNotification = \App\Models\TourNotification::where('tour_code', $validated['tour_code'])
                ->where('phone_number', $phoneNumber)
                ->first();
            
            if ($existingNotification) {
                return response()->json([
                    'success' => true,
                    'message' => 'You are already registered for notifications!',
                    'already_exists' => true
                ]);
            }
            
            // Create notification
            $notification = \App\Models\TourNotification::create([
                'tour_code' => $validated['tour_code'],
                'booking_id' => $booking ? $booking->id : null,
                'phone_number' => $phoneNumber,
                'status' => 'pending',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'metadata' => [
                    'source' => 'tour_coming_soon_page',
                    'submitted_at' => now()->toDateTimeString(),
                ],
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Notification saved successfully!',
                'notification_id' => $notification->id
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', $e->errors()['phone_number'] ?? ['Invalid phone number format.'])
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Tour Notification Save Error: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to save notification. Please try again later.'
            ], 500);
        }
    }
}

