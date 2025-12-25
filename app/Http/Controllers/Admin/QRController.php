<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\QR;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;

class QRController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Handle grid view request
        if ($request->has('view') && $request->view === 'grid') {
            return $this->gridView($request);
        }
        
        if ($request->ajax()) {
            $filter = $request->get('filter', 'all'); // all, active, inactive
            $data = QR::with(['booking', 'creator'])->select('qr_code.*');
            
            // Apply filters (same logic as grid view)
            if ($filter === 'active') {
                $data->whereNotNull('booking_id');
            } elseif ($filter === 'inactive') {
                $data->whereNull('booking_id');
            }
            
            return DataTables::of($data)
                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" class="form-check-input qr-checkbox" value="' . $row->id . '" data-qr-id="' . $row->id . '">';
                })
                ->addColumn('actions', function ($row) {
                    $editUrl = route('admin.qr.edit', $row->id);
                    $showUrl = route('admin.qr.show', $row->id);
                    $deleteUrl = route('admin.qr.destroy', $row->id);
                    $csrf = csrf_field();
                    $method = method_field('DELETE');
                    $actions = '<div class="d-flex gap-1">';
                    $actions .= '<a href="' . $showUrl . '" class="btn btn-light btn-sm border" title="View QR Code Details" data-bs-toggle="tooltip" data-bs-placement="top"><i class="ri-eye-line"></i></a>';
                    $actions .= '<a href="' . $editUrl . '" class="btn btn-soft-primary btn-sm border" title="Edit QR Code" data-bs-toggle="tooltip" data-bs-placement="top"><i class="ri-edit-line"></i></a>';
                    $actions .= '<form action="' . $deleteUrl . '" method="POST" class="d-inline">' . $csrf . $method .
                        '<button type="submit" class="btn btn-soft-danger btn-sm border" onclick="return confirm(\'Delete this QR code?\')" title="Delete QR Code" data-bs-toggle="tooltip" data-bs-placement="top"><i class="ri-delete-bin-line"></i></button></form>';
                    $actions .= '</div>';
                    return $actions;
                })
                ->addColumn('qr_code_svg', function ($row) {
                    try {
                        $logoPath = public_path('images/proppik-logo-sm.png');
                        $hasLogo = file_exists($logoPath);
                        
                        $qrCode = QrCode::size(300)->color(0, 0, 128);
                        
                        if ($hasLogo) {
                            $qrCode = $qrCode->merge($logoPath, 0.2, true);
                        }
                        
                        if ($row->qr_link) {
                            return $qrCode->generate($row->qr_link);
                        }else{
                            return $qrCode->generate('https://qr.proppik.com/'.$row->code);
                        }
                    } catch (\Exception $e) {
                        \Log::error('QR Code Generation Error: ' . $e->getMessage());
                    }
                    return '';
                })
                ->editColumn('image', function ($row) {
                    return $row->image ? '<img src="/storage/' . $row->image . '" width="50"/>' : '';
                })
                ->editColumn('created_by', function ($row) {
                    return $row->creator ? $row->creator->firstname . ' ' . $row->creator->lastname : '';
                })
                ->rawColumns(['checkbox', 'actions', 'image', 'qr_code_svg'])
                ->make(true);
        }
        return view('admin.qr.index');
    }

    /**
     * Return grid view HTML
     */
    public function gridView(Request $request)
    {
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 12);
        $filter = $request->get('filter', 'all'); // all, active, inactive
        $selectedIds = $request->get('selected_ids', []); // Array of selected QR IDs
        
        $query = QR::with(['booking', 'creator'])->select('qr_code.*');
        
        // Apply filters
        if ($filter === 'active') {
            $query->whereNotNull('booking_id');
        } elseif ($filter === 'inactive') {
            $query->whereNull('booking_id');
        }
        
        // Generate QR code SVG for each QR
        $qrs = $query->orderBy('id', 'desc')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get()
            ->map(function ($qr) {
                try {
                    $logoPath = public_path('images/proppik-logo-sm.png');
                    $hasLogo = file_exists($logoPath);
                    
                    $qrCode = QrCode::size(300)->color(0, 0, 128);
                    
                    if ($hasLogo) {
                        $qrCode = $qrCode->merge($logoPath, 0.2, true);
                    }
                    
                    if ($qr->qr_link) {
                        $qr->qr_code_svg = $qrCode->generate($qr->qr_link);
                    } else {
                        $qr->qr_code_svg = $qrCode->generate('https://qr.proppik.com/'.$qr->code);
                    }
                } catch (\Exception $e) {
                    \Log::error('QR Code Generation Error: ' . $e->getMessage());
                    $qr->qr_code_svg = null;
                }
                return $qr;
            });
        
        $total = $query->count();
        $totalPages = ceil($total / $perPage);
        
        $html = view('admin.qr.partials.grid', [
            'qrs' => $qrs,
            'selectedIds' => is_array($selectedIds) ? $selectedIds : explode(',', $selectedIds)
        ])->render();
        
        return response()->json([
            'html' => $html,
            'pagination' => [
                'current_page' => (int)$page,
                'total_pages' => $totalPages,
                'total_records' => $total,
                'per_page' => $perPage
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get bookings that don't already have a QR code assigned
        $bookings = Booking::with(['user', 'propertyType', 'propertySubType', 'bhk', 'city', 'state'])
            ->whereDoesntHave('qr')
            ->orderBy('id', 'desc')
            ->get();
        
        $defaultCode = $this->generateRandomCode();
        return view('admin.qr.create', compact('bookings', 'defaultCode'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:8|regex:/^[A-Za-z0-9]{8}$/',
            'name' => 'nullable|string|max:255',
            'booking_id' => 'nullable|exists:bookings,id',
            'image' => 'nullable|image|max:2048',
            'qr_link' => 'nullable|string|max:255',
        ], [
            'code.required' => 'The code field is required.',
            'code.size' => 'The code must be exactly 8 characters.',
            'code.regex' => 'The code must contain only letters (A-Z, a-z) and numbers (0-9).',
        ]);

        // Get code - required
        $code = $request->input('code');
        if (empty($code) || !preg_match('/^[A-Za-z0-9]{8}$/', $code)) {
            $code = $this->generateRandomCode();
        }
        
        // Ensure code is unique
        while (QR::where('code', $code)->exists()) {
            $code = $this->generateRandomCode();
        }

        // Get name - optional, can be any string
        $name = $request->input('name');
        if (empty($name) || trim($name) === '') {
            $name = null; // Allow null for name
        } else {
            $name = trim($name); // Trim whitespace
        }

        $bookingId = $request->input('booking_id');
        
        $data = $request->only(['qr_link']);
        $data['code'] = $code;
        $data['name'] = $name;
        $data['booking_id'] = $bookingId ? (int) $bookingId : null; // Set to null if not provided
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('qr_images', 'public');
        }

        $qr = QR::create($data);
        
        // If booking_id is provided, update the booking's tour_code field with the QR code
        if ($bookingId) {
            $booking = Booking::find($bookingId);
            if ($booking) {
                $booking->tour_code = $code;
                $booking->save();
            }
        }
        
        activity()->performedOn($qr)->causedBy(Auth::user())->log('Created QR code');
        return redirect()->route('admin.qr.index')->with('success', 'QR code created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $qr = QR::with(['booking', 'creator'])->findOrFail($id);
        return view('admin.qr.show', compact('qr'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $qr = QR::findOrFail($id);
        
        // Get bookings that don't have a QR code assigned, OR the current booking (even if it has a QR)
        $bookings = Booking::with(['user', 'propertyType', 'propertySubType', 'bhk', 'city', 'state'])
            ->where(function($query) use ($qr) {
                $query->whereDoesntHave('qr')
                      ->orWhere('id', $qr->booking_id); // Include current booking even if it has QR
            })
            ->orderBy('id', 'desc')
            ->get();
            
        return view('admin.qr.edit', compact('qr', 'bookings'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $qr = QR::findOrFail($id);
        $request->validate([
            'code' => 'required|string|size:8|regex:/^[A-Za-z0-9]{8}$/',
            'name' => 'nullable|string|max:255',
            'booking_id' => 'nullable|exists:bookings,id',
            'image' => 'nullable|image|max:2048',
            'qr_link' => 'nullable|string|max:255',
        ], [
            'code.required' => 'The code field is required.',
            'code.size' => 'The code must be exactly 8 characters.',
            'code.regex' => 'The code must contain only letters (A-Z, a-z) and numbers (0-9).',
        ]);
        
        // Get code - required
        $code = $request->input('code');
        if (empty($code) || !preg_match('/^[A-Za-z0-9]{8}$/', $code)) {
            $code = $qr->code; // Keep existing if invalid
        }
        
        // Ensure code is unique (except for current record)
        while (QR::where('code', $code)->where('id', '!=', $id)->exists()) {
            $code = $this->generateRandomCode();
        }
        
        // Get name - optional, can be any string
        $name = $request->input('name');
        if (empty($name) || trim($name) === '') {
            $name = null; // Allow null for name
        } else {
            $name = trim($name); // Trim whitespace
        }
        
        $newBookingId = $request->input('booking_id');
        $newBookingId = $newBookingId ? (int) $newBookingId : null;
        $oldBookingId = $qr->booking_id;
        
        // If booking was changed, handle tour_code updates
        if ($oldBookingId !== $newBookingId) {
            // Clear tour_code from old booking (if it matches this QR code)
            if ($oldBookingId) {
                $oldBooking = Booking::find($oldBookingId);
                if ($oldBooking && $oldBooking->tour_code === $qr->code) {
                    $oldBooking->tour_code = null;
                    $oldBooking->save();
                }
            }
            
            // Set tour_code for new booking
            if ($newBookingId) {
                $newBooking = Booking::find($newBookingId);
                if ($newBooking) {
                    $newBooking->tour_code = $code;
                    $newBooking->save();
                }
            }
        } elseif ($newBookingId && $qr->code !== $code) {
            // If booking is same but code changed, update tour_code
            $booking = Booking::find($newBookingId);
            if ($booking) {
                $booking->tour_code = $code;
                $booking->save();
            }
        }
        
        $data = $request->only(['qr_link']);
        $data['code'] = $code;
        $data['name'] = $name;
        $data['booking_id'] = $newBookingId; // Set to null if not provided
        $data['updated_by'] = Auth::id();
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('qr_images', 'public');
        }
        $qr->update($data);
        activity()->performedOn($qr)->causedBy(Auth::user())->log('Updated QR code');
        return redirect()->route('admin.qr.index')->with('success', 'QR code updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    /**
     * Download QR code with details as PDF
     */
    public function download($id)
    {
        try {
            $qr = QR::with(['booking.user', 'booking.propertyType', 'booking.propertySubType', 'booking.bhk', 'booking.city', 'booking.state'])->findOrFail($id);
            
            // Generate QR code and save as temporary PNG file using endroid/qr-code
            $qrCodePath = null;
            if ($qr->qr_link) {
                try {
                    // Create temp directory if it doesn't exist
                    $tempDir = storage_path('app/temp');
                    if (!file_exists($tempDir)) {
                        mkdir($tempDir, 0755, true);
                    }
                    
                    // Generate unique filename
                    $filename = 'qr_' . $qr->code . '_' . time() . '.png';
                    $qrCodePath = $tempDir . '/' . $filename;
                    
                    // Generate QR code using endroid/qr-code with GD (PNG)
                    $logoPath = public_path('images/proppik-logo-sm.png');
                    $hasLogo = file_exists($logoPath);
                    
                    $builder = Builder::create()
                        ->writer(new PngWriter())
                        ->data($qr->qr_link)
                        ->encoding(new Encoding('UTF-8'))
                        ->errorCorrectionLevel(ErrorCorrectionLevel::High)
                        ->size(400)
                        ->margin(10)
                        ->foregroundColor(0, 0, 128); // #000080 - Navy blue
                    
                    // Add logo if available
                    if ($hasLogo) {
                        $builder = $builder->logoPath($logoPath)
                            ->logoResizeToWidth(80); // Resize logo to 80px width
                    }
                    
                    $result = $builder->build();
                    
                    // Save to file
                    $result->saveToFile($qrCodePath);
                    
                } catch (\Exception $e) {
                    \Log::error('QR Code generation failed for QR ID ' . $qr->id . ': ' . $e->getMessage());
                    $qrCodePath = null;
                }
            }
            
            // Prepare booking details
            $bookingDetails = null;
            if ($qr->booking) {
                $b = $qr->booking;
                $bookingDetails = [
                    'id' => $b->id,
                    'customer' => $b->user ? $b->user->firstname . ' ' . $b->user->lastname : 'N/A',
                    'mobile' => $b->user?->mobile ?? 'N/A',
                    'property_type' => $b->propertyType?->name ?? 'N/A',
                    'property_sub_type' => $b->propertySubType?->name ?? 'N/A',
                    'bhk' => $b->bhk?->name ?? 'N/A',
                    'city' => $b->city?->name ?? 'N/A',
                    'state' => $b->state?->name ?? 'N/A',
                    'area' => $b->area ? number_format($b->area) . ' sq.ft' : 'N/A',
                    'price' => $b->price ? '₹ ' . number_format($b->price) : 'N/A',
                    'booking_date' => optional($b->booking_date)->format('d M Y') ?? 'N/A',
                    'address' => $b->full_address ?? 'N/A',
                    'pin_code' => $b->pin_code ?? 'N/A',
                    'status' => $b->status ?? 'N/A',
                ];
            }
            
            // Generate PDF
            $pdf = Pdf::loadView('admin.qr.pdf', [
                'qr' => $qr,
                'qrCodePath' => $qrCodePath,
                'bookingDetails' => $bookingDetails,
                'generatedAt' => now()
            ]);
            
            // Set paper size and orientation
            $pdf->setPaper('A4', 'portrait');
            
            // Generate PDF output
            $output = $pdf->download('QR-' . $qr->code . '.pdf');
            
            // Clean up temporary QR code file
            if ($qrCodePath && file_exists($qrCodePath)) {
                @unlink($qrCodePath);
            }
            
            return $output;
            
        } catch (\Exception $e) {
            \Log::error('PDF Download Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $qr = QR::findOrFail($id);
        
        // Clear tour_code from booking if QR is assigned
        if ($qr->booking_id) {
            $booking = Booking::find($qr->booking_id);
            if ($booking && $booking->tour_code === $qr->code) {
                $booking->tour_code = null;
                $booking->save();
            }
        }
        
        $qr->delete();
        activity()->performedOn($qr)->causedBy(Auth::user())->log('Deleted QR code');
        return redirect()->route('admin.qr.index')->with('success', 'QR code deleted successfully.');
    }

    private function generateUniqueCode()
    {
        do {
            $code = $this->generateRandomCode();
        } while (QR::where('code', $code)->exists());
        return $code;
    }

    /**
     * Generate a random 8-character code (A-Za-z0-9)
     */
    private function generateRandomCode()
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $code = '';
        for ($i = 0; $i < 8; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $code;
    }

    /**
     * Generate a random 8-character name (A-Za-z0-9)
     */
    private function generateRandomName()
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $name = '';
        for ($i = 0; $i < 8; $i++) {
            $name .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $name;
    }

    /**
     * Bulk generate QR codes
     */
    public function bulkGenerate(Request $request)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:1000',
        ], [
            'quantity.required' => 'Quantity is required.',
            'quantity.integer' => 'Quantity must be a number.',
            'quantity.min' => 'Quantity must be at least 1.',
            'quantity.max' => 'Quantity cannot exceed 1000.',
        ]);

        try {
            $quantity = $request->input('quantity');
            $created = 0;
            $userId = Auth::id();

            for ($i = 0; $i < $quantity; $i++) {
                // Generate unique code (required)
                $code = $this->generateUniqueCode();

                // Create QR code with only code (name is null)
                $qr = QR::create([
                    'code' => $code,
                    'name' => null, // Name is null for bulk generated QR codes
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);

                activity()->performedOn($qr)->causedBy(Auth::user())->log('Bulk created QR code');
                $created++;
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully generated {$created} QR code(s).",
                'count' => $created,
            ]);

        } catch (\Exception $e) {
            \Log::error('Bulk QR Generation Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate QR codes: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk delete QR codes
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer|exists:qr_code,id',
        ], [
            'ids.required' => 'Please select at least one QR code to delete.',
            'ids.array' => 'Invalid request format.',
            'ids.*.integer' => 'Invalid QR code ID.',
            'ids.*.exists' => 'One or more selected QR codes do not exist.',
        ]);

        try {
            $ids = $request->input('ids');
            $deleted = 0;
            $skipped = 0;
            $skippedCodes = [];
            $userId = Auth::id();

            foreach ($ids as $id) {
                $qr = QR::find($id);
                if ($qr) {
                    // Check if QR code is assigned to a booking
                    if ($qr->booking_id) {
                        $skipped++;
                        $skippedCodes[] = [
                            'code' => $qr->code,
                            'booking_id' => $qr->booking_id
                        ];
                        continue; // Skip this QR code
                    }
                    
                    // Log activity before deletion
                    activity()->performedOn($qr)->causedBy(Auth::user())->log('Bulk deleted QR code');
                    $qr->delete();
                    $deleted++;
                }
            }

            // Build response message
            $message = '';
            if ($deleted > 0 && $skipped == 0) {
                $message = "Successfully deleted {$deleted} QR code(s).";
            } elseif ($deleted > 0 && $skipped > 0) {
                $message = "Successfully deleted {$deleted} QR code(s).\n\n";
                $message .= "⚠️ {$skipped} QR code(s) could not be deleted because they are assigned to bookings.";
                if (count($skippedCodes) <= 5) {
                    $codesList = implode(', ', array_column($skippedCodes, 'code'));
                    $message .= "\n\nAssigned QR Codes: {$codesList}";
                } else {
                    $message .= "\n\n(" . count($skippedCodes) . " QR codes are assigned to bookings)";
                }
                $message .= "\n\nPlease unassign them from bookings first if you want to delete them.";
            } elseif ($deleted == 0 && $skipped > 0) {
                $message = "❌ Cannot delete selected QR code(s).\n\n";
                $message .= "All {$skipped} selected QR code(s) are assigned to bookings and cannot be deleted.";
                if (count($skippedCodes) <= 5) {
                    $codesList = implode(', ', array_column($skippedCodes, 'code'));
                    $message .= "\n\nAssigned QR Codes: {$codesList}";
                }
                $message .= "\n\nPlease unassign them from bookings first if you want to delete them.";
            } else {
                $message = "No QR codes were deleted.";
            }

            return response()->json([
                'success' => $deleted > 0,
                'message' => $message,
                'deleted' => $deleted,
                'skipped' => $skipped,
                'skipped_codes' => $skippedCodes,
            ]);

        } catch (\Exception $e) {
            \Log::error('Bulk QR Delete Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete QR codes: ' . $e->getMessage(),
            ], 500);
        }
    }
}
