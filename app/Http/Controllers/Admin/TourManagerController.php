<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Tour;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TourManagerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of bookings for tour management
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Booking::with([
                'user',
                'propertyType',
                'propertySubType',
                'bhk',
                'city',
                'state'
            ])->orderBy('created_at', 'desc');

            // Apply filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('payment_status')) {
                $query->where('payment_status', $request->payment_status);
            }

            if ($request->filled('property_type_id')) {
                $query->where('property_type_id', $request->property_type_id);
            }

            if ($request->filled('city_id')) {
                $query->where('city_id', $request->city_id);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('booking_date', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('booking_date', '<=', $request->date_to);
            }

            return DataTables::of($query)
                ->addColumn('booking_info', function (Booking $booking) {
                    $propertyType = $booking->propertyType?->name ?? 'N/A';
                    $subType = $booking->propertySubType?->name ?? '';
                    $bhk = $booking->bhk?->name ?? '';
                    
                    $info = '<strong>#' . $booking->id . '</strong><br>';
                    $info .= '<small class="text-muted">' . $propertyType;
                    if ($subType) $info .= ' - ' . $subType;
                    if ($bhk) $info .= ' - ' . $bhk;
                    $info .= '</small>';
                    
                    return $info;
                })
                ->addColumn('customer', function (Booking $booking) {
                    if ($booking->user) {
                        return '<strong>' . $booking->user->firstname . ' ' . $booking->user->lastname . '</strong><br>' .
                               '<small class="text-muted">' . $booking->user->mobile . '</small>';
                    }
                    return '<span class="text-muted">N/A</span>';
                })
                ->addColumn('location', function (Booking $booking) {
                    $location = [];
                    if ($booking->society_name) $location[] = $booking->society_name;
                    if ($booking->address_area) $location[] = $booking->address_area;
                    if ($booking->city) $location[] = $booking->city->name;
                    
                    return implode(', ', $location) ?: 'N/A';
                })
                ->addColumn('booking_date', function (Booking $booking) {
                    if ($booking->booking_date) {
                        return \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') . '<br>' .
                               '<small class="text-muted">' . \Carbon\Carbon::parse($booking->booking_date)->format('h:i A') . '</small>';
                    }
                    return '<span class="text-muted">Not scheduled</span>';
                })
                ->addColumn('status', function (Booking $booking) {
                    $badges = [
                        'pending' => 'secondary',
                        'confirmed' => 'primary',
                        'scheduled' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger'
                    ];
                    $color = $badges[$booking->status] ?? 'secondary';
                    return '<span class="badge bg-' . $color . '">' . ucfirst($booking->status) . '</span>';
                })
                ->addColumn('payment_status', function (Booking $booking) {
                    $badges = [
                        'pending' => 'warning',
                        'paid' => 'success',
                        'failed' => 'danger',
                        'refunded' => 'info'
                    ];
                    $color = $badges[$booking->payment_status] ?? 'secondary';
                    return '<span class="badge bg-' . $color . '">' . ucfirst($booking->payment_status) . '</span>';
                })
                ->addColumn('price', function (Booking $booking) {
                    return '₹' . number_format($booking->price, 2);
                })
                ->addColumn('actions', function (Booking $booking) {
                    $actions = '<div class="btn-group" role="group">';
                    
                    // View button
                    $actions .= '<a href="' . route('admin.tour-manager.show', $booking) . '" class="btn btn-sm btn-primary" title="View Details"><i class="ri-eye-line"></i></a>';
                    
                    // Edit tour button (only if booking has tours)
                    if ($booking->tours()->exists()) {
                        $tour = $booking->tours()->first();
                        $actions .= ' <a href="' . route('admin.tour-manager.edit', $tour) . '" class="btn btn-sm btn-warning" title="Edit Tour"><i class="ri-edit-line"></i></a>';
                    }
                    
                    // Schedule tour button
                    if (in_array($booking->status, ['pending', 'confirmed'])) {
                        $actions .= ' <button type="button" class="btn btn-sm btn-info schedule-tour-btn" data-id="' . $booking->id . '" title="Schedule Tour"><i class="ri-calendar-line"></i></button>';
                    }
                    
                    $actions .= '</div>';
                    return $actions;
                })
                ->rawColumns(['booking_info', 'customer', 'booking_date', 'status', 'payment_status', 'actions'])
                ->make(true);
        }

        $statuses = ['pending', 'confirmed', 'scheduled', 'completed', 'cancelled'];
        $paymentStatuses = ['pending', 'paid', 'failed', 'refunded'];
        
        return view('admin.tour-manager.index', compact('statuses', 'paymentStatuses'));
    }

    /**
     * Display the specified booking
     */
    public function show(Booking $booking)
    {
        $booking->load([
            'user',
            'propertyType',
            'propertySubType',
            'bhk',
            'city',
            'state',
            'tours'
        ]);

        return view('admin.tour-manager.show', compact('booking'));
    }

    /**
     * Show the form for editing the specified tour
     */
    public function edit(Tour $tour)
    {
        $tour->load('booking');
        
        $statuses = ['draft', 'published', 'archived'];
        $structuredDataTypes = ['Article', 'Event', 'Product', 'Organization', 'Person', 'Place'];
        
        return view('admin.tour-manager.edit', compact('tour', 'statuses', 'structuredDataTypes'));
    }

    /**
     * Update the specified tour in storage
     */
    public function update(Request $request, Tour $tour)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'status' => 'required|in:draft,published,archived',
            'files.*' => 'nullable|file|max:101200', // 50MB per file
        ]);

        // Handle file uploads
        $uploadedFiles = [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                try {
                    $filename = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('tours', $filename, 'public');
                    
                    $uploadedFiles[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'url' => asset('storage/' . $path),
                        'size' => $file->getSize(),
                        'type' => $file->getMimeType(),
                        'uploaded_at' => now()->toDateTimeString()
                    ];
                } catch (\Exception $e) {
                    \Log::error('File upload error: ' . $e->getMessage());
                }
            }
        }

        // Merge with existing files or create new array
        $existingFiles = $tour->final_json['files'] ?? [];
        $validated['final_json'] = [
            'files' => array_merge($existingFiles, $uploadedFiles),
            'updated_at' => now()->toDateTimeString()
        ];

        $validated['updated_by'] = auth()->id();

        $tour->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tour updated successfully!',
                'booking_id' => $tour->booking_id,
                'redirect' => route('admin.tour-manager.show', $tour->booking_id)
            ]);
        }

        return redirect()->route('admin.tour-manager.show', $tour->booking_id)
            ->with('success', 'Tour updated successfully!');
    }

    /**
     * Handle file upload via AJAX
     */
    public function uploadFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:51200', // 50MB max
        ]);

        try {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('tours', $filename, 'public');

            return response()->json([
                'success' => true,
                'path' => $path,
                'url' => asset('storage/' . $path),
                'message' => 'File uploaded successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
