<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PhotographerVisit;
use App\Models\PhotographerCheckIn;
use App\Models\PhotographerCheckOut;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PhotographerVisitController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:photographer_visit_view')->only(['index', 'show']);
        $this->middleware('permission:photographer_visit_create')->only(['create', 'store']);
        $this->middleware('permission:photographer_visit_edit')->only(['edit', 'update']);
        $this->middleware('permission:photographer_visit_delete')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = PhotographerVisit::with([
                'photographer',
                'booking',
                'checkIn',
                'checkOut'
            ])->orderBy('created_at', 'desc');

            // Apply filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('photographer_id')) {
                $query->where('photographer_id', $request->photographer_id);
            }

            if ($request->filled('booking_id')) {
                $query->where('booking_id', $request->booking_id);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('visit_date', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('visit_date', '<=', $request->date_to);
            }

            return \Yajra\DataTables\Facades\DataTables::of($query)
                ->addColumn('photographer_name', function (PhotographerVisit $visit) {
                    return $visit->photographer 
                        ? $visit->photographer->firstname . ' ' . $visit->photographer->lastname 
                        : '-';
                })
                ->addColumn('booking_info', function (PhotographerVisit $visit) {
                    if ($visit->booking) {
                        return '#' . $visit->booking->id . '<div class="text-muted small">' 
                            . ($visit->booking->society_name ?? $visit->booking->address_area) 
                            . '</div>';
                    }
                    return '-';
                })
                ->editColumn('visit_date', fn(PhotographerVisit $visit) => 
                    optional($visit->visit_date)->format('d M Y, h:i A') ?? '-'
                )
                ->editColumn('status', function (PhotographerVisit $visit) {
                    $badges = [
                        'pending' => 'secondary',
                        'checked_in' => 'info',
                        'checked_out' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger'
                    ];
                    $color = $badges[$visit->status] ?? 'secondary';
                    return '<span class="badge bg-' . $color . ' text-uppercase">' . $visit->status . '</span>';
                })
                ->addColumn('check_status', function (PhotographerVisit $visit) {
                    $html = '';
                    if ($visit->checkIn) {
                        $html .= '<span class="badge bg-success me-1" title="Checked In"><i class="ri-login-circle-line"></i></span>';
                    }
                    if ($visit->checkOut) {
                        $html .= '<span class="badge bg-warning" title="Checked Out"><i class="ri-logout-circle-line"></i></span>';
                    }
                    return $html ?: '-';
                })
                ->addColumn('duration', function (PhotographerVisit $visit) {
                    $duration = $visit->getDuration();
                    if ($duration) {
                        $hours = floor($duration / 60);
                        $minutes = $duration % 60;
                        return $hours > 0 ? "{$hours}h {$minutes}m" : "{$minutes}m";
                    }
                    return '-';
                })
                ->addColumn('actions', function (PhotographerVisit $visit) {
                    $view = route('admin.photographer-visits.show', $visit);
                    $edit = route('admin.photographer-visits.edit', $visit);
                    $delete = route('admin.photographer-visits.destroy', $visit);
                    $csrf = csrf_field();
                    $method = method_field('DELETE');

                    return '<a href="' . $view . '" class="btn btn-light btn-sm border" title="View"><i class="ri-eye-line"></i></a>' .
                        ' <a href="' . $edit . '" class="btn btn-soft-primary btn-sm border" title="Edit"><i class="ri-edit-line"></i></a>' .
                        ' <form action="' . $delete . '" method="POST" class="d-inline">' . $csrf . $method .
                        '<button type="submit" class="btn btn-soft-danger btn-sm border" onclick="return confirm(\'Delete this visit?\')"><i class="ri-delete-bin-line"></i></button></form>';
                })
                ->rawColumns(['booking_info', 'status', 'check_status', 'actions'])
                ->toJson();
        }

        $photographers = User::role('photographer')->get();
        $bookings = Booking::orderBy('created_at', 'desc')->limit(100)->get();
        
        $canCreate = $request->user()->can('photographer_visit_create');
        $canEdit = $request->user()->can('photographer_visit_edit');
        $canDelete = $request->user()->can('photographer_visit_delete');

        return view('admin.photographer-visits.index', compact(
            'photographers',
            'bookings',
            'canCreate',
            'canEdit',
            'canDelete'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $photographers = User::role('photographer')->get();
        $bookings = Booking::with(['city', 'state'])
            ->where('status', '!=', 'cancelled')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.photographer-visits.create', compact('photographers', 'bookings'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|exists:bookings,id',
            'photographer_id' => 'required|exists:users,id',
            'visit_date' => 'required|date',
            'status' => 'required|in:pending,checked_in,checked_out,completed,cancelled',
            'notes' => 'nullable|string|max:1000',
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $visit = PhotographerVisit::create([
                'booking_id' => $request->booking_id,
                'tour_id' => $request->tour_id,
                'photographer_id' => $request->photographer_id,
                'visit_date' => $request->visit_date,
                'status' => $request->status,
                'notes' => $request->notes,
                'metadata' => $request->metadata ?? [],
            ]);

            DB::commit();

            return redirect()
                ->route('admin.photographer-visits.show', $visit)
                ->with('success', 'Photographer visit created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to create photographer visit: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PhotographerVisit $photographerVisit)
    {
        $photographerVisit->load([
            'photographer',
            'booking.city',
            'booking.state',
            'booking.propertyType',
            'booking.propertySubType',
            'checkIn',
            'checkOut'
        ]);

        return view('admin.photographer-visits.show', compact('photographerVisit'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PhotographerVisit $photographerVisit)
    {
        $photographers = User::role('photographer')->get();
        $bookings = Booking::with(['city', 'state'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.photographer-visits.edit', compact('photographerVisit', 'photographers', 'bookings'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PhotographerVisit $photographerVisit)
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|exists:bookings,id',
            'photographer_id' => 'required|exists:users,id',
            'visit_date' => 'required|date',
            'status' => 'required|in:pending,checked_in,checked_out,completed,cancelled',
            'notes' => 'nullable|string|max:1000',
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $photographerVisit->update([
                'booking_id' => $request->booking_id,
                'tour_id' => $request->tour_id,
                'photographer_id' => $request->photographer_id,
                'visit_date' => $request->visit_date,
                'status' => $request->status,
                'notes' => $request->notes,
                'metadata' => $request->metadata ?? $photographerVisit->metadata,
            ]);

            DB::commit();

            return redirect()
                ->route('admin.photographer-visits.show', $photographerVisit)
                ->with('success', 'Photographer visit updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update photographer visit: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PhotographerVisit $photographerVisit)
    {
        try {
            DB::beginTransaction();

            // Delete associated check-in photo if exists
            if ($photographerVisit->checkIn && $photographerVisit->checkIn->photo) {
                Storage::disk('public')->delete($photographerVisit->checkIn->photo);
            }

            // Delete associated check-out photo if exists
            if ($photographerVisit->checkOut && $photographerVisit->checkOut->photo) {
                Storage::disk('public')->delete($photographerVisit->checkOut->photo);
            }

            $photographerVisit->delete();

            DB::commit();

            return redirect()
                ->route('admin.photographer-visits.index')
                ->with('success', 'Photographer visit deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to delete photographer visit: ' . $e->getMessage());
        }
    }

    /**
     * Check in photographer
     */
    public function checkIn(Request $request, PhotographerVisit $photographerVisit)
    {
        $validator = Validator::make($request->all(), [
            'photo' => 'nullable|image|max:5120',
            'location' => 'nullable|string',
            'remarks' => 'nullable|string|max:500',
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            if ($photographerVisit->checkIn) {
                return response()->json(['error' => 'Already checked in'], 400);
            }

            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('photographer-checkins', 'public');
            }

            $checkIn = PhotographerCheckIn::create([
                'visit_id' => $photographerVisit->id,
                'photo' => $photoPath,
                'location' => $request->location,
                'ip_address' => $request->ip(),
                'device_info' => $request->userAgent(),
                'remarks' => $request->remarks,
                'metadata' => $request->metadata ?? [],
                'checked_in_at' => now(),
            ]);

            $photographerVisit->update([
                'check_in_id' => $checkIn->id,
                'status' => 'checked_in',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Checked in successfully',
                'data' => $checkIn
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Check out photographer
     */
    public function checkOut(Request $request, PhotographerVisit $photographerVisit)
    {
        $validator = Validator::make($request->all(), [
            'photo' => 'nullable|image|max:5120',
            'location' => 'nullable|string',
            'remarks' => 'nullable|string|max:500',
            'photos_taken' => 'nullable|integer|min:0',
            'work_summary' => 'nullable|string|max:1000',
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            if (!$photographerVisit->checkIn) {
                return response()->json(['error' => 'Must check in first'], 400);
            }

            if ($photographerVisit->checkOut) {
                return response()->json(['error' => 'Already checked out'], 400);
            }

            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('photographer-checkouts', 'public');
            }

            $checkOut = PhotographerCheckOut::create([
                'visit_id' => $photographerVisit->id,
                'photo' => $photoPath,
                'location' => $request->location,
                'ip_address' => $request->ip(),
                'device_info' => $request->userAgent(),
                'remarks' => $request->remarks,
                'photos_taken' => $request->photos_taken ?? 0,
                'work_summary' => $request->work_summary,
                'metadata' => $request->metadata ?? [],
                'checked_out_at' => now(),
            ]);

            $photographerVisit->update([
                'check_out_id' => $checkOut->id,
                'status' => 'completed',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Checked out successfully',
                'data' => $checkOut
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
