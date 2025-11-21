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
