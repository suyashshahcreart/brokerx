<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PhotographerVisitJob;
use App\Models\User;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PhotographerVisitJobController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:photographer_visit_view', ['only' => ['index', 'show']]);
        $this->middleware('permission:photographer_visit_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:photographer_visit_edit', ['only' => ['edit', 'update', 'assign']]);
        $this->middleware('permission:photographer_visit_delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of jobs
     */
    public function index(Request $request)
    {
        // Return statistics if requested
        if ($request->get('get_stats')) {
            $stats = [
                'pending' => PhotographerVisitJob::where('status', 'pending')->count(),
                'assigned' => PhotographerVisitJob::where('status', 'assigned')->count(),
                'in_progress' => PhotographerVisitJob::where('status', 'in_progress')->count(),
                'completed' => PhotographerVisitJob::where('status', 'completed')->count(),
            ];
            return response()->json(['stats' => $stats]);
        }

        if ($request->ajax()) {
            $query = PhotographerVisitJob::with(['booking.propertyType', 'photographer']);

            // Apply filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('priority')) {
                $query->where('priority', $request->priority);
            }

            if ($request->filled('photographer_id')) {
                $query->where('photographer_id', $request->photographer_id);
            }

            if ($request->filled('scheduled_date')) {
                $query->whereDate('scheduled_date', $request->scheduled_date);
            }

            return DataTables::of($query)
                ->addColumn('job_code', function ($job) {
                    $overdue = $job->isOverdue() ? '<span class="badge bg-danger ms-1">Overdue</span>' : '';
                    return '<strong>' . $job->job_code . '</strong>' . $overdue;
                })
                ->addColumn('booking', function ($job) {
                    return '#' . $job->booking_id . '<br><small class="text-muted">' . ($job->booking->propertyType?->name ?? 'N/A') . '</small>';
                })
                ->addColumn('photographer', function ($job) {
                    return $job->photographer?->name ?? '<span class="badge bg-secondary">Unassigned</span>';
                })
                ->addColumn('scheduled_date', function ($job) {
                    return $job->scheduled_date ? $job->scheduled_date->format('d M Y') . '<br><small class="text-muted">' . $job->scheduled_date->format('h:i A') . '</small>' : '<span class="text-muted">Not scheduled</span>';
                })
                ->addColumn('priority', function ($job) {
                    return '<span class="badge bg-' . $job->priority_color . '">' . ucfirst($job->priority) . '</span>';
                })
                ->addColumn('status', function ($job) {
                    return '<span class="badge bg-' . $job->status_color . '">' . ucfirst(str_replace('_', ' ', $job->status)) . '</span>';
                })
                ->addColumn('checkin_button', function ($job) {
                    $status = strtolower($job->status ?? '');
                    if ($status === 'pending') {
                        $url = route('admin.photographer-visit-jobs.check-in-form', $job);
                        return '<a href="' . $url . '" class="btn btn-soft-success btn-sm border" title="Check In" data-bs-toggle="tooltip"><i class="ri-login-circle-line"></i></a>';
                    }
                    return '<span class="text-muted">-</span>';
                })
                ->addColumn('action', function ($job) {
                    $status = strtolower($job->status);
                    $actions = '<div class="btn-group" role="group">';

                    // View button
                    $actions .= '<a href="' . route('admin.photographer-visit-jobs.show', $job) . '" class="btn btn-light btn-sm border" title="View" data-bs-toggle="tooltip"><i class="ri-eye-line"></i></a>';

                    // Edit button (only for pending/assigned jobs)
                    if (in_array($status, ['pending', 'assigned'])) {
                        $actions .= '<a href="' . route('admin.photographer-visit-jobs.edit', $job) . '" class="btn btn-soft-primary btn-sm border" title="Edit" data-bs-toggle="tooltip"><i class="ri-edit-line"></i></a>';
                    }

                    // Check-in button (only for assigned jobs)
                    if ($status === 'assigned') {
                        $actions .= '<a href="' . route('admin.photographer-visit-jobs.check-in-form', $job) . '" class="btn btn-soft-success btn-sm border" title="Check In" data-bs-toggle="tooltip"><i class="ri-login-circle-line"></i></a>';
                    }

                    // Check-out button (only for in_progress jobs)
                    if ($status === 'in_progress') {
                        $actions .= '<a href="' . route('admin.photographer-visit-jobs.check-out-form', $job) . '" class="btn btn-soft-warning btn-sm border" title="Check Out" data-bs-toggle="tooltip"><i class="ri-logout-circle-line"></i></a>';
                    }

                    // Delete button (only for pending/assigned jobs)
                    if (in_array($status, ['pending', 'assigned'])) {
                        $actions .= '<button type="button" class="btn btn-soft-danger btn-sm border delete-btn" data-id="' . $job->id . '" data-code="' . htmlspecialchars($job->job_code, ENT_QUOTES) . '" title="Delete" data-bs-toggle="tooltip"><i class="ri-delete-bin-line"></i></button>';
                    }

                    $actions .= '</div>';
                    return $actions;
                })
                ->filterColumn('job_code', function($query, $keyword) {
                    $query->where('job_code', 'like', "%{$keyword}%");
                })
                ->filterColumn('booking', function($query, $keyword) {
                    $query->where('booking_id', 'like', "%{$keyword}%");
                })
                ->filterColumn('photographer', function($query, $keyword) {
                    $query->whereHas('photographer', function($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->rawColumns(['job_code', 'booking', 'photographer', 'scheduled_date', 'priority', 'status', 'checkin_button', 'action'])
                ->make(true);
        }

        $photographers = User::role('photographer')->get();
        return view('admin.photographer-visit-jobs.index', compact('photographers'));
    }

    /**
     * Show the form for creating a new job
     */
    public function create()
    {
        $bookings = Booking::with('propertyType')->whereNotNull('id')->get();
        $photographers = User::role('photographer')->get();
        
        return view('admin.photographer-visit-jobs.create', compact('bookings', 'photographers'));
    }

    /**
     * Store a newly created job
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'tour_id' => 'nullable|exists:tours,id',
            'photographer_id' => 'nullable|exists:users,id',
            'priority' => 'required|in:low,normal,high,urgent',
            'scheduled_date' => 'required|date',
            'instructions' => 'nullable|string',
            'special_requirements' => 'nullable|string',
            'estimated_duration' => 'nullable|integer|min:1',
        ]);

        $validated['status'] = empty($validated['photographer_id']) ? 'pending' : 'assigned';
        $validated['created_by'] = auth()->id();
        
        if (!empty($validated['photographer_id'])) {
            $validated['assigned_at'] = now();
            $validated['assigned_by'] = auth()->id();
        }

        $job = PhotographerVisitJob::create($validated);

        activity('photographer_visit_jobs')
            ->performedOn($job)
            ->causedBy(auth()->user())
            ->withProperties(['event' => 'created'])
            ->log('Photographer visit job created');

        return redirect()->route('admin.photographer-visit-jobs.index')
            ->with('success', 'Photographer visit job created successfully.');
    }

    /**
     * Display the specified job
     */
    public function show(PhotographerVisitJob $photographerVisitJob)
    {
        $photographerVisitJob->load(['booking.user', 'booking.propertyType', 'tour', 'photographer', 'visits']);
        return view('admin.photographer-visit-jobs.show', compact('photographerVisitJob'));
    }

    /**
     * Show the form for editing the specified job
     */
    public function edit(PhotographerVisitJob $photographerVisitJob)
    {
        $bookings = Booking::with('propertyType')->get();
        $photographers = User::role('photographer')->get();
        
        return view('admin.photographer-visit-jobs.edit', compact('photographerVisitJob', 'bookings', 'photographers'));
    }

    /**
     * Update the specified job
     */
    public function update(Request $request, PhotographerVisitJob $photographerVisitJob)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'tour_id' => 'nullable|exists:tours,id',
            'photographer_id' => 'nullable|exists:users,id',
            'priority' => 'required|in:low,normal,high,urgent',
            'status' => 'required|in:pending,assigned,in_progress,completed,cancelled',
            'scheduled_date' => 'required|date',
            'instructions' => 'nullable|string',
            'special_requirements' => 'nullable|string',
            'estimated_duration' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
            'cancellation_reason' => 'required_if:status,cancelled|nullable|string',
        ]);

        // Handle status changes
        $oldStatus = $photographerVisitJob->status;
        $newStatus = $validated['status'];

        // Check if photographer was just assigned
        if (!empty($validated['photographer_id']) && $photographerVisitJob->photographer_id !== $validated['photographer_id']) {
            $validated['assigned_at'] = now();
            $validated['assigned_by'] = auth()->id();
        }

        // Handle status transitions
        if ($oldStatus !== $newStatus) {
            if ($newStatus === 'assigned' && !$photographerVisitJob->assigned_at) {
                $validated['assigned_at'] = now();
                $validated['assigned_by'] = auth()->id();
            } elseif ($newStatus === 'in_progress' && !$photographerVisitJob->started_at) {
                $validated['started_at'] = now();
            } elseif ($newStatus === 'completed' && !$photographerVisitJob->completed_at) {
                $validated['completed_at'] = now();
            }
        }

        $validated['updated_by'] = auth()->id();

        $photographerVisitJob->update($validated);

        activity('photographer_visit_jobs')
            ->performedOn($photographerVisitJob)
            ->causedBy(auth()->user())
            ->withProperties(['event' => 'updated', 'old_status' => $oldStatus, 'new_status' => $newStatus])
            ->log('Photographer visit job updated');

        return redirect()->route('admin.photographer-visit-jobs.show', $photographerVisitJob)
            ->with('success', 'Photographer visit job updated successfully.');
    }

    /**
     * Assign photographer to job
     */
    public function assign(Request $request, PhotographerVisitJob $photographerVisitJob)
    {
        $validated = $request->validate([
            'photographer_id' => 'required|exists:users,id',
        ]);

        $photographerVisitJob->assignPhotographer($validated['photographer_id'], auth()->id());

        activity('photographer_visit_jobs')
            ->performedOn($photographerVisitJob)
            ->causedBy(auth()->user())
            ->withProperties(['event' => 'assigned', 'photographer_id' => $validated['photographer_id']])
            ->log('Photographer assigned to job');

        return redirect()->back()->with('success', 'Photographer assigned successfully.');
    }

    /**
     * Remove the specified job
     */
    public function destroy(Request $request, PhotographerVisitJob $photographerVisitJob)
    {
        try {
            $photographerVisitJob->deleted_by = auth()->id();
            $photographerVisitJob->save();
            $photographerVisitJob->delete();

            activity('photographer_visit_jobs')
                ->performedOn($photographerVisitJob)
                ->causedBy(auth()->user())
                ->withProperties(['event' => 'deleted'])
                ->log('Photographer visit job deleted');

            // Return JSON for AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Photographer visit job deleted successfully.'
                ]);
            }

            return redirect()->route('admin.photographer-visit-jobs.index')
                ->with('success', 'Photographer visit job deleted successfully.');
        } catch (\Exception $e) {
            \Log::error('Error deleting photographer visit job', [
                'job_id' => $photographerVisitJob->id,
                'error' => $e->getMessage()
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete job. Please try again.'
                ], 500);
            }

            return redirect()->route('admin.photographer-visit-jobs.index')
                ->with('error', 'Failed to delete job. Please try again.');
        }
    }

    /**
     * Show check-in form
     */
    public function checkInForm(PhotographerVisitJob $photographerVisitJob)
    {
        // Check if job can be checked in
        // if (!$photographerVisitJob->isAssigned()) {
        //     return redirect()->route('admin.photographer-visit-jobs.show', $photographerVisitJob)
        //         ->with('error', 'Job must be assigned to a photographer before check-in.');
        // }

        if ($photographerVisitJob->isInProgress() || $photographerVisitJob->isCompleted()) {
            return redirect()->route('admin.photographer-visit-jobs.show', $photographerVisitJob)
                ->with('error', 'Job is already in progress or completed.');
        }

        $photographerVisitJob->load(['booking', 'photographer']);
        return view('admin.photographer-visit-jobs.check-in', compact('photographerVisitJob'));
    }

    /**
     * Process check-in
     */
    public function checkIn(Request $request, PhotographerVisitJob $photographerVisitJob)
    {
        $validated = $request->validate([
            'location' => 'nullable|string|max:255',
            'location_timestamp' => 'nullable|date',
            'location_accuracy' => 'nullable|numeric',
            'location_source' => 'nullable|string|max:50',
            'remarks' => 'nullable|string|max:500',
            'photo' => 'nullable|image|max:5120', // 5MB max
        ]);

        try {
            // Check if job can be checked in
            // if (!$photographerVisitJob->isAssigned()) {
            //     return redirect()->route('admin.photographer-visit-jobs.show', $photographerVisitJob)
            //         ->with('error', 'Job must be assigned to a photographer before check-in.');
            // }

            if ($photographerVisitJob->isInProgress() || $photographerVisitJob->isCompleted()) {
                return redirect()->route('admin.photographer-visit-jobs.show', $photographerVisitJob)
                    ->with('error', 'Job is already in progress or completed.');
            }

            // Handle photo upload
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('photographer-job-checkins', 'public');
            }

            // Update job status and metadata
            $metadata = $photographerVisitJob->metadata ?? [];
            $metadata['check_in'] = [
                'location' => $validated['location'] ?? null,
                'location_timestamp' => !empty($validated['location_timestamp']) ? Carbon::parse($validated['location_timestamp'])->toIso8601String() : null,
                'location_accuracy' => $validated['location_accuracy'] ?? null,
                'location_source' => $validated['location_source'] ?? null,
                'remarks' => $validated['remarks'] ?? null,
                'photo' => $photoPath,
                'checked_in_at' => now(),
                'ip_address' => $request->ip(),
                'device_info' => $request->userAgent(),
            ];

            $photographerVisitJob->update([
                'status' => 'in_progress',
                'started_at' => now(),
                'metadata' => $metadata,
            ]);

            activity('photographer_visit_jobs')
                ->performedOn($photographerVisitJob)
                ->causedBy(auth()->user())
                ->withProperties(['event' => 'checked_in'])
                ->log('Photographer checked in for job');

            return redirect()->route('admin.photographer-visit-jobs.show', $photographerVisitJob)
                ->with('success', 'Successfully checked in for the job.');

        } catch (\Exception $e) {
            \Log::error('Error during photographer job check-in', [
                'job_id' => $photographerVisitJob->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('admin.photographer-visit-jobs.show', $photographerVisitJob)
                ->with('error', 'Failed to check in. Please try again.');
        }
    }

    /**
     * Show check-out form
     */
    public function checkOutForm(PhotographerVisitJob $photographerVisitJob)
    {
        // Check if job can be checked out
        if (!$photographerVisitJob->isInProgress()) {
            return redirect()->route('admin.photographer-visit-jobs.show', $photographerVisitJob)
                ->with('error', 'Job must be in progress before check-out.');
        }

        $photographerVisitJob->load(['booking', 'photographer']);
        return view('admin.photographer-visit-jobs.check-out', compact('photographerVisitJob'));
    }

    /**
     * Process check-out
     */
    public function checkOut(Request $request, PhotographerVisitJob $photographerVisitJob)
    {
        $validated = $request->validate([
            'location' => 'nullable|string|max:255',
            'remarks' => 'nullable|string|max:500',
            'photos_taken' => 'nullable|integer|min:0',
            'work_summary' => 'nullable|string|max:1000',
            'photo' => 'nullable|image|max:5120', // 5MB max
        ]);

        try {
            // Check if job can be checked out
            if (!$photographerVisitJob->isInProgress()) {
                return redirect()->route('admin.photographer-visit-jobs.show', $photographerVisitJob)
                    ->with('error', 'Job must be in progress before check-out.');
            }

            // Handle photo upload
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('photographer-job-checkouts', 'public');
            }

            // Update job status and metadata
            $metadata = $photographerVisitJob->metadata ?? [];
            $metadata['check_out'] = [
                'location' => $validated['location'] ?? null,
                'remarks' => $validated['remarks'] ?? null,
                'photos_taken' => $validated['photos_taken'] ?? 0,
                'work_summary' => $validated['work_summary'] ?? null,
                'photo' => $photoPath,
                'checked_out_at' => now(),
                'ip_address' => $request->ip(),
                'device_info' => $request->userAgent(),
            ];

            $photographerVisitJob->update([
                'status' => 'completed',
                'completed_at' => now(),
                'metadata' => $metadata,
            ]);

            activity('photographer_visit_jobs')
                ->performedOn($photographerVisitJob)
                ->causedBy(auth()->user())
                ->withProperties(['event' => 'checked_out'])
                ->log('Photographer checked out from job');

            return redirect()->route('admin.photographer-visit-jobs.show', $photographerVisitJob)
                ->with('success', 'Successfully checked out from the job.');

        } catch (\Exception $e) {
            \Log::error('Error during photographer job check-out', [
                'job_id' => $photographerVisitJob->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('admin.photographer-visit-jobs.show', $photographerVisitJob)
                ->with('error', 'Failed to check out. Please try again.');
        }
    }
}
