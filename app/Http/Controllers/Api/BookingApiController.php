<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookingCollection;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingApiController extends Controller
{
    /**
     * Display a listing of bookings with pagination, filters, and search.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Resources\BookingCollection
     */
    public function index(Request $request)
    {
        $query = Booking::with([
            'user',
            'propertyType',
            'propertySubType',
            'bhk',
            'city',
            'state',
            'creator',
            'updater'
        ]);

        // Authorization: Show all bookings for admin, only user's own bookings for others
        $user = Auth::user();
        if (!$user->hasRole('admin')) {
            $query->where('user_id', $user->id);
        }

        // Apply filters
        $this->applyFilters($query, $request);

        // Apply search
        if ($request->filled('search')) {
            $this->applySearch($query, $request->search);
        }

        // Apply sorting
        $this->applySorting($query, $request);

        // Paginate results
        $perPage = $request->input('per_page', 15);
        $perPage = min(max((int) $perPage, 1), 100); // Limit between 1 and 100

        $bookings = $query->paginate($perPage);

        return new BookingCollection($bookings);
    }

    /**
     * Display the specified booking.
     *
     * @param  int  $id
     * @return \App\Http\Resources\BookingResource
     */
    public function show($id)
    {
        $query = Booking::with([
            'user',
            'propertyType',
            'propertySubType',
            'bhk',
            'city',
            'state',
            'creator',
            'updater'
        ]);

        // Authorization: Show booking if admin or if booking belongs to user
        $user = Auth::user();
        if (!$user->hasRole('admin')) {
            $query->where('user_id', $user->id);
        }

        $booking = $query->findOrFail($id);

        return new BookingResource($booking);
    }

    /**
     * Apply filters to the query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function applyFilters($query, Request $request)
    {
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by property type
        if ($request->filled('property_type_id')) {
            $query->where('property_type_id', $request->property_type_id);
        }

        // Filter by property sub type
        if ($request->filled('property_sub_type_id')) {
            $query->where('property_sub_type_id', $request->property_sub_type_id);
        }

        // Filter by city
        if ($request->filled('city_id')) {
            $query->where('city_id', $request->city_id);
        }

        // Filter by state
        if ($request->filled('state_id')) {
            $query->where('state_id', $request->state_id);
        }

        // Filter by BHK
        if ($request->filled('bhk_id')) {
            $query->where('bhk_id', $request->bhk_id);
        }

        // Filter by furniture type
        if ($request->filled('furniture_type')) {
            $query->where('furniture_type', $request->furniture_type);
        }

        // Filter by owner type
        if ($request->filled('owner_type')) {
            $query->where('owner_type', $request->owner_type);
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('booking_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('booking_date', '<=', $request->to_date);
        }

        // Filter by created date range
        if ($request->filled('created_from')) {
            $query->whereDate('created_at', '>=', $request->created_from);
        }

        if ($request->filled('created_to')) {
            $query->whereDate('created_at', '<=', $request->created_to);
        }

        // Filter by price range
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Filter by specific user (admin only)
        if ($request->filled('user_id') && Auth::user()->hasRole('admin')) {
            $query->where('user_id', $request->user_id);
        }

        // Include or exclude soft deleted records
        if ($request->filled('include_deleted') && $request->include_deleted == 'true') {
            $query->withTrashed();
        }

        if ($request->filled('only_deleted') && $request->only_deleted == 'true') {
            $query->onlyTrashed();
        }
    }

    /**
     * Apply search to the query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $search
     * @return void
     */
    protected function applySearch($query, $search)
    {
        $query->where(function ($q) use ($search) {
            $q->where('firm_name', 'like', "%{$search}%")
                ->orWhere('gst_no', 'like', "%{$search}%")
                ->orWhere('house_no', 'like', "%{$search}%")
                ->orWhere('building', 'like', "%{$search}%")
                ->orWhere('society_name', 'like', "%{$search}%")
                ->orWhere('full_address', 'like', "%{$search}%")
                ->orWhere('pin_code', 'like', "%{$search}%")
                ->orWhere('landmark', 'like', "%{$search}%")
                ->orWhere('address_area', 'like', "%{$search}%")
                ->orWhere('booking_notes', 'like', "%{$search}%")
                ->orWhere('tour_code', 'like', "%{$search}%")
                ->orWhere('cashfree_order_id', 'like', "%{$search}%")
                ->orWhere('cashfree_reference_id', 'like', "%{$search}%")
                ->orWhereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
        });
    }

    /**
     * Apply sorting to the query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function applySorting($query, Request $request)
    {
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');

        // Validate sort order
        $sortOrder = in_array(strtolower($sortOrder), ['asc', 'desc']) ? strtolower($sortOrder) : 'desc';

        // Allowed sort fields
        $allowedSortFields = [
            'id',
            'booking_date',
            'price',
            'status',
            'payment_status',
            'created_at',
            'updated_at',
            'area',
            'pin_code',
        ];

        // Apply sorting
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            // Default sorting
            $query->orderBy('created_at', 'desc');
        }
    }

    /**
     * Get JSON data for a specific booking.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getJson($id)
    {
        $query = Booking::query();

        // Authorization: Show booking if admin or if booking belongs to user
        $user = Auth::user();
        if (!$user->hasRole('admin')) {
            $query->where('user_id', $user->id);
        }

        $booking = $query->findOrFail($id);

        // Return json_data as a string
        $jsonData = $booking->json_data;
        $jsonString = $jsonData ? json_encode($jsonData) : null;

        return response()->json([
            'success' => true,
            'data' => [
                'booking_id' => $booking->id,
                'json_data' => $jsonString,
            ],
        ]);
    }

    /**
     * Set JSON data for a specific booking.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function setJson(Request $request, $id)
    {
        $query = Booking::query();

        // Authorization: Update booking if admin or if booking belongs to user
        $user = Auth::user();
        if (!$user->hasRole('admin')) {
            $query->where('user_id', $user->id);
        }

        $booking = $query->findOrFail($id);

        // Validate that json_data is provided as a string
        $request->validate([
            'json_data' => 'required|string',
        ]);

        // Parse the JSON string to validate it
        $jsonData = json_decode($request->json_data, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid JSON format: ' . json_last_error_msg(),
            ], 422);
        }

        // Update the booking with parsed JSON data
        $booking->json_data = $jsonData;
        $booking->updated_by = $user->id;
        $booking->save();

        return response()->json([
            'success' => true,
            'message' => 'JSON data updated successfully',
            'data' => [
                'booking_id' => $booking->id,
                'json_data' => $request->json_data,
            ],
        ]);
    }
}
