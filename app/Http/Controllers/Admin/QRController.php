<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\QR;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;

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
                ->addColumn('actions', function($row) {
                    $editUrl = route('admin.qr.edit', $row->id);
                    $showUrl = route('admin.qr.show', $row->id);
                    $deleteUrl = route('admin.qr.destroy', $row->id);
                    return '<a href="'.$showUrl.'" class="btn btn-info btn-sm">View</a> '
                        .'<a href="'.$editUrl.'" class="btn btn-warning btn-sm">Edit</a> '
                        .'<form action="'.$deleteUrl.'" method="POST" style="display:inline-block;">'
                        .csrf_field().method_field('DELETE')
                        .'<button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\'Delete this QR code?\')">Delete</button></form>';
                })
                ->editColumn('image', function($row) {
                    return $row->image ? '<img src="/storage/'.$row->image.'" width="50"/>' : '';
                })
                ->editColumn('created_by', function($row) {
                    return $row->creator ? $row->creator->firstname.' '.$row->creator->lastname : '';
                })
                ->rawColumns(['actions', 'image'])
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
