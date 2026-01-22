<?php

namespace App\Http\Controllers\Photographer;

use App\Http\Controllers\Controller;
use App\Models\PhotographerVisitJob;
use Illuminate\Http\Request;

class JobController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:photographer']);
    }

    /**
     * Display a listing of jobs for the authenticated photographer
     */
    public function index(Request $request)
    {
        $query = PhotographerVisitJob::with(['booking', 'tour', 'photographer'])
            ->forPhotographer(auth()->id());

        // Filter by status
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->byPriority($request->priority);
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->where('scheduled_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->where('scheduled_date', '<=', $request->to_date);
        }

        $jobs = $query->orderBy('scheduled_date', 'desc')->paginate(15);

        return view('photographer.jobs.index', compact('jobs'));
    }

    /**
     * Display the specified job
     */
    public function show(PhotographerVisitJob $job)
    {
        // Ensure photographer can only view their own jobs
        if ($job->photographer_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this job.');
        }

        $job->load(['booking.user', 'booking.propertyType', 'tour', 'visits.checkIn', 'visits.checkOut']);

        return view('photographer.jobs.show', compact('job'));
    }

    /**
     * Accept a job
     */
    public function accept(PhotographerVisitJob $job)
    {
        if ($job->photographer_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this job.');
        }

        if ($job->status !== 'assigned') {
            return redirect()->back()->with('error', 'Job cannot be accepted in current status.');
        }

        $job->markAsInProgress();

        activity('photographer_visit_jobs')
            ->performedOn($job)
            ->causedBy(auth()->user())
            ->withProperties([
                'event' => 'accepted',
                'status_changed' => 'assigned -> in_progress'
            ])
            ->log('Photographer accepted the job');

        return redirect()->route('photographer.jobs.show', $job)->with('success', 'Job accepted successfully.');
    }

    /**
     * Mark job as completed
     */
    public function complete(PhotographerVisitJob $job, Request $request)
    {
        if ($job->photographer_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this job.');
        }

        if (!$job->isInProgress()) {
            return redirect()->back()->with('error', 'Only in-progress jobs can be marked as completed.');
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $job->markAsCompleted();
        
        if (!empty($validated['notes'])) {
            $job->update(['notes' => $validated['notes']]);
        }

        activity('photographer_visit_jobs')
            ->performedOn($job)
            ->causedBy(auth()->user())
            ->withProperties([
                'event' => 'completed',
                'notes' => $validated['notes'] ?? null
            ])
            ->log('Photographer marked job as completed');

        return redirect()->route('photographer.jobs.index')->with('success', 'Job marked as completed.');
    }

    /**
     * Get upcoming jobs for dashboard
     */
    public function upcoming()
    {
        $jobs = PhotographerVisitJob::upcoming()
            ->forPhotographer(auth()->id())
            ->orderBy('scheduled_date', 'asc')
            ->limit(5)
            ->get();

        return view('photographer.jobs.upcoming', compact('jobs'));
    }
}
