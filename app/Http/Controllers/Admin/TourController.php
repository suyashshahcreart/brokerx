<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tour;
use Arr;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use JsonException;
use Spatie\Activitylog\Models\Activity;
use App\Models\QR;
use Storage;
use Yajra\DataTables\DataTables;
use App\Services\TourService;

require_once app_path('Helpers/JsObfuscator.php');

class TourController extends Controller
{
    protected $tourService;

    public function __construct(TourService $tourService)
    {
        $this->tourService = $tourService;
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
            'footer_title' => ['nullable', 'array'],
            'footer_title.*' => ['nullable', 'string'],
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
            'sidebar_tag_text' => ['nullable', 'string', 'max:255'],
            'sidebar_tag_color' => ['nullable', 'string', 'max:255'],
            'sidebar_tag_bg_color' => ['nullable', 'string', 'max:255'],
            'bottommark_property_name_en' => ['nullable', 'string'],
            'bottommark_property_name_gu' => ['nullable', 'string'],
            'bottommark_property_name_hi' => ['nullable', 'string'],
            'bottommark_room_type_en' => ['nullable', 'string'],
            'bottommark_room_type_gu' => ['nullable', 'string'],
            'bottommark_room_type_hi' => ['nullable', 'string'],
            'bottommark_dimensions_en' => ['nullable', 'string'],
            'bottommark_dimensions_gu' => ['nullable', 'string'],
            'bottommark_dimensions_hi' => ['nullable', 'string'],
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
            'footer_title' => ['nullable', 'array'],
            'footer_title.*' => ['nullable', 'string'],
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
            'sidebar_tag_text' => ['nullable', 'string', 'max:255'],
            'sidebar_tag_color' => ['nullable', 'string', 'max:255'],
            'sidebar_tag_bg_color' => ['nullable', 'string', 'max:255'],
            'bottommark_property_name_en' => ['nullable', 'string'],
            'bottommark_property_name_gu' => ['nullable', 'string'],
            'bottommark_property_name_hi' => ['nullable', 'string'],
            'bottommark_room_type_en' => ['nullable', 'string'],
            'bottommark_room_type_gu' => ['nullable', 'string'],
            'bottommark_room_type_hi' => ['nullable', 'string'],
            'bottommark_dimensions_en' => ['nullable', 'string'],
            'bottommark_dimensions_gu' => ['nullable', 'string'],
            'bottommark_dimensions_hi' => ['nullable', 'string'],
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

        if (isset($validated['footer_name']) && !isset($validated['footer_title'])) {
            $validated['footer_title'] = ['en' => $validated['footer_name']];
        }
        unset($validated['footer_name']);


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
            'footer_title' => ['nullable', 'array'],
            'footer_title.en' => ['nullable', 'string'],
            'footer_title.gu' => ['nullable', 'string'],
            'footer_title.hi' => ['nullable', 'string'],
            'footer_subtitle' => ['nullable', 'array'],
            'footer_subtitle.en' => ['nullable', 'string'],
            'footer_subtitle.gu' => ['nullable', 'string'],
            'footer_subtitle.hi' => ['nullable', 'string'],
            'footer_decription' => ['nullable', 'array'],
            'footer_decription.en' => ['nullable', 'string'],
            'footer_decription.gu' => ['nullable', 'string'],
            'footer_decription.hi' => ['nullable', 'string'],
            'footer_name_en' => ['nullable', 'string'],
            'footer_name_gu' => ['nullable', 'string'],
            'footer_name_hi' => ['nullable', 'string'],
            'footer_subtitle_en' => ['nullable', 'string'],
            'footer_subtitle_gu' => ['nullable', 'string'],
            'footer_subtitle_hi' => ['nullable', 'string'],
            'footer_decription_en' => ['nullable', 'string'],
            'footer_decription_gu' => ['nullable', 'string'],
            'footer_decription_hi' => ['nullable', 'string'],
            'footer_name' => ['nullable', 'string'],
            'footer_email' => ['nullable', 'string'],
            'footer_mobile' => ['nullable', 'string'],
            // sidebar tag fields 
            'sidebar_tag_text' => ['nullable', 'string', 'max:255'],
            'sidebar_tag_color' => ['nullable', 'string', 'max:255'],
            'sidebar_tag_bg_color' => ['nullable', 'string', 'max:255'],
            // Bottommark multilingual fields
            'bottommark_property_name_en' => ['nullable', 'string'],
            'bottommark_property_name_gu' => ['nullable', 'string'],
            'bottommark_property_name_hi' => ['nullable', 'string'],
            'bottommark_room_type_en' => ['nullable', 'string'],
            'bottommark_room_type_gu' => ['nullable', 'string'],
            'bottommark_room_type_hi' => ['nullable', 'string'],
            'bottommark_dimensions_en' => ['nullable', 'string'],
            'bottommark_dimensions_gu' => ['nullable', 'string'],
            'bottommark_dimensions_hi' => ['nullable', 'string'],
            'made_by_text' => ['nullable', 'string'],
            'made_by_link' => ['nullable', 'string', 'max:500'],
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
        $finalJson['sidebarConfig']['sidebarTag'] = $finalJson['sidebarConfig']['sidebarTag'] ?? [];
        $finalJson['bottomMarker'] = $finalJson['bottomMarker'] ?? [];

        // Update sidebar footer button JSON while preserving multilingual structure if present
        $existingFooterButtonText = $finalJson['sidebarConfig']['footerButton']['text'] ?? [];
        if (!is_array($existingFooterButtonText)) {
            $existingFooterButtonText = ['en' => $existingFooterButtonText];
        }
        if (array_key_exists('sidebar_footer_text', $validated)) {
            $existingFooterButtonText['en'] = $validated['sidebar_footer_text'];
        }
        $finalJson['sidebarConfig']['footerButton']['text'] = $existingFooterButtonText;
        if (array_key_exists('sidebar_footer_link', $validated)) {
            $finalJson['sidebarConfig']['footerButton']['link'] = $validated['sidebar_footer_link'];
        }
        if (array_key_exists('sidebar_footer_link_show', $validated)) {
            $finalJson['sidebarConfig']['footerButton']['show'] = (bool) $validated['sidebar_footer_link_show'];
        }

        // Sidebar tag and made-by info
        if (array_key_exists('sidebar_tag_text', $validated)) {
            $finalJson['sidebarConfig']['sidebarTag']['text'] = $validated['sidebar_tag_text'];
        }
        if (array_key_exists('sidebar_tag_color', $validated)) {
            $finalJson['sidebarConfig']['sidebarTag']['color'] = $validated['sidebar_tag_color'];
        }
        if (array_key_exists('sidebar_tag_bg_color', $validated)) {
            $finalJson['sidebarConfig']['sidebarTag']['backgroundColor'] = $validated['sidebar_tag_bg_color'];
        }

        $existingMadeByText = $finalJson['bottomMarker']['madeByText'] ?? [];
        if (!is_array($existingMadeByText)) {
            $existingMadeByText = ['en' => $existingMadeByText];
        }
        if (array_key_exists('made_by_text', $validated)) {
            $existingMadeByText['en'] = $validated['made_by_text'];
        }
        $finalJson['bottomMarker']['madeByText'] = $existingMadeByText;
        if (array_key_exists('made_by_link', $validated)) {
            $finalJson['bottomMarker']['madeByLink'] = $validated['made_by_link'];
        }

        // Handle bottommark multilingual fields (property name, room type, dimensions)
        $resolveBotommarkField = function ($legacyPrefix) use ($validated) {
            $resolved = [];
            foreach (['en', 'gu', 'hi'] as $lang) {
                $legacyKey = $legacyPrefix . '_' . $lang;
                if (array_key_exists($legacyKey, $validated) && !empty($validated[$legacyKey])) {
                    $resolved[$lang] = $validated[$legacyKey];
                }
            }
            return $resolved;
        };

        $resolvedPropertyName = $resolveBotommarkField('bottommark_property_name');
        if (!empty($resolvedPropertyName)) {
            $finalJson['bottomMarker']['propertyName'] = $resolvedPropertyName;
        }

        $resolvedRoomType = $resolveBotommarkField('bottommark_room_type');
        if (!empty($resolvedRoomType)) {
            $finalJson['bottomMarker']['roomType'] = $resolvedRoomType;
        }

        $resolvedDimensions = $resolveBotommarkField('bottommark_dimensions');
        if (!empty($resolvedDimensions)) {
            $finalJson['bottomMarker']['dimensions'] = $resolvedDimensions;
        }

        // Normalize multilingual footer payload (supports new JSON fields and legacy flat fields)
        $resolveLangMap = function ($field, $legacyPrefix) use ($validated) {
            $resolved = $validated[$field] ?? [];
            if (!is_array($resolved)) {
                $resolved = [];
            }

            foreach (['en', 'gu', 'hi'] as $lang) {
                $legacyKey = $legacyPrefix . '_' . $lang;
                if (array_key_exists($legacyKey, $validated)) {
                    $resolved[$lang] = $validated[$legacyKey];
                }
            }

            return $resolved;
        };

        $resolvedFooterTitle = $resolveLangMap('footer_title', 'footer_name');
        if (isset($validated['footer_name']) && !array_key_exists('en', $resolvedFooterTitle)) {
            $resolvedFooterTitle['en'] = $validated['footer_name'];
        }
        $resolvedFooterSubtitle = $resolveLangMap('footer_subtitle', 'footer_subtitle');
        $resolvedFooterDescription = $resolveLangMap('footer_decription', 'footer_decription');

        // Footer multilingual fields
        $existingTopTitle = $finalJson['bottomMarker']['topTitle'] ?? [];
        if (!is_array($existingTopTitle)) {
            $existingTopTitle = ['en' => $existingTopTitle];
        }
        foreach (['en', 'gu', 'hi'] as $lang) {
            if (array_key_exists($lang, $resolvedFooterTitle)) {
                $existingTopTitle[$lang] = $resolvedFooterTitle[$lang];
            }
        }
        $finalJson['bottomMarker']['topTitle'] = $existingTopTitle;

        $existingTopSubTitle = $finalJson['bottomMarker']['topSubTitle'] ?? [];
        if (!is_array($existingTopSubTitle)) {
            $existingTopSubTitle = ['en' => $existingTopSubTitle];
        }
        foreach (['en', 'gu', 'hi'] as $lang) {
            if (array_key_exists($lang, $resolvedFooterSubtitle)) {
                $existingTopSubTitle[$lang] = $resolvedFooterSubtitle[$lang];
            }
        }
        $finalJson['bottomMarker']['topSubTitle'] = $existingTopSubTitle;

        $existingTopDescription = $finalJson['bottomMarker']['topDescription'] ?? [];
        if (!is_array($existingTopDescription)) {
            $existingTopDescription = ['en' => $existingTopDescription];
        }
        foreach (['en', 'gu', 'hi'] as $lang) {
            if (array_key_exists($lang, $resolvedFooterDescription)) {
                $existingTopDescription[$lang] = $resolvedFooterDescription[$lang];
            }
        }
        $finalJson['bottomMarker']['topDescription'] = $existingTopDescription;

        if (array_key_exists('footer_mobile', $validated)) {
            $finalJson['bottomMarker']['contactNumber'] = $validated['footer_mobile'];
        }
        if (array_key_exists('footer_email', $validated)) {
            $finalJson['bottomMarker']['contactEmail'] = $validated['footer_email'];
        }

        // Footer brand info update in json
        if (array_key_exists('footer_brand_text', $validated)) {
            $finalJson['bottomMarker']['tourContactText'] = $validated['footer_brand_text'];
        }
        if (array_key_exists('footer_brand_mobile', $validated)) {
            $finalJson['bottomMarker']['tourContactNumber'] = $validated['footer_brand_mobile'];
        }

        // Handle file uploads (sidebar_logo, footer_logo, footer_brand_logo)
        $logoSidebarFile = $request->file('sidebar_logo');
        $logoFooterFile = $request->file('footer_logo');
        $logoBrandFile = $request->file('footer_brand_logo');

        $updateData = $validated;

        // Map multilingual fields into legacy DB columns for compatibility
        $updateData['footer_title'] = empty($resolvedFooterTitle) ? null : $resolvedFooterTitle;
        $updateData['footer_subtitle'] = empty($resolvedFooterSubtitle) ? null : $resolvedFooterSubtitle;
        $updateData['footer_decription'] = empty($resolvedFooterDescription) ? null : $resolvedFooterDescription;
        // Map bottommark multilingual fields into DB columns
        $updateData['bottommark_property_name'] = empty($resolvedPropertyName) ? null : $resolvedPropertyName;
        $updateData['bottommark_room_type'] = empty($resolvedRoomType) ? null : $resolvedRoomType;
        $updateData['bottommark_dimensions'] = empty($resolvedDimensions) ? null : $resolvedDimensions;

        // Form-only keys should only update final_json, not DB columns
        unset(
            $updateData['footer_name_en'],
            $updateData['footer_name_gu'],
            $updateData['footer_name_hi'],
            $updateData['footer_name'],
            $updateData['footer_subtitle_en'],
            $updateData['footer_subtitle_gu'],
            $updateData['footer_subtitle_hi'],
            $updateData['footer_decription_en'],
            $updateData['footer_decription_gu'],
            $updateData['footer_decription_hi'],
            $updateData['made_by_text'],
            $updateData['made_by_link'],
            $updateData['bottommark_property_name_en'],
            $updateData['bottommark_property_name_gu'],
            $updateData['bottommark_property_name_hi'],
            $updateData['bottommark_room_type_en'],
            $updateData['bottommark_room_type_gu'],
            $updateData['bottommark_room_type_hi'],
            $updateData['bottommark_dimensions_en'],
            $updateData['bottommark_dimensions_gu'],
            $updateData['bottommark_dimensions_hi']
        );

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

        // Update JSON and JS files in S3
        $this->updateTourJsonAndJsFilesInS3($tour, $finalJson);

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
     * Update tour JSON and JS files in S3 storage
     *
    * Updates 3 files in order:
    * 1. virtual-tour-nodes.json - other data from $finalJson, nodes from $finalJson when provided
    * 2. tour-data.json - other data from $finalJson, nodes from $finalJson when provided
     * 3. tour-data.js - same content as tour-data.json, wrapped in JS and obfuscated
     *
     * @param Tour $tour The tour model
    * @param array $finalJson The final JSON data to save (userInfo, bottomMarker, nodes, etc.)
     * @return bool True if successful, false otherwise
     */
    private function updateTourJsonAndJsFilesInS3(Tour $tour, array $finalJson): bool
    {
        // Check if tour has a booking
        if (!$tour->booking_id) {
            \Log::warning('Cannot update tour files - no booking associated', ['tour_id' => $tour->id]);
            return false;
        }

        try {
            // Get QR code for the tour
            $qrCode = QR::where('booking_id', $tour->booking_id)->value('code');

            if (!$qrCode) {
                \Log::warning('Cannot update tour files - no QR code found', ['booking_id' => $tour->booking_id]);
                return false;
            }

            // Check if finalJson is empty
            if (empty($finalJson)) {
                \Log::info('Skipping S3 update - final_json is empty', ['tour_id' => $tour->id]);
                return false;
            }

            // Build S3 paths
            $virtualTourNodesPath = 'tours/' . $qrCode . '/virtual-tour-nodes.json';
            $tourDataJsonPath = 'tours/' . $qrCode . '/assets/js/tour-data.json';
            $tourDataJsPath = 'tours/' . $qrCode . '/assets/js/tour-data.js';

            // Fetch existing nodes from S3 for backwards compatibility when the payload does not include nodes
            $existingVirtualTourNodes = [];
            $existingTourDataJsonNodes = [];

            if (Storage::disk('s3')->exists($virtualTourNodesPath)) {
                $content = Storage::disk('s3')->get($virtualTourNodesPath);
                $decoded = json_decode($content, true);
                if (json_last_error() === JSON_ERROR_NONE && !empty($decoded['nodes'])) {
                    $existingVirtualTourNodes = $decoded['nodes'];
                }
            }

            if (Storage::disk('s3')->exists($tourDataJsonPath)) {
                $content = Storage::disk('s3')->get($tourDataJsonPath);
                $decoded = json_decode($content, true);
                if (json_last_error() === JSON_ERROR_NONE && !empty($decoded['nodes'])) {
                    $existingTourDataJsonNodes = $decoded['nodes'];
                }
            }

            $finalNodes = array_key_exists('nodes', $finalJson) && is_array($finalJson['nodes'])
                ? array_values($finalJson['nodes'])
                : null;

            // Merge: our updates (userInfo, etc.) + nodes from the payload when present, otherwise keep S3 nodes
            $virtualTourNodesContent = $finalJson;
            $virtualTourNodesContent['nodes'] = $finalNodes ?? $existingVirtualTourNodes;

            $tourDataJsonContent = $finalJson;
            $tourDataJsonContent['nodes'] = $finalNodes ?? $existingTourDataJsonNodes;

            // Upload 1: virtual-tour-nodes.json (first)
            $virtualTourNodesString = json_encode(
                $virtualTourNodesContent,
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            );
            Storage::disk('s3')->put($virtualTourNodesPath, $virtualTourNodesString, ['ContentType' => 'application/json']);

            // Upload 2: tour-data.json (second)
            $tourDataJsonString = json_encode(
                $tourDataJsonContent,
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            );
            Storage::disk('s3')->put($tourDataJsonPath, $tourDataJsonString, ['ContentType' => 'application/json']);

            // Upload 3: tour-data.js (third) - based on tour-data.json content
            $jsFileContent = '
        window.EMBEDDED_TOUR_DATA= ' . $tourDataJsonString . '
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

            $obfuscatedJs = obfuscateJs($jsFileContent);
            Storage::disk('s3')->put($tourDataJsPath, $obfuscatedJs, ['ContentType' => 'application/javascript']);
            \Log::info('Tour JSON and JS files updated successfully in S3', [
                'tour_id' => $tour->id,
                'qr_code' => $qrCode,
                'virtual_tour_nodes_path' => $virtualTourNodesPath,
                'tour_data_json_path' => $tourDataJsonPath,
                'tour_data_js_path' => $tourDataJsPath,
            ]);

            return true;

        } catch (\Exception $e) {
            \Log::error('Error updating tour JSON and JS files in S3', [
                'tour_id' => $tour->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /** 
     * Update the json in DB and js and json files
     * This is a helper function to be used in updateTourSeo and updateAjax to avoid code duplication
     * 
     */
    public function updateTourJson(Tour $tour, Request $request): JsonResponse|RedirectResponse
    {
        if (!$request->has('final_json') && $request->has('final_josn')) {
            $request->merge(['final_json' => $request->input('final_josn')]);
        }
        $diffJson = json_decode($request->diff_json ?? '{}', true);

        $validator = Validator::make($request->all(), [
            'final_json' => [
                'required',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (is_array($value)) {
                        if (empty($value)) {
                            $fail('The final json field must not be empty.');
                        }
                        return;
                    }

                    if (!is_string($value) || trim($value) === '') {
                        $fail('The final json field is required.');
                        return;
                    }

                    try {
                        $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
                    } catch (JsonException) {
                        $fail('The final json field must contain valid JSON.');
                        return;
                    }

                    if (!is_array($decoded) || empty($decoded)) {
                        $fail('The final json field must contain a non-empty JSON object or array.');
                    }
                },
            ],
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first('final_json'),
                    'errors' => $validator->errors(),
                ], 422);
            }

            return redirect()->back()->withInput()->withErrors($validator);
        }

        try {
            $finalJsonInput = $request->input('final_json');
            $finalJson = is_array($finalJsonInput)
                ? $finalJsonInput
                : json_decode($finalJsonInput, true, 512, JSON_THROW_ON_ERROR);

            DB::beginTransaction();

            // save final_json for backup
            $tour->update(['final_json' => $finalJson]);

            // sync fields from json to tour model based on diff paths
            $this->tourService->syncTourFieldsFromJson($tour, $finalJson, $diffJson, true);

            // save tour updates once after syncing fields
            $tour->save();

            if (!$this->updateTourJsonAndJsFilesInS3($tour, $finalJson)) {
                DB::rollBack();

                $message = 'Tour JSON could not be uploaded to S3. No changes were saved.';

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message,
                    ], 500);
                }

                return redirect()->back()->withInput()->withErrors(['final_json' => $message]);
            }

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tour JSON updated successfully.',
                    'tour' => $tour->fresh(),
                ]);
            }

            return redirect()->back()->with('success', 'Tour JSON updated successfully.');
        } catch (JsonException $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            \Log::warning('Invalid JSON payload received for tour JSON update', [
                'tour_id' => $tour->id,
                'error' => $e->getMessage(),
            ]);

            $message = 'The final json field must contain valid JSON.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }

            return redirect()->back()->withInput()->withErrors(['final_json' => $message]);
        } catch (\Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            \Log::error('Failed to update tour JSON', [
                'tour_id' => $tour->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $message = 'An error occurred while updating the tour JSON.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 500);
            }

            return redirect()->back()->withInput()->withErrors(['final_json' => $message]);
        }
    }


    /**
     * Upload JSON file and update tour final_json
     */
    public function uploadJsonFile(Request $request, Tour $tour)
    {
        $request->validate([
            'json_file' => 'required|file|mimetypes:application/json',
            'DB_sync' => 'nullable|boolean'
        ]);
        $file = $request->file('json_file');
        $content = file_get_contents($file->getRealPath());
        $json = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return back()->withErrors(['json_file' => 'Invalid JSON file.']);
        }

        DB::beginTransaction();
        // update json
        $tour->final_json = $json;
        // sync fields from json to tour model, force-sync all fields since it's a full file upload
        $this->tourService->syncTourFieldsFromJson($tour, $json, [], true);
        // save tour updates once after syncing fields
        $tour->save();
        // commit DB changes before S3 upload, so if S3 fails we at least have the JSON in DB
        DB::commit();

        $this->updateTourJsonAndJsFilesInS3($tour, $json);

        return back()->with('success', 'JSON uploaded and updated successfully.');
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

    /**
     * Update contact information for a tour
     */
    public function updateContactInfo(Request $request, Tour $tour)
    {
        $validated = $request->validate([
            'contact_google_location' => ['nullable', 'string', 'max:255'],
            'contact_website' => ['nullable', 'url', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone_no' => ['nullable', 'string', 'max:20'],
            'contact_whatsapp_no' => ['nullable', 'string', 'max:20'],
            // New validation rules for attachments
            'attachment_file' => ['nullable', 'array'],
            'attachment_file.*.type' => ['nullable', 'string', 'in:image,video,document'],
            'attachment_file.*.tooltip' => ['nullable', 'string', 'max:255'],
            'attachment_file.*.file' => ['nullable', 'file', 'max:10240'], // 10MB max
            'attachment_file.*.action' => ['nullable', 'string', 'in:modal,download'],
        ]);

        $oldData = $tour->toArray();
        $final_json = $tour->toArray()['final_json'] ?? [];
        $qrCode = $tour->booking_id ? QR::where('booking_id', $tour->booking_id)->value('code') : null;

        // Process attachment files
        $attachmentFiles = [];
        if ($request->has('attachment_file')) {
            $existingAttachments = $tour->attachment_file ?? [];

            foreach ($request->input('attachment_file') as $index => $attachment) {
                // Skip if all fields are empty
                if (
                    empty($attachment['type']) && empty($attachment['tooltip']) &&
                    empty($attachment['action']) && !$request->hasFile("attachment_file.{$index}.file")
                ) {
                    // Keep existing attachment if it exists
                    if (isset($existingAttachments[$index])) {
                        $attachmentFiles[] = $existingAttachments[$index];
                    }
                    continue;
                }

                $attachmentData = [
                    'documentType' => $attachment['type'] ?? $existingAttachments[$index]['documentType'] ?? null,
                    'documentTooltip' => $attachment['tooltip'] ?? $existingAttachments[$index]['documentTooltip'] ?? null,
                    'documentAction' => $attachment['action'] ?? $existingAttachments[$index]['documentAction'] ?? 'modal',
                ];

                // Handle file upload
                if ($request->hasFile("attachment_file.{$index}.file")) {
                    $file = $request->file("attachment_file.{$index}.file");
                    if ($file->isValid()) {
                        $fileName = time() . '_' . $index . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                        $attachmentPath = $qrCode
                            ? 'tours/' . $qrCode . '/assets/attachments/' . $fileName
                            : 'attachments/' . $fileName;
                        $fileContent = file_get_contents($file->getRealPath());

                        if ($fileContent !== false) {
                            Storage::disk('s3')->put($attachmentPath, $fileContent, [
                                'ContentType' => $file->getMimeType() ?: 'application/octet-stream',
                            ]);
                            $attachmentData['documentUrl'] = Storage::disk('s3')->url($attachmentPath);
                        }

                        $attachmentData['documentFileName'] = $fileName;
                    }
                } else {

                    if ($request->input("attachment_file.{$index}.link")) {
                        $attachmentData['documentUrl'] = $request->input("attachment_file.{$index}.link");
                    } else {
                        // Keep existing file data if no new file uploaded
                        $attachmentData['documentUrl'] = $existingAttachments[$index]['documentUrl'] ?? null;
                        $attachmentData['documentFileName'] = $existingAttachments[$index]['documentFileName'] ?? null;
                    }
                }

                $attachmentFiles[] = $attachmentData;
            }
        }

        $userInfo = $final_json['userInfo'] ?? [];

        // Update the contact info of the user.
        $userInfo['googleLocation'] = $validated['contact_google_location'] ?? null;
        $userInfo['website'] = $validated['contact_website'] ?? null;
        $userInfo['email'] = $validated['contact_email'] ?? null;
        $userInfo['phoneNumber'] = $validated['contact_phone_no'] ?? null;
        $userInfo['whatsAppNumber'] = $validated['contact_whatsapp_no'] ?? null;
        $userInfo['autoRotateOnLoad'] = $validated['autoRotateOnLoad'] ?? false;

        // Update validated data with processed attachments
        if (!empty($attachmentFiles)) {
            $validated['attachment_file'] = $attachmentFiles;

            foreach ($attachmentFiles as $index => $doc) {

                // First document has no suffix
                $suffix = $index === 0 ? '' : $index + 1;

                $userInfo["documentType{$suffix}"] = $doc['documentType'] ?? null;
                $userInfo["documentUrl{$suffix}"] = $doc['documentUrl'] ?? null;
                $userInfo["documentTooltip{$suffix}"] = $doc['documentTooltip'] ?? null;
                $userInfo["documentAction{$suffix}"] = $doc['documentAction'] ?? null;

                // Only set fileName if exists
                if (!empty($doc['documentFileName'])) {
                    $userInfo["documentFileName{$suffix}"] = $doc['documentFileName'];
                }

                // Optional: if second document is video
                if ($suffix === '2' && ($doc['documentType'] ?? null) === 'video') {
                    $userInfo["documentIsYouTube2"] = false;
                }
            }
        } else {
            $validated['attachment_file'] = null;
        }

        $final_json['userInfo'] = $userInfo;

        // Add final_json to validated data to save in DB
        $validated['final_json'] = $final_json;

        $oldData = $tour->toArray();
        $tour->update($validated);
        $newData = $tour->fresh()->toArray();

        // Log activity
        activity('tours')
            ->performedOn($tour)
            ->causedBy(auth()->user())
            ->withProperties([
                'old' => $oldData,
                'new' => $newData,
            ])
            ->log('Contact information updated');

        // Update JSON and JS files in S3
        $this->updateTourJsonAndJsFilesInS3($tour, $final_json);

        // Return JSON response for AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Contact information updated successfully.',
                'tour' => $tour->fresh(),
            ]);
        }

        return redirect()->back()->with(['success' => 'Contact information updated successfully.', 'active_tab' => 'contact']);
    }

    /**
     * Update only tour contact information tab data.
     */
    public function updateTourContactInfoTab(Request $request, Tour $tour): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'contact_user_name' => ['nullable', 'string', 'max:255'],
            'contact_google_location' => ['nullable', 'string', 'max:255'],
            'contact_website' => ['nullable', 'url', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone_no' => ['nullable', 'string', 'max:20'],
            'contact_whatsapp_no' => ['nullable', 'string', 'max:20'],
            'show_contact_user_name' => ['nullable', 'boolean'],
            'show_contact_google_location' => ['nullable', 'boolean'],
            'show_contact_email' => ['nullable', 'boolean'],
            'show_contact_website' => ['nullable', 'boolean'],
            'show_contact_phone_no' => ['nullable', 'boolean'],
            'show_contact_whatsapp_no' => ['nullable', 'boolean'],
        ]);

        // Normalise checkbox booleans (unchecked checkboxes are absent from POST)
        foreach (['show_contact_user_name', 'show_contact_google_location', 'show_contact_email', 'show_contact_website', 'show_contact_phone_no', 'show_contact_whatsapp_no'] as $field) {
            $validated[$field] = $request->boolean($field);
        }

        $oldData = $tour->toArray();
        $finalJson = $this->normalizeFinalJsonPayload($tour);
        $userInfo = $finalJson['userInfo'] ?? [];
        $qrCode = $tour->booking_id ? QR::where('booking_id', $tour->booking_id)->value('code') : null;

        $userInfo['userName'] = $validated['contact_user_name'] ?? null;
        $userInfo['showUserName'] = $validated['show_contact_user_name'];
        $userInfo['googleLocation'] = $validated['contact_google_location'] ?? null;
        $userInfo['showGoogleLocation'] = $validated['show_contact_google_location'];
        $userInfo['website'] = $validated['contact_website'] ?? null;
        $userInfo['showWebsite'] = $validated['show_contact_website'];
        $userInfo['email'] = $validated['contact_email'] ?? null;
        $userInfo['showEmail'] = $validated['show_contact_email'];
        $userInfo['phoneNumber'] = $validated['contact_phone_no'] ?? null;
        $userInfo['showPhoneNumber'] = $validated['show_contact_phone_no'];
        $userInfo['whatsAppNumber'] = $validated['contact_whatsapp_no'] ?? null;
        $userInfo['showWhatsAppNumber'] = $validated['show_contact_whatsapp_no'];

        $finalJson['userInfo'] = $userInfo;

        $updateData = $validated;
        $updateData['final_json'] = $finalJson;
        $tour->update($updateData);
        $newData = $tour->fresh()->toArray();

        activity('tours')
            ->performedOn($tour)
            ->causedBy(auth()->user())
            ->withProperties([
                'old' => $oldData,
                'new' => $newData,
            ])
            ->log('Tour contact info tab updated');

        $this->updateTourJsonAndJsFilesInS3($tour, $finalJson);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tour contact information updated successfully.',
                'tour' => $tour->fresh(),
            ]);
        }

        return redirect()->back()->with(['success' => 'Tour contact information updated successfully.', 'active_tab' => 'vl-pills-tour-contact-info']);
    }

    /**
     * Update only tour attachments tab data.
     */
    public function updateTourAttachmentsTab(Request $request, Tour $tour): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'document_auth_required' => ['nullable', 'boolean'],
            'show_document_url' => ['nullable', 'boolean'],
            'show_document_url2' => ['nullable', 'boolean'],
            'attachment_file' => ['nullable', 'array'],
            'attachment_file.*.type' => ['nullable', 'string', 'in:image,video,document'],
            'attachment_file.*.tooltip' => ['nullable', 'string', 'max:255'],
            'attachment_file.*.link' => ['nullable', 'url', 'max:255'],
            'attachment_file.*.file' => ['nullable', 'file', 'max:10240'],
            'attachment_file.*.action' => ['nullable', 'string', 'in:modal,download'],
        ]);

        // Normalize checkbox value so unchecked state is stored as false.
        $validated['document_auth_required'] = $request->boolean('document_auth_required');
        $validated['show_document_url'] = $request->boolean('show_document_url');
        $validated['show_document_url2'] = $request->boolean('show_document_url2');

        $oldData = $tour->toArray();
        $finalJson = $this->normalizeFinalJsonPayload($tour);
        $userInfo = $finalJson['userInfo'] ?? [];
        $qrCode = $tour->booking_id ? QR::where('booking_id', $tour->booking_id)->value('code') : null;

        $attachmentFiles = [];
        if ($request->has('attachment_file')) {
            $existingAttachments = $tour->attachment_file ?? [];

            foreach ($request->input('attachment_file') as $index => $attachment) {
                if (
                    empty($attachment['type']) && empty($attachment['tooltip']) &&
                    empty($attachment['action']) && empty($attachment['link']) &&
                    !$request->hasFile("attachment_file.{$index}.file")
                ) {
                    if (isset($existingAttachments[$index])) {
                        $attachmentFiles[] = $existingAttachments[$index];
                    }
                    continue;
                }

                $attachmentData = [
                    'documentType' => $attachment['type'] ?? $existingAttachments[$index]['documentType'] ?? null,
                    'documentTooltip' => $attachment['tooltip'] ?? $existingAttachments[$index]['documentTooltip'] ?? null,
                    'documentAction' => $attachment['action'] ?? $existingAttachments[$index]['documentAction'] ?? 'modal',
                ];

                if ($request->hasFile("attachment_file.{$index}.file")) {
                    $file = $request->file("attachment_file.{$index}.file");
                    if ($file->isValid()) {
                        $fileName = time() . '_' . $index . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                        $attachmentPath = $qrCode
                            ? 'tours/' . $qrCode . '/assets/attachments/' . $fileName
                            : 'attachments/' . $fileName;
                        $fileContent = file_get_contents($file->getRealPath());

                        if ($fileContent !== false) {
                            Storage::disk('s3')->put($attachmentPath, $fileContent, [
                                'ContentType' => $file->getMimeType() ?: 'application/octet-stream',
                            ]);
                            $attachmentData['documentUrl'] = Storage::disk('s3')->url($attachmentPath);
                        }

                        $attachmentData['documentFileName'] = $fileName;
                    }
                } else {
                    if (!empty($attachment['link'])) {
                        $attachmentData['documentUrl'] = $attachment['link'];
                    } else {
                        $attachmentData['documentUrl'] = $existingAttachments[$index]['documentUrl'] ?? null;
                        $attachmentData['documentFileName'] = $existingAttachments[$index]['documentFileName'] ?? null;
                    }
                }

                $attachmentFiles[] = $attachmentData;
            }
        }

        if (!empty($attachmentFiles)) {
            foreach ($attachmentFiles as $index => $doc) {
                $suffix = $index === 0 ? '' : $index + 1;
                $userInfo["documentType{$suffix}"] = $doc['documentType'] ?? null;
                $userInfo["documentUrl{$suffix}"] = $doc['documentUrl'] ?? null;
                $userInfo["documentTooltip{$suffix}"] = $doc['documentTooltip'] ?? null;
                $userInfo["documentAction{$suffix}"] = $doc['documentAction'] ?? null;

                if (!empty($doc['documentFileName'])) {
                    $userInfo["documentFileName{$suffix}"] = $doc['documentFileName'];
                }

                if ($suffix === '2' && ($doc['documentType'] ?? null) === 'video') {
                    $userInfo['documentIsYouTube2'] = false;
                }
            }
        }

        $userInfo['documentAuthRequired'] = $validated['document_auth_required'];
        $userInfo['showDocumentUrl'] = $validated['show_document_url'];
        $userInfo['showDocumentUrl2'] = $validated['show_document_url2'];
        $finalJson['userInfo'] = $userInfo;

        $updateData = [
            'document_auth_required' => $validated['document_auth_required'],
            'show_document_url' => $validated['show_document_url'],
            'show_document_url2' => $validated['show_document_url2'],
            'attachment_file' => empty($attachmentFiles) ? null : $attachmentFiles,
            'final_json' => $finalJson,
        ];

        $tour->update($updateData);
        $newData = $tour->fresh()->toArray();

        activity('tours')
            ->performedOn($tour)
            ->causedBy(auth()->user())
            ->withProperties([
                'old' => $oldData,
                'new' => $newData,
            ])
            ->log('Tour attachments tab updated');

        $this->updateTourJsonAndJsFilesInS3($tour, $finalJson);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tour attachments updated successfully.',
                'tour' => $tour->fresh(),
            ]);
        }

        return redirect()->back()->with(['success' => 'Tour attachments updated successfully.', 'active_tab' => 'vl-pills-attachments']);
    }

    /**
     * Update tour settings (language and other settings)
     */
    public function updateTourSettings(Request $request, Tour $tour)
    {
        $validated = $request->validate([
            // language settings validation
            'enable_language' => ['nullable', 'array'],
            'enable_language.*' => ['string'],
            'default_language' => ['nullable', 'string', 'max:10'],
            //Loader settings validation
            'overlay_bg_color' => ['nullable', 'string', 'max:255'],
            'loader_text' => ['nullable', 'string', 'max:255'],
            'loader_color' => ['nullable', 'array'],
            'loader_color.*' => ['string'],
            'spinner_color' => ['nullable', 'array'],
            'spinner_color.*' => ['string'],
        ]);

        // Ensure array fields are arrays or null
        if (isset($validated['enable_language'])) {
            $validated['enable_language'] = array_values($validated['enable_language']);
        } else {
            $validated['enable_language'] = null;
        }

        if (isset($validated['loader_color'])) {
            $validated['loader_color'] = array_values($validated['loader_color']);
        } else {
            $validated['loader_color'] = null;
        }

        if (isset($validated['spinner_color'])) {
            $validated['spinner_color'] = array_values($validated['spinner_color']);
        } else {
            $validated['spinner_color'] = null;
        }

        $oldData = $tour->toArray();

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

        // Initialize or update loaderConfig in final_json
        $finalJson['loaderConfig'] = $finalJson['loaderConfig'] ?? [];

        // Update loader configuration in JSON
        if (!empty($validated['overlay_bg_color'])) {
            $finalJson['loaderConfig']['overlayBackgroundColor'] = $validated['overlay_bg_color'];
        }
        if (!empty($validated['loader_text'])) {
            $finalJson['loaderConfig']['loadingText'] = $validated['loader_text'];
        }
        if (!empty($validated['loader_color'])) {
            // Map array colors to gradient colors (assuming 3 colors for gradient)
            $finalJson['loaderConfig']['spinnerGradientColor1'] = $validated['loader_color'][0] ?? null;
            $finalJson['loaderConfig']['spinnerGradientColor2'] = $validated['loader_color'][1] ?? null;
            $finalJson['loaderConfig']['spinnerGradientColor3'] = $validated['loader_color'][2] ?? null;

            $finalJson['loaderConfig']['textGradientColor1'] = $validated['loader_color'][0] ?? null;
            $finalJson['loaderConfig']['textGradientColor2'] = $validated['loader_color'][1] ?? null;
            $finalJson['loaderConfig']['textGradientColor3'] = $validated['loader_color'][2] ?? null;
        }
        if (!empty($validated['spinner_color'])) {
            // Map array colors to spinner gradient (assuming 3 colors for gradient)
            $finalJson['loaderConfig']['spinnerGradientColor1'] = $validated['spinner_color'][0] ?? null;
            $finalJson['loaderConfig']['spinnerGradientColor2'] = $validated['spinner_color'][1] ?? null;
            $finalJson['loaderConfig']['spinnerGradientColor3'] = $validated['spinner_color'][2] ?? null;
        }

        // Initialize or update localeConfig in final_json
        $finalJson['localeConfig'] = $finalJson['localeConfig'] ?? [];

        // Update locale configuration in JSON
        if (!empty($validated['enable_language'])) {
            $finalJson['localeConfig']['enabledLanguages'] = $validated['enable_language'];
        }
        if (!empty($validated['default_language'])) {
            $finalJson['localeConfig']['defaultLanguage'] = $validated['default_language'];
        }

        // Add final_json to validated data to save in DB
        $validated['final_json'] = $finalJson;

        $tour->update($validated);
        $newData = $tour->fresh()->toArray();

        // Log activity
        activity('tours')
            ->performedOn($tour)
            ->causedBy(auth()->user())
            ->withProperties([
                'old' => $oldData,
                'new' => $newData,
            ])
            ->log('Tour settings updated');

        // Update JSON and JS files in S3
        $this->updateTourJsonAndJsFilesInS3($tour, $finalJson);

        // Return JSON response for AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tour settings updated successfully.',
                'tour' => $tour->fresh(),
            ]);
        }

        return redirect()->back()->with(['success' => 'Tour settings updated successfully.', 'active_tab' => 'tour-setting']);
    }

    /**
     * Update only loader configuration tab data.
     */
    public function updateTourLoaderConfigTab(Request $request, Tour $tour): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'overlay_bg_color' => ['nullable', 'string', 'max:255'],
            'loader_text' => ['nullable', 'string', 'max:255'],
            'loader_color' => ['nullable', 'array'],
            'loader_color.*' => ['nullable', 'string'],
            'spinner_color' => ['nullable', 'array'],
            'spinner_color.*' => ['nullable', 'string'],
        ]);

        $validated['loader_color'] = isset($validated['loader_color'])
            ? array_values(array_filter($validated['loader_color'], static fn($value) => !is_null($value) && $value !== ''))
            : null;

        $validated['spinner_color'] = isset($validated['spinner_color'])
            ? array_values(array_filter($validated['spinner_color'], static fn($value) => !is_null($value) && $value !== ''))
            : null;

        $oldData = $tour->toArray();
        $finalJson = $this->normalizeFinalJsonPayload($tour);
        $finalJson['loaderConfig'] = $finalJson['loaderConfig'] ?? [];

        if (array_key_exists('overlay_bg_color', $validated)) {
            $finalJson['loaderConfig']['overlayBackgroundColor'] = $validated['overlay_bg_color'];
        }
        if (array_key_exists('loader_text', $validated)) {
            $finalJson['loaderConfig']['loadingText'] = $validated['loader_text'];
        }

        if (!empty($validated['loader_color'])) {
            $finalJson['loaderConfig']['textGradientColor1'] = $validated['loader_color'][0] ?? null;
            $finalJson['loaderConfig']['textGradientColor2'] = $validated['loader_color'][1] ?? null;
            $finalJson['loaderConfig']['textGradientColor3'] = $validated['loader_color'][2] ?? null;
        }

        if (!empty($validated['spinner_color'])) {
            $finalJson['loaderConfig']['spinnerGradientColor1'] = $validated['spinner_color'][0] ?? null;
            $finalJson['loaderConfig']['spinnerGradientColor2'] = $validated['spinner_color'][1] ?? null;
            $finalJson['loaderConfig']['spinnerGradientColor3'] = $validated['spinner_color'][2] ?? null;
        }

        $updateData = [
            'overlay_bg_color' => $validated['overlay_bg_color'] ?? null,
            'loader_text' => $validated['loader_text'] ?? null,
            'loader_color' => $validated['loader_color'] ?? null,
            'spinner_color' => $validated['spinner_color'] ?? null,
            'final_json' => $finalJson,
        ];

        $tour->update($updateData);
        $newData = $tour->fresh()->toArray();

        activity('tours')
            ->performedOn($tour)
            ->causedBy(auth()->user())
            ->withProperties([
                'old' => $oldData,
                'new' => $newData,
            ])
            ->log('Tour loader config tab updated');

        $this->updateTourJsonAndJsFilesInS3($tour, $finalJson);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Loader configuration updated successfully.',
                'tour' => $tour->fresh(),
            ]);
        }

        return redirect()->back()->with(['success' => 'Loader configuration updated successfully.', 'active_tab' => 'vl-pills-loader-config']);
    }

    /**
     * Update only language tab data.
     */
    public function updateTourLanguageTab(Request $request, Tour $tour): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'enable_language' => ['nullable', 'array'],
            'enable_language.*' => ['string'],
            'default_language' => ['nullable', 'string', 'max:10'],
        ]);

        $validated['enable_language'] = isset($validated['enable_language'])
            ? array_values($validated['enable_language'])
            : null;

        $oldData = $tour->toArray();
        $finalJson = $this->normalizeFinalJsonPayload($tour);

        $finalJson['localeConfig'] = $finalJson['localeConfig'] ?? [];
        $finalJson['localeConfig']['enabledLanguages'] = $validated['enable_language'] ?? [];

        if (array_key_exists('default_language', $validated)) {
            $finalJson['localeConfig']['defaultLanguage'] = $validated['default_language'];
        }

        $updateData = [
            'enable_language' => $validated['enable_language'],
            'default_language' => $validated['default_language'] ?? null,
            'final_json' => $finalJson,
        ];

        $tour->update($updateData);
        $newData = $tour->fresh()->toArray();

        activity('tours')
            ->performedOn($tour)
            ->causedBy(auth()->user())
            ->withProperties([
                'old' => $oldData,
                'new' => $newData,
            ])
            ->log('Tour language tab updated');

        $this->updateTourJsonAndJsFilesInS3($tour, $finalJson);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Language section updated successfully.',
                'tour' => $tour->fresh(),
            ]);
        }

        return redirect()->back()->with(['success' => 'Language section updated successfully.', 'active_tab' => 'vl-pills-language']);
    }

    /**
     * Update only sidebar tab data.
     */
    public function updateTourSidebarTab(Request $request, Tour $tour): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'sidebar_logo' => ['nullable', 'file', 'image', 'max:5120'],
            'sidebar_tag_text' => ['nullable', 'string', 'max:255'],
            'sidebar_tag_color' => ['nullable', 'string', 'max:255'],
            'sidebar_tag_bg_color' => ['nullable', 'string', 'max:255'],
            'sidebar_footer_text' => ['nullable', 'string'],
            'sidebar_footer_link' => ['nullable', 'string'],
        ]);

        $oldData = $tour->toArray();
        $finalJson = $this->normalizeFinalJsonPayload($tour);
        $finalJson['sidebarConfig'] = $finalJson['sidebarConfig'] ?? [];
        $finalJson['sidebarConfig']['footerButton'] = $finalJson['sidebarConfig']['footerButton'] ?? [];
        $finalJson['sidebarConfig']['sidebarTag'] = $finalJson['sidebarConfig']['sidebarTag'] ?? [];

        $existingFooterButtonText = $finalJson['sidebarConfig']['footerButton']['text'] ?? [];
        if (!is_array($existingFooterButtonText)) {
            $existingFooterButtonText = ['en' => $existingFooterButtonText];
        }

        if (array_key_exists('sidebar_footer_text', $validated)) {
            $existingFooterButtonText['en'] = $validated['sidebar_footer_text'];
        }
        $finalJson['sidebarConfig']['footerButton']['text'] = $existingFooterButtonText;

        if (array_key_exists('sidebar_footer_link', $validated)) {
            $finalJson['sidebarConfig']['footerButton']['link'] = $validated['sidebar_footer_link'];
        }
        if (array_key_exists('sidebar_tag_text', $validated)) {
            $finalJson['sidebarConfig']['sidebarTag']['text'] = $validated['sidebar_tag_text'];
        }
        if (array_key_exists('sidebar_tag_color', $validated)) {
            $finalJson['sidebarConfig']['sidebarTag']['textColor'] = $validated['sidebar_tag_color'];
        }
        if (array_key_exists('sidebar_tag_bg_color', $validated)) {
            $finalJson['sidebarConfig']['sidebarTag']['backgroundColor'] = $validated['sidebar_tag_bg_color'];
        }

        $updateData = $validated;
        $qrCode = QR::where('booking_id', $tour->booking_id)->value('code');
        $logoSidebarFile = $request->file('sidebar_logo');

        if ($logoSidebarFile && $qrCode) {
            $sidebarFilename = 'logo_sidebar_' . time() . '_' . Str::random(8) . '.' . $logoSidebarFile->getClientOriginalExtension();
            $sidebarPath = 'tours/' . $qrCode . '/assets/' . $sidebarFilename;
            $sidebarContent = file_get_contents($logoSidebarFile->getRealPath());
            $sidebarMime = $logoSidebarFile->getMimeType();
            $uploaded = Storage::disk('s3')->put($sidebarPath, $sidebarContent, ['ContentType' => $sidebarMime]);
            $finalJson['sidebarConfig']['logo'] = 'assets/' . $sidebarFilename;

            if ($uploaded) {
                $updateData['sidebar_logo'] = Storage::disk('s3')->url($sidebarPath);
            }
        }

        $updateData['final_json'] = $finalJson;
        $tour->update($updateData);
        $newData = $tour->fresh()->toArray();

        activity('tours')
            ->performedOn($tour)
            ->causedBy(auth()->user())
            ->withProperties([
                'old' => $oldData,
                'new' => $newData,
            ])
            ->log('Tour sidebar tab updated');

        $this->updateTourJsonAndJsFilesInS3($tour, $finalJson);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Sidebar section updated successfully.',
                'tour' => $tour->fresh(),
            ]);
        }

        return redirect()->back()->with(['success' => 'Sidebar section updated successfully.', 'active_tab' => 'vl-pills-sidebar-section']);
    }

    public function updateSidebarLinks(Request $request, Tour $tour): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'sidebar_links' => ['nullable', 'array'],
            'sidebar_links.*.icon' => ['nullable', 'string', 'max:255'],
            'sidebar_links.*.title' => ['nullable', 'array'],
            'sidebar_links.*.title.en' => ['nullable', 'string'],
            'sidebar_links.*.title.gu' => ['nullable', 'string'],
            'sidebar_links.*.title.hi' => ['nullable', 'string'],
            'sidebar_links.*.type' => ['required', 'string', 'in:link,content,infoModal'],
            'sidebar_links.*.order' => ['required', 'integer', 'min:1'],
            'sidebar_links.*.link' => ['nullable', 'url', 'max:255'],
            'sidebar_links.*.content' => ['nullable', 'array'],
            'sidebar_links.*.content.en' => ['nullable', 'string'],
            'sidebar_links.*.content.gu' => ['nullable', 'string'],
            'sidebar_links.*.content.hi' => ['nullable', 'string'],
        ]);

        $sidebarLinks = collect($validated['sidebar_links'] ?? [])->map(function ($item) {
            $title = isset($item['title']) ? (array) $item['title'] : [];
            $content = isset($item['content']) ? (array) $item['content'] : [];

            return [
                'icon' => !empty($item['icon']) ? trim($item['icon']) : null,
                'title' => [
                    'en' => trim($title['en']),
                    'gu' => trim($title['gu']),
                    'hi' => trim($title['hi']),
                ],
                'type' => $item['type'] ?? 'link',
                'order' => (int) ($item['order'] ?? 140),
                'link' => $item['type'] === 'link' ? trim($item['link'] ?? '') : null,
                'content' => [
                    'en' => $content['en'],
                    'gu' => $content['gu'],
                    'hi' => $content['hi'],
                ],
            ];
        })->filter(function ($item) {
            // Ensure English title is not empty and type is valid
            return !empty($item['title']['en']) && in_array($item['type'], ['link', 'content', 'infoModal'], true);
        })->sortBy('order')->values()->toArray();

        $oldData = $tour->toArray();
        $finalJson = $this->normalizeFinalJsonPayload($tour);

        // Ensure sidebarConfig structure exists
        $finalJson['sidebarLinks'] = $sidebarLinks;

        // Persist both DB column and final_json for consistency
        $updateData = [
            'final_json' => $finalJson,
            'sidebar_links' => $sidebarLinks,
        ];

        $tour->update($updateData);
        $newData = $tour->fresh()->toArray();

        // Update S3 JSON/JS files with new final_json
        $this->updateTourJsonAndJsFilesInS3($tour, $finalJson);

        activity('tours')
            ->performedOn($tour)
            ->causedBy(auth()->user())
            ->withProperties([
                'old' => $oldData,
                'new' => $newData,
            ])
            ->log('Tour sidebar links updated');

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Sidebar links updated successfully.',
                'tour' => $tour->fresh(),
            ]);
        }

        return redirect()->back()->with(['success' => 'Sidebar links updated successfully.', 'active_tab' => 'vl-pills-sidebar-section']);
    }

    public function updateTourSidebarNodes(Request $request, Tour $tour): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'nodes' => ['nullable', 'array'],
            'nodes.*' => ['nullable', 'array'],
            'sidebar_node' => ['nullable', 'array'],
            'sidebar_node.*' => ['nullable', 'array'],
        ]);

        $sidebarNodes = collect($validated['nodes'] ?? $validated['sidebar_node'] ?? [])
            ->filter(function ($node) {
                return is_array($node)
                    && array_key_exists('sideMenuOrder', $node)
                    && $node['sideMenuOrder'] !== null
                    && $node['sideMenuOrder'] !== '';
            })
            ->values()
            ->map(function (array $node, int $index) {
                $node['sideMenuOrder'] = $index;

                return $node;
            })
            ->toArray();

        $oldData = $tour->toArray();
        $finalJson = $this->normalizeFinalJsonPayload($tour);
        $finalJson['nodes'] = $sidebarNodes;

        $tour->update([
            'sidebar_node' => $sidebarNodes,
            'final_json' => $finalJson,
        ]);
        $newData = $tour->fresh()->toArray();

        activity('tours')
            ->performedOn($tour)
            ->causedBy(auth()->user())
            ->withProperties([
                'old' => $oldData,
                'new' => $newData,
            ])
            ->log('Tour sidebar nodes updated');

        $this->updateTourJsonAndJsFilesInS3($tour, $finalJson);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Sidebar nodes updated successfully.',
                'tour' => $tour->fresh(),
            ]);
        }

        return redirect()->back()->with(['success' => 'Sidebar nodes updated successfully.', 'active_tab' => 'vl-pills-sidebar-section']);
    }

    /**
     * Update only bottom top tab data.
     */
    public function updateTourBottomTopTab(Request $request, Tour $tour): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'footer_logo' => ['nullable', 'file', 'image', 'max:5120'],
            'footer_title' => ['nullable', 'array'],
            'footer_title.en' => ['nullable', 'string'],
            'footer_title.gu' => ['nullable', 'string'],
            'footer_title.hi' => ['nullable', 'string'],
            'footer_subtitle' => ['nullable', 'array'],
            'footer_subtitle.en' => ['nullable', 'string'],
            'footer_subtitle.gu' => ['nullable', 'string'],
            'footer_subtitle.hi' => ['nullable', 'string'],
            'footer_decription' => ['nullable', 'array'],
            'footer_decription.en' => ['nullable', 'string'],
            'footer_decription.gu' => ['nullable', 'string'],
            'footer_decription.hi' => ['nullable', 'string'],
            'footer_email' => ['nullable', 'string', 'max:255'],
            'footer_mobile' => ['nullable', 'string', 'max:255'],
        ]);

        $oldData = $tour->toArray();
        $finalJson = $this->normalizeFinalJsonPayload($tour);
        $finalJson['bottomMarker'] = $finalJson['bottomMarker'] ?? [];

        $resolvedFooterTitle = is_array($validated['footer_title'] ?? null) ? $validated['footer_title'] : [];
        $resolvedFooterSubtitle = is_array($validated['footer_subtitle'] ?? null) ? $validated['footer_subtitle'] : [];
        $resolvedFooterDescription = is_array($validated['footer_decription'] ?? null) ? $validated['footer_decription'] : [];

        $existingTopTitle = $finalJson['bottomMarker']['topTitle'] ?? [];
        if (!is_array($existingTopTitle)) {
            $existingTopTitle = ['en' => $existingTopTitle];
        }
        foreach (['en', 'gu', 'hi'] as $lang) {
            if (array_key_exists($lang, $resolvedFooterTitle)) {
                $existingTopTitle[$lang] = $resolvedFooterTitle[$lang];
            }
        }
        $finalJson['bottomMarker']['topTitle'] = $existingTopTitle;

        $existingTopSubTitle = $finalJson['bottomMarker']['topSubTitle'] ?? [];
        if (!is_array($existingTopSubTitle)) {
            $existingTopSubTitle = ['en' => $existingTopSubTitle];
        }
        foreach (['en', 'gu', 'hi'] as $lang) {
            if (array_key_exists($lang, $resolvedFooterSubtitle)) {
                $existingTopSubTitle[$lang] = $resolvedFooterSubtitle[$lang];
            }
        }
        $finalJson['bottomMarker']['topSubTitle'] = $existingTopSubTitle;

        $existingTopDescription = $finalJson['bottomMarker']['topDescription'] ?? [];
        if (!is_array($existingTopDescription)) {
            $existingTopDescription = ['en' => $existingTopDescription];
        }
        foreach (['en', 'gu', 'hi'] as $lang) {
            if (array_key_exists($lang, $resolvedFooterDescription)) {
                $existingTopDescription[$lang] = $resolvedFooterDescription[$lang];
            }
        }
        $finalJson['bottomMarker']['topDescription'] = $existingTopDescription;

        if (array_key_exists('footer_mobile', $validated)) {
            $finalJson['bottomMarker']['contactNumber'] = $validated['footer_mobile'];
        }
        if (array_key_exists('footer_email', $validated)) {
            $finalJson['bottomMarker']['contactEmail'] = $validated['footer_email'];
        }

        $updateData = $validated;
        $updateData['footer_title'] = empty($resolvedFooterTitle) ? null : $resolvedFooterTitle;
        $updateData['footer_subtitle'] = empty($resolvedFooterSubtitle) ? null : $resolvedFooterSubtitle;
        $updateData['footer_decription'] = empty($resolvedFooterDescription) ? null : $resolvedFooterDescription;

        $qrCode = QR::where('booking_id', $tour->booking_id)->value('code');
        $logoFooterFile = $request->file('footer_logo');
        if ($logoFooterFile && $qrCode) {
            $footerFilename = 'logo_footer_' . time() . '_' . Str::random(8) . '.' . $logoFooterFile->getClientOriginalExtension();
            $footerPath = 'tours/' . $qrCode . '/assets/' . $footerFilename;
            $footerContent = file_get_contents($logoFooterFile->getRealPath());
            $footerMime = $logoFooterFile->getMimeType();
            $uploaded = Storage::disk('s3')->put($footerPath, $footerContent, ['ContentType' => $footerMime]);
            $finalJson['bottomMarker']['topImage'] = 'assets/' . $footerFilename;

            if ($uploaded) {
                $updateData['footer_logo'] = Storage::disk('s3')->url($footerPath);
            }
        }

        $updateData['final_json'] = $finalJson;
        $tour->update($updateData);
        $newData = $tour->fresh()->toArray();

        activity('tours')
            ->performedOn($tour)
            ->causedBy(auth()->user())
            ->withProperties([
                'old' => $oldData,
                'new' => $newData,
            ])
            ->log('Tour bottom top tab updated');

        $this->updateTourJsonAndJsFilesInS3($tour, $finalJson);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Bottom top section updated successfully.',
                'tour' => $tour->fresh(),
            ]);
        }

        return redirect()->back()->with(['success' => 'Bottom top section updated successfully.', 'active_tab' => 'vl-pills-bottom-mark-top']);
    }

    /**
     * Update only bottom property tab data.
     */
    public function updateTourBottomPropertyTab(Request $request, Tour $tour): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'bottommark_property_name_en' => ['nullable', 'string'],
            'bottommark_property_name_gu' => ['nullable', 'string'],
            'bottommark_property_name_hi' => ['nullable', 'string'],
            'bottommark_room_type_en' => ['nullable', 'string'],
            'bottommark_room_type_gu' => ['nullable', 'string'],
            'bottommark_room_type_hi' => ['nullable', 'string'],
            'bottommark_dimensions_en' => ['nullable', 'string'],
            'bottommark_dimensions_gu' => ['nullable', 'string'],
            'bottommark_dimensions_hi' => ['nullable', 'string'],
        ]);

        $oldData = $tour->toArray();
        $finalJson = $this->normalizeFinalJsonPayload($tour);
        $finalJson['bottomMarker'] = $finalJson['bottomMarker'] ?? [];

        $resolvedPropertyName = array_filter([
            'en' => $validated['bottommark_property_name_en'] ?? '',
            'gu' => $validated['bottommark_property_name_gu'] ?? '',
            'hi' => $validated['bottommark_property_name_hi'] ?? '',
        ], static fn($value) => !is_null($value));

        $resolvedRoomType = array_filter([
            'en' => $validated['bottommark_room_type_en'] ?? '',
            'gu' => $validated['bottommark_room_type_gu'] ?? '',
            'hi' => $validated['bottommark_room_type_hi'] ?? '',
        ], static fn($value) => !is_null($value));

        $resolvedDimensions = array_filter([
            'en' => $validated['bottommark_dimensions_en'] ?? '',
            'gu' => $validated['bottommark_dimensions_gu'] ?? '',
            'hi' => $validated['bottommark_dimensions_hi'] ?? '',
        ], static fn($value) => !is_null($value));

        if (!empty($resolvedPropertyName)) {
            $finalJson['bottomMarker']['propertyName'] = $resolvedPropertyName;
        }
        if (!empty($resolvedRoomType)) {
            $finalJson['bottomMarker']['roomType'] = $resolvedRoomType;
        }
        if (!empty($resolvedDimensions)) {
            $finalJson['bottomMarker']['dimensions'] = $resolvedDimensions;
        }

        $updateData = $validated;
        $updateData['bottommark_property_name'] = empty($resolvedPropertyName) ? null : $resolvedPropertyName;
        $updateData['bottommark_room_type'] = empty($resolvedRoomType) ? null : $resolvedRoomType;
        $updateData['bottommark_dimensions'] = empty($resolvedDimensions) ? null : $resolvedDimensions;

        unset(
            $updateData['bottommark_property_name_en'],
            $updateData['bottommark_property_name_gu'],
            $updateData['bottommark_property_name_hi'],
            $updateData['bottommark_room_type_en'],
            $updateData['bottommark_room_type_gu'],
            $updateData['bottommark_room_type_hi'],
            $updateData['bottommark_dimensions_en'],
            $updateData['bottommark_dimensions_gu'],
            $updateData['bottommark_dimensions_hi']
        );

        $updateData['final_json'] = $finalJson;
        $tour->update($updateData);
        $newData = $tour->fresh()->toArray();

        activity('tours')
            ->performedOn($tour)
            ->causedBy(auth()->user())
            ->withProperties([
                'old' => $oldData,
                'new' => $newData,
            ])
            ->log('Tour bottom property tab updated');

        $this->updateTourJsonAndJsFilesInS3($tour, $finalJson);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Bottom property section updated successfully.',
                'tour' => $tour->fresh(),
            ]);
        }

        return redirect()->back()->with(['success' => 'Bottom property section updated successfully.', 'active_tab' => 'vl-pills-bottom-mark-property']);
    }

    private function normalizeFinalJsonPayload(Tour $tour): array
    {
        $rawFinalJson = $tour->final_json;

        if (is_array($rawFinalJson)) {
            return $rawFinalJson;
        }

        if (is_string($rawFinalJson) && trim($rawFinalJson) !== '') {
            $decoded = json_decode($rawFinalJson, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        if (is_object($rawFinalJson)) {
            $decoded = json_decode(json_encode($rawFinalJson), true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }

    /**
     * Method to update the basic details of the tour.
     * tab page function
     * @param Tour $tour The tour model
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse | \Illuminate\Http\RedirectResponse
     */
    public function UpdateBasicInfoOfTourDetails(Request $request, Tour $tour): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:tours,slug,' . $tour->id],
            'description' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:draft,published,archived'],
            'revision' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'duration_days' => ['nullable', 'integer', 'min:1'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'max_participants' => ['nullable', 'integer', 'min:1'],
            'featured_image' => ['nullable', 'string'],
            'tour_thumbnail' => ['nullable', 'file', 'image', 'max:5120'],
            'is_active' => ['nullable', 'boolean'],
            'is_credentials' => ['nullable', 'boolean'],
            'is_mobile_validation' => ['nullable', 'boolean'],
            'is_hosted' => ['nullable', 'boolean'],
            'hosted_link' => ['nullable', 'url', 'max:255'],
            'credentials' => ['nullable', 'array'],
            'credentials.*.id' => ['nullable', 'exists:tour_credentials,id'],
            'credentials.*.user_name' => ['required_with:credentials', 'string', 'max:255'],
            'credentials.*.password' => ['required_with:credentials', 'string', 'max:255'],
            'credentials.*.is_active' => ['nullable', 'boolean'],
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);

            $originalSlug = $validated['slug'];
            $counter = 1;
            while (Tour::where('slug', $validated['slug'])->where('id', '!=', $tour->id)->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        $updateData = $validated;

        $updateData['is_active'] = $request->has('is_active');
        $updateData['is_credentials'] = $request->has('is_credentials');
        $updateData['is_mobile_validation'] = $request->has('is_mobile_validation');
        $updateData['is_hosted'] = $request->has('is_hosted');

        if (!$updateData['is_hosted']) {
            $updateData['hosted_link'] = null;
        }

        $tourThumbnailFile = $request->file('tour_thumbnail');
        if ($tourThumbnailFile) {
            $thumbFilename = 'tour_thumb_' . time() . '_' . Str::random(8) . '.' . $tourThumbnailFile->getClientOriginalExtension();
            $thumbPath = 'settings/tour_thumbnails/' . $thumbFilename;
            $thumbContent = file_get_contents($tourThumbnailFile->getRealPath());
            $thumbMime = $tourThumbnailFile->getMimeType();
            $uploaded = Storage::disk('s3')->put($thumbPath, $thumbContent, ['ContentType' => $thumbMime]);
            if ($uploaded) {
                $updateData['tour_thumbnail'] = $thumbPath;
            }
        }

        if ($request->has('credentials')) {
            $tour->credentials()->delete();
            foreach ($request->credentials as $credentialData) {
                if (!empty($credentialData['user_name']) && !empty($credentialData['password'])) {
                    $tour->credentials()->create([
                        'user_name' => $credentialData['user_name'],
                        'password' => $credentialData['password'],
                        'is_active' => isset($credentialData['is_active']) ? (bool) $credentialData['is_active'] : true,
                    ]);
                }
            }
        } elseif (!$updateData['is_credentials']) {
            $tour->credentials()->delete();
        }

        unset($updateData['credentials']);

        $oldData = $tour->toArray();
        $tour->update($updateData);
        $newData = $tour->fresh()->toArray();

        // Log activity
        activity('tours')
            ->performedOn($tour)
            ->causedBy(auth()->user())
            ->withProperties([
                'old' => $oldData,
                'new' => $newData,
            ])
            ->log('Tour basic info updated');

        // Return JSON response for AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tour basic information updated successfully.',
                'tour' => $tour->fresh(),
            ]);
        }

        return redirect()->back()->with(['success' => 'Tour basic information updated successfully.', 'active_tab' => 'basic-info']);
    }

    public function updateUserDetails(Request $request, Tour $tour): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'show_user_details_button' => ['boolean'],
            'user_details_button_icon' => ['nullable', 'string'],
            'user_details_button_tooltip' => ['nullable', 'string'],
            'user_details' => ['nullable', 'array'],
        ]);

        // Update DB columns for simple fields
        $tour->update([
            'show_user_details_button' => $request->has('show_user_details_button'),
            'user_details_button_icon' => $validated['user_details_button_icon'] ?? null,
            'user_details_button_tooltip' => $validated['user_details_button_tooltip'] ?? null,
            'user_details' => $validated['user_details'] ?? null,
        ]);

        // Get current final_json
        $finalJson = $tour->final_json ?? [];

        // Update user details array in final_json
        $finalJson['userInfo']['userDetails'] = $validated['user_details'] ?? [];
        $finalJson['userInfo']['showUserDetailsButton'] = $request->has('show_user_details_button');
        $finalJson['userInfo']['userDetailsButtonIcon'] = $validated['user_details_button_icon'] ?? '';
        $finalJson['userInfo']['userDetailsButtonTooltip'] = $validated['user_details_button_tooltip'] ?? '';

        // Update the tour with final_json
        $tour->update(['final_json' => $finalJson]);

        // Sync to S3
        $this->updateTourJsonAndJsFilesInS3($tour, $finalJson);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'User details updated successfully.',
                'tour' => $tour->fresh(),
            ]);
        }

        return redirect()->back()->with(['success' => 'User details updated successfully.']);
    }


    /**
     * Update only bookmark tab data. 
     * Bookmark Updating 
     * @param Request $request
     * @param Tour $tour
     * @return JsonResponse|RedirectResponse
     * */
    public function updateBookmarkFields(Request $request, Tour $tour): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'bookmark_title' => ['nullable', 'string', 'max:255'],
            'bookmark_ribbon_background_color' => ['nullable', 'string', 'max:100'],
            'bookmark_ribbon_text_color' => ['nullable', 'string', 'max:100'],
            'bookmark_show_on_tour_load' => ['nullable', 'boolean'],
            'bookmark_show_on_tour_load_delay_ms' => ['nullable', 'integer', 'min:0'],
            'bookmark_action' => ['nullable', 'string', 'max:255'],
            'bookmark_modal_title' => ['nullable', 'array'],
            'bookmark_modal_title.en' => ['nullable', 'string'],
            'bookmark_modal_title.gu' => ['nullable', 'string'],
            'bookmark_modal_title.hi' => ['nullable', 'string'],
            'bookmark_modal_description' => ['nullable', 'array'],
            'bookmark_modal_description.en' => ['nullable', 'string'],
            'bookmark_modal_description.gu' => ['nullable', 'string'],
            'bookmark_modal_description.hi' => ['nullable', 'string'],
            'bookmark_info_modal_footer_button_title' => ['nullable', 'array'],
            'bookmark_info_modal_footer_button_title.en' => ['nullable', 'string'],
            'bookmark_info_modal_footer_button_title.gu' => ['nullable', 'string'],
            'bookmark_info_modal_footer_button_link' => ['nullable', 'string', 'max:500'],
            'bookmark_info_modal_footer_text' => ['nullable', 'array'],
            'bookmark_info_modal_footer_text.en' => ['nullable', 'string'],
            'bookmark_info_modal_footer_text.gu' => ['nullable', 'string'],
            'bookmark_open_link_url' => ['nullable', 'string', 'max:500'],
            'bookmark_document_url' => ['nullable', 'string', 'max:500'],
            'bookmark_video_url' => ['nullable', 'string', 'max:500'],
            'bookmark_image_url' => ['nullable', 'string', 'max:500'],
            'bookmark_document_file' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt'],
            'bookmark_video_file' => ['nullable', 'file', 'max:102400', 'mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/x-matroska,video/webm'],
            'bookmark_image_file' => ['nullable', 'file', 'image', 'max:10240'],
        ]);

        $oldData = $tour->toArray();
        $finalJson = $this->normalizeFinalJsonPayload($tour);

        $qrCode = $tour->booking_id ? QR::where('booking_id', $tour->booking_id)->value('code') : null;

        $bookmarkDocumentUrl = $validated['bookmark_document_url'] ?? null;
        $bookmarkVideoUrl = $validated['bookmark_video_url'] ?? null;
        $bookmarkImageUrl = $validated['bookmark_image_url'] ?? null;

        $uploadConfigs = [
            'bookmark_document_file' => [
                'prefix' => 'bookmark_document',
                'field' => 'bookmarkDocumentUrl',
            ],
            'bookmark_video_file' => [
                'prefix' => 'bookmark_video',
                'field' => 'bookmarkVideoUrl',
            ],
            'bookmark_image_file' => [
                'prefix' => 'bookmark_image',
                'field' => 'bookmarkImageUrl',
            ],
        ];

        foreach ($uploadConfigs as $inputName => $config) {
            if (!$request->hasFile($inputName)) {
                continue;
            }

            $file = $request->file($inputName);
            if (!$file || !$file->isValid()) {
                continue;
            }

            $fileName = $config['prefix'] . '_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $filePath = $qrCode
                ? 'tours/' . $qrCode . '/info/' . $fileName
                : 'info/' . $fileName;
            $fileContent = file_get_contents($file->getRealPath());

            if ($fileContent === false) {
                continue;
            }

            Storage::disk('s3')->put($filePath, $fileContent, [
                'ContentType' => $file->getMimeType() ?: 'application/octet-stream',
            ]);

            $uploadedUrl = Storage::disk('s3')->url($filePath);
            if ($config['field'] === 'bookmarkDocumentUrl') {
                $bookmarkDocumentUrl = $uploadedUrl;
            } elseif ($config['field'] === 'bookmarkVideoUrl') {
                $bookmarkVideoUrl = $uploadedUrl;
            } elseif ($config['field'] === 'bookmarkImageUrl') {
                $bookmarkImageUrl = $uploadedUrl;
            }
        }

        $updateData = [
            'bookmark_title' => $validated['bookmark_title'] ?? null,
            'bookmark_ribbon_background_color' => $validated['bookmark_ribbon_background_color'] ?? null,
            'bookmark_ribbon_text_color' => $validated['bookmark_ribbon_text_color'] ?? null,
            'bookmark_show_on_tour_load' => $request->boolean('bookmark_show_on_tour_load'),
            'bookmark_show_on_tour_load_delay_ms' => $validated['bookmark_show_on_tour_load_delay_ms'] ?? 0,
            'bookmark_action' => $validated['bookmark_action'] ?? null,
            'bookmark_modal_title' => $validated['bookmark_modal_title'] ?? null,
            'bookmark_modal_description' => $validated['bookmark_modal_description'] ?? null,
            'bookmark_info_modal_footer_button_title' => $validated['bookmark_info_modal_footer_button_title'] ?? null,
            'bookmark_info_modal_footer_button_link' => $validated['bookmark_info_modal_footer_button_link'] ?? null,
            'bookmark_info_modal_footer_text' => $validated['bookmark_info_modal_footer_text'] ?? null,
            'bookmark_open_link_url' => $validated['bookmark_open_link_url'] ?? null,
            'bookmark_document_url' => $bookmarkDocumentUrl,
            'bookmark_video_url' => $bookmarkVideoUrl,
            'bookmark_image_url' => $bookmarkImageUrl,
        ];

        // Keep DB fields snake_case, but persist tour JSON inside bookmark object in camelCase.
        $finalJson['bookmark'] = $finalJson['bookmark'] ?? [];
        $finalJson['bookmark']['bookmarkTitle'] = $updateData['bookmark_title'];
        $finalJson['bookmark']['ribbonBackgroundColor'] = $updateData['bookmark_ribbon_background_color'];
        $finalJson['bookmark']['ribbonTextColor'] = $updateData['bookmark_ribbon_text_color'];
        $finalJson['bookmark']['showOnTourLoad'] = $updateData['bookmark_show_on_tour_load'];
        $finalJson['bookmark']['showOnTourLoadDelayMs'] = $updateData['bookmark_show_on_tour_load_delay_ms'];
        $finalJson['bookmark']['action'] = $updateData['bookmark_action'];
        $finalJson['bookmark']['modalTitle'] = $updateData['bookmark_modal_title'];
        $finalJson['bookmark']['modalDescription'] = $updateData['bookmark_modal_description'];
        $finalJson['bookmark']['infoModalFooterButtonTitle'] = $updateData['bookmark_info_modal_footer_button_title'];
        $finalJson['bookmark']['infoModalFooterButtonLink'] = $updateData['bookmark_info_modal_footer_button_link'];
        $finalJson['bookmark']['infoModalFooterText'] = $updateData['bookmark_info_modal_footer_text'];
        $finalJson['bookmark']['openLinkUrl'] = $updateData['bookmark_open_link_url'];
        $finalJson['bookmark']['documentUrl'] = $updateData['bookmark_document_url'];
        $finalJson['bookmark']['videoUrl'] = $updateData['bookmark_video_url'];
        $finalJson['bookmark']['imageUrl'] = $updateData['bookmark_image_url'];

        $updateData['final_json'] = $finalJson;

        $tour->update($updateData);
        $newData = $tour->fresh()->toArray();

        activity('tours')
            ->performedOn($tour)
            ->causedBy(auth()->user())
            ->withProperties([
                'old' => $oldData,
                'new' => $newData,
            ])
            ->log('Tour bookmark fields updated');

        $this->updateTourJsonAndJsFilesInS3($tour, $finalJson);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Bookmark fields updated successfully.',
                'tour' => $tour->fresh(),
            ]);
        }

        return redirect()->back()->with(['success' => 'Bookmark fields updated successfully.']);
    }

    /**
     * Update user star configuration and details.
     * @param Request $request
     * @param Tour $tour
     * @return JsonResponse|RedirectResponse
     */
    public function updateUserStar(Request $request, Tour $tour): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'user_star_show_ribbon' => ['nullable', 'boolean'],
            'user_star_show_modal' => ['nullable', 'boolean'],
            'user_star_show_cta_button' => ['nullable', 'boolean'],
            'user_star_cta_button_text' => ['nullable', 'string'],
            'user_star_cta_button_link' => ['nullable', 'string'],
            'user_star_cta_label_size' => ['nullable', 'string'],
            'user_star_cta_label_color' => ['nullable', 'string'],
            'stars' => ['nullable', 'array'],
            'stars.*.label' => ['nullable', 'string'],
            'stars.*.count' => ['nullable', 'numeric', 'min:0'],
            'stars.*.url' => ['nullable', 'string'],
        ]);

        $stars = collect($validated['stars'] ?? [])
            ->map(function ($star) {
                return [
                    'label' => $star['label'] ?? '',
                    'count' => isset($star['count']) && $star['count'] !== '' ? (float) $star['count'] : 0,
                    'url' => $star['url'] ?? '',
                ];
            })
            ->filter(function ($star) {
                return $star['label'] !== '' || $star['url'] !== '' || (float) $star['count'] > 0;
            })
            ->values()
            ->all();

        $userStar = [
            'stars' => $stars,
            'showRibbon' => $request->has('user_star_show_ribbon'),
            'showModalOnLoad' => $request->has('user_star_show_modal'),
            'showCtaButton' => $request->has('user_star_show_cta_button'),
            'ctaLabel' => $validated['user_star_cta_button_text'] ?? 'Learn More',
            'ctaColor' => $validated['user_star_cta_label_color'] ?? '#da8f67',
            'ctaSize' => $validated['user_star_cta_label_size'] ?? 'medium',
            'ctaLink' => $validated['user_star_cta_button_link'] ?? null,
        ];

        $finalJson = $this->normalizeFinalJsonPayload($tour);
        $finalJson['bottomMarker']['userStars'] = $userStar;

        $tour->update([
            'user_star' => $userStar,
            'final_json' => $finalJson,
        ]);

        $this->updateTourJsonAndJsFilesInS3($tour, $finalJson);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'User star updated successfully.',
                'tour' => $tour->fresh(),
            ]);
        }

        return redirect()->back()->with([
            'success' => 'User star updated successfully.',
            'active_tab' => 'vl-pills-userStar',
        ]);
    }

}


