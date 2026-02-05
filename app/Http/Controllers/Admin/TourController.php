<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tour;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;
use App\Models\QR;
use Storage;
use Yajra\DataTables\DataTables;

require_once app_path('Helpers/JsObfuscator.php');

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

            return DataTables::of($query)
                ->addColumn('title', fn(Tour $tour) => $tour->title)
                ->addColumn('location', fn(Tour $tour) => $tour->location ?? '-')
                ->addColumn('price', fn(Tour $tour) => $tour->formatted_price)
                ->addColumn('duration', fn(Tour $tour) => $tour->duration_text)
                ->addColumn('dates', function (Tour $tour) {
                    if (!$tour->start_date)
                        return '-';
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

                    $actions = '<div class="d-flex gap-1">';
                    $actions .= '<a href="' . $view . '" class="btn btn-soft-primary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="View Tour Public Page"><iconify-icon icon="solar:eye-broken" class="align-middle fs-18"></iconify-icon></a>';

                    if (auth()->user()->can('tour_edit')) {
                        $actions .= ' <a href="' . $edit . '" class="btn btn-soft-info btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Tour Details"><iconify-icon icon="solar:pen-2-broken" class="align-middle fs-18"></iconify-icon></a>';
                    }

                    if (auth()->user()->can('tour_delete')) {
                        $actions .= ' <form action="' . $delete . '" method="POST" class="d-inline">' . $csrf . $method .
                            '<button type="submit" class="btn btn-soft-danger btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete Tour" onclick="return confirm(\'Delete this tour?\')"><iconify-icon icon="solar:trash-bin-minimalistic-2-broken" class="align-middle fs-18"></iconify-icon></button></form>';
                    }

                    $actions .= '</div>';
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
            'tour_thumbnail' => ['nullable', 'file', 'image', 'max:5120'],
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

            // Custom fields
            'sidebar_logo' => ['nullable'],
            'footer_logo' => ['nullable', 'string', 'max:255'],
            'footer_name' => ['nullable', 'string', 'max:255'],
            'footer_email' => ['nullable', 'string', 'max:255'],
            'footer_mobile' => ['nullable', 'string', 'max:255'],
            'custom_type' => ['nullable', 'string', 'max:255'],
            'footer_decription' => ['nullable', 'string'],
            // Sidebar and Footer fields
            'company_address' => ['nullable', 'string'],
            'sidebar_footer_link' => ['nullable', 'string'],
            'sidebar_footer_text' => ['nullable', 'string'],
            'sidebar_footer_link_show' => ['nullable', 'boolean'],
            'footer_info_type' => ['nullable', 'string'],
            'footer_brand_logo' => ['nullable'],
            'footer_brand_text' => ['nullable', 'string'],
            'footer_brand_mobile' => ['nullable', 'string'],
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


            // Temporarily remove logo fields for file upload
            $logoSidebarFile = $request->file('sidebar_logo');
            $logoFooterFile = $request->file('footer_logo');
            $logoBrandFile = $request->file('footer_brand_logo');
            $tourThumbnailFile = $request->file('tour_thumbnail');
            unset($validated['sidebar_logo'], $validated['footer_logo'], $validated['footer_brand_logo'], $validated['tour_thumbnail']);

            $tour = Tour::create($validated);
            try {

                $updateData = [];
                if ($logoSidebarFile) {
                    $sidebarFilename = 'logo_sidebar_' . time() . '_' . Str::random(8) . '.' . $logoSidebarFile->getClientOriginalExtension();
                    $sidebarPath = 'tours_logo/' . $tour->id . '/' . $sidebarFilename;
                    $sidebarContent = file_get_contents($logoSidebarFile->getRealPath());
                    $sidebarMime = $logoSidebarFile->getMimeType();
                    $uploaded = Storage::disk('s3')->put($sidebarPath, $sidebarContent, ['ContentType' => $sidebarMime]);
                    if ($uploaded) {
                        $updateData['sidebar_logo'] = $sidebarPath;
                    }
                }
                if ($logoFooterFile) {
                    $footerFilename = 'logo_footer_' . time() . '_' . Str::random(8) . '.' . $logoFooterFile->getClientOriginalExtension();
                    $footerPath = 'tours_logo/' . $tour->id . '/' . $footerFilename;
                    $footerContent = file_get_contents($logoFooterFile->getRealPath());
                    $footerMime = $logoFooterFile->getMimeType();
                    $uploaded = Storage::disk('s3')->put($footerPath, $footerContent, ['ContentType' => $footerMime]);
                    if ($uploaded) {
                        $updateData['footer_logo'] = $footerPath;
                    }
                }
                if ($logoBrandFile) {
                    $brandFilename = 'footer_brand_logo_' . time() . '_' . Str::random(8) . '.' . $logoBrandFile->getClientOriginalExtension();
                    $brandPath = 'tours_logo/' . $tour->id . '/' . $brandFilename;
                    $brandContent = file_get_contents($logoBrandFile->getRealPath());
                    $brandMime = $logoBrandFile->getMimeType();
                    $uploaded = Storage::disk('s3')->put($brandPath, $brandContent, ['ContentType' => $brandMime]);
                    if ($uploaded) {
                        $updateData['footer_brand_logo'] = $brandPath;
                    }
                }
                // Handle tour thumbnail upload
                if ($tourThumbnailFile) {
                    $thumbnailFilename = 'tour_thumbnail_' . time() . '_' . Str::random(8) . '.' . $tourThumbnailFile->getClientOriginalExtension();
                    $thumbnailPath = 'settings/tour_thumbnails/' . $thumbnailFilename;
                    $thumbnailContent = file_get_contents($tourThumbnailFile->getRealPath());
                    $thumbnailMime = $tourThumbnailFile->getMimeType();
                    $uploaded = Storage::disk('s3')->put($thumbnailPath, $thumbnailContent, ['ContentType' => $thumbnailMime]);
                    if ($uploaded) {
                        $updateData['tour_thumbnail'] = $thumbnailPath;
                    }
                }
                if (!empty($updateData)) {
                    $tour->update($updateData);
                }

            } catch (\Exception $e) {
                return back()->withInput()->withErrors(['general' => 'An error occurred while saving the tour: ' . $e->getMessage()]);
            }

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
            'booking_id' => ['nullable', 'exists:bookings,id'],
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

            // Custom fields
            'sidebar_logo' => ['nullable'],
            'footer_logo' => ['nullable'],
            'footer_name' => ['nullable', 'string', 'max:255'],
            'footer_email' => ['nullable', 'string', 'max:255'],
            'footer_mobile' => ['nullable', 'string', 'max:255'],
            'custom_type' => ['nullable', 'string', 'max:255'],
            'footer_decription' => ['nullable', 'string'],
            // Sidebar and Footer fields
            'company_address' => ['nullable', 'string'],
            'sidebar_footer_link' => ['nullable', 'string'],
            'sidebar_footer_text' => ['nullable', 'string'],
            'sidebar_footer_link_show' => ['nullable', 'boolean'],
            'footer_info_type' => ['nullable', 'string'],
            'footer_brand_logo' => ['nullable'],
            'footer_brand_text' => ['nullable', 'string'],
            'footer_brand_mobile' => ['nullable', 'string'],
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


        // Temporarily remove logo fields for file upload
        $logoSidebarFile = $request->file('sidebar_logo');
        $logoFooterFile = $request->file('footer_logo');
        $logoBrandFile = $request->file('footer_brand_logo');
        unset($validated['sidebar_logo'], $validated['footer_logo'], $validated['footer_brand_logo']);

        $oldData = $tour->toArray();
        try {
            $tour->update($validated);

            $updateData = [];
            if ($logoSidebarFile) {
                $sidebarFilename = 'logo_sidebar_' . time() . '_' . Str::random(8) . '.' . $logoSidebarFile->getClientOriginalExtension();
                $sidebarPath = 'tours_logo/' . $tour->id . '/' . $sidebarFilename;
                $sidebarContent = file_get_contents($logoSidebarFile->getRealPath());
                $sidebarMime = $logoSidebarFile->getMimeType();
                $uploaded = Storage::disk('s3')->put($sidebarPath, $sidebarContent, ['ContentType' => $sidebarMime]);
                if ($uploaded) {
                    $updateData['sidebar_logo'] = $sidebarPath;
                }
            }
            if ($logoFooterFile) {
                $footerFilename = 'logo_footer_' . time() . '_' . Str::random(8) . '.' . $logoFooterFile->getClientOriginalExtension();
                $footerPath = 'tours_logo/' . $tour->id . '/' . $footerFilename;
                $footerContent = file_get_contents($logoFooterFile->getRealPath());
                $footerMime = $logoFooterFile->getMimeType();
                $uploaded = Storage::disk('s3')->put($footerPath, $footerContent, ['ContentType' => $footerMime]);
                if ($uploaded) {
                    $updateData['footer_logo'] = $footerPath;
                }
            }
            if ($logoBrandFile) {
                $brandFilename = 'footer_brand_logo_' . time() . '_' . Str::random(8) . '.' . $logoBrandFile->getClientOriginalExtension();
                $brandPath = 'tours_logo/' . $tour->id . '/' . $brandFilename;
                $brandContent = file_get_contents($logoBrandFile->getRealPath());
                $brandMime = $logoBrandFile->getMimeType();
                $uploaded = Storage::disk('s3')->put($brandPath, $brandContent, ['ContentType' => $brandMime]);
                if ($uploaded) {
                    $updateData['footer_brand_logo'] = $brandPath;
                }
            }
            if (!empty($updateData)) {
                $tour->update($updateData);
            }

        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['general' => 'An error occurred while updating the tour: ' . $e->getMessage()]);
        }
        activity('tours')
            ->performedOn($tour)
            ->causedBy($request->user())
            ->withProperties([
                'event' => 'updated',
                'before' => $oldData,
                'after' => $tour->fresh()->toArray()
            ])
            ->log('Tour updated');

        // If updated from booking page, redirect back to booking edit
        if ($request->has('booking_id') && $request->booking_id) {
            return redirect()->route('admin.bookings.edit', $request->booking_id)->with('success', 'Tour updated successfully.');
        }

        return redirect()->route('admin.tours.index')->with('success', 'Tour updated successfully.');
    }

    /**
     * Update tour from booking edit form (custom route)
     */
    public function updateTourDetails(Request $request, Tour $tour)
    {
        $validated = $request->validate([
            'booking_id' => ['nullable', 'exists:bookings,id'],
            'name' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:tours,slug,' . $tour->id],
            'description' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'featured_image' => ['nullable', 'string', 'max:255'],
            'tour_thumbnail' => ['nullable', 'file', 'image', 'max:5120'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'duration_days' => ['nullable', 'integer', 'min:1'],
            'location' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'max_participants' => ['nullable', 'integer', 'min:1'],
            'status' => ['required', 'in:draft,published,archived'],
            'revision' => ['nullable', 'string', 'max:255'],
            'sidebar_footer_link_show' => ['nullable', 'boolean'],
            'sidebar_footer_text' => ['nullable', 'string'],
            'sidebar_footer_link' => ['nullable', 'string'],
            'footer_info_type' => ['nullable', 'string'],
            'footer_brand_logo_text' => ['nullable', 'string'],
            'footer_brand_text' => ['nullable', 'string'],
            'footer_brand_mobile' => ['nullable', 'string'],
            'footer_name' => ['nullable', 'string'],
            'footer_subtitle' => ['nullable', 'string'],
            'footer_email' => ['nullable', 'string'],
            'footer_mobile' => ['nullable', 'string'],
            'footer_decription' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'is_credentials' => ['nullable', 'boolean'],
            'is_mobile_validation' => ['nullable', 'boolean'],
            'is_hosted' => ['nullable', 'boolean'],
            'hosted_link' => ['nullable', 'string', 'max:500'],
            'credentials' => ['nullable', 'array'],
            'credentials.*.id' => ['nullable', 'integer'],
            'credentials.*.user_name' => ['required_with:credentials', 'string', 'max:255'],
            'credentials.*.password' => ['required_with:credentials', 'string', 'max:255'],
            'credentials.*.is_active' => ['boolean'],
        ]);

        $qr_code = QR::where('booking_id', $tour->booking_id)->value('code');
        $jsPath = 'tours/' . $qr_code . '/assets/js/tour-data.js';
        // Handle slug uniqueness
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
            $originalSlug = $validated['slug'];
            $counter = 1;
            while (Tour::where('slug', $validated['slug'])->where('id', '!=', $tour->id)->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        // Normalize final_json from DB (can be array, JSON string, or empty)
        $rawFinalJson = $tour->final_json;
        $finalJson = [];

        if (is_array($rawFinalJson)) {
            $finalJson = $rawFinalJson;
        } elseif (is_string($rawFinalJson) && trim($rawFinalJson) !== '') {
            $decoded = json_decode($rawFinalJson, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $finalJson = $decoded;
            }
        } elseif (is_object($rawFinalJson)) {
            $decoded = json_decode(json_encode($rawFinalJson), true);
            $finalJson = is_array($decoded) ? $decoded : [];
        }

        $finalJsonWasEmpty = empty($finalJson);

        // Ensure expected structure exists before updates
        $finalJson['sidebarConfig'] = $finalJson['sidebarConfig'] ?? [];
        $finalJson['sidebarConfig']['footerButton'] = $finalJson['sidebarConfig']['footerButton'] ?? [];
        $finalJson['bottomMarker'] = $finalJson['bottomMarker'] ?? [];

        // update the sidebar data in json;
        $finalJson['sidebarConfig']['footerButton']['text'] = $validated['sidebar_footer_text'];
        $finalJson['sidebarConfig']['footerButton']['link'] = $validated['sidebar_footer_link'];
        $finalJson['sidebarConfig']['footerButton']['show'] = $validated['sidebar_footer_link_show'];
        // footer data update in json
        // $finalJson['bottomMarker']
        $finalJson['bottomMarker']['topTitle'] = $validated['footer_name'];
        $finalJson['bottomMarker']['topSubTitle'] = $validated['footer_subtitle'];
        $finalJson['bottomMarker']['topDescription'] = $validated['footer_decription'];
        $finalJson['bottomMarker']['contactNumber'] = $validated['footer_mobile'];
        $finalJson['bottomMarker']['contactEmail'] = $validated['footer_email'];

        // footer brand info update in json
        $finalJson['bottomMarker']['tourContactText'] = $validated['footer_brand_text'];
        $finalJson['bottomMarker']['tourContactNumber'] = $validated['footer_brand_mobile'];

        // Handle file uploads (sidebar_logo, footer_logo, footer_brand_logo)
        $logoSidebarFile = $request->file('sidebar_logo');
        $logoFooterFile = $request->file('footer_logo');
        $logoBrandFile = $request->file('footer_brand_logo');

        $updateData = $validated;

        // Sidebar logo
        if ($logoSidebarFile && $qr_code) {
            $sidebarFilename = 'logo_sidebar_' . time() . '_' . Str::random(8) . '.' . $logoSidebarFile->getClientOriginalExtension();
            $sidebarPath = 'tours/' . $qr_code . '/assets/' . $sidebarFilename;
            $sidebarContent = file_get_contents($logoSidebarFile->getRealPath());
            $sidebarMime = $logoSidebarFile->getMimeType();
            $uploaded = Storage::disk('s3')->put($sidebarPath, $sidebarContent, ['ContentType' => $sidebarMime]);
            $finalJson['sidebarConfig']['logo'] = 'assets/' . $sidebarFilename;
            if ($uploaded) {
                $updateData['sidebar_logo'] = $sidebarPath;
            }
        }
        // Footer logo
        if ($logoFooterFile && $qr_code) {
            $footerFilename = 'logo_footer_' . time() . '_' . Str::random(8) . '.' . $logoFooterFile->getClientOriginalExtension();
            $footerPath = 'tours/' . $qr_code . '/assets/' . $footerFilename;
            $footerContent = file_get_contents($logoFooterFile->getRealPath());
            $footerMime = $logoFooterFile->getMimeType();
            $uploaded = Storage::disk('s3')->put($footerPath, $footerContent, ['ContentType' => $footerMime]);
            $finalJson['bottomMarker']['topImage'] = 'assets/' . $footerFilename;
            if ($uploaded) {
                $updateData['footer_logo'] = $footerPath;
            }
        }
        // Footer brand logo
        if ($logoBrandFile && $qr_code) {
            $brandFilename = 'footer_brand_logo_' . time() . '_' . Str::random(8) . '.' . $logoBrandFile->getClientOriginalExtension();
            $brandPath = 'tours/' . $qr_code . '/assets/' . $brandFilename;
            $brandContent = file_get_contents($logoBrandFile->getRealPath());
            $brandMime = $logoBrandFile->getMimeType();
            $uploaded = Storage::disk('s3')->put($brandPath, $brandContent, ['ContentType' => $brandMime]);
            $finalJson['bottomMarker']['brandLogo'] = 'assets/' . $brandFilename;
            if ($uploaded) {
                $updateData['footer_brand_logo'] = $brandPath;
            }
        }

        // Handle tour thumbnail upload
        $tourThumbnailFile = $request->file('tour_thumbnail');
        if ($tourThumbnailFile) {
            $thumbnailFilename = 'tour_thumbnail_' . time() . '_' . Str::random(8) . '.' . $tourThumbnailFile->getClientOriginalExtension();
            $thumbnailPath = 'settings/tour_thumbnails/' . $thumbnailFilename;
            $thumbnailContent = file_get_contents($tourThumbnailFile->getRealPath());
            $thumbnailMime = $tourThumbnailFile->getMimeType();
            $uploaded = Storage::disk('s3')->put($thumbnailPath, $thumbnailContent, ['ContentType' => $thumbnailMime]);
            if ($uploaded) {
                $updateData['tour_thumbnail'] = $thumbnailPath;
            }
        }

        // If footer_brand_logo_text is present, update it as a text field
        if ($request->has('footer_brand_logo_text')) {
            $updateData['footer_brand_logo_text'] = $request->input('footer_brand_logo_text');
        }

        // Only save final_json to DB if it was not empty originally
        if (!$finalJsonWasEmpty) {
            $updateData['final_json'] = $finalJson;
        }

        // Update new boolean fields
        $updateData['is_active'] = $request->has('is_active');
        $updateData['is_credentials'] = $request->has('is_credentials');
        $updateData['is_mobile_validation'] = $request->has('is_mobile_validation');
        $updateData['is_hosted'] = $request->has('is_hosted');
        $updateData['hosted_link'] = $request->input('hosted_link');

        // Manage Credentials
        if ($request->has('credentials')) {
            $inputCredentials = $request->input('credentials', []);
            $existingCredentialIds = $tour->credentials()->pluck('id')->toArray();
            $processedIds = [];

            foreach ($inputCredentials as $credentialData) {
                if (isset($credentialData['id']) && in_array($credentialData['id'], $existingCredentialIds)) {
                    // Update existing
                    $credential = \App\Models\TourCredential::find($credentialData['id']);
                    $credential->update([
                        'user_name' => $credentialData['user_name'],
                        'password' => $credentialData['password'],
                        'is_active' => $credentialData['is_active'] ?? true,
                    ]);
                    $processedIds[] = $credentialData['id'];
                } else {
                    // Create new
                    $tour->credentials()->create([
                        'user_name' => $credentialData['user_name'],
                        'password' => $credentialData['password'],
                        'is_active' => $credentialData['is_active'] ?? true,
                    ]);
                }
            }

            // Delete removed credentials
            $idsToDelete = array_diff($existingCredentialIds, $processedIds);
            if (!empty($idsToDelete)) {
                \App\Models\TourCredential::destroy($idsToDelete);
            }
        } elseif (!$request->has('is_credentials') || !$request->input('is_credentials')) {
            // If credentials are not required, or the array is empty but we want to be safe, 
            // you might choose to keep them or delete them. 
            // User requirement implies "if is_credentials is on that time below add credentials data".
            // Implementation choice: if is_credentials turned OFF, maybe keep them but they are hidden?
            // Or if the array is not sent (empty form), we might need to check if we should delete all.
            // The form sends 'credentials' array only if rows exist. 
            // If user deletes all rows in UI, 'credentials' might be missing or empty.
            if ($request->filled('is_credentials') && empty($request->input('credentials'))) {
                // User selected "Required" but provided no credentials -> could mean delete all if list was cleared
                //  $tour->credentials()->delete();
            }
        }

        // Update the tour with new data DB
        $tour->update($updateData);

        // Auto-purge Cloudflare cache for this tour
        try {
            // Refresh tour to ensure we have fresh relationships
            $tour->refresh();
            
            // Load booking relationship if not already loaded
            if (!$tour->relationLoaded('booking')) {
                $tour->load('booking');
            }
            
            $booking = $tour->booking;
            if ($booking && !empty($booking->tour_code)) {
                // Check if Cloudflare credentials are configured
                $zoneId = getCloudflareZoneId();
                $apiToken = getCloudflareApiToken();
                
                if (!$zoneId || !$apiToken) {
                    \Log::info('Cloudflare cache purge skipped - credentials not configured', [
                        'tour_id' => $tour->id,
                        'tour_code' => $booking->tour_code
                    ]);
                } else {
                    $cloudflareService = app(\App\Services\CloudflareCacheService::class);
                    $prefix = $cloudflareService->buildTourPrefix($booking->tour_code);
                    $purgeResult = $cloudflareService->purgeByPrefixes([$prefix]);
                    
                    // Log purge result (success or failure) but don't fail the request
                    if (!$purgeResult['success']) {
                        \Log::warning('Cloudflare cache purge failed after tour update', [
                            'tour_id' => $tour->id,
                            'tour_code' => $booking->tour_code,
                            'prefix' => $prefix,
                            'error' => $purgeResult['message']
                        ]);
                    } else {
                        \Log::info('Cloudflare cache purged successfully after tour update', [
                            'tour_id' => $tour->id,
                            'tour_code' => $booking->tour_code,
                            'prefix' => $prefix
                        ]);
                    }
                }
            } else {
                \Log::debug('Cloudflare cache purge skipped - no booking or tour_code found', [
                    'tour_id' => $tour->id,
                    'has_booking' => $booking !== null,
                    'tour_code' => $booking->tour_code ?? null
                ]);
            }
        } catch (\Exception $e) {
            // Don't fail the request if Cloudflare purge fails
            \Log::error('Exception during Cloudflare cache purge after tour update', [
                'tour_id' => $tour->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        // If final_json was empty originally, only persist DB changes and skip S3
        if ($finalJsonWasEmpty) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tour updated, but files were not uploaded to S3 because final JSON was empty.'
                ]);
            }
            return redirect()->back()->with(['warning' => 'Tour updated, but files were not uploaded to S3 because final JSON was empty.', 'active_tab' => 'tour']);
        }

        //  create a new js file with updated final json
        $jsonString = json_encode(
            $finalJson,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );

        $jsFileContent = '
        window.EMBEDDED_TOUR_DATA= ' . $jsonString . '
        // Helper function to extract YouTube video ID from URL
        window.extractYouTubeVideoId = function(url) {
        if (!url) return null;
        const patterns = [
            /(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&\n?#]+)/,
            /youtube\.com\/watch\?.*v=([^&\n?#]+)/,
        ];
        for (const pattern of patterns) {
            const match = url.match(pattern);
            if (match && match[1]) {
            return match[1];
            }
        }
        return null;
        };

        // Helper function to create YouTube iframe element
        window.createYouTubeIframe = function(videoId, width, height) {
        if (!videoId) return null;
        const iframe = document.createElement("iframe");
        iframe.src = `https://www.youtube.com/embed/${videoId}`;
        iframe.width = String(width || 640);
        iframe.height = String(height || 360);
        iframe.style.width = `${width || 640}px`;
        iframe.style.height = `${height || 360}px`;
        iframe.style.border = "none";
        iframe.style.display = "block";
        iframe.allow = "fullscreen";
        iframe.setAttribute("allowfullscreen", "true");
        return iframe;
        };';

        // encript the js code and testing things.
        $obfuscatedJs = obfuscateJs($jsFileContent);

        // Upload the JS file to S3
        Storage::disk('s3')->put($jsPath, $obfuscatedJs, ['ContentType' => 'application/javascript']);

        // update the json file of virtual-tour-nodes.json
        Storage::disk('s3')->put('tours/' . $qr_code . '/virtual-tour-nodes.json', $jsonString, ['ContentType' => 'application/json']);

        // Return JSON response for AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tour updated successfully'
            ]);
        }

        return redirect()->back()->with(['success' => 'Tour updated successfully from booking edit.', 'active_tab' => 'tour']);
    }

    /**
     * Update SEO fields for a tour from the SEO form.
     */
    public function updateTourSeo(Request $request, Tour $tour)
    {

        $validated = $request->validate([
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'canonical_url' => ['nullable', 'url', 'max:255'],
            'meta_robots' => ['nullable', 'string', 'max:255'],
            'og_title' => ['nullable', 'string', 'max:255'],
            'og_image' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string'],
            'twitter_title' => ['nullable', 'string', 'max:255'],
            'twitter_image' => ['nullable', 'string', 'max:255'],
            'twitter_description' => ['nullable', 'string'],
            'structured_data_type' => ['nullable', 'string', 'max:255'],
            'structured_data' => ['nullable', 'string'],
            'header_code' => ['nullable', 'string'],
            'footer_code' => ['nullable', 'string'],
            'gtm_tag' => ['nullable', 'string', 'max:255'],
        ]);

        // Validate structured_data as JSON if present
        if (!empty($validated['structured_data'])) {
            json_decode($validated['structured_data']);
            if (json_last_error() !== JSON_ERROR_NONE) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Structured Data must be valid JSON.'
                    ], 422);
                }

                return back()->withInput()->withErrors(['structured_data' => 'Structured Data must be valid JSON.']);
            }
        }

        // Update all SEO fields in the database
        $tour->update([
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_keywords' => $validated['meta_keywords'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
            'canonical_url' => $validated['canonical_url'] ?? null,
            'meta_robots' => $validated['meta_robots'] ?? null,
            'og_title' => $validated['og_title'] ?? null,
            'og_image' => $validated['og_image'] ?? null,
            'og_description' => $validated['og_description'] ?? null,
            'twitter_title' => $validated['twitter_title'] ?? null,
            'twitter_image' => $validated['twitter_image'] ?? null,
            'twitter_description' => $validated['twitter_description'] ?? null,
            'structured_data_type' => $validated['structured_data_type'] ?? null,
            'structured_data' => $validated['structured_data'] ?? null,
            'header_code' => $validated['header_code'] ?? null,
            'footer_code' => $validated['footer_code'] ?? null,
            'gtm_tag' => $validated['gtm_tag'] ?? null,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'SEO details updated successfully.'
            ]);
        }

        return redirect()->back()->with(['success' => 'SEO details updated successfully.', 'active_tab' => 'seo']);
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
            $validated['slug'] = Str::slug($validated['title']);
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
            $validated['slug'] = Str::slug($validated['title']);
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
