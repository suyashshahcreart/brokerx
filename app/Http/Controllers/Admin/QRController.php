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
        if ($request->ajax()) {
            $data = QR::with(['booking', 'creator'])->select('qr_code.*');
            return DataTables::of($data)
                ->addColumn('actions', function ($row) {
                    $editUrl = route('admin.qr.edit', $row->id);
                    $showUrl = route('admin.qr.show', $row->id);
                    $deleteUrl = route('admin.qr.destroy', $row->id);
                    $csrf = csrf_field();
                    $method = method_field('DELETE');
                    $actions = '<div class="btn-group" role="group">';
                    $actions .= '<a href="' . $showUrl . '" class="btn btn-light btn-sm border me-1" title="View"><i class="ri-eye-line"></i></a>';
                    $actions .= '<a href="' . $editUrl . '" class="btn btn-soft-primary btn-sm border me-1" title="Edit"><i class="ri-edit-line"></i></a>';
                    $actions .= '<form action="' . $deleteUrl . '" method="POST" class="d-inline">' . $csrf . $method .
                        '<button type="submit" class="btn btn-soft-danger btn-sm border" onclick="return confirm(\'Delete this QR code?\')" title="Delete"><i class="ri-delete-bin-line"></i></button></form>';
                    $actions .= '</div>';
                    return $actions;
                })
                ->addColumn('qr_code_svg', function ($row) {
                    try {
                        if ($row->qr_link) {
                            return QrCode::size(300)->generate($row->qr_link);
                        }else{
                            return QrCode::size(300)->generate('https://github.com/deepeshsuryawanshi');
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
                ->rawColumns(['actions', 'image', 'qr_code_svg'])
                ->make(true);
        }
        return view('admin.qr.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $bookings = Booking::all();
        return view('admin.qr.create', compact('bookings'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'booking_id' => 'nullable|exists:bookings,id',
            'image' => 'nullable|image|max:2048',
            'qr_link' => 'nullable|string|max:255',
        ]);

        $code = $this->generateUniqueCode();
        $data = $request->only(['name', 'booking_id', 'qr_link']);
        $data['code'] = $code;
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('qr_images', 'public');
        }

        $qr = QR::create($data);
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
        $bookings = Booking::all();
        return view('admin.qr.edit', compact('qr', 'bookings'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $qr = QR::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'booking_id' => 'nullable|exists:bookings,id',
            'image' => 'nullable|image|max:2048',
            'qr_link' => 'nullable|string|max:255',
        ]);
        $data = $request->only(['name', 'booking_id', 'qr_link']);
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
                    $result = Builder::create()
                        ->writer(new PngWriter())
                        ->data($qr->qr_link)
                        ->encoding(new Encoding('UTF-8'))
                        ->errorCorrectionLevel(ErrorCorrectionLevel::High)
                        ->size(400)
                        ->margin(10)
                        ->build();
                    
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
        $qr->delete();
        activity()->performedOn($qr)->causedBy(Auth::user())->log('Deleted QR code');
        return redirect()->route('admin.qr.index')->with('success', 'QR code deleted successfully.');
    }

    private function generateUniqueCode()
    {
        do {
            $code = Str::random(9);
        } while (QR::where('code', $code)->exists());
        return $code;
    }
}
