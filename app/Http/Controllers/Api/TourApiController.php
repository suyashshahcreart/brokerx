<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tour;
use Illuminate\Http\Request;

class TourApiController extends Controller
{
    /**
     * List tours with pagination and optional filters.
     */
    public function index(Request $request)
    {
        $query = Tour::with(['booking:id,user_id,booking_date']);

        // Filter by category (mapped to tour location)
        if ($request->filled('category')) {
            $query->where('location', $request->category);
        }

        // Filter by customer_id matching booking->user_id
        if ($request->filled('customer_id')) {
            $query->whereHas('booking', function ($q) use ($request) {
                $q->where('user_id', $request->customer_id);
            });
        }

        // Optional status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Optional search by title or name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        // Dynamic page size with sane limits
        $perPage = (int) $request->input('per_page', 15);
        $perPage = min(max($perPage, 1), 100);

        $tours = $query->paginate($perPage);

        // Hide large JSON blobs from response
        $tours->getCollection()->makeHidden(['final_json']);

        return response()->json([
            'success' => true,
            'data' => $tours->items(),
            'meta' => [
                'current_page' => $tours->currentPage(),
                'per_page' => $tours->perPage(),
                'total' => $tours->total(),
                'last_page' => $tours->lastPage(),
            ],
        ]);
    }
}
