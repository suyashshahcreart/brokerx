<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tour;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;

class TourController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:tour_view')->only(['index', 'show']);
        $this->middleware('permission:tour_create')->only(['create', 'store']);
        $this->middleware('permission:tour_edit')->only(['edit', 'update']);
        $this->middleware('permission:tour_delete')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Tour::query();
            
            return \Yajra\DataTables\Facades\DataTables::of($query)
                ->addColumn('title', fn(Tour $tour) => $tour->title)
                ->addColumn('location', fn(Tour $tour) => $tour->location ?? '-')
                ->addColumn('price', fn(Tour $tour) => $tour->formatted_price)
                ->addColumn('duration', fn(Tour $tour) => $tour->duration_text)
                ->addColumn('dates', function (Tour $tour) {
                    if (!$tour->start_date) return '-';
                    $start = $tour->start_date->format('d M Y');
                    $end = $tour->end_date ? $tour->end_date->format('d M Y') : '-';
                    return $start . '<div class="text-muted small">to ' . $end . '</div>';
                })
                ->addColumn('participants', fn(Tour $tour) => $tour->max_participants ? number_format($tour->max_participants) : '-')
                ->editColumn('status', function (Tour $tour) {
                    $badges = [
                        'draft' => 'bg-secondary',
                        'published' => 'bg-success',
                        'archived' => 'bg-warning'
                    ];
                    $class = $badges[$tour->status] ?? 'bg-secondary';
                    return '<span class="badge ' . $class . ' text-uppercase">' . $tour->status . '</span>';
                })
                ->addColumn('actions', function (Tour $tour) {
                    $view = route('admin.tours.show', $tour);
                    $edit = route('admin.tours.edit', $tour);
                    $delete = route('admin.tours.destroy', $tour);
                    $csrf = csrf_field();
                    $method = method_field('DELETE');
                    
                    $actions = '<a href="' . $view . '" class="btn btn-light btn-sm border" title="View"><i class="ri-eye-line"></i></a>';
                    
                    if (auth()->user()->can('tour_edit')) {
                        $actions .= ' <a href="' . $edit . '" class="btn btn-soft-primary btn-sm" title="Edit"><i class="ri-edit-line"></i></a>';
                    }
                    
                    if (auth()->user()->can('tour_delete')) {
                        $actions .= ' <form action="' . $delete . '" method="POST" class="d-inline">' . $csrf . $method .
                            '<button type="submit" class="btn btn-soft-danger btn-sm" onclick="return confirm(\'Delete this tour?\')"><i class="ri-delete-bin-line"></i></button></form>';
                    }
                    
                    return $actions;
                })
                ->rawColumns(['dates', 'status', 'actions'])
                ->toJson();
        }
        
        $canCreate = $request->user()->can('tour_create');
        $canEdit = $request->user()->can('tour_edit');
        $canDelete = $request->user()->can('tour_delete');
        
        return view('admin.tours.index', compact('canCreate', 'canEdit', 'canDelete'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $statuses = ['draft', 'published', 'archived'];
        $structuredDataTypes = ['Article', 'Place', 'Event', 'Product', 'TouristAttraction'];
        
        return view('admin.tours.create', compact('statuses', 'structuredDataTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => ['nullable', 'exists:bookings,id'],
            'name' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:tours,slug'],
            'description' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'featured_image' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'duration_days' => ['nullable', 'integer', 'min:1'],
            'location' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'max_participants' => ['nullable', 'integer', 'min:1'],
            'status' => ['required', 'in:draft,published,archived'],
            'revision' => ['nullable', 'string', 'max:255'],
            'final_json' => ['nullable', 'json'],
            
            // SEO Fields
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'og_image' => ['nullable', 'string', 'max:255'],
            'og_title' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string'],
            'canonical_url' => ['nullable', 'url', 'max:255'],
            'meta_robots' => ['nullable', 'string', 'max:255'],
            'twitter_title' => ['nullable', 'string', 'max:255'],
            'twitter_description' => ['nullable', 'string'],
            'twitter_image' => ['nullable', 'string', 'max:255'],
            'structured_data_type' => ['nullable', 'string', 'max:255'],
            'structured_data' => ['nullable', 'json'],
            'header_code' => ['nullable', 'string'],
            'footer_code' => ['nullable', 'string'],
        ]);

        // Auto-generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
            
            // Ensure uniqueness
            $originalSlug = $validated['slug'];
            $counter = 1;
            while (Tour::where('slug', $validated['slug'])->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        $tour = Tour::create($validated);

        activity('tours')
            ->performedOn($tour)
            ->causedBy($request->user())
            ->withProperties([
                'event' => 'created',
                'after' => $tour->toArray()
            ])
            ->log('Tour created');

        // If created from booking page, redirect back to booking edit
        if ($request->has('booking_id') && $request->booking_id) {
            return redirect()->route('admin.bookings.edit', $request->booking_id)->with('success', 'Tour created and linked to booking successfully.');
        }

        return redirect()->route('admin.tours.index')->with('success', 'Tour created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Tour $tour)
    {
        return view('admin.tours.show', compact('tour'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tour $tour)
    {
        $statuses = ['draft', 'published', 'archived'];
        $structuredDataTypes = ['Article', 'Place', 'Event', 'Product', 'TouristAttraction'];
        
        return view('admin.tours.edit', compact('tour', 'statuses', 'structuredDataTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tour $tour)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:tours,slug,' . $tour->id],
            'description' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'featured_image' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'duration_days' => ['nullable', 'integer', 'min:1'],
            'location' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'max_participants' => ['nullable', 'integer', 'min:1'],
            'status' => ['required', 'in:draft,published,archived'],
            'revision' => ['nullable', 'string', 'max:255'],
            'final_json' => ['nullable', 'json'],
            
            // SEO Fields
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'og_image' => ['nullable', 'string', 'max:255'],
            'og_title' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string'],
            'canonical_url' => ['nullable', 'url', 'max:255'],
            'meta_robots' => ['nullable', 'string', 'max:255'],
            'twitter_title' => ['nullable', 'string', 'max:255'],
            'twitter_description' => ['nullable', 'string'],
            'twitter_image' => ['nullable', 'string', 'max:255'],
            'structured_data_type' => ['nullable', 'string', 'max:255'],
            'structured_data' => ['nullable', 'json'],
            'header_code' => ['nullable', 'string'],
            'footer_code' => ['nullable', 'string'],
        ]);

        // Auto-generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
            
            // Ensure uniqueness
            $originalSlug = $validated['slug'];
            $counter = 1;
            while (Tour::where('slug', $validated['slug'])->where('id', '!=', $tour->id)->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        $oldData = $tour->toArray();
        $tour->update($validated);

        activity('tours')
            ->performedOn($tour)
            ->causedBy($request->user())
            ->withProperties([
                'event' => 'updated',
                'before' => $oldData,
                'after' => $tour->fresh()->toArray()
            ])
            ->log('Tour updated');

        return redirect()->route('admin.tours.index')->with('success', 'Tour updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tour $tour)
    {
        $tourData = $tour->toArray();
        
        activity('tours')
            ->performedOn($tour)
            ->causedBy(auth()->user())
            ->withProperties([
                'event' => 'deleted',
                'before' => $tourData
            ])
            ->log('Tour deleted');

        $tour->delete();

        return redirect()->route('admin.tours.index')->with('success', 'Tour deleted successfully.');
    }

    /**
     * Update tour via AJAX
     */
    public function updateAjax(Request $request, Tour $tour)
    {
        $validated = $request->validate([
            'booking_id' => ['nullable', 'exists:bookings,id'],
            'name' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:tours,slug,' . $tour->id],
            'description' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'duration_days' => ['nullable', 'integer', 'min:1'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'max_participants' => ['nullable', 'integer', 'min:1'],
            'featured_image' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,published,archived'],
            'revision' => ['nullable', 'string', 'max:255'],
            'final_json' => ['nullable', 'json'],
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = \Illuminate\Support\Str::slug($validated['title']);
        }

        $oldData = $tour->toArray();
        $tour->update($validated);

        activity('tours')
            ->performedOn($tour)
            ->causedBy($request->user())
            ->withProperties([
                'old' => $oldData,
                'attributes' => $tour->toArray(),
            ])
            ->log('Tour updated via AJAX');

        return response()->json([
            'success' => true,
            'message' => 'Tour updated successfully',
            'tour' => $tour->fresh(),
        ]);
    }

    /**
     * Create tour via AJAX
     */
    public function createAjax(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => ['required', 'exists:bookings,id'],
            'name' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:tours,slug'],
            'description' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'duration_days' => ['nullable', 'integer', 'min:1'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'max_participants' => ['nullable', 'integer', 'min:1'],
            'featured_image' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,published,archived'],
            'revision' => ['nullable', 'string', 'max:255'],
            'final_json' => ['nullable', 'json'],
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = \Illuminate\Support\Str::slug($validated['title']);
        }

        $tour = Tour::create($validated);

        activity('tours')
            ->performedOn($tour)
            ->causedBy($request->user())
            ->withProperties(['attributes' => $tour->toArray()])
            ->log('Tour created via AJAX');

        return response()->json([
            'success' => true,
            'message' => 'Tour created successfully',
            'tour' => $tour,
        ], 201);
    }

    /**
     * Unlink tour from booking via AJAX
     */
    public function unlinkAjax(Tour $tour)
    {
        $oldBookingId = $tour->booking_id;
        $tour->booking_id = null;
        $tour->save();

        activity('tours')
            ->performedOn($tour)
            ->causedBy(auth()->user())
            ->withProperties([
                'event' => 'unlinked',
                'old_booking_id' => $oldBookingId,
            ])
            ->log('Tour unlinked from booking');

        return response()->json([
            'success' => true,
            'message' => 'Tour unlinked from booking successfully',
        ]);
    }
}
