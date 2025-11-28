<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PhotographerVisit;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

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
            try {
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

                return DataTables::of($query)
                    ->addColumn('photographer_name', function (PhotographerVisit $visit) {
                        return $visit->photographer
                            ? $visit->photographer->firstname . ' ' . $visit->photographer->lastname
                            : '-';
                    })
                    ->addColumn('booking_info', function (PhotographerVisit $visit) {
                        if ($visit->booking) {
                            return '#' . $visit->booking->id . '<div class="text-muted small">'
                                . ($visit->booking->society_name ?? $visit->booking->address_area ?? '')
                                . '</div>';
                        }
                        return '-';
                    })
                    ->editColumn('visit_date', function (PhotographerVisit $visit) {
                        return optional($visit->visit_date)->format('d M Y, h:i A') ?? '-';
                    })
                    ->editColumn('status', function (PhotographerVisit $visit) {
                        $badges = [
                            'pending' => 'secondary',
                            'checked_in' => 'info',
                            'checked_out' => 'warning',
                            'completed' => 'success',
                            'cancelled' => 'danger'
                        ];
                        $color = $badges[$visit->status] ?? 'secondary';
                        return '<span class="badge bg-' . $color . ' text-uppercase">' . str_replace('_', ' ', $visit->status) . '</span>';
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
                        $actions = '<div class="btn-group" role="group">';

                        // View button
                        $view = route('admin.photographer-visits.show', $visit);
                        $actions .= '<a href="' . $view . '" class="btn btn-light btn-sm border" title="View" data-bs-toggle="tooltip"><i class="ri-eye-line"></i></a>';

                        // Delete button (only for pending visits)
                        if ($visit->status === 'pending') {
                            $delete = route('admin.photographer-visits.destroy', $visit);
                            $csrf = csrf_field();
                            $method = method_field('DELETE');
                            $actions .= ' <form action="' . $delete . '" method="POST" class="d-inline">' . $csrf . $method .
                                '<button type="submit" class="btn btn-soft-danger btn-sm border" onclick="return confirm(\'Delete this visit?\')" title="Delete" data-bs-toggle="tooltip"><i class="ri-delete-bin-line"></i></button></form>';
                        }

                        $actions .= '</div>';
                        return $actions;
                    })
                    ->rawColumns(['booking_info', 'status', 'check_status', 'actions'])
                    ->make(true);
            } catch (\Exception $e) {
                \Log::error('DataTables Error in PhotographerVisitController', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json(['error' => $e->getMessage()], 500);
            }
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

}
