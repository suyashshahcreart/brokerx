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
                    'booking',
                    'booking.assignees.user',
                    'photographer',
                    'job',
                    'tour'
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

                \Log::info('Query built, total visits: ' . $query->count());

                return DataTables::of($query)
                    ->addColumn('photographer_name', function (PhotographerVisit $visit) {
                        return $visit->photographer
                            ? $visit->photographer->firstname . ' ' . $visit->photographer->lastname
                            : '-';
                    })
                    ->addColumn('booking_info', function (PhotographerVisit $visit) {
                        if ($visit->booking) {
                            $location = $visit->booking->society_name ?? $visit->booking->address_area ?? ($visit->booking->city ? $visit->booking->city->name : '');
                            return '<strong>#' . $visit->booking->id . '</strong>' . 
                                   ($location ? '<br><small class=\"text-muted\">' . $location . '</small>' : '');
                        }
                        return '-';
                    })
                    ->editColumn('visit_date', function (PhotographerVisit $visit) {
                        return $visit->visit_date ? $visit->visit_date->format('d M Y') . '<br><small class=\"text-muted\">' . $visit->visit_date->format('h:i A') . '</small>' : '-';
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
                        $statusText = ucwords(str_replace('_', ' ', $visit->status));
                        return '<span class="badge bg-' . $color . '">' . $statusText . '</span>';
                    })
                    ->addColumn('actions', function (PhotographerVisit $visit) {
                        $actions = '<div class=\"btn-group\" role=\"group\">';

                        // View button
                        $view = route('admin.photographer-visits.show', $visit);
                        $actions .= '<a href="' . $view . '" class="btn btn-sm btn-primary" title="View Details"><i class="ri-eye-line"></i></a>';

                        // Check-out button (only for checked_in visits that have a job_id)
                        if ($visit->status === 'checked_in' && $visit->job_id) {
                            $checkoutUrl = route('admin.photographer-visit-jobs.check-out-form', $visit->job_id);
                            $actions .= ' <a href="' . $checkoutUrl . '" class="btn btn-sm btn-warning" title="Check Out"><i class="ri-logout-circle-line"></i></a>';
                        }

                        // Delete button (only for pending visits)
                        if ($visit->status === 'pending') {
                            $delete = route('admin.photographer-visits.destroy', $visit);
                            $csrf = csrf_field();
                            $method = method_field('DELETE');
                            $actions .= ' <form action="' . $delete . '" method="POST" class="d-inline" onsubmit="return confirm(\'Are you sure you want to delete this visit?\');">' . $csrf . $method .
                                '<button type="submit" class="btn btn-sm btn-danger" title="Delete"><i class="ri-delete-bin-line"></i></button></form>';
                        }

                        $actions .= '</div>';
                        return $actions;
                    })
                    ->addColumn('check_actions', function (PhotographerVisit $visit) {
                        if (!$visit->booking || !$visit->booking->assignees || $visit->booking->assignees->isEmpty()) {
                            return '<span class="text-muted small">No assignee</span>';
                        }

                        $assignee = $visit->booking->assignees->first();
                        $actions = '<div class="btn-group" role="group">';

                        // Check if there's a completed visit for this booking
                        $completedVisit = PhotographerVisit::where('booking_id', $visit->booking_id)
                            ->where('status', 'completed')
                            ->exists();

                        if ($completedVisit) {
                            // Show TOUR COMPLETE badge
                            $actions .= '<span class="badge bg-success"><i class="ri-check-double-line me-1"></i>Tour Complete</span>';
                        } else {
                            // Check if there's an active checked-in visit for this booking
                            $activeCheckedIn = PhotographerVisit::where('booking_id', $visit->booking_id)
                                ->where('status', 'checked_in')
                                ->exists();

                            if ($activeCheckedIn) {
                                // Show CHECK-OUT button
                                $checkoutUrl = route('admin.booking-assignees.check-out-form', $assignee);
                                $actions .= '<a href="' . $checkoutUrl . '" class="btn btn-sm btn-warning" title="Check Out"><i class="ri-logout-circle-line me-1"></i>Check Out</a>';
                            } else {
                                // Show CHECK-IN button
                                $checkinUrl = route('admin.booking-assignees.check-in-form', $assignee);
                                $actions .= '<a href="' . $checkinUrl . '" class="btn btn-sm btn-success" title="Check In"><i class="ri-login-circle-line me-1"></i>Check In</a>';
                            }
                        }

                        $actions .= '</div>';
                        return $actions;
                    })
                    ->rawColumns(['booking_info', 'visit_date', 'status', 'actions', 'check_actions'])
                    ->only(['id', 'photographer_name', 'booking_info', 'visit_date', 'status', 'actions', 'check_actions'])
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
            'job_id' => 'nullable|exists:photographer_visit_jobs,id',
            'booking_id' => 'required|exists:bookings,id',
            'tour_id' => 'nullable|exists:tours,id',
            'photographer_id' => 'required|exists:users,id',
            'visit_date' => 'required|date',
            'status' => 'required|in:pending,checked_in,checked_out,completed,cancelled',
            'cancel_reason' => 'nullable|string|max:1000',
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
                'job_id' => $request->job_id,
                'booking_id' => $request->booking_id,
                'tour_id' => $request->tour_id,
                'photographer_id' => $request->photographer_id,
                'visit_date' => $request->visit_date,
                'status' => $request->status,
                'cancel_reason' => $request->cancel_reason,
                'notes' => $request->notes,
                'metadata' => $request->metadata ?? [],
                'created_by' => auth()->id(),
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
            'job',
            'tour'
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
            'job_id' => 'nullable|exists:photographer_visit_jobs,id',
            'booking_id' => 'required|exists:bookings,id',
            'tour_id' => 'nullable|exists:tours,id',
            'photographer_id' => 'required|exists:users,id',
            'visit_date' => 'required|date',
            'status' => 'required|in:pending,checked_in,checked_out,completed,cancelled',
            'cancel_reason' => 'nullable|string|max:1000',
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
                'job_id' => $request->job_id,
                'booking_id' => $request->booking_id,
                'tour_id' => $request->tour_id,
                'photographer_id' => $request->photographer_id,
                'visit_date' => $request->visit_date,
                'status' => $request->status,
                'cancel_reason' => $request->cancel_reason,
                'notes' => $request->notes,
                'metadata' => $request->metadata ?? $photographerVisit->metadata,
                'updated_by' => auth()->id(),
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
            if ($photographerVisit->check_in_photo) {
                Storage::disk('public')->delete($photographerVisit->check_in_photo);
            }

            // Delete associated check-out photo if exists
            if ($photographerVisit->check_out_photo) {
                Storage::disk('public')->delete($photographerVisit->check_out_photo);
            }

            $photographerVisit->deleted_by = auth()->id();
            $photographerVisit->save();
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
