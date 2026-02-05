<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\QR;
use App\Models\Setting;
use App\Models\Tour;
use App\Models\FtpConfiguration;
use App\Jobs\UploadTourAssetsToS3;
use App\Jobs\ProcessTourZipFile;
use GrahamCampbell\ResultType\Success;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use ZipArchive;
use Aws\S3\Exception\S3Exception;

class TourManagerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:tour_manager_view')->only(['index', 'show']);
        $this->middleware('permission:tour_manager_edit')->only(['edit', 'update', 'uploadFile', 'scheduleTour']);
    }

    /**
     * Display a listing of bookings for tour management
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Booking::with([
                'user',
                'propertyType',
                'propertySubType',
                'bhk',
                'city',
                'state',
                'tours'
            ])->orderBy('created_at', 'desc');

            // Apply filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('payment_status')) {
                $query->where('payment_status', $request->payment_status);
            }

            if ($request->filled('property_type_id')) {
                $query->where('property_type_id', $request->property_type_id);
            }

            if ($request->filled('city_id')) {
                $query->where('city_id', $request->city_id);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('booking_date', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('booking_date', '<=', $request->date_to);
            }
            return DataTables::of($query)
                ->addColumn('booking_id', function (Booking $booking) {
                    return '<strong>#' . $booking->id . '</strong>';
                })
                ->addColumn('booking_info', function (Booking $booking) {
                    $propertyType = $booking->propertyType?->name ?? 'N/A';
                    $subType = $booking->propertySubType?->name ?? '';
                    $bhk = $booking->bhk?->name ?? '';

                    // Get tour name safely - check if tours relation is loaded
                    $tourName = null;
                    if (isset($booking->tours) && is_object($booking->tours)) {
                        if (is_iterable($booking->tours)) {
                            $tour = $booking->tours instanceof \Illuminate\Database\Eloquent\Collection
                                ? $booking->tours->first()
                                : current($booking->tours);
                            $tourName = $tour?->name;
                        }
                    }

                    $info = '';
                    $info .= '<p>' . $propertyType;
                    if ($subType)
                        $info .= ' - ' . $subType;
                    if ($bhk)
                        $info .= ' - ' . $bhk;
                    $info .= '</br>';
                    if ($tourName) {
                        $info .= e($tourName) . '</p>';
                    }
                    return $info;
                })
                ->addColumn('customer', function (Booking $booking) {
                    $name = $booking->user ? $booking->user->firstname . ' ' . $booking->user->lastname : '-';
                    return '<strong>' . e($name) . '</strong><br>' .
                        '<small class="text-muted">' . e($booking->user->mobile ?? '') . '</small>';
                })
                ->addColumn('location', function (Booking $booking) {
                    $location = [];
                    if ($booking->society_name)
                        $location[] = $booking->society_name;
                    if ($booking->address_area)
                        $location[] = $booking->address_area;
                    if ($booking->city)
                        $location[] = $booking->city->name;

                    return implode(', ', $location) ?: 'N/A';
                })
                ->addColumn('city_state', function (Booking $booking) {
                    return ($booking->city?->name ?? '-') . '<div class="text-muted small">' . ($booking->state?->name ?? '-') . '</div>';
                })
                ->addColumn('qr_code', function (Booking $booking) {
                    if ($booking->qr && $booking->qr->code) {
                        $qrBaseUrl = rtrim(Setting::where('name', 'qr_link_base')->value('value') ?? '', '/');
                        $qrUrl = $booking->qr->qr_link ?: ($qrBaseUrl ? $qrBaseUrl . '/' . $booking->qr->code : null);

                        // Fallback to plain code when we cannot build a URL
                        if (!$qrUrl) {
                            return '<span class="text-muted">' . htmlspecialchars($booking->qr->code, ENT_QUOTES, 'UTF-8') . '</span>';
                        }

                        $safeUrl = htmlspecialchars($qrUrl, ENT_QUOTES, 'UTF-8');
                        $safeCode = htmlspecialchars($booking->qr->code, ENT_QUOTES, 'UTF-8');

                        return '<a href="' . $safeUrl . '" target="_blank" rel="noopener" data-bs-toggle="tooltip" data-bs-placement="top" title="Open QR link">' . $safeCode . '</a>';
                    }

                    return '<span class="text-muted">N/A</span>';
                })
                ->addColumn('created_at', function (Booking $booking) {
                    return \Carbon\Carbon::parse($booking->created_at)->format('d M Y') . '<br>' .
                        '<small class="text-muted">' . \Carbon\Carbon::parse($booking->created_at)->format('h:i A') . '</small>';
                })
                ->addColumn('status', function (Booking $booking) {
                    $badges = [
                        'pending' => 'secondary',
                        'confirmed' => 'primary',
                        'scheduled' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger'
                    ];
                    $color = $badges[$booking->status] ?? 'secondary';
                    return '<span class="badge bg-' . $color . '">' . ucfirst($booking->status) . '</span>';
                })
                ->addColumn('payment_status', function (Booking $booking) {
                    $badges = [
                        'pending' => 'warning',
                        'paid' => 'success',
                        'failed' => 'danger',
                        'refunded' => 'info'
                    ];
                    $color = $badges[$booking->payment_status] ?? 'secondary';
                    return '<span class="badge bg-' . $color . '">' . ucfirst($booking->payment_status) . '</span>';
                })
                ->addColumn('price', function (Booking $booking) {
                    return '₹' . number_format($booking->price, 2);
                })
                ->addColumn('actions', function (Booking $booking) use ($request) {
                    $actions = '<div class="d-flex gap-1">';

                    // View button
                    $actions .= '<a href="' . route('admin.tour-manager.show', $booking) . '" class="btn btn-sm btn-soft-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="View Tour Public Page"><iconify-icon icon="solar:eye-broken" class="align-middle fs-18"></iconify-icon></a>';

                    // Edit booking button (Main booking edit)
                    if ($request->user()->can('booking_edit')) {
                        $actions .= ' <a href="' . route('admin.bookings.edit', $booking->id) . '" class="btn btn-sm btn-soft-info" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Booking Info"><iconify-icon icon="solar:pen-new-square-broken" class="align-middle fs-18"></iconify-icon></a>';
                    }

                    // Edit tour button (Upload Tour)
                    if ($booking->tours()->exists() && $request->user()->can('tour_manager_edit')) {
                        $actions .= ' <a href="' . route('admin.tour-manager.upload', $booking) . '" class="btn btn-sm btn-soft-warning" data-bs-toggle="tooltip" data-bs-placement="top" title="Upload & Manage Tour Assets"><iconify-icon icon="solar:upload-minimalistic-broken" class="align-middle fs-18"></iconify-icon></a>';
                    }

                    $actions .= '</div>';
                    return $actions;
                })
                ->rawColumns(['booking_id', 'booking_info', 'customer', 'location', 'city_state', 'qr_code', 'created_at', 'status', 'payment_status', 'actions'])
                ->make(true);
        }

        $statuses = ['pending', 'confirmed', 'scheduled', 'completed', 'cancelled'];
        $paymentStatuses = ['pending', 'paid', 'failed', 'refunded'];
        $canEdit = $request->user()->can('tour_manager_edit');

        return view('admin.tour-manager.index', compact('statuses', 'paymentStatuses', 'canEdit'));
    }

    /**
     * Display the specified booking
     */
    public function show(Booking $booking)
    {
        $booking->load([
            'user',
            'propertyType',
            'propertySubType',
            'bhk',
            'city',
            'state',
            'tours',
            'qr'
        ]);

        // Get the tour for this booking
        $tour = $booking->tours()->first();
        $canEdit = auth()->user()->can('tour_manager_edit');

        return view('admin.tour-manager.show', compact('booking', 'tour', 'canEdit'));
    }

    /**
     * Return the current ZIP processing status for a booking (polling endpoint)
     */
    public function status(Booking $booking)
    {
        // Ensure relationships needed to compute live url exist
        $booking->load(['tours', 'qr']);

        $tourLiveUrl = $booking->getTourLiveUrl();
        $hasLiveLink = !empty($booking->qr?->qr_link) && $tourLiveUrl !== '#';

        return response()->json([
            'booking_id' => $booking->id,
            'tour_zip_status' => $booking->tour_zip_status ?? 'pending',
            'tour_zip_progress' => (int)($booking->tour_zip_progress ?? 0),
            'tour_zip_message' => $booking->tour_zip_message,
            // Use ISO8601 so JS can parse timezone correctly
            'tour_zip_started_at' => optional($booking->tour_zip_started_at)->toIso8601String(),
            'tour_zip_finished_at' => optional($booking->tour_zip_finished_at)->toIso8601String(),
            'has_live_link' => $hasLiveLink,
            'tour_live_url' => $tourLiveUrl,
        ]);
    }

    /**
     * Show the form for editing the specified tour
     */
    public function edit(Booking $booking)
    {
        // Permission check is handled by middleware, but verify again
        if (!auth()->user()->can('tour_manager_edit')) {
            abort(403, 'You do not have permission to edit tours.');
        }

        // Get the tour for this booking
        $tour = $booking->tours()->first();

        if (!$tour) {
            return redirect()->route('admin.tour-manager.show', $booking)
                ->withErrors(['error' => 'No tour found for this booking.']);
        }

        $tour->load('booking.qr');
        $booking->load('qr');

        $statuses = ['draft', 'published', 'archived'];
        $structuredDataTypes = ['Article', 'Event', 'Product', 'Organization', 'Person', 'Place'];

        return view('admin.tour-manager.edit', compact('booking', 'tour', 'statuses', 'structuredDataTypes'));
    }

    /**
     * Update the specified tour in storage
     */
    public function update(Request $request, Booking $booking)
    {
        // Increase execution time limit for large ZIP file processing (5 hours for files up to 1GB)
        set_time_limit(18000);
        ini_set('max_execution_time', '18000');
        
        // Get valid location values from FTP configurations
        $validLocations = FtpConfiguration::active()->pluck('category_name')->toArray();
        
        $validated = $request->validate([
            'slug' => 'required|string|max:255|regex:/^[a-zA-Z0-9\/\-_]+$/',
            'location' => ['required', 'string', Rule::in($validLocations)],
            'files.*' => 'nullable|file|max:1024000', // 1GB for zip files - single file only
        ]);

        // Get the tour for this booking
        $tour = $booking->tours()->first();
        if (!$tour) {
            return back()->withErrors(['error' => 'No tour found for this booking.']);
        }

        // Update tour slug and location if provided
        $tourUpdated = false;
        if (isset($validated['slug']) && $tour->slug !== $validated['slug']) {
            $tour->slug = $validated['slug'];
            $tourUpdated = true;
        }
        if (isset($validated['location']) && $tour->location !== $validated['location']) {
            $tour->location = $validated['location'];
            $tourUpdated = true;
        }
        
        // Save tour if slug or location changed
        if ($tourUpdated) {
            $tour->updated_by = auth()->id();
            $tour->save();
            \Log::info("Tour slug/location updated: slug={$tour->slug}, location={$tour->location}");
        }

        // Get or assign QR code to booking
        $qrCode = $booking->qr;
        if (!$qrCode) {
            // Find an available QR code (one without a booking)
            $qrCode = QR::whereNull('booking_id')->first();

            if (!$qrCode) {
                return back()->withErrors(['error' => 'No available QR codes. Please generate a new QR code first.']);
            }

            // Assign QR code to booking
            $qrCode->booking_id = $booking->id;
            $qrCode->updated_by = auth()->id();
            $qrCode->save();

            $booking->tour_code = $qrCode->code;
            $booking->save();
        }

        $tourData = [];
        $uploadedFiles = [];

        // Handle single ZIP file upload only
        if ($request->hasFile('files')) {
            $files = $request->file('files');
            
            // Check if more than one file is uploaded
            if (count($files) > 1) {
                return back()->withErrors(['files' => 'Only one ZIP file is allowed. Please upload a single ZIP file.']);
            }
            
            $file = $files[0]; // Get the first (and only) file
            
                try {
                    $extension = strtolower($file->getClientOriginalExtension());

                // Only accept ZIP files
                if ($extension !== 'zip') {
                    return back()->withErrors(['files' => 'Only ZIP files are allowed. Please upload a ZIP file.']);
                }

                // Reload tour to get latest slug and location if they were updated
                $tour->refresh();
                
                // Check file size - use background processing for files > 75MB
                $fileSize = $file->getSize();
                $useBackgroundProcessing = $fileSize > (75 * 1024 * 1024); // 75MB
                
                if ($useBackgroundProcessing) {
                    // Save file temporarily and dispatch background job
                    $tempPath = $file->storeAs('temp_uploads', 'tour_' . $booking->id . '_' . time() . '.zip', 'local');
                    $fullTempPath = storage_path('app/' . $tempPath);
                    
                    \Log::info("Large file detected ({$fileSize} bytes), using background processing. Booking ID: {$booking->id}");

                    // Track status for UI
                    $booking->tour_zip_status = 'processing';
                    $booking->tour_zip_progress = 0;
                    $booking->tour_zip_message = 'Queued for background processing';
                    $booking->tour_zip_started_at = now();
                    $booking->tour_zip_finished_at = null;
                    $booking->save();
                    
                    // Dispatch background job
                    // Use unique identifier to prevent duplicate jobs
                    $jobUniqueId = 'tour-processing-' . $booking->id . '-' . md5($file->getClientOriginalName() . $fullTempPath);
                    ProcessTourZipFile::dispatch(
                        $booking->id,
                        $fullTempPath,
                        $file->getClientOriginalName(),
                        $validated['slug'],
                        $validated['location']
                    )->onQueue('tour-processing');
                    
                    // Log job dispatch with unique identifier for tracking
                    \Log::info("Large file processing queued. Booking ID: {$booking->id}, Job ID: {$jobUniqueId}");
                    
                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => true,
                            'message' => 'Large file uploaded successfully! Processing will continue in the background.',
                            'booking_id' => $booking->id,
                            'processing' => true,
                            'tour_zip_status' => $booking->tour_zip_status,
                            'tour_zip_progress' => $booking->tour_zip_progress,
                            'tour_zip_message' => $booking->tour_zip_message,
                            'redirect' => route('admin.tour-manager.show', $booking)
                        ]);
                    }
                    
                    return redirect()->route('admin.tour-manager.show', $booking)
                        ->with('success', 'Large file uploaded! Processing will continue in the background.');
                } else {
                    // Process zip file synchronously for smaller files
                    $booking->tour_zip_status = 'processing';
                    $booking->tour_zip_progress = 5;
                    $booking->tour_zip_message = 'Processing ZIP (sync)';
                    $booking->tour_zip_started_at = now();
                    $booking->tour_zip_finished_at = null;
                    $booking->save();

                    $result = $this->processZipFile($file, $tour, $qrCode->code);
                    if ($result['success']) {
                        $tourData = $result['data'];
                        $uploadedFiles[] = [
                            'name' => $file->getClientOriginalName(),
                            'type' => 'zip',
                            'processed' => true,
                            'tour_path' => $result['tour_path'],
                            'tour_url' => $result['tour_url'],
                            's3_path' => $result['s3_path'],
                            's3_url' => $result['s3_url'],
                            'size' => $file->getSize(),
                            'uploaded_at' => now()->toDateTimeString()
                        ];

                        // Save the S3 base URL of the storage folder to booking
                        $booking->base_url = $result['s3_url'];

                        // Mark done for UI
                        $booking->tour_zip_status = 'done';
                        $booking->tour_zip_progress = 100;
                        $booking->tour_zip_message = 'Processing completed';
                        $booking->tour_zip_finished_at = now();
                        $booking->save();
                    } else {
                        throw new \Exception($result['message']);
                    }
                }
                } catch (\Exception $e) {
                    \Log::error('File upload error: ' . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());

                    // Track error for UI
                    try {
                        $booking->tour_zip_status = 'failed';
                        $booking->tour_zip_progress = 0;
                        $booking->tour_zip_message = 'Processing failed: ' . $e->getMessage();
                        $booking->tour_zip_finished_at = now();
                        $booking->save();
                    } catch (\Exception $inner) {
                        // avoid masking original error
                    }

                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'File processing error: ' . $e->getMessage()
                        ], 422);
                    }

                    return back()->withErrors(['files' => 'File processing error: ' . $e->getMessage()]);
                }
        } else {
            // No file uploaded
            return back()->withErrors(['files' => 'Please upload a ZIP file.']);
        }

        // Merge with existing files or create new array
        $existingFiles = $tour->final_json['files'] ?? [];
        $existingTourData = is_array($tour->final_json) ? $tour->final_json : [];

        // Only update final_json, not other tour fields
        $tour->final_json = array_merge(
            $existingTourData,
            $tourData,
            [
                'files' => array_merge($existingFiles, $uploadedFiles),
                'qr_code' => $qrCode->code,
                'updated_at' => now()->toDateTimeString()
            ]
        );


        $tour->updated_by = auth()->id();
        $tour->save();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tour updated successfully!',
                'booking_id' => $booking->id,
                'redirect' => route('admin.tour-manager.show', $booking)
            ]);
        }

        return redirect()->route('admin.tour-manager.show', $booking)
            ->with('success', 'Tour updated successfully!');
    }  


    /**
     * Process and validate zip file containing tour assets
     */
    public function processZipFile($zipFile, Tour $tour, $uniqueCode)
    {
        try {
            // Ensure sufficient execution time and memory for large ZIP processing (5 hours for files up to 1GB)
            set_time_limit(18000);
            ini_set('max_execution_time', '18000');
            ini_set('memory_limit', '2048M'); // Increase memory limit for large ZIP processing
            
            // Start processing
            \Log::info("Starting ZIP processing for tour code: {$uniqueCode}");
            
            // Load booking relationship to get customer_id
            $tour->load('booking');
            $zip = new ZipArchive();
            $tempPath = $zipFile->getPathname();

            if ($zip->open($tempPath) !== true) {
                return [
                    'success' => false,
                    'message' => 'Failed to open zip file'
                ];
            }

            // Validate required files
            $validation = $this->validateZipStructure($zip);
            if (!$validation['valid']) {
                $zip->close();
                return [
                    'success' => false,
                    'message' => $validation['message']
                ];
            }

            // Create directories:
            // 1. Local tours/{code}/ for index.php (kept locally)
            $rootTourPath = 'tours/' . $uniqueCode;
            $rootTourDirectory = base_path($rootTourPath);

            // 2. S3 path for tour assets (images, assets, gallery, tiles)
            $s3TourPath = 'tours/' . $uniqueCode;

            // STEP 1: Upload the original ZIP file to S3
            try {
                $zipContent = file_get_contents($tempPath);
                if ($zipContent !== false) {
                    Storage::disk('s3')->put(
                        $s3TourPath . '/tour.zip',
                        $zipContent,
                        ['ContentType' => 'application/zip']
                    );
                    try {
                        Storage::disk('s3')->setVisibility($s3TourPath . '/tour.zip', 'public');
                    } catch (\Exception $e) {
                        // Visibility failure is not critical
                    }
                }
            } catch (\Exception $zipUploadException) {
                \Log::warning("Error uploading ZIP to S3 (continuing): " . $zipUploadException->getMessage() . " in " . $zipUploadException->getFile() . ":" . $zipUploadException->getLine());
            }

            // Delete old tour files if they exist locally
            if (\File::exists($rootTourDirectory)) {
                \File::deleteDirectory($rootTourDirectory);
            }

            // Create local directory for index.php only
            if (!\File::exists($rootTourDirectory)) {
                \File::makeDirectory($rootTourDirectory, 0755, true);
            }

            // HYBRID APPROACH: Analyze ZIP structure first, then extract and upload directly to S3
            
            // STEP 2: Analyze ZIP structure (lightweight - no extraction yet)
            $zipStructure = [];
            $indexHtmlPath = null;
            $swJsPath = null;
            $jsonPath = null;
            $totalFiles = $zip->numFiles;
            
            \Log::info("Analyzing ZIP structure for tour code: {$uniqueCode} ({$totalFiles} files)");
            
            for ($i = 0; $i < $totalFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                $fileInfo = $zip->statIndex($i);
                
                // Skip system files and directories
                if (strpos($filename, '__MACOSX') !== false || 
                    strpos($filename, '.DS_Store') !== false ||
                    empty(trim($filename)) ||
                    substr($filename, -1) === '/') {
                    continue;
                }
                
                // Track structure
                $zipStructure[] = [
                    'index' => $i,
                    'name' => $filename,
                    'size' => $fileInfo['size'] ?? 0
                ];
                
                // Find index.html, sw.js, and JSON files
                $lowerName = strtolower($filename);
                if (basename($lowerName) === 'index.html') {
                    $indexHtmlPath = $filename;
                }
                if (basename($lowerName) === 'sw.js') {
                    $swJsPath = $filename;
                }
                if (pathinfo($lowerName, PATHINFO_EXTENSION) === 'json') {
                    // Prefer virtual-tour-nodes.json
                    if (stripos($filename, 'virtual-tour-nodes') !== false) {
                        $jsonPath = $filename;
                    } elseif (!$jsonPath) {
                        // Use first JSON found as fallback
                        $jsonPath = $filename;
                    }
                }
            }
            
            if (!$indexHtmlPath) {
                \Log::warning("index.html not found in ZIP structure in " . __FILE__ . ":" . __LINE__);
            }
            
            if ($swJsPath) {
                \Log::info("sw.js file detected in ZIP: {$swJsPath}");
            } else {
                \Log::info("sw.js file not found in ZIP structure");
            }
            
            // STEP 3: Verify S3 configuration before processing
            if (!$this->verifyS3Configuration()) {
                $zip->close();
                \Log::error("S3 configuration verification failed. Cannot upload files to S3 in " . __FILE__ . ":" . __LINE__);
                return [
                    'success' => false,
                    'message' => 'S3 configuration error. Please check AWS credentials in .env file.'
                ];
            }
            
            // STEP 4: Extract and upload directly to S3 (streaming approach)
            \Log::info("Starting direct extraction and S3 upload for tour code: {$uniqueCode}");
            
            $uploadedFiles = [];
            $uploadErrors = [];
            $indexHtmlContent = null;
            $swJsContent = null;
            $jsonContent = null;
            $jsonFilename = null;
            
            // Process each file in ZIP and upload directly to S3
            $batchSize = 50; // Process in batches for memory management
            $processedCount = 0;
            
            foreach ($zipStructure as $fileInfo) {
                $i = $fileInfo['index'];
                $filename = $fileInfo['name'];
                
                // Extract file content directly from ZIP
                $fileContent = $zip->getFromIndex($i);
                if ($fileContent === false) {
                    continue;
                }
                
                // Handle special files (index.html, sw.js, JSON) - upload to S3 first, then save in memory for processing
                $lowerFilename = strtolower($filename);
                $basenameLower = basename($lowerFilename);
                
                if ($filename === $indexHtmlPath || $basenameLower === 'index.html') {
                    // Upload original index.html to S3
                    $s3IndexPath = $s3TourPath . '/' . $filename;
                    try {
                        $uploaded = Storage::disk('s3')->put(
                            $s3IndexPath,
                            $fileContent,
                            ['ContentType' => 'text/html']
                        );
                        if ($uploaded) {
                            try {
                                Storage::disk('s3')->setVisibility($s3IndexPath, 'public');
                            } catch (\Exception $e) {
                                // Visibility failure is not critical
                            }
                            $uploadedFiles[] = $filename;
                        }
                    } catch (\Exception $e) {
                        \Log::error("Error uploading index.html to S3: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
                    }
                    
                    // Save in memory for local processing (will be converted to index.php)
                    $indexHtmlContent = $fileContent;
                    unset($fileContent);
                    continue; // Will process later for local index.php
                }
                
                // Check if this is sw.js file (only process if sw.js exists in ZIP)
                if ($swJsPath && ($filename === $swJsPath || $basenameLower === 'sw.js')) {
                    \Log::info("Processing sw.js file: {$filename} (detected path: {$swJsPath})");
                    // Upload sw.js to S3
                    $s3SwJsPath = $s3TourPath . '/' . $filename;
                    try {
                        $uploaded = Storage::disk('s3')->put(
                            $s3SwJsPath,
                            $fileContent,
                            ['ContentType' => 'application/javascript']
                        );
                        if ($uploaded) {
                            try {
                                Storage::disk('s3')->setVisibility($s3SwJsPath, 'public');
                            } catch (\Exception $e) {
                                // Visibility failure is not critical
                            }
                            $uploadedFiles[] = $filename;
                            \Log::info("Successfully uploaded sw.js to S3: {$s3SwJsPath}");
                        }
                    } catch (\Exception $e) {
                        \Log::error("Error uploading sw.js to S3: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
                    }
                    
                    // Save in memory for FTP upload (will be uploaded without changes)
                    $swJsContent = $fileContent;
                    \Log::info("Saved sw.js content in memory for FTP upload (size: " . strlen($swJsContent) . " bytes)");
                    unset($fileContent);
                    continue; // Will process later for FTP upload
                }
                
                if ($filename === $jsonPath || (pathinfo($lowerFilename, PATHINFO_EXTENSION) === 'json' && stripos($filename, 'virtual-tour-nodes') !== false)) {
                    // Upload original JSON to S3
                    $s3JsonPath = $s3TourPath . '/' . $filename;
                    try {
                        $uploaded = Storage::disk('s3')->put(
                            $s3JsonPath,
                            $fileContent,
                            ['ContentType' => 'application/json']
                        );
                        if ($uploaded) {
                            try {
                                Storage::disk('s3')->setVisibility($s3JsonPath, 'public');
                            } catch (\Exception $e) {
                                // Visibility failure is not critical
                            }
                            $uploadedFiles[] = $filename;
                        }
                    } catch (\Exception $e) {
                        \Log::error("Error uploading JSON to S3: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
                    }
                    
                    // Save in memory for local processing
                    $jsonContent = $fileContent;
                    $jsonFilename = basename($filename);
                    unset($fileContent);
                    continue; // Will process later for local save
                }
                
                // For all other files: Upload directly to S3
                $s3FilePath = $s3TourPath . '/' . $filename;
                
                try {
                    // Determine MIME type
                    $mimeType = $this->getMimeType($filename);
                    
                    // Upload directly to S3
                    $uploaded = Storage::disk('s3')->put(
                        $s3FilePath,
                        $fileContent,
                        ['ContentType' => $mimeType]
                    );
                    
                    if ($uploaded) {
                        // Set visibility (silently fail if it doesn't work)
                        try {
                            Storage::disk('s3')->setVisibility($s3FilePath, 'public');
                        } catch (\Exception $e) {
                            // Visibility failure is not critical
                        }
                        
                        // Track uploaded files
                        $uploadedFiles[] = $filename;
                        $processedCount++;
                    } else {
                        $errorMsg = "Failed to upload {$filename} to S3";
                        $uploadErrors[] = $errorMsg;
                        \Log::error($errorMsg . " in " . __FILE__ . ":" . __LINE__);
                    }
                } catch (\Exception $e) {
                    $errorMsg = "Error uploading {$filename}: " . $e->getMessage();
                    $uploadErrors[] = $errorMsg;
                    \Log::error($errorMsg . " in " . $e->getFile() . ":" . $e->getLine());
                }
                
                // Free memory
                unset($fileContent);
                
                // Periodic memory cleanup
                if ($processedCount % $batchSize === 0) {
                    gc_collect_cycles();
                }
            }
            
            $zip->close();
            
            // Variables for processing
            $jsonData = null;
            $ftpUploadResult = null;
            
            // STEP 5: Process index.html and save as index.php locally
            if ($indexHtmlContent) {
                try {

                // Prepare PHP echo snippet for GTM code replacement
                $gtmPhpEcho = '<?php echo escAttr($gtmCode); ?>';

                // Tracker for replaced tags and improved transformation logic
                $replacedTags = [];
                
                // Unified meta/link replacement helper with fallback to original content
                $metaReplace = function($attrName, $attrValue, $key, $phpVarName) use (&$indexHtmlContent, &$replacedTags) {
                    $found = false;
                    $callback = function($matches) use ($phpVarName, &$found) {
                        $found = true;
                        $prefix = $matches[1] . $matches[2];
                        $originalValue = $matches[3];
                        $suffix = $matches[2] . $matches[4];
                        $fallback = var_export($originalValue, true);
                        return $prefix . '<?php echo (!empty($' . $phpVarName . ') ? escAttr($' . $phpVarName . ') : ' . $fallback . '); ?>' . $suffix;
                    };

                    // Match meta tag regardless of attribute order
                    $pattern = '/(<meta[^>]*?' . $attrName . '\s*=\s*["\']' . preg_quote($attrValue, '/') . '["\'][^>]*?content\s*=\s*)(["\'])(.*?)\2([^>]*?>)/is';
                    $patternAlt = '/(<meta[^>]*?content\s*=\s*)(["\'])(.*?)\2([^>]*?' . $attrName . '\s*=\s*["\']' . preg_quote($attrValue, '/') . '["\'][^>]*?>)/is';

                    $indexHtmlContent = preg_replace_callback($pattern, $callback, $indexHtmlContent, 1, $count);
                    if ($count === 0) {
                        $indexHtmlContent = preg_replace_callback($patternAlt, $callback, $indexHtmlContent, 1, $count);
                    }
                    
                    if ($found) $replacedTags[$key] = true;
                    return $found;
                };

                // Link tag replacement helper
                $linkReplace = function($relValue, $key, $phpVarName) use (&$indexHtmlContent, &$replacedTags) {
                    $found = false;
                    $callback = function($matches) use ($phpVarName, &$found) {
                        $found = true;
                        $prefix = $matches[1] . $matches[2];
                        $originalValue = $matches[3];
                        $suffix = $matches[2] . $matches[4];
                        $fallback = var_export($originalValue, true);
                        return $prefix . '<?php echo (!empty($' . $phpVarName . ') ? escAttr($' . $phpVarName . ') : ' . $fallback . '); ?>' . $suffix;
                    };

                    $pattern = '/(<link[^>]*?rel\s*=\s*["\']' . preg_quote($relValue, '/') . '["\'][^>]*?href\s*=\s*)(["\'])(.*?)\2([^>]*?>)/is';
                    $patternAlt = '/(<link[^>]*?href\s*=\s*)(["\'])(.*?)\2([^>]*?rel\s*=\s*["\']' . preg_quote($relValue, '/') . '["\'][^>]*?>)/is';

                    $indexHtmlContent = preg_replace_callback($pattern, $callback, $indexHtmlContent, 1, $count);
                    if ($count === 0) {
                        $indexHtmlContent = preg_replace_callback($patternAlt, $callback, $indexHtmlContent, 1, $count);
                    }
                    
                    if ($found) $replacedTags[$key] = true;
                    return $found;
                };

                // 1. Title (preserves attributes like id)
                $indexHtmlContent = preg_replace_callback('/(<title[^>]*>)(.*?)(<\/title>)/is', function($matches) use (&$replacedTags) {
                    $replacedTags['title'] = true;
                    $fallback = var_export($matches[2], true);
                    return $matches[1] . '<?php echo (!empty($metaTitle) ? escAttr($metaTitle) : ' . $fallback . '); ?>' . $matches[3];
                }, $indexHtmlContent, 1);

                // 2. Canonical
                $linkReplace('canonical', 'canonical', 'canonicalUrl');

                // 3. SEO Meta Tags
                $metaReplace('name', 'description', 'description', 'metaDescription');
                $metaReplace('name', 'keywords', 'keywords', 'metaKeywords');
                $metaReplace('name', 'robots', 'robots', 'metaRobots');

                // 4. Open Graph Tags
                $metaReplace('property', 'og:title', 'og:title', 'ogTitle');
                $metaReplace('property', 'og:description', 'og:description', 'ogDescription');
                $metaReplace('property', 'og:image', 'og:image', 'ogImage');
                $metaReplace('property', 'og:image:secure_url', 'og:image:secure_url', 'ogImage');
                $metaReplace('property', 'og:url', 'og:url', 'ogUrl');

                // 5. Twitter Card Tags
                $metaReplace('name', 'twitter:title', 'twitter:title', 'twitterTitle');
                $metaReplace('name', 'twitter:description', 'twitter:description', 'twitterDescription');
                $metaReplace('name', 'twitter:image', 'twitter:image', 'twitterImage');
                $metaReplace('name', 'twitter:image:src', 'twitter:image:src', 'twitterImage');

                // 6. Replace Google Tag Manager occurrences with dynamic GTM code (All occurrences)
                $indexHtmlContent = preg_replace(
                    '/https:\/\/www\.googletagmanager\.com\/gtm\.js\?id=[^"\'\s)]+/i',
                    'https://www.googletagmanager.com/gtm.js?id=' . $gtmPhpEcho,
                    $indexHtmlContent
                );
                $indexHtmlContent = preg_replace(
                    '/https:\/\/www\.googletagmanager\.com\/ns\.html\?id=[^"\'\s)]+/i',
                    'https://www.googletagmanager.com/ns.html?id=' . $gtmPhpEcho,
                    $indexHtmlContent
                );
                $indexHtmlContent = preg_replace(
                    '/["\']GTM-[A-Z0-9]+["\']/i',
                    '"' . $gtmPhpEcho . '"',
                    $indexHtmlContent
                );
                // Prepend flags and fetch script to the content
                $phpScript = $this->generateDatabaseFetchScript($tour);
                $flagsScript = "<?php \$replacedTags = " . var_export($replacedTags, true) . "; ?>";
                $indexPhpContent = $flagsScript . "\n" . $phpScript . "\n" . $indexHtmlContent;

                // Inject JavaScript, SEO meta tags, and header code before </head>
                if (preg_match('/<\/head>/i', $indexPhpContent)) {
                    $jsDataScript = $this->generateJavaScriptDataScript();
                    $indexPhpContent = preg_replace(
                        '/<\/head>/i',
                        $jsDataScript . "\n</head>",
                        $indexPhpContent,
                        1
                    );
                }
                
                // Inject footer code before </body>
                if (preg_match('/<\/body>/i', $indexPhpContent)) {
                    $footerScript = $this->generateFooterCodeScript();
                    $indexPhpContent = preg_replace(
                        '/<\/body>/i',
                        $footerScript . "\n</body>",
                        $indexPhpContent,
                        1
                    );
                }
                // Save index.php LOCALLY
                // Ensure directory exists and is writable
                if (!is_dir($rootTourDirectory)) {
                    \File::makeDirectory($rootTourDirectory, 0775, true);
                    @chmod($rootTourDirectory, 0775);
                }
                
                $indexPhpPath = $rootTourDirectory . '/index.php';
                if (file_put_contents($indexPhpPath, $indexPhpContent) === false) {
                    throw new \Exception("Failed to write index.php to {$indexPhpPath}. Please check directory permissions.");
                }
                @chmod($indexPhpPath, 0664);
                \Log::info("Successfully created index.php from index.html");
                    
                    // Upload index.php to FTP server based on tour location
                    $ftpUploadResult = $this->uploadIndexPhpToFtp(
                        $rootTourDirectory . '/index.php', 
                        $tour
                    );
                    
                } catch (\Exception $e) {
                    \Log::error("Error processing index.html: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
                    $indexHtmlContent = null;
                }
            } else {
                \Log::error("index.html file not found in ZIP in " . __FILE__ . ":" . __LINE__);
            }
            
            // STEP 5.5: Upload sw.js to FTP if it exists (same path as index.php)
            // Do this outside index.html processing so it works even if index.html fails
            if ($swJsContent) {
                try {
                    \Log::info("Preparing to upload sw.js to FTP (content size: " . strlen($swJsContent) . " bytes)");
                    // Ensure directory exists
                    if (!is_dir($rootTourDirectory)) {
                        \File::makeDirectory($rootTourDirectory, 0775, true);
                        @chmod($rootTourDirectory, 0775);
                    }
                    
                    // Save sw.js locally first
                    $swJsLocalPath = $rootTourDirectory . '/sw.js';
                    if (file_put_contents($swJsLocalPath, $swJsContent) === false) {
                        throw new \Exception("Failed to write sw.js to {$swJsLocalPath}. Please check directory permissions.");
                    }
                    @chmod($swJsLocalPath, 0664);
                    \Log::info("Successfully saved sw.js locally: {$swJsLocalPath}");
                    
                    // Upload sw.js to FTP (same directory as index.php)
                    $swJsFtpResult = $this->uploadSwJsToFtp($swJsLocalPath, $tour);
                    if ($swJsFtpResult['success']) {
                        \Log::info("✓ Successfully uploaded sw.js to FTP: " . ($swJsFtpResult['ftp_path'] ?? 'N/A'));
                    } else {
                        \Log::warning("Failed to upload sw.js to FTP: " . ($swJsFtpResult['message'] ?? 'Unknown error'));
                    }
                } catch (\Exception $e) {
                    \Log::error("Error processing sw.js: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
                    \Log::error("Stack trace: " . $e->getTraceAsString());
                }
            } else {
                \Log::info("sw.js content not found, skipping FTP upload");
            }

            // STEP 6: Process JSON file - Save locally
            if ($jsonContent && $jsonFilename) {
                try {
                    $jsonData = json_decode($jsonContent, true);
                    
                    // Ensure directory exists
                    if (!is_dir($rootTourDirectory)) {
                        \File::makeDirectory($rootTourDirectory, 0775, true);
                        @chmod($rootTourDirectory, 0775);
                    }
                    
                    $jsonLocalPath = $rootTourDirectory . '/' . $jsonFilename;
                    if (file_put_contents($jsonLocalPath, $jsonContent) === false) {
                        throw new \Exception("Failed to write JSON file to {$jsonLocalPath}. Please check directory permissions.");
                    }
                    @chmod($jsonLocalPath, 0664);
                } catch (\Exception $e) {
                    \Log::error("Error processing JSON file: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
                }
            } else {
                \Log::warning("JSON file not found in ZIP in " . __FILE__ . ":" . __LINE__);
            }

            // STEP 7: Final summary and cleanup
            $totalUploaded = count($uploadedFiles);
            
            if ($totalUploaded === 0) {
                \Log::error("No files were uploaded to S3 in " . __FILE__ . ":" . __LINE__);
                if (!empty($uploadErrors)) {
                    \Log::error("Upload errors: " . implode(' | ', array_slice($uploadErrors, 0, 5)));
                }
            } else if (!empty($uploadErrors)) {
                \Log::warning("Some upload errors occurred: " . count($uploadErrors) . " error(s) in " . __FILE__ . ":" . __LINE__);
            }
            
            // No temp directory cleanup needed - we never created one!
            // Files were uploaded directly from ZIP to S3

            // STEP 8: Validate that we got the required files
            if (!$indexHtmlContent) {
                \Log::error("index.html not found in ZIP file in " . __FILE__ . ":" . __LINE__);
                return [
                    'success' => false,
                    'message' => 'index.html file not found in ZIP file. Please ensure your ZIP contains an index.html file.'
                ];
            }

            if (!$jsonData) {
                \Log::warning("JSON file not found, but continuing as it's not critical for basic functionality in " . __FILE__ . ":" . __LINE__);
                // JSON is not critical - we can continue without it
            }

            // Generate base URL for S3 assets
            $s3BaseUrl = config('filesystems.disks.s3.url') ?: 
                        'https://' . config('filesystems.disks.s3.bucket') . '.s3.' . 
                        config('filesystems.disks.s3.region') . '.amazonaws.com';
            $s3Url = rtrim($s3BaseUrl, '/') . '/' . $s3TourPath;
            
            // Update booking with S3 base URL for assets
            $booking = $tour->booking;
            if ($booking) {
                $booking->base_url = $s3Url;
                $booking->save();
            }

            // Build return data
            $returnData = [
                'success' => true,
                'data' => $jsonData,
                'tour_path' => $rootTourPath,
                'tour_url' => url('/' . $rootTourPath . '/index.php'),
                's3_path' => $s3TourPath,
                's3_url' => $s3Url,
                'message' => 'Zip file processed successfully - index.php stored locally, assets uploaded to S3'
            ];
            
            // Add FTP upload result if available
            if ($ftpUploadResult && isset($ftpUploadResult['success'])) {
                if ($ftpUploadResult['success']) {
                    $returnData['ftp_url'] = $ftpUploadResult['ftp_url'] ?? null;
                    $returnData['ftp_path'] = $ftpUploadResult['ftp_path'] ?? null;
                    $returnData['message'] .= ' and uploaded to FTP';
                } else {
                    \Log::warning("FTP upload failed: " . ($ftpUploadResult['message'] ?? 'Unknown error'));
                }
            }
            
            return $returnData;

        } catch (\Exception $e) {
            \Log::error('Zip processing error: ' . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
            return [
                'success' => false,
                'message' => 'Error processing zip file: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Upload directory contents to S3 recursively with optimization
     */
    /**
     * Get proper MIME type based on file extension
     * 
     * @param string $filePath File path or filename
     * @return string MIME type
     */
    private function getMimeType($filePath)
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        // Comprehensive MIME type mapping
        $mimeTypes = [
            // Images
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
            'bmp' => 'image/bmp',
            'ico' => 'image/x-icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',

            // JavaScript
            'js' => 'application/javascript',
            'mjs' => 'application/javascript',

            // CSS
            'css' => 'text/css',

            // HTML
            'html' => 'text/html',
            'htm' => 'text/html',

            // JSON
            'json' => 'application/json',
            'jsonld' => 'application/ld+json',

            // Text
            'txt' => 'text/plain',
            'md' => 'text/markdown',

            // Fonts
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'otf' => 'font/otf',
            'eot' => 'application/vnd.ms-fontobject',

            // Video
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'ogg' => 'video/ogg',
            'mov' => 'video/quicktime',
            'avi' => 'video/x-msvideo',

            // Audio
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'ogg' => 'audio/ogg',
            'm4a' => 'audio/mp4',

            // Archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            '7z' => 'application/x-7z-compressed',
            'tar' => 'application/x-tar',
            'gz' => 'application/gzip',

            // Documents
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',

            // XML
            'xml' => 'application/xml',
            'rss' => 'application/rss+xml',

            // Other
            'php' => 'application/x-httpd-php',
            'sh' => 'application/x-sh',
            'exe' => 'application/x-msdownload',
        ];

        // Return mapped MIME type or try mime_content_type as fallback
        if (isset($mimeTypes[$extension])) {
            return $mimeTypes[$extension];
        }

        // Fallback to mime_content_type if file exists
        if (file_exists($filePath)) {
            $detected = mime_content_type($filePath);
            if ($detected && $detected !== 'application/octet-stream') {
                return $detected;
            }
        }

        // Default fallback
        return 'application/octet-stream';
    }

    private function uploadDirectoryToS3($localPath, $s3Path, $excludeFiles = [])
    {
        // First, verify S3 configuration
        if (!$this->verifyS3Configuration()) {
            \Log::error("S3 configuration is missing or invalid. Check AWS credentials in .env file in " . __FILE__ . ":" . __LINE__);
            return [
                'success' => false,
                'message' => 'S3 configuration error. Please check AWS credentials in .env file.',
                'files_count' => 0,
                'total_size' => 0
            ];
        }

        if (!is_dir($localPath)) {
            \Log::warning("Directory not found for S3 upload: {$localPath}");
            return [
                'success' => false,
                'message' => 'Directory not found',
                'files_count' => 0,
                'total_size' => 0
            ];
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($localPath, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        $filesToUpload = [];

        // Collect all files first
        foreach ($files as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $filePath = $file->getPathname();
            $relativePath = str_replace($localPath . DIRECTORY_SEPARATOR, '', $filePath);
            $relativePath = str_replace('\\', '/', $relativePath);

            // Skip excluded files
            $fileName = basename($relativePath);
            if (in_array($fileName, $excludeFiles)) {
                continue;
            }

            // Skip hidden files and system files
            if (
                strpos($fileName, '.') === 0 ||
                strpos($relativePath, '__MACOSX') !== false ||
                strpos($relativePath, '.DS_Store') !== false
            ) {
                continue;
            }

            // Normalize S3 path (remove leading slashes)
            $s3FilePath = ltrim($s3Path . '/' . $relativePath, '/');
            $filesToUpload[] = [
                'local' => $filePath,
                's3' => $s3FilePath,
                'size' => filesize($filePath)
            ];
        }

        // Log summary of files to upload
        \Log::info("Found " . count($filesToUpload) . " files to upload from: {$localPath} to S3 path: {$s3Path}");

        if (empty($filesToUpload)) {
            \Log::warning("No files found to upload in directory: {$localPath}");
            return [
                'success' => false,
                'message' => 'No files found in directory',
                'files_count' => 0,
                'total_size' => 0
            ];
        }

        // Upload files in batches - OPTIMIZED: Increased batch size for better performance
        $batchSize = 20; // Increased from 5 to 20 for faster uploads
        $totalSize = 0;
        $uploadedCount = 0;
        $failedCount = 0;
        $errors = [];

        foreach (array_chunk($filesToUpload, $batchSize) as $batchIndex => $batch) {
            foreach ($batch as $fileData) {
                try {
                    // Verify file exists before reading
                    if (!file_exists($fileData['local'])) {
                        throw new \Exception("Local file not found: {$fileData['local']}");
                    }

                    $fileContent = file_get_contents($fileData['local']);
                    if ($fileContent === false) {
                        throw new \Exception("Failed to read file content");
                    }

                    // Get proper MIME type based on file extension
                    $mimeType = $this->getMimeType($fileData['local']);

                    // Get S3 disk instance
                    $s3Disk = Storage::disk('s3');

                    // Upload to S3 - use AWS SDK directly for better error handling
                    try {
                        // First, verify S3 connection works
                        $bucket = config('filesystems.disks.s3.bucket');
                        $region = config('filesystems.disks.s3.region');
                        $key = config('filesystems.disks.s3.key');

                        if (empty($bucket) || empty($region) || empty($key)) {
                            throw new \Exception("S3 configuration incomplete. Check AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY, AWS_DEFAULT_REGION, and AWS_BUCKET in .env");
                        }

                        // Try to upload using Laravel Storage facade
                        // If this fails, we'll catch the exception
                        $uploaded = $s3Disk->put(
                            $fileData['s3'],
                            $fileContent,
                            [
                                'ContentType' => $mimeType
                            ]
                        );

                        if ($uploaded === false || $uploaded === null) {
                            // If put() returns false, try to get more info
                            // This usually means credentials or permissions issue
                            throw new \Exception("S3 upload failed. Possible causes: 1) Invalid AWS credentials, 2) Bucket '{$bucket}' doesn't exist, 3) IAM user lacks s3:PutObject permission, 4) Network connectivity issue. Path: {$fileData['s3']}");
                        }
                    } catch (S3Exception $s3Exception) {
                        // Catch AWS S3 specific exceptions
                        $errorCode = $s3Exception->getAwsErrorCode();
                        $errorMessage = $s3Exception->getAwsErrorMessage();
                        $requestId = $s3Exception->getAwsRequestId();
                        throw new \Exception("AWS S3 Error [{$errorCode}]: {$errorMessage} (Request ID: {$requestId})");
                    } catch (\Exception $uploadEx) {
                        // Re-throw with more context
                        throw $uploadEx;
                    }

                    // Set visibility separately (this is the correct way for Laravel S3)
                    try {
                        $s3Disk->setVisibility($fileData['s3'], 'public');
                    } catch (\Exception $visibilityException) {
                        // Visibility setting failure is not critical - file is still uploaded
                        // Only log if it's a persistent issue (not just visibility)
                    }

                    // Verify the file was actually uploaded (with retry for eventual consistency)
                    $verified = false;
                    for ($i = 0; $i < 3; $i++) {
                        if ($s3Disk->exists($fileData['s3'])) {
                            $verified = true;
                            break;
                        }
                        if ($i < 2) {
                            usleep(500000); // Wait 0.5 seconds before retry
                        }
                    }

                    // Verification skipped - S3 eventual consistency means file might exist but not be immediately visible
                    // The upload likely succeeded if put() returned true
                    
                    $totalSize += $fileData['size'];
                    $uploadedCount++;
                    
                    
                } catch (\Exception $e) {
                    $failedCount++;
                    $errorMsg = "Failed to upload {$fileData['s3']}: " . $e->getMessage();
                    $errors[] = $errorMsg;
                    \Log::error($errorMsg . " | File: {$fileData['local']} in " . __FILE__ . ":" . __LINE__);
                    

                    // If too many failures, stop and report
                    if ($failedCount > 10) {
                        \Log::error("Too many upload failures ({$failedCount}). Stopping upload process in " . __FILE__ . ":" . __LINE__);
                        break 2;
                    }
                }
            }
        }

        $sizeMB = round($totalSize / 1024 / 1024, 2);
        $totalFiles = count($filesToUpload);

        if ($failedCount > 0) {
            \Log::error("Upload completed with errors: {$uploadedCount}/{$totalFiles} files uploaded ({$sizeMB} MB), {$failedCount} failed to S3 path: {$s3Path} in " . __FILE__ . ":" . __LINE__);
            \Log::error("Upload errors: " . implode(' | ', array_slice($errors, 0, 5)));
        }

        return [
            'success' => $uploadedCount > 0,
            'message' => $failedCount > 0
                ? "Uploaded {$uploadedCount}/{$totalFiles} files, {$failedCount} failed"
                : "Successfully uploaded {$uploadedCount} files",
            'files_count' => $uploadedCount,
            'total_size' => $sizeMB,
            'failed_count' => $failedCount,
            'errors' => $errors
        ];
    }

    /**
     * Verify S3 configuration is properly set
     */
    private function verifyS3Configuration()
    {
        $required = [
            'AWS_ACCESS_KEY_ID',
            'AWS_SECRET_ACCESS_KEY',
            'AWS_DEFAULT_REGION',
            'AWS_BUCKET'
        ];

        foreach ($required as $key) {
            $value = env($key);
            if (empty($value)) {
                \Log::error("Missing S3 configuration: {$key} in " . __FILE__ . ":" . __LINE__);
                return false;
            }
        }

        // Try to test S3 connection by attempting a simple operation
        try {
            $disk = Storage::disk('s3');
            $bucket = config('filesystems.disks.s3.bucket');
            $region = config('filesystems.disks.s3.region');

            if (empty($bucket)) {
                \Log::error("S3 bucket name is not configured in " . __FILE__ . ":" . __LINE__);
                return false;
            }

            // Try to test connection by checking if we can access the bucket
            // This is a lightweight test that doesn't require listing
            return true;
        } catch (\Exception $e) {
            \Log::error("S3 configuration test failed: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
            return false;
        }
    }

    /**
     * Validate zip file structure
     * Updated to check for folders at any level (handles root folder structure)
     */
    private function validateZipStructure(ZipArchive $zip)
    {
        $hasIndexHtml = false;
        $hasJsonFile = false;
        $requiredFolders = ['images', 'assets', 'gallery', 'tiles'];
        $foundFolders = [];

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);

            // Skip hidden files and system folders
            if (
                strpos($filename, '__MACOSX') !== false ||
                strpos($filename, '.DS_Store') !== false ||
                empty(trim($filename))
            ) {
                continue;
            }

            // Normalize path separators and trim
            $filename = trim(str_replace('\\', '/', $filename));

            // Skip empty entries after normalization
            if (empty($filename)) {
                continue;
            }

            // Split path into parts
            $parts = array_filter(explode('/', $filename), function ($part) {
                return !empty($part) && $part !== '.';
            });
            $parts = array_values($parts); // Re-index array

            // Check for index.html at any level (but prefer root level)
            $cleanFilename = rtrim($filename, '/');
            $basename = strtolower(basename($cleanFilename));
            if ($basename === 'index.html') {
                $hasIndexHtml = true;
            }

            // Check for JSON file at any level
            $fileInfo = pathinfo($cleanFilename);
            if (isset($fileInfo['extension']) && strtolower($fileInfo['extension']) === 'json') {
                $hasJsonFile = true;
            }

            // Check for required folders at ANY level in the path
            // This handles both: "images/file.jpg" and "rootFolder/images/file.jpg"
            foreach ($parts as $part) {
                $partLower = strtolower($part);
                if (in_array($partLower, $requiredFolders) && !in_array($partLower, $foundFolders)) {
                    $foundFolders[] = $partLower;
                }
            }
        }

        $missingFolders = array_diff($requiredFolders, $foundFolders);

        if (!$hasIndexHtml) {
            return ['valid' => false, 'message' => 'Zip file must contain index.html'];
        }

        if (!$hasJsonFile) {
            return ['valid' => false, 'message' => 'Zip file must contain a JSON configuration file'];
        }

        if (!empty($missingFolders)) {
            return [
                'valid' => false,
                'message' => 'Zip file missing required folders: ' . implode(', ', $missingFolders)
            ];
        }

        return ['valid' => true];
    }

    /**
     * Cleanup failed extraction
     */
    private function cleanupFailedExtraction($tourDirectory)
    {
        if (\File::exists($tourDirectory)) {
            \File::deleteDirectory($tourDirectory);
        }
    }

    private function generateDatabaseFetchScript(Tour $tour)
    {
        $apiUrlBase = url('/api/tour/page_data');
        $token = md5($tour->slug . $tour->created_at . 'tour_secret_2026');

        return <<<PHP
        <?php
        // Helper function to escape HTML attributes
        if (!function_exists('escAttr')) {
            function escAttr(\$str) {
                return htmlspecialchars(\$str ?? '', ENT_QUOTES, 'UTF-8');
            }
        }

        // Get the current tour code from the URL path
        \$currentPath = dirname(\$_SERVER['SCRIPT_NAME']);
        \$tourCode = basename(\$currentPath);

        // API Endpoint for fetching tour data
        \$apiUrl = "{$apiUrlBase}/" . \$tourCode . "?token={$token}";

        // Initialize variables
        \$tourData = null;
        \$bookingData = null;
        \$baseUrl = '';
        \$seoMetaTags = '';
        \$headerCode = '';
        \$footerCode = '';
        \$gtmCode = '';
        \$replacedTags = isset(\$replacedTags) ? \$replacedTags : [];

        // Dynamic variables for HTML placeholders (placeholders used by transformation logic)
        \$metaTitle = '';
        \$metaDescription = '';
        \$metaKeywords = '';
        \$metaRobots = '';
        \$canonicalUrl = '';
        \$ogTitle = '';
        \$ogDescription = '';
        \$ogImage = '';
        \$ogUrl = '';
        \$twitterTitle = '';
        \$twitterDescription = '';
        \$twitterImage = '';

        // Fetch data from API
        \$ctx = stream_context_create(['http' => ['timeout' => 5]]);
        \$response = @file_get_contents(\$apiUrl, false, \$ctx);
        
        if (\$response) {
            \$data = json_decode(\$response, true);
            
            if (isset(\$data['success']) && \$data['success']) {
                // Check for expired status
                if (isset(\$data['bookingStatus']) && \$data['bookingStatus'] === 'expired') {
                    \$redirectUrl = 'https://www.proppik.com/';
                    echo <<<HTML
                    <!DOCTYPE html>
                    <html lang="en">
                    <head>
                        <meta charset="UTF-8" />
                        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
                        <title>Tour Expired</title>
                        <style>
                            body { margin:0; padding:0; font-family: Arial, sans-serif; background:#f6f7fb; color:#1f2933; display:flex; align-items:center; justify-content:center; min-height:100vh; }
                            .card { background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:32px; box-shadow:0 10px 30px rgba(0,0,0,0.08); max-width:420px; text-align:center; }
                            h1 { margin:0 0 12px; font-size:24px; color:#111827; }
                            p { margin:0 0 20px; line-height:1.5; }
                            .btn { display:inline-block; padding:12px 20px; background:#2563eb; color:#fff; border-radius:8px; text-decoration:none; font-weight:600; }
                            .btn:hover { background:#1d4ed8; }
                        </style>
                        <div class="card">
                            <h1>Tour Expired</h1>
                            <p>This virtual tour is no longer available. Please visit our site to explore more experiences.</p>
                            <a class="btn" href="{\$redirectUrl}">Go to PROP PIK</a>
                        </div>
                    HTML;
                    exit;
                }

                // Map data
                \$tourData = \$data['tourData'] ?? null;
                \$bookingData = \$data['bookingData'] ?? null;
                \$baseUrl = \$data['baseUrl'] ?? '';
                
                \$meta = \$data['meta'] ?? [];
                \$metaTitle = \$meta['title'] ?? '';
                \$metaDescription = \$meta['description'] ?? '';
                \$metaKeywords = \$meta['keywords'] ?? '';
                \$metaRobots = \$meta['robots'] ?? '';
                \$canonicalUrl = \$meta['canonical'] ?? '';
                \$ogTitle = \$meta['ogTitle'] ?? \$metaTitle;
                \$ogDescription = \$meta['ogDesc'] ?? \$metaDescription;
                \$ogImage = \$meta['ogImage'] ?? '';
                
                \$protocol = (!empty(\$_SERVER['HTTPS']) && \$_SERVER['HTTPS'] !== 'off' || (\$_SERVER['SERVER_PORT'] ?? '') == 443) ? "https://" : "http://";
                \$ogUrl = \$protocol . (\$_SERVER['HTTP_HOST'] ?? '') . (\$_SERVER['REQUEST_URI'] ?? '');
                
                \$twitterTitle = \$meta['twitterTitle'] ?? \$ogTitle;
                \$twitterDescription = \$meta['twitterDesc'] ?? \$ogDescription;
                \$twitterImage = \$meta['twitterImage'] ?? \$ogImage;
                \$gtmCode = \$meta['gtmCode'] ?? '';
                \$headerCode = \$meta['headerCode'] ?? '';
                \$footerCode = \$meta['footerCode'] ?? '';

                if (\$tourData) {
                    \$seoTags = [];
                    
                    // Basic Meta Tags (Only add if NOT already replaced in HTML)
                    if (!empty(\$metaTitle) && !isset(\$replacedTags['title'])) {
                        \$seoTags[] = '<title id="pageTitle">' . escAttr(\$metaTitle) . '</title>';
                    }
                    if (!empty(\$metaDescription) && !isset(\$replacedTags['description'])) {
                        \$seoTags[] = '<meta name="description" id="metaDescription" content="' . escAttr(\$metaDescription) . '" />';
                    }
                    if (!empty(\$metaKeywords) && !isset(\$replacedTags['keywords'])) {
                        \$seoTags[] = '<meta name="keywords" content="' . escAttr(\$metaKeywords) . '" />';
                    }
                    if (!empty(\$metaRobots) && !isset(\$replacedTags['robots'])) {
                        \$seoTags[] = '<meta name="robots" content="' . escAttr(\$metaRobots) . '" />';
                    }
                    if (!empty(\$canonicalUrl) && !isset(\$replacedTags['canonical'])) {
                        \$seoTags[] = '<link rel="canonical" href="' . escAttr(\$canonicalUrl) . '" />';
                    }
                    
                    // Open Graph Tags
                    if (!empty(\$ogTitle) && !isset(\$replacedTags['og:title'])) {
                        \$seoTags[] = '<meta property="og:title" id="ogTitle" content="' . escAttr(\$ogTitle) . '" />';
                    }
                    if (!empty(\$ogDescription) && !isset(\$replacedTags['og:description'])) {
                        \$seoTags[] = '<meta property="og:description" id="ogDescription" content="' . escAttr(\$ogDescription) . '" />';
                    }
                    if (!empty(\$ogImage) && !isset(\$replacedTags['og:image'])) {
                        \$seoTags[] = '<meta property="og:image" content="' . escAttr(\$ogImage) . '" />';
                        \$seoTags[] = '<meta property="og:image:secure_url" content="' . escAttr(\$ogImage) . '" />';
                    }
                    if (!isset(\$replacedTags['og:url'])) {
                        \$seoTags[] = '<meta property="og:url" content="' . escAttr(\$ogUrl) . '" />';
                    }
                    \$seoTags[] = '<meta property="og:type" content="website" />';
                    \$seoTags[] = '<meta property="og:site_name" content="PROP PIK" />';
                    
                    // Twitter Card Tags
                    if (!empty(\$twitterTitle) && !isset(\$replacedTags['twitter:title'])) {
                        \$seoTags[] = '<meta name="twitter:title" content="' . escAttr(\$twitterTitle) . '" />';
                    }
                    if (!empty(\$twitterDescription) && !isset(\$replacedTags['twitter:description'])) {
                        \$seoTags[] = '<meta name="twitter:description" content="' . escAttr(\$twitterDescription) . '" />';
                    }
                    if (!empty(\$twitterImage) && !isset(\$replacedTags['twitter:image'])) {
                        \$seoTags[] = '<meta name="twitter:image" content="' . escAttr(\$twitterImage) . '" />';
                    }
                    \$seoTags[] = '<meta name="twitter:card" content="summary_large_image" />';
                    
                    // Structured Data (JSON-LD)
                    if (!empty(\$tourData['structured_data'])) {
                        \$structuredData = json_decode(\$tourData['structured_data'], true);
                        if (\$structuredData) {
                            \$seoTags[] = '<script type="application/ld+json">' . json_encode(\$structuredData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>';
                        }
                    }
                    
                    \$seoMetaTags = implode("\\n    ", \$seoTags);
                }
            }
        }

        // Make data available as JSON for JavaScript
        // \$tourDataJson = json_encode(\$tourData);
        // \$bookingDataJson = json_encode(\$bookingData);
        // \$baseUrlJson = json_encode(\$baseUrl);

        // Start output buffering to modify HTML
        ob_start();
        ?>
PHP;
    }

    /**
     * Generate JavaScript script to make PHP data available in browser
     */
    private function generateJavaScriptDataScript()
    {
        return <<<'JS'
    
            <!-- Dynamic SEO Meta Tags from Database -->
            <?php if (!empty($seoMetaTags)) echo $seoMetaTags; ?>
            
            <!-- Custom Header Code from Tour -->
            <?php if (!empty($headerCode)) echo $headerCode; ?>
            
            <!-- Tour and Booking Data from Database -->
            <script>
            // Make PHP data available to JavaScript
            // window.tourData = <?php echo $tourDataJson; ?>;
            // window.bookingData = <?php echo $bookingDataJson; ?>;
            // window.baseUrl = <?php echo $baseUrlJson; ?>;
            
            // console.log('Tour Data:', window.tourData);
            // console.log('Booking Data:', window.bookingData);
            // console.log('Base URL:', window.baseUrl);
            </script>
        JS;
    }

    /**
     * Generate footer code to inject before </body>
     */
    private function generateFooterCodeScript()
    {
        return <<<'JS'
    
            <!-- Custom Footer Code from Tour -->
            <?php if (!empty($footerCode)) echo $footerCode; ?>
            
            <?php
            // End output buffering and send
            ob_end_flush();
            ?>
        JS;
    }

    /**
     * Handle file upload via AJAX
     */
    public function uploadFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:51200', // 50MB max
        ]);

        try {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('tours', $filename, 'public');

            return response()->json([
                'success' => true,
                'path' => $path,
                'url' => asset('storage/' . $path),
                'message' => 'File uploaded successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Initialize chunked upload for large files
     */
    public function initChunkedUpload(Request $request)
    {
        $request->validate([
            'filename' => 'required|string|max:255',
            'total_size' => 'required|integer|min:1',
            'booking_id' => 'required|exists:bookings,id',
        ]);

        $filename = $request->input('filename');
        $totalSize = $request->input('total_size');
        $bookingId = $request->input('booking_id');
        
        // Generate unique upload ID
        $uploadId = md5($filename . $totalSize . $bookingId . time());
        
        // Create temporary directory for chunks
        $chunkDir = storage_path('app/chunks/' . $uploadId);
        
        // Ensure parent chunks directory exists and has correct permissions
        $chunksBaseDir = storage_path('app/chunks');
        if (!is_dir($chunksBaseDir)) {
            if (!mkdir($chunksBaseDir, 0775, true)) {
                \Log::error("Failed to create chunks base directory: {$chunksBaseDir} in " . __FILE__ . ":" . __LINE__);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create upload directory. Please check server permissions.'
                ], 500);
            }
            // Set ownership if possible
            @chmod($chunksBaseDir, 0775);
        }
        
        // Create upload-specific directory
        if (!is_dir($chunkDir)) {
            if (!mkdir($chunkDir, 0775, true)) {
                \Log::error("Failed to create chunk directory: {$chunkDir} in " . __FILE__ . ":" . __LINE__);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create upload directory. Please check server permissions.'
                ], 500);
            }
            @chmod($chunkDir, 0775);
        }
        
        // Store upload metadata
        $metadata = [
            'upload_id' => $uploadId,
            'filename' => $filename,
            'total_size' => $totalSize,
            'booking_id' => $bookingId,
            'chunks_uploaded' => 0,
            'total_chunks' => ceil($totalSize / (10 * 1024 * 1024)), // 10MB chunks
            'created_at' => now()->toDateTimeString(),
        ];
        
        file_put_contents($chunkDir . '/metadata.json', json_encode($metadata));
        
        return response()->json([
            'success' => true,
            'upload_id' => $uploadId,
            'chunk_size' => 10 * 1024 * 1024, // 10MB chunks
            'total_chunks' => $metadata['total_chunks'],
        ]);
    }

    /**
     * Upload a chunk of file
     */
    public function uploadChunk(Request $request)
    {
        $request->validate([
            'upload_id' => 'required|string',
            'chunk_number' => 'required|integer|min:0',
            'chunk' => 'required|file',
        ]);

        $uploadId = $request->input('upload_id');
        $chunkNumber = $request->input('chunk_number');
        $chunkFile = $request->file('chunk');
        
        $chunkDir = storage_path('app/chunks/' . $uploadId);
        
        if (!is_dir($chunkDir)) {
            return response()->json([
                'success' => false,
                'message' => 'Upload session not found. Please restart the upload.'
            ], 404);
        }
        
        // Load metadata
        $metadataPath = $chunkDir . '/metadata.json';
        if (!file_exists($metadataPath)) {
            return response()->json([
                'success' => false,
                'message' => 'Upload metadata not found.'
            ], 404);
        }
        
        $metadata = json_decode(file_get_contents($metadataPath), true);
        
        // Save chunk
        $chunkPath = $chunkDir . '/chunk_' . $chunkNumber;
        $chunkFile->move($chunkDir, 'chunk_' . $chunkNumber);
        
        // Update metadata
        $metadata['chunks_uploaded'] = max($metadata['chunks_uploaded'], $chunkNumber + 1);
        file_put_contents($metadataPath, json_encode($metadata));
        
        return response()->json([
            'success' => true,
            'chunk_number' => $chunkNumber,
            'chunks_uploaded' => $metadata['chunks_uploaded'],
            'total_chunks' => $metadata['total_chunks'],
            'progress' => round(($metadata['chunks_uploaded'] / $metadata['total_chunks']) * 100, 2),
        ]);
    }

    /**
     * Finalize chunked upload and process the file
     */
    public function finalizeChunkedUpload(Request $request, Booking $booking)
    {
        // Increase execution time limit for large ZIP file processing
        set_time_limit(18000);
        ini_set('max_execution_time', '18000');
        
        // Get valid location values from FTP configurations
        $validLocations = FtpConfiguration::active()->pluck('category_name')->toArray();
        
        $request->validate([
            'upload_id' => 'required|string',
            'slug' => 'required|string|max:255|regex:/^[a-zA-Z0-9\/\-_]+$/',
            'location' => ['required', 'string', Rule::in($validLocations)],
        ]);

        $uploadId = $request->input('upload_id');
        $chunkDir = storage_path('app/chunks/' . $uploadId);
        
        if (!is_dir($chunkDir)) {
            return response()->json([
                'success' => false,
                'message' => 'Upload session not found.'
            ], 404);
        }
        
        // Load metadata
        $metadataPath = $chunkDir . '/metadata.json';
        if (!file_exists($metadataPath)) {
            return response()->json([
                'success' => false,
                'message' => 'Upload metadata not found.'
            ], 404);
        }
        
        $metadata = json_decode(file_get_contents($metadataPath), true);
        
        // Verify all chunks are uploaded
        $expectedChunks = $metadata['total_chunks'];
        $uploadedChunks = 0;
        for ($i = 0; $i < $expectedChunks; $i++) {
            if (file_exists($chunkDir . '/chunk_' . $i)) {
                $uploadedChunks++;
            }
        }
        
        if ($uploadedChunks < $expectedChunks) {
            return response()->json([
                'success' => false,
                'message' => "Not all chunks uploaded. Expected: {$expectedChunks}, Uploaded: {$uploadedChunks}"
            ], 400);
        }
        
        // Combine chunks into single file
        $finalPath = $chunkDir . '/final.zip';
        $finalFile = fopen($finalPath, 'wb');
        
        if (!$finalFile) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create final file.'
            ], 500);
        }
        
        for ($i = 0; $i < $expectedChunks; $i++) {
            $chunkPath = $chunkDir . '/chunk_' . $i;
            $chunkContent = file_get_contents($chunkPath);
            fwrite($finalFile, $chunkContent);
            unlink($chunkPath); // Clean up chunk
        }
        
        fclose($finalFile);
        
        try {
            $slug = $request->input('slug');
            $location = $request->input('location');

            // Track status for UI
            $booking->tour_zip_status = 'processing';
            $booking->tour_zip_progress = 0;
            $booking->tour_zip_message = 'Queued for background processing';
            $booking->tour_zip_started_at = now();
            $booking->tour_zip_finished_at = null;
            $booking->save();
            
            // Dispatch background job to process the ZIP file
            // Use unique identifier to prevent duplicate jobs
            $jobUniqueId = 'tour-processing-' . $booking->id . '-' . md5($metadata['filename'] . $finalPath);
            \App\Jobs\ProcessTourZipFile::dispatch(
                $booking->id,
                $finalPath,
                $metadata['filename'],
                $slug,
                $location
            )->onQueue('tour-processing');
            
            // Log job dispatch with unique identifier for tracking
            \Log::info("ZIP file processing queued. Booking ID: {$booking->id}, Job ID: {$jobUniqueId}");
            
            \Log::info("ZIP file processing queued for background processing. Booking ID: {$booking->id}");
            
            // Clean up metadata files (keep final.zip for the job)
            unlink($metadataPath);
            
            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully! Processing will continue in the background.',
                'booking_id' => $booking->id,
                'processing' => true,
                'tour_zip_status' => $booking->tour_zip_status,
                'tour_zip_progress' => $booking->tour_zip_progress,
                'tour_zip_message' => $booking->tour_zip_message,
                'redirect' => route('admin.tour-manager.show', $booking)
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Chunked upload finalization error: ' . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());

            // Track error for UI
            try {
                $booking->tour_zip_status = 'failed';
                $booking->tour_zip_progress = 0;
                $booking->tour_zip_message = 'Failed to queue processing: ' . $e->getMessage();
                $booking->tour_zip_finished_at = now();
                $booking->save();
            } catch (\Exception $inner) {
                // ignore status update failure
            }
            
            // Clean up on error
            if (file_exists($finalPath)) {
                @unlink($finalPath);
            }
            if (file_exists($metadataPath)) {
                @unlink($metadataPath);
            }
            if (is_dir($chunkDir)) {
                @array_map('unlink', glob($chunkDir . '/*'));
                @rmdir($chunkDir);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to queue processing: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Schedule a tour for a booking
     */
    public function scheduleTour(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'tour_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $booking = Booking::findOrFail($validated['booking_id']);

            // Update booking date and status
            $booking->booking_date = $validated['tour_date'];
            if (in_array($booking->status, ['pending', 'confirmed'])) {
                $booking->status = 'scheduled';
            }
            $booking->save();

            // Create or update tour record
            $tour = Tour::firstOrNew(['booking_id' => $booking->id]);
            if (!$tour->exists) {
                $tour->name = 'Tour for Booking #' . $booking->id;
                $tour->title = 'Property Tour - ' . ($booking->propertyType?->name ?? 'Property');
                $tour->slug = 'tour-' . $booking->id . '-' . time();
                $tour->status = 'draft';
                $tour->revision = 1;
            }
            $tour->save();

            \Log::info("Tour scheduled for booking #{$booking->id} on {$validated['tour_date']}");

            return response()->json([
                'success' => true,
                'message' => 'Tour scheduled successfully!',
                'tour' => $tour,
                'booking' => $booking
            ]);
        } catch (\Exception $e) {
            \Log::error('Schedule tour error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to schedule tour: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Upload index.php to FTP server using dynamic FTP configuration
     * 
     * @param string $localIndexPhpPath Local path to index.php file
     * @param Tour $tour Tour object containing location, slug, and booking relationship
     * @return array Result with success status and FTP URL
     */
    private function uploadIndexPhpToFtp($localIndexPhpPath, Tour $tour)
    {
        try {
            $location = $tour->location;
            $tourSlug = $tour->slug;

            \Log::info("=== Starting FTP upload for tour: {$tourSlug} (Location: {$location}) ===");

            // Validate location
            if (empty($location)) {
                \Log::warning("Location is missing. Skipping FTP upload.");
                return [
                    'success' => false,
                    'message' => 'Tour location is required for FTP upload'
                ];
            }

            // Validate tour slug
            if (empty($tourSlug)) {
                \Log::warning("Tour slug is missing. Skipping FTP upload.");
                return [
                    'success' => false,
                    'message' => 'Tour slug is required for FTP upload'
                ];
            }

            // Get customer_id from tour's booking
            $customerId = null;
            if ($tour->booking) {
                $customerId = $tour->booking->user_id;
            }

            if (empty($customerId)) {
                \Log::warning("Customer ID is missing. Skipping FTP upload.");
                return [
                    'success' => false,
                    'message' => 'Customer ID is required for FTP upload. Tour must be associated with a booking.'
                ];
            }

            // Get FTP configuration from database
            $ftpConfig = FtpConfiguration::where('category_name', $location)
                ->active()
                ->first();

            if (!$ftpConfig) {
                \Log::error("FTP configuration not found for location: {$location}");
                return [
                    'success' => false,
                    'message' => "FTP configuration not found for location: {$location}"
                ];
            }

            // Verify local file exists
            if (!file_exists($localIndexPhpPath)) {
                \Log::error("Local index.php file not found: {$localIndexPhpPath}");
                return [
                    'success' => false,
                    'message' => 'Local index.php file not found'
                ];
            }

            // Get remote path and URL using FTP config methods (includes customer_id)
            $ftpRemotePath = $ftpConfig->getRemotePathForTour($tourSlug, $customerId);
            $ftpUrl = $ftpConfig->getUrlForTour($tourSlug, $customerId);

            \Log::info("FTP Upload Details:");
            \Log::info("  Category: {$ftpConfig->category_name}");
            \Log::info("  Display Name: {$ftpConfig->display_name}");
            \Log::info("  Main URL: {$ftpConfig->main_url}");
            \Log::info("  Driver: {$ftpConfig->driver}");
            \Log::info("  Host: {$ftpConfig->host}:{$ftpConfig->port}");
            \Log::info("  Username: {$ftpConfig->username}");
            \Log::info("  Customer ID: {$customerId}");
            \Log::info("  Local file: {$localIndexPhpPath}");
            \Log::info("  Remote path: {$ftpRemotePath}");
            \Log::info("  Final URL: {$ftpUrl}");

            // Create a temporary disk config for this FTP configuration
            $diskName = 'ftp_temp_' . $ftpConfig->id;
            config(["filesystems.disks.{$diskName}" => $ftpConfig->storage_config]);

            // Use SFTP driver if configured
            if ($ftpConfig->driver === 'sftp') {
                \Log::info("Using Storage SFTP driver for upload...");

                $ftpDisk = Storage::disk($diskName);
                $fileContent = file_get_contents($localIndexPhpPath);

                if ($fileContent === false) {
                    throw new \Exception("Failed to read local index.php file");
                }

                // Ensure remote directory exists (create customer_id folder and tour slug folder)
                $remoteDir = trim(dirname($ftpRemotePath), '/');
                if (!empty($remoteDir) && $remoteDir !== '.') {
                    try {
                        $ftpDisk->makeDirectory($remoteDir);
                        \Log::info("Ensured remote directory exists: {$remoteDir}");
                    } catch (\Exception $dirEx) {
                        \Log::warning("Could not create remote directory '{$remoteDir}': " . $dirEx->getMessage());
                    }
                }

                // Upload with explicit visibility so Flysystem maps to 0777 per config
                $uploaded = $ftpDisk->put($ftpRemotePath, $fileContent, ['visibility' => 'public']);

                if (!$uploaded) {
                    throw new \Exception("SFTP put() returned false for {$ftpRemotePath}");
                }

                // Verify upload
                if (!$ftpDisk->exists($ftpRemotePath)) {
                    throw new \Exception("SFTP upload verification failed; file not found at {$ftpRemotePath}");
                }

                // Set permissions
                try {
                    $ftpDisk->setVisibility($ftpRemotePath, 'public');
                    if (!empty($remoteDir) && $remoteDir !== '.') {
                        $ftpDisk->setVisibility($remoteDir, 'public');
                    }
                } catch (\Exception $visEx) {
                    \Log::warning("Could not set visibility: " . $visEx->getMessage());
                }

            } else {
                // Use native PHP FTP functions for FTP (more reliable for directory creation)
                \Log::info("Using native PHP FTP functions for upload...");
                try {
                    $host = preg_replace('#^ftps?://#', '', $ftpConfig->host);

                    // Prepend root if defined in FTP configuration for native call
                    // Note: root is relative to FTP user's home directory (chroot)
                    $root = trim($ftpConfig->root ?? '', '/');
                    $remotePathWithRoot = $ftpRemotePath;
                    if (!empty($root)) {
                        $remotePathWithRoot = $root . '/' . ltrim($ftpRemotePath, '/');
                    }

                    \Log::info("FTP upload path with root: {$remotePathWithRoot}");

                    $uploaded = $this->uploadToFtpNative(
                        $host,
                        $ftpConfig->port,
                        $ftpConfig->username,
                        $ftpConfig->password,
                        $remotePathWithRoot,
                        $localIndexPhpPath,
                        $ftpConfig->passive
                    );
                } catch (\Exception $nativeException) {
                    \Log::error("Native FTP upload failed: " . $nativeException->getMessage());

                    // Try Laravel Storage as fallback
                    \Log::info("Trying Laravel Storage FTP driver as fallback...");
                    try {
                        $ftpDisk = Storage::disk($diskName);

                        // Read local file content
                        $fileContent = file_get_contents($localIndexPhpPath);
                        if ($fileContent === false) {
                            throw new \Exception("Failed to read local index.php file");
                        }

                        // Ensure remote directory exists
                        $remoteDir = trim(dirname($ftpRemotePath), '/');
                        if (!empty($remoteDir) && $remoteDir !== '.') {
                            try {
                                $ftpDisk->makeDirectory($remoteDir);
                            } catch (\Exception $dirEx) {
                                \Log::warning("Could not create remote directory: " . $dirEx->getMessage());
                            }
                        }

                        // Upload file to FTP using Storage facade
                        \Log::info("Uploading index.php to FTP using Storage facade...");
                        $uploaded = $ftpDisk->put($ftpRemotePath, $fileContent);
                    } catch (\Exception $storageException) {
                        \Log::error("Storage FTP driver also failed: " . $storageException->getMessage());
                        throw new \Exception("FTP upload failed: " . $nativeException->getMessage() . " | Storage: " . $storageException->getMessage());
                    }
                }
            }

            if ($uploaded) {
                \Log::info("✓ Successfully uploaded index.php to FTP: {$ftpRemotePath}");
                \Log::info("Tour accessible at: {$ftpUrl}");

                return [
                    'success' => true,
                    'message' => 'index.php uploaded to FTP successfully',
                    'ftp_path' => $ftpRemotePath,
                    'ftp_url' => $ftpUrl,
                    'ftp_host' => $ftpConfig->host,
                    'location' => $ftpConfig->category_name,
                    'customer_id' => $customerId
                ];
            } else {
                \Log::error("FTP upload returned false for: {$ftpRemotePath}");
                return [
                    'success' => false,
                    'message' => 'FTP upload failed (returned false)'
                ];
            }

        } catch (\Exception $e) {
            \Log::error("FTP upload error: " . $e->getMessage());
            \Log::error("Stack trace: " . $e->getTraceAsString());
            return [
                'success' => false,
                'message' => 'FTP upload error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Upload sw.js to FTP server using dynamic FTP configuration
     * 
     * @param string $localSwJsPath Local path to sw.js file
     * @param Tour $tour Tour object containing location, slug, and booking relationship
     * @return array Result with success status and FTP URL
     */
    private function uploadSwJsToFtp($localSwJsPath, Tour $tour)
    {
        try {
            $location = $tour->location;
            $tourSlug = $tour->slug;
            
            \Log::info("=== Starting sw.js FTP upload for tour: {$tourSlug} (Location: {$location}) ===");
            
            // Validate location
            if (empty($location)) {
                \Log::warning("Location is missing. Skipping sw.js FTP upload.");
                return [
                    'success' => false,
                    'message' => 'Tour location is required for FTP upload'
                ];
            }
            
            // Validate tour slug
            if (empty($tourSlug)) {
                \Log::warning("Tour slug is missing. Skipping sw.js FTP upload.");
                return [
                    'success' => false,
                    'message' => 'Tour slug is required for FTP upload'
                ];
            }
            
            // Get customer_id from tour's booking
            $customerId = null;
            if ($tour->booking) {
                $customerId = $tour->booking->user_id;
            }
            
            if (empty($customerId)) {
                \Log::warning("Customer ID is missing. Skipping sw.js FTP upload.");
                return [
                    'success' => false,
                    'message' => 'Customer ID is required for FTP upload. Tour must be associated with a booking.'
                ];
            }
            
            // Get FTP configuration from database
            $ftpConfig = FtpConfiguration::where('category_name', $location)
                ->active()
                ->first();
                
            if (!$ftpConfig) {
                \Log::error("FTP configuration not found for location: {$location}");
                return [
                    'success' => false,
                    'message' => "FTP configuration not found for location: {$location}"
                ];
            }
            
            // Verify local file exists
            if (!file_exists($localSwJsPath)) {
                \Log::error("Local sw.js file not found: {$localSwJsPath}");
                return [
                    'success' => false,
                    'message' => 'Local sw.js file not found'
                ];
            }
            
            // Get remote path (same directory as index.php, just sw.js instead)
            // Use the same pattern as index.php but replace index.php with sw.js
            $ftpRemotePath = $ftpConfig->getRemotePathForTour($tourSlug, $customerId);
            // Replace index.php with sw.js in the remote path
            $ftpRemotePath = str_replace('index.php', 'sw.js', $ftpRemotePath);
            $ftpUrl = $ftpConfig->getUrlForTour($tourSlug, $customerId);
            $ftpUrl = str_replace('index.php', 'sw.js', $ftpUrl);
            
            \Log::info("sw.js FTP Upload Details:");
            \Log::info("  Category: {$ftpConfig->category_name}");
            \Log::info("  Display Name: {$ftpConfig->display_name}");
            \Log::info("  Main URL: {$ftpConfig->main_url}");
            \Log::info("  Driver: {$ftpConfig->driver}");
            \Log::info("  Host: {$ftpConfig->host}:{$ftpConfig->port}");
            \Log::info("  Username: {$ftpConfig->username}");
            \Log::info("  Customer ID: {$customerId}");
            \Log::info("  Local file: {$localSwJsPath}");
            \Log::info("  Remote path: {$ftpRemotePath}");
            \Log::info("  Final URL: {$ftpUrl}");
            
            // Create a temporary disk config for this FTP configuration
            $diskName = 'ftp_temp_' . $ftpConfig->id;
            config(["filesystems.disks.{$diskName}" => $ftpConfig->storage_config]);
            
            // Use SFTP driver if configured
            if ($ftpConfig->driver === 'sftp') {
                \Log::info("Using Storage SFTP driver for sw.js upload...");
                
                $ftpDisk = Storage::disk($diskName);
                $fileContent = file_get_contents($localSwJsPath);
                
                if ($fileContent === false) {
                    throw new \Exception("Failed to read local sw.js file");
                }
                
                // Ensure remote directory exists (same as index.php)
                $remoteDir = trim(dirname($ftpRemotePath), '/');
                if (!empty($remoteDir) && $remoteDir !== '.') {
                    try {
                        $ftpDisk->makeDirectory($remoteDir);
                        \Log::info("Ensured remote directory exists: {$remoteDir}");
                    } catch (\Exception $dirEx) {
                        \Log::warning("Could not create remote directory '{$remoteDir}': " . $dirEx->getMessage());
                    }
                }
                
                // Upload with explicit visibility
                $uploaded = $ftpDisk->put($ftpRemotePath, $fileContent, ['visibility' => 'public']);
                
                if (!$uploaded) {
                    throw new \Exception("SFTP put() returned false for {$ftpRemotePath}");
                }
                
                // Verify upload
                if (!$ftpDisk->exists($ftpRemotePath)) {
                    throw new \Exception("SFTP upload verification failed; file not found at {$ftpRemotePath}");
                }
                
                // Set permissions
                try {
                    $ftpDisk->setVisibility($ftpRemotePath, 'public');
                } catch (\Exception $visEx) {
                    \Log::warning("Could not set visibility: " . $visEx->getMessage());
                }
                
            } else {
                // Use native PHP FTP functions for FTP
                \Log::info("Using native PHP FTP functions for sw.js upload...");
                try {
                    $host = preg_replace('#^ftps?://#', '', $ftpConfig->host);
                    
                    // Prepend root if defined in FTP configuration for native call
                    // Note: root is relative to FTP user's home directory (chroot)
                    $root = trim($ftpConfig->root ?? '', '/');
                    $remotePathWithRoot = $ftpRemotePath;
                    if (!empty($root)) {
                        $remotePathWithRoot = $root . '/' . ltrim($ftpRemotePath, '/');
                    }

                    \Log::info("sw.js FTP upload path with root: {$remotePathWithRoot}");
                    
                    $uploaded = $this->uploadToFtpNative(
                        $host,
                        $ftpConfig->port,
                        $ftpConfig->username,
                        $ftpConfig->password,
                        $remotePathWithRoot,
                        $localSwJsPath,
                        $ftpConfig->passive
                    );
                } catch (\Exception $nativeException) {
                    \Log::error("Native FTP upload failed for sw.js: " . $nativeException->getMessage());
                    
                    // Try Laravel Storage as fallback
                    \Log::info("Trying Laravel Storage FTP driver as fallback for sw.js...");
                    try {
                        $ftpDisk = Storage::disk($diskName);
                        
                        // Read local file content
                        $fileContent = file_get_contents($localSwJsPath);
                        if ($fileContent === false) {
                            throw new \Exception("Failed to read local sw.js file");
                        }
                        
                        // Ensure remote directory exists
                        $remoteDir = trim(dirname($ftpRemotePath), '/');
                        if (!empty($remoteDir) && $remoteDir !== '.') {
                            try {
                                $ftpDisk->makeDirectory($remoteDir);
                            } catch (\Exception $dirEx) {
                                \Log::warning("Could not create remote directory: " . $dirEx->getMessage());
                            }
                        }
                        
                        // Upload file to FTP using Storage facade
                        \Log::info("Uploading sw.js to FTP using Storage facade...");
                        $uploaded = $ftpDisk->put($ftpRemotePath, $fileContent);
                    } catch (\Exception $storageException) {
                        \Log::error("Storage FTP driver also failed for sw.js: " . $storageException->getMessage());
                        throw new \Exception("FTP upload failed: " . $nativeException->getMessage() . " | Storage: " . $storageException->getMessage());
                    }
                }
            }
            
            if ($uploaded) {
                \Log::info("✓ Successfully uploaded sw.js to FTP: {$ftpRemotePath}");
                \Log::info("sw.js accessible at: {$ftpUrl}");
                
                return [
                    'success' => true,
                    'message' => 'sw.js uploaded to FTP successfully',
                    'ftp_path' => $ftpRemotePath,
                    'ftp_url' => $ftpUrl,
                    'ftp_host' => $ftpConfig->host,
                    'location' => $ftpConfig->category_name,
                    'customer_id' => $customerId
                ];
            } else {
                \Log::error("FTP upload returned false for sw.js: {$ftpRemotePath}");
                return [
                    'success' => false,
                    'message' => 'FTP upload failed (returned false)'
                ];
            }
            
        } catch (\Exception $e) {
            \Log::error("sw.js FTP upload error: " . $e->getMessage());
            \Log::error("Stack trace: " . $e->getTraceAsString());
            return [
                'success' => false,
                'message' => 'FTP upload error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Upload file to FTP using native PHP FTP functions (fallback method)
     * 
     * @param string $host FTP host
     * @param int $port FTP port
     * @param string $username FTP username
     * @param string $password FTP password
     * @param string $remotePath Remote path on FTP server
     * @param string $localPath Local file path
     * @param bool $passive Whether to use passive mode (default: true)
     * @return bool Success status
     */
    private function uploadToFtpNative($host, $port, $username, $password, $remotePath, $localPath, $passive = true)
    {
        if (!function_exists('ftp_connect')) {
            throw new \Exception("PHP FTP extension is not enabled");
        }

        \Log::info("Connecting to FTP server using native PHP functions: {$host}:{$port}");
        \Log::info("FTP Credentials - Username: {$username}");

        // Connect to FTP server
        $connection = @ftp_connect($host, $port, 30);
        if (!$connection) {
            $error = error_get_last();
            throw new \Exception("Failed to connect to FTP server: {$host}:{$port}. Error: " . ($error['message'] ?? 'Unknown error'));
        }

        \Log::info("✓ FTP connection established");

        // Login
        $login = @ftp_login($connection, $username, $password);
        if (!$login) {
            $error = error_get_last();
            ftp_close($connection);
            throw new \Exception("Failed to login to FTP server with username: {$username}. Error: " . ($error['message'] ?? 'Invalid credentials'));
        }

        \Log::info("✓ FTP login successful");

        // Set passive mode
        ftp_pasv($connection, $passive);
        \Log::info("✓ Passive mode " . ($passive ? "enabled" : "disabled"));

        try {
            // Create directory structure if needed
            $directoryPath = dirname($remotePath);
            if ($directoryPath !== '.' && $directoryPath !== '') {
                \Log::info("Creating/Verifying directory structure: {$directoryPath}");

                // For chrooted FTP users, we start from current directory (home directory)
                // Don't use ftp_chdir('/') as it won't work with chroot
                $currentDir = @ftp_pwd($connection);
                \Log::info("Current FTP directory: " . ($currentDir ?: 'unknown'));

                $pathParts = explode('/', ltrim($directoryPath, '/'));
                $currentPath = '';
                foreach ($pathParts as $part) {
                    if (empty($part))
                        continue;
                    $currentPath .= ($currentPath ? '/' : '') . $part;

                    // Check if directory exists by trying to change into it
                    $exists = @ftp_chdir($connection, $currentPath);
                    if (!$exists) {
                        // Directory doesn't exist, create it
                        $created = @ftp_mkdir($connection, $currentPath);
                        if ($created) {
                            \Log::info("✓ Created directory: {$currentPath}");
                            // Change into the newly created directory
                            @ftp_chdir($connection, $currentPath);
                        } else {
                            $error = error_get_last();
                            $ftpError = @ftp_get_option($connection, FTP_TIMEOUT_SEC);
                            \Log::warning("Failed to create directory {$currentPath}: " . ($error['message'] ?? 'Unknown error'));
                            // Try to continue anyway - directory might exist but chdir failed
                        }
                    } else {
                        \Log::info("Directory already exists: {$currentPath}");
                    }

                    // Always try to set permissions to 0777 for the folder
                    if (function_exists('ftp_chmod')) {
                        if (@ftp_chmod($connection, 0777, $currentPath) !== false) {
                            \Log::info("✓ Set permissions 0777 for {$currentPath}");
                        }
                    }
                }
                
                // Return to the directory where we need to upload the file
                @ftp_chdir($connection, $directoryPath);
            }

            // Upload file
            \Log::info("Uploading file to FTP: {$localPath} -> {$remotePath}");
            $uploaded = @ftp_put($connection, $remotePath, $localPath, FTP_BINARY);

            if ($uploaded) {
                \Log::info("✓ File uploaded successfully using native FTP");
                // Try to chmod file to 0777
                if (function_exists('ftp_chmod')) {
                    $chmodResult = @ftp_chmod($connection, 0777, $remotePath);
                    if ($chmodResult === false) {
                        \Log::warning("Failed to chmod file to 0777: {$remotePath}");
                    } else {
                        \Log::info("✓ Set file permissions to 0777 for {$remotePath}");
                    }
                } else {
                    \Log::warning("ftp_chmod not available; cannot set file permissions for {$remotePath}");
                }
            } else {
                $error = error_get_last();
                throw new \Exception("FTP upload failed - ftp_put returned false. Error: " . ($error['message'] ?? 'Unknown error'));
            }

            ftp_close($connection);
            return true;

        } catch (\Exception $e) {
            ftp_close($connection);
            throw $e;
        }
    }
}
