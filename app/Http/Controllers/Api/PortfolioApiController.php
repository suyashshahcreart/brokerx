<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PortfolioApiService;
use App\Models\Booking;
use App\Models\Tour;
use App\Models\PropertyType;
use App\Models\PropertySubType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PortfolioApiController extends Controller
{
    protected PortfolioApiService $portfolioApiService;

    public function __construct(PortfolioApiService $portfolioApiService)
    {
        $this->portfolioApiService = $portfolioApiService;
    }

    /**
     * Send OTP to configured mobile number
     */
    public function sendOtp(Request $request)
    {
        try {
            $result = $this->portfolioApiService->sendOtp($request);
            return response()->json($result);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            Log::error('Portfolio API sendOtp error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP. Please try again later.'
            ], 500);
        }
    }

    /**
     * Verify OTP and return access token
     */
    public function verifyOtp(Request $request)
    {
        // Validate OTP input - allow string or numeric, ensure it's exactly 6 digits
        $otp = $request->input('otp');
        
        // Clean and validate OTP
        if (is_numeric($otp)) {
            $otp = (string) $otp;
        }
        $otp = trim($otp ?? '');
        
        // Validate format
        if (empty($otp) || strlen($otp) !== 6 || !ctype_digit($otp)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP format. Please enter a 6-digit code.'
            ], 400);
        }

        try {
            $result = $this->portfolioApiService->verifyOtp($request, $otp);
            return response()->json($result);
        } catch (\RuntimeException $e) {
            Log::warning('Portfolio API verifyOtp failed', [
                'error' => $e->getMessage(),
                'device_fingerprint' => $this->portfolioApiService->generateDeviceFingerprint($request),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            Log::error('Portfolio API verifyOtp error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to verify OTP. Please try again later.'
            ], 500);
        }
    }

    /**
     * Get portfolio list with filters and pagination
     */
    public function list(Request $request)
    {
        try {
            // Build query with relationships
            $query = Booking::with([
                'propertyType:id,name',
                'propertySubType:id,name,property_type_id',
                'tours' => function($q) {
                    $q->select([
                        'id', 'booking_id', 'name', 'title', 'slug', 
                        'location', 'tour_thumbnail', 'is_active', 
                        'is_credentials', 'is_mobile_validation', 
                        'is_hosted', 'hosted_link'
                    ]);
                }
            ])->select([
                'id', 'status', 'owner_type', 
                'property_type_id', 'property_sub_type_id', 'user_id'
            ]);

            // Apply property type filter
            if ($request->filled('property_type_filter')) {
                $filterIds = is_array($request->input('property_type_filter')) 
                    ? $request->input('property_type_filter')
                    : explode(',', $request->input('property_type_filter'));
                
                $filterIds = array_map('intval', $filterIds);
                
                $query->where(function($q) use ($filterIds) {
                    // Match property_type_id if it's in the filter
                    $q->whereIn('property_type_id', $filterIds)
                      // OR match property_sub_type_id if it's in the filter
                      ->orWhereIn('property_sub_type_id', $filterIds);
                });
            }

            // Apply pagination if page parameter is provided
            $page = $request->input('page');
            $perPage = $request->input('per_page', 15);
            
            if ($page !== null) {
                // Pagination enabled
                $perPage = min(max((int) $perPage, 1), 100); // Limit between 1 and 100
                $bookings = $query->orderBy('id', 'desc')->paginate($perPage);
                
                $data = $bookings->map(function($booking) {
                    return $this->formatBookingData($booking);
                });

                return response()->json([
                    'success' => true,
                    'data' => $data,
                    'meta' => [
                        'total' => $bookings->total(),
                        'per_page' => $bookings->perPage(),
                        'current_page' => $bookings->currentPage(),
                        'total_pages' => $bookings->lastPage(),
                    ],
                    'links' => [
                        'first' => $bookings->url(1),
                        'last' => $bookings->url($bookings->lastPage()),
                        'prev' => $bookings->previousPageUrl(),
                        'next' => $bookings->nextPageUrl(),
                    ],
                ]);
            } else {
                // No pagination - return all
                $bookings = $query->orderBy('id', 'desc')->get();
                
                $data = $bookings->map(function($booking) {
                    return $this->formatBookingData($booking);
                });

                return response()->json([
                    'success' => true,
                    'data' => $data,
                    'meta' => [
                        'total' => $bookings->count(),
                    ],
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Portfolio API list error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch portfolio data.'
            ], 500);
        }
    }

    /**
     * Get property type filters
     */
    public function getPropertyTypeFilters(Request $request)
    {
        try {
            // Get all property types except "other"
            $propertyTypes = PropertyType::where('name', '!=', 'other')
                ->select('id', 'name', 'icon')
                ->orderBy('name')
                ->get();

            // Get all property sub types where property type is "other"
            $otherSubTypes = PropertySubType::whereHas('propertyType', function($q) {
                $q->where('name', 'other');
            })
            ->select('id', 'name', 'property_type_id', 'icon')
            ->orderBy('name')
            ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'property_types' => $propertyTypes->map(function($type) {
                        return [
                            'id' => $type->id,
                            'name' => $type->name,
                            'icon' => $type->icon,
                            'type' => 'property_type',
                        ];
                    }),
                    'other_sub_types' => $otherSubTypes->map(function($subType) {
                        return [
                            'id' => $subType->id,
                            'name' => $subType->name,
                            'property_type_id' => $subType->property_type_id,
                            'icon' => $subType->icon,
                            'type' => 'property_sub_type',
                        ];
                    }),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Portfolio API getPropertyTypeFilters error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch property type filters.'
            ], 500);
        }
    }

    /**
     * Get tour thumbnail URL
     */
    protected function getTourThumbnailUrl(?string $thumbnail): ?string
    {
        if (empty($thumbnail)) {
            return null;
        }

        // If already an absolute URL, return as is
        if (preg_match('#^https?://#i', $thumbnail)) {
            return $thumbnail;
        }

        // Try to get S3 URL
        try {
            return Storage::disk('s3')->url($thumbnail);
        } catch (\Exception $e) {
            Log::warning('Failed to get S3 URL for tour thumbnail', [
                'thumbnail' => $thumbnail,
                'error' => $e->getMessage()
            ]);
            return $thumbnail; // Return original value as fallback
        }
    }

    /**
     * Format booking data for API response
     */
    protected function formatBookingData(Booking $booking): array
    {
        // Get the latest tour for this booking
        $tour = $booking->tours->first();

        // Get booking live link
        $bookingLiveLink = $booking->getTourLiveUrl();

        // Get tour live link if tour exists
        // Ensure booking relationship is set on tour for getTourLiveUrl() to work
        $tourLiveLink = '#';
        if ($tour) {
            // Set the booking relationship on the tour so getTourLiveUrl() can access booking->user_id
            $tour->setRelation('booking', $booking);
            $tourLiveLink = $tour->getTourLiveUrl();
        }

        return [
            'booking_id' => $booking->id,
            'booking_status' => $booking->status,
            'owner_type' => $booking->owner_type,
            'property_type' => $booking->propertyType ? $booking->propertyType->name : null,
            'property_sub_type' => $booking->propertySubType ? $booking->propertySubType->name : null,
            'booking_live_link' => $bookingLiveLink,
            'tour' => $tour ? [
                'name' => $tour->name,
                'title' => $tour->title,
                'slug' => $tour->slug,
                'location' => $tour->location,
                'tour_thumbnail' => $this->getTourThumbnailUrl($tour->tour_thumbnail),
                'is_active' => (bool) $tour->is_active,
                'is_credentials' => (bool) $tour->is_credentials,
                'is_mobile_validation' => (bool) $tour->is_mobile_validation,
                'is_hosted' => (bool) $tour->is_hosted,
                'hosted_link' => $tour->hosted_link,
                'tour_live_link' => $tourLiveLink,
            ] : null,
        ];
    }
}
