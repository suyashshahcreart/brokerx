<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\QR;
use App\Models\Tour;
use GrahamCampbell\ResultType\Success;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use ZipArchive;

class TourManagerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
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
                'state'
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
                ->addColumn('booking_info', function (Booking $booking) {
                    $propertyType = $booking->propertyType?->name ?? 'N/A';
                    $subType = $booking->propertySubType?->name ?? '';
                    $bhk = $booking->bhk?->name ?? '';

                    $info = '<strong>#' . $booking->id . '</strong><br>';
                    $info .= '<small class="text-muted">' . $propertyType;
                    if ($subType)
                        $info .= ' - ' . $subType;
                    if ($bhk)
                        $info .= ' - ' . $bhk;
                    $info .= '</small>';

                    return $info;
                })
                ->addColumn('customer', function (Booking $booking) {
                    if ($booking->user) {
                        return '<strong>' . $booking->user->firstname . ' ' . $booking->user->lastname . '</strong><br>' .
                            '<small class="text-muted">' . $booking->user->mobile . '</small>';
                    }
                    return '<span class="text-muted">N/A</span>';
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
                ->addColumn('booking_date', function (Booking $booking) {
                    if ($booking->booking_date) {
                        return \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') . '<br>' .
                            '<small class="text-muted">' . \Carbon\Carbon::parse($booking->booking_date)->format('h:i A') . '</small>';
                    }
                    return '<span class="text-muted">Not scheduled</span>';
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
                ->addColumn('actions', function (Booking $booking) {
                    $actions = '<div class="btn-group" role="group">';

                    // View button
                    $actions .= '<a href="' . route('admin.tour-manager.show', $booking) . '" class="btn btn-sm btn-primary" title="View Details"><i class="ri-eye-line"></i></a>';

                    // Edit tour button (only if booking has tours)
                    if ($booking->tours()->exists()) {
                        $tour = $booking->tours()->first();
                        $actions .= ' <a href="' . route('admin.tour-manager.edit', $tour) . '" class="btn btn-sm btn-warning" title="Edit Tour"><i class="ri-edit-line"></i></a>';
                    }

                    // Schedule tour button
                    if (in_array($booking->status, ['pending', 'confirmed'])) {
                        $actions .= ' <button type="button" class="btn btn-sm btn-info schedule-tour-btn" data-id="' . $booking->id . '" title="Schedule Tour"><i class="ri-calendar-line"></i></button>';
                    }

                    $actions .= '</div>';
                    return $actions;
                })
                ->rawColumns(['booking_info', 'customer', 'booking_date', 'status', 'payment_status', 'actions'])
                ->make(true);
        }

        $statuses = ['pending', 'confirmed', 'scheduled', 'completed', 'cancelled'];
        $paymentStatuses = ['pending', 'paid', 'failed', 'refunded'];

        return view('admin.tour-manager.index', compact('statuses', 'paymentStatuses'));
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

        return view('admin.tour-manager.show', compact('booking'));
    }

    /**
     * Show the form for editing the specified tour
     */
    public function edit(Tour $tour)
    {
        $tour->load('booking');

        $statuses = ['draft', 'published', 'archived'];
        $structuredDataTypes = ['Article', 'Event', 'Product', 'Organization', 'Person', 'Place'];

        return view('admin.tour-manager.edit', compact('tour', 'statuses', 'structuredDataTypes'));
    }

    /**
     * Update the specified tour in storage
     */
    public function update(Request $request, Tour $tour)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'status' => 'required|in:draft,published,archived',
            'files.*' => 'nullable|file|max:512000', // 500MB for zip files
        ]);

        // Get booking
        $booking = $tour->booking;
        if (!$booking) {
            return back()->withErrors(['error' => 'No booking associated with this tour.']);
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
        }

        $tourData = [];
        $uploadedFiles = [];

        // Handle file uploads
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                try {
                    $extension = strtolower($file->getClientOriginalExtension());

                    // Check if it's a zip file
                    if ($extension === 'zip') {
                        // Process zip file - extract and validate
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
                            $booking->save();
                        } else {
                            throw new \Exception($result['message']);
                        }
                    } else {
                        // Handle regular files (images, pdfs, etc.) - Upload to S3
                        try {
                            $filename = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                            $s3AssetPath = 'tour-assets/' . $booking->id . '/' . $filename;
                            
                            // Upload to S3 (bucket policy handles public access)
                            $fileStream = fopen($file->getPathname(), 'r');
                            Storage::disk('s3')->put(
                                $s3AssetPath,
                                $fileStream,
                                ['ContentType' => $file->getMimeType()]
                            );
                            if (is_resource($fileStream)) {
                                fclose($fileStream);
                            }
                            
                            // Generate S3 URL
                            $s3BaseUrl = config('filesystems.disks.s3.url') ?: 
                                ('https://' . config('filesystems.disks.s3.bucket') . '.s3.' . 
                                 config('filesystems.disks.s3.region') . '.amazonaws.com');
                            $s3Url = rtrim($s3BaseUrl, '/') . '/' . $s3AssetPath;
                            
                            $uploadedFiles[] = [
                                'name' => $file->getClientOriginalName(),
                                's3_path' => $s3AssetPath,
                                's3_url' => $s3Url,
                                'size' => $file->getSize(),
                                'type' => $file->getMimeType(),
                                'uploaded_at' => now()->toDateTimeString()
                            ];
                            
                            \Log::info("File uploaded to S3: {$s3AssetPath}");
                        } catch (\Exception $uploadException) {
                            \Log::error('S3 file upload error: ' . $uploadException->getMessage());
                            throw new \Exception('Failed to upload file to S3: ' . $uploadException->getMessage());
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error('File upload error: ' . $e->getMessage());

                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'File processing error: ' . $e->getMessage()
                        ], 422);
                    }

                    return back()->withErrors(['files' => 'File processing error: ' . $e->getMessage()]);
                }
            }
        }

        // Merge with existing files or create new array
        $existingFiles = $tour->final_json['files'] ?? [];
        $existingTourData = is_array($tour->final_json) ? $tour->final_json : [];

        $validated['final_json'] = array_merge(
            $existingTourData,
            $tourData,
            [
                'files' => array_merge($existingFiles, $uploadedFiles),
                'qr_code' => $qrCode->code,
                'updated_at' => now()->toDateTimeString()
            ]
        );

        $validated['updated_by'] = auth()->id();

        $tour->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tour updated successfully!',
                'booking_id' => $tour->booking_id,
                'redirect' => route('admin.tour-manager.show', $tour->booking_id)
            ]);
        }

        return redirect()->route('admin.tour-manager.show', $tour->booking_id)
            ->with('success', 'Tour updated successfully!');
    }    /**
         * Process and validate zip file containing tour assets
         */
    private function processZipFile($zipFile, Tour $tour, $uniqueCode)
    {
        try {
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

            // Delete old tour files if they exist locally
            if (\File::exists($rootTourDirectory)) {
                \File::deleteDirectory($rootTourDirectory);
            }

            // Create local directory for index.php
            \File::makeDirectory($rootTourDirectory, 0755, true);

            // Extract all files to local temp directory first
            $tempExtractPath = storage_path('app/temp_tour_' . $uniqueCode . '_' . time());
            if (!\File::exists($tempExtractPath)) {
                \File::makeDirectory($tempExtractPath, 0755, true);
            }

            $zip->extractTo($tempExtractPath);
            $zip->close();

            // Find the root folder
            $items = scandir($tempExtractPath);
            foreach ($items as $item) {
                if ($item !== '.' && $item !== '..' && strpos($item, '__MACOSX') === false) {
                    if (is_dir($tempExtractPath . '/' . $item)) {
                        $rootFolder = $item;
                        break;
                    }
                }
            }

            // Path to actual content
            $contentPath = $rootFolder ? $tempExtractPath . '/' . $rootFolder : $tempExtractPath;

            // Extract files
            $jsonData = null;
            $indexHtmlContent = null;
            
            // Process index.html - SAVE LOCALLY
            $indexPath = $contentPath . '/index.html';
            if (file_exists($indexPath)) {
                $indexHtmlContent = file_get_contents($indexPath);

                // Prepend PHP script to fetch tour and booking data
                $phpScript = $this->generateDatabaseFetchScript();

                // Inject JavaScript and SEO meta tags
                $jsDataScript = $this->generateJavaScriptDataScript();
                
                // Inject footer code
                $footerScript = $this->generateFooterCodeScript();

                // Insert PHP at the beginning
                $indexPhpContent = $phpScript . "\n" . $indexHtmlContent;

                // Inject SEO meta tags, header code, and JavaScript data before </head>
                if (preg_match('/<\/head>/i', $indexPhpContent)) {
                    $indexPhpContent = preg_replace(
                        '/<\/head>/i',
                        $jsDataScript . "\n</head>",
                        $indexPhpContent,
                        1
                    );
                }
                
                // Inject footer code before </body>
                if (preg_match('/<\/body>/i', $indexPhpContent)) {
                    $indexPhpContent = preg_replace(
                        '/<\/body>/i',
                        $footerScript . "\n</body>",
                        $indexPhpContent,
                        1
                    );
                }

                // Save index.php LOCALLY
                file_put_contents($rootTourDirectory . '/index.php', $indexPhpContent);
            }

            // Process JSON file - SAVE LOCALLY
            $jsonFiles = glob($contentPath . '/*.json');
            if (!empty($jsonFiles)) {
                $jsonContent = file_get_contents($jsonFiles[0]);
                $jsonData = json_decode($jsonContent, true);
                
                // Save JSON locally
                file_put_contents($rootTourDirectory . '/' . basename($jsonFiles[0]), $jsonContent);
            }

            // Upload ONLY asset folders (images, assets, gallery, tiles) to S3
            $assetFolders = ['images', 'assets', 'gallery', 'tiles'];
            $uploadedFolders = [];
            
            \Log::info("Starting folder upload to S3. Content path: {$contentPath}");
            
            foreach ($assetFolders as $folder) {
                $folderPath = $contentPath . '/' . $folder;
                
                if (is_dir($folderPath)) {
                    $uploadResult = $this->uploadDirectoryToS3($folderPath, $s3TourPath . '/' . $folder, []);
                    
                    if ($uploadResult['success']) {
                        $uploadedFolders[] = $folder;
                    } else {
                        \Log::warning("Failed to upload '{$folder}' folder: {$uploadResult['message']}");
                    }
                } else {
                    \Log::warning("Folder '{$folder}' not found at path: {$folderPath}");
                }
            }
            
            if (empty($uploadedFolders)) {
                \Log::error("No asset folders were uploaded to S3. Available folders in content path: " . implode(', ', array_diff(scandir($contentPath), ['.', '..'])));
            } else {
                \Log::info("Successfully uploaded folders to S3: " . implode(', ', $uploadedFolders));
            }

            // Clean up temporary directory
            \File::deleteDirectory($tempExtractPath);

            // Validate that we got the required files
            if (!$indexHtmlContent) {
                return [
                    'success' => false,
                    'message' => 'index.html file not found in zip root'
                ];
            }

            if (!$jsonData) {
                return [
                    'success' => false,
                    'message' => 'JSON configuration file not found in zip root'
                ];
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

            return [
                'success' => true,
                'data' => $jsonData,
                'tour_path' => $rootTourPath,
                'tour_url' => url('/' . $rootTourPath . '/index.php'),
                's3_path' => $s3TourPath,
                's3_url' => $s3Url,
                'message' => 'Zip file processed successfully - index.php stored locally, assets uploaded to S3'
            ];

        } catch (\Exception $e) {
            \Log::error('Zip processing error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error processing zip file: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Upload directory contents to S3 recursively with optimization
     */
    private function uploadDirectoryToS3($localPath, $s3Path, $excludeFiles = [])
    {
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
            if (strpos($fileName, '.') === 0 || 
                strpos($relativePath, '__MACOSX') !== false || 
                strpos($relativePath, '.DS_Store') !== false) {
                continue;
            }

            $s3FilePath = $s3Path . '/' . $relativePath;
            $filesToUpload[] = [
                'local' => $filePath,
                's3' => $s3FilePath,
                'size' => filesize($filePath)
            ];
        }

        // Log summary of files to upload
        \Log::info("Found " . count($filesToUpload) . " files to upload from: {$localPath}");
        
        if (empty($filesToUpload)) {
            \Log::warning("No files found to upload in directory: {$localPath}");
            return [
                'success' => false,
                'message' => 'No files found in directory',
                'files_count' => 0,
                'total_size' => 0
            ];
        }
        
        // Upload files in batches
        $batchSize = 5; // Upload 5 files at a time
        $totalSize = 0;
        $uploadedCount = 0;
        $failedCount = 0;
        
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
                    
                    $mimeType = mime_content_type($fileData['local']) ?: 'application/octet-stream';
                    
                    // Try to upload to S3 (bucket policy handles public access)
                    $uploaded = Storage::disk('s3')->put(
                        $fileData['s3'],
                        $fileContent,
                        [
                            'ContentType' => $mimeType
                        ]
                    );
                    
                    if (!$uploaded) {
                        throw new \Exception("S3 upload returned false");
                    }
                    
                    $totalSize += $fileData['size'];
                    $uploadedCount++;
                    
                } catch (\Exception $e) {
                    $failedCount++;
                    \Log::error("Failed to upload {$fileData['s3']}: " . $e->getMessage() . " | File: {$fileData['local']}");
                    
                    // If too many failures, stop and report
                    if ($failedCount > 10) {
                        \Log::error("Too many upload failures ({$failedCount}). Stopping upload process.");
                        break 2;
                    }
                }
            }
        }
        
        $sizeMB = round($totalSize / 1024 / 1024, 2);
        $totalFiles = count($filesToUpload);
        
        if ($failedCount > 0) {
            \Log::warning("Upload completed with errors: {$uploadedCount}/{$totalFiles} files uploaded ({$sizeMB} MB), {$failedCount} failed to S3 path: {$s3Path}");
        } else {
            \Log::info("Successfully uploaded all {$uploadedCount}/{$totalFiles} files ({$sizeMB} MB) to S3 path: {$s3Path}");
        }
        
        return [
            'success' => $uploadedCount > 0,
            'message' => $failedCount > 0 
                ? "Uploaded {$uploadedCount}/{$totalFiles} files, {$failedCount} failed"
                : "Successfully uploaded {$uploadedCount} files",
            'files_count' => $uploadedCount,
            'total_size' => $sizeMB,
            'failed_count' => $failedCount
        ];
    }

    /**
     * Validate zip file structure
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

            // Check for index.html in root (must not be in subdirectory)
            // File is in root if it doesn't contain any slash, or only has trailing slash
            $cleanFilename = rtrim($filename, '/');
            if (strtolower(basename($cleanFilename)) === 'index.html') {
                $hasIndexHtml = true;
            }

            // Check for JSON file in root (must not be in subdirectory)
            $fileInfo = pathinfo($cleanFilename);
            if (isset($fileInfo['extension']) && strtolower($fileInfo['extension']) === 'json') {
                $hasJsonFile = true;
            }

            // Check for required folders
            // A folder entry typically ends with / or contains files like: foldername/filename
            $parts = explode('/', $filename);
            if (count($parts) >= 2 && !empty($parts[0])) {
                $topFolder = strtolower($parts[1]);
                if (in_array($topFolder, $requiredFolders) && !in_array($topFolder, $foundFolders)) {
                    $foundFolders[] = $topFolder;
                }
            }
        }

        $missingFolders = array_diff($requiredFolders, $foundFolders);

        if (!$hasIndexHtml) {
            return ['valid' => false, 'message' => 'Zip file must contain index.html in root'];
        }

        if (!$hasJsonFile) {
            return ['valid' => false, 'message' => 'Zip file must contain a JSON configuration file in root'];
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

    /**
     * Generate PHP script to fetch tour and booking data from database
     */
    private function generateDatabaseFetchScript()
    {
        return <<<'PHP'
<?php
// Load environment variables from .env file
function loadEnv($filePath) {
    if (!file_exists($filePath)) {
        die("Error: .env file not found at $filePath");
    }
    
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $env = [];
    
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse line
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove quotes if present
            if (preg_match('/^(["\'])(.*)\1$/', $value, $matches)) {
                $value = $matches[2];
            }
            
            $env[$key] = $value;
        }
    }
    
    return $env;
}

// Helper function to escape HTML attributes
function escAttr($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

// Get the current tour code from the URL path
$currentPath = dirname($_SERVER['SCRIPT_NAME']);
$tourCode = basename($currentPath);

// Load environment variables
$envPath = __DIR__ . '/../../.env';
$env = loadEnv($envPath);

// Database configuration from .env
$dbHost = $env['DB_HOST'] ?? '127.0.0.1';
$dbPort = $env['DB_PORT'] ?? '3306';
$dbName = $env['DB_DATABASE'] ?? '';
$dbUser = $env['DB_USERNAME'] ?? '';
$dbPass = $env['DB_PASSWORD'] ?? '';

// Initialize variables
$tourData = null;
$bookingData = null;
$baseUrl = '';
$seoMetaTags = '';
$headerCode = '';
$footerCode = '';

try {
    // Create database connection
    $dsn = "mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
    // Fetch booking data by tour code (QR code)
    $stmt = $pdo->prepare("
        SELECT b.*, 
               u.firstname, u.lastname, u.mobile, u.email,
               pt.name as property_type_name,
               pst.name as property_sub_type_name,
               bhk.name as bhk_name,
               c.name as city_name,
               s.name as state_name,
               qr.code as qr_code
        FROM bookings b
        LEFT JOIN users u ON b.user_id = u.id
        LEFT JOIN property_types pt ON b.property_type_id = pt.id
        LEFT JOIN property_sub_types pst ON b.property_sub_type_id = pst.id
        LEFT JOIN b_h_k_s bhk ON b.bhk_id = bhk.id
        LEFT JOIN cities c ON b.city_id = c.id
        LEFT JOIN states s ON b.state_id = s.id
        LEFT JOIN qr_code qr ON b.id = qr.booking_id
        WHERE qr.code = :tour_code
        LIMIT 1
    ");
    $stmt->execute(['tour_code' => $tourCode]);
    $bookingData = $stmt->fetch();
    
    if ($bookingData) {
        // Fetch tour data for this booking (with all SEO fields)
        $stmt = $pdo->prepare("
            SELECT t.*, 
                   u.firstname as creator_firstname, 
                   u.lastname as creator_lastname
            FROM tours t
            LEFT JOIN users u ON t.created_by = u.id
            WHERE t.booking_id = :booking_id
            ORDER BY t.created_at DESC
            LIMIT 1
        ");
        $stmt->execute(['booking_id' => $bookingData['id']]);
        $tourData = $stmt->fetch();
        
        // Get base URL from booking
        $baseUrl = $bookingData['base_url'] ?? '';
        
        // Generate SEO meta tags if tour data exists
        if ($tourData) {
            $seoTags = [];
            
            // Basic Meta Tags
            if (!empty($tourData['meta_title'])) {
                $seoTags[] = '<title>' . escAttr($tourData['meta_title']) . '</title>';
            }
            if (!empty($tourData['meta_description'])) {
                $seoTags[] = '<meta name="description" content="' . escAttr($tourData['meta_description']) . '" />';
            }
            if (!empty($tourData['meta_keywords'])) {
                $seoTags[] = '<meta name="keywords" content="' . escAttr($tourData['meta_keywords']) . '" />';
            }
            if (!empty($tourData['meta_robots'])) {
                $seoTags[] = '<meta name="robots" content="' . escAttr($tourData['meta_robots']) . '" />';
            }
            if (!empty($tourData['canonical_url'])) {
                $seoTags[] = '<link rel="canonical" href="' . escAttr($tourData['canonical_url']) . '" />';
            }
            
            // Open Graph Tags
            if (!empty($tourData['og_title'])) {
                $seoTags[] = '<meta property="og:title" content="' . escAttr($tourData['og_title']) . '" />';
            }
            if (!empty($tourData['og_description'])) {
                $seoTags[] = '<meta property="og:description" content="' . escAttr($tourData['og_description']) . '" />';
            }
            if (!empty($tourData['og_image'])) {
                $seoTags[] = '<meta property="og:image" content="' . escAttr($tourData['og_image']) . '" />';
                $seoTags[] = '<meta property="og:image:secure_url" content="' . escAttr($tourData['og_image']) . '" />';
            }
            $seoTags[] = '<meta property="og:type" content="website" />';
            $seoTags[] = '<meta property="og:url" content="' . escAttr($_SERVER['REQUEST_URI'] ?? '') . '" />';
            
            // Twitter Card Tags
            $seoTags[] = '<meta name="twitter:card" content="summary_large_image" />';
            if (!empty($tourData['twitter_title'])) {
                $seoTags[] = '<meta name="twitter:title" content="' . escAttr($tourData['twitter_title']) . '" />';
            }
            if (!empty($tourData['twitter_description'])) {
                $seoTags[] = '<meta name="twitter:description" content="' . escAttr($tourData['twitter_description']) . '" />';
            }
            if (!empty($tourData['twitter_image'])) {
                $seoTags[] = '<meta name="twitter:image" content="' . escAttr($tourData['twitter_image']) . '" />';
            }
            
            // Structured Data (JSON-LD)
            if (!empty($tourData['structured_data'])) {
                $structuredData = json_decode($tourData['structured_data'], true);
                if ($structuredData) {
                    $seoTags[] = '<script type="application/ld+json">' . json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>';
                }
            }
            
            $seoMetaTags = implode("\n    ", $seoTags);
            
            // Get custom header and footer code
            $headerCode = $tourData['header_code'] ?? '';
            $footerCode = $tourData['footer_code'] ?? '';
        }
    }
    
} catch (PDOException $e) {
    // Log error but don't break the page
    error_log("Database error in tour index.php: " . $e->getMessage());
    $tourData = null;
    $bookingData = null;
}

// Make data available as JSON for JavaScript
$tourDataJson = json_encode($tourData);
$bookingDataJson = json_encode($bookingData);
$baseUrlJson = json_encode($baseUrl);

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
      window.tourData = <?php echo $tourDataJson; ?>;
      window.bookingData = <?php echo $bookingDataJson; ?>;
      window.baseUrl = <?php echo $baseUrlJson; ?>;
      
      console.log('Tour Data:', window.tourData);
      console.log('Booking Data:', window.bookingData);
      console.log('Base URL:', window.baseUrl);
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
}
