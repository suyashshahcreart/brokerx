<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Tour;
use Illuminate\Http\Request;
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
                    if ($subType) $info .= ' - ' . $subType;
                    if ($bhk) $info .= ' - ' . $bhk;
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
                    if ($booking->society_name) $location[] = $booking->society_name;
                    if ($booking->address_area) $location[] = $booking->address_area;
                    if ($booking->city) $location[] = $booking->city->name;
                    
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
            'tours'
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
                        $result = $this->processZipFile($file, $tour);
                        
                        if ($result['success']) {
                            $tourData = $result['data'];
                            $uploadedFiles[] = [
                                'name' => $file->getClientOriginalName(),
                                'type' => 'zip',
                                'processed' => true,
                                'extracted_path' => $result['storage_path'],
                                'public_path' => $result['public_path'],
                                'size' => $file->getSize(),
                                'uploaded_at' => now()->toDateTimeString()
                            ];
                        } else {
                            throw new \Exception($result['message']);
                        }
                    } else {
                        // Handle regular files (images, pdfs, etc.)
                        $filename = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                        $path = $file->storeAs('tours', $filename, 'public');
                        
                        $uploadedFiles[] = [
                            'name' => $file->getClientOriginalName(),
                            'path' => $path,
                            'url' => asset('storage/' . $path),
                            'size' => $file->getSize(),
                            'type' => $file->getMimeType(),
                            'uploaded_at' => now()->toDateTimeString()
                        ];
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
    }

    /**
     * Process and validate zip file containing tour assets
     */
    private function processZipFile($zipFile, Tour $tour)
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

            // Validate required files and folders
            $validation = $this->validateZipStructure($zip);
            if (!$validation['valid']) {
                $zip->close();
                return [
                    'success' => false,
                    'message' => $validation['message']
                ];
            }

            // Create tour-specific directories
            $tourId = $tour->id;
            $storagePath = storage_path('app/public/tours/' . $tourId);
            $publicPath = public_path('tours/' . $tourId);

            // Delete old tour files if they exist
            if (\File::exists($storagePath)) {
                \File::deleteDirectory($storagePath);
            }
            if (\File::exists($publicPath)) {
                \File::deleteDirectory($publicPath);
            }

            // Create directories
            \File::makeDirectory($storagePath, 0755, true);
            \File::makeDirectory($publicPath, 0755, true);

            // Extract files
            $jsonData = null;
            $indexHtmlContent = null;

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                $fileInfo = pathinfo($filename);

                // Skip hidden files and __MACOSX
                if (strpos($filename, '__MACOSX') !== false || strpos($filename, '.DS_Store') !== false) {
                    continue;
                }

                // Handle index.html - save to public folder
                if (strtolower($fileInfo['basename']) === 'index.html' && !str_contains($filename, '/')) {
                    $indexHtmlContent = $zip->getFromIndex($i);
                    file_put_contents($publicPath . '/index.html', $indexHtmlContent);
                    continue;
                }

                // Handle JSON file - read and parse
                if (isset($fileInfo['extension']) && strtolower($fileInfo['extension']) === 'json' && !str_contains($filename, '/')) {
                    $jsonContent = $zip->getFromIndex($i);
                    $jsonData = json_decode($jsonContent, true);
                    
                    // Also save the JSON file to storage
                    file_put_contents($storagePath . '/' . $fileInfo['basename'], $jsonContent);
                    continue;
                }

                // Extract all other files to storage
                $targetPath = $storagePath . '/' . $filename;
                
                // Create directory if needed
                if (substr($filename, -1) === '/') {
                    if (!\File::exists($targetPath)) {
                        \File::makeDirectory($targetPath, 0755, true);
                    }
                } else {
                    // Ensure parent directory exists
                    $parentDir = dirname($targetPath);
                    if (!\File::exists($parentDir)) {
                        \File::makeDirectory($parentDir, 0755, true);
                    }
                    
                    // Extract file
                    $fileContent = $zip->getFromIndex($i);
                    if ($fileContent !== false) {
                        file_put_contents($targetPath, $fileContent);
                    }
                }
            }

            $zip->close();

            // Validate that we got the required files
            if (!$indexHtmlContent) {
                $this->cleanupFailedExtraction($storagePath, $publicPath);
                return [
                    'success' => false,
                    'message' => 'index.html file not found in zip root'
                ];
            }

            if (!$jsonData) {
                $this->cleanupFailedExtraction($storagePath, $publicPath);
                return [
                    'success' => false,
                    'message' => 'JSON configuration file not found in zip root'
                ];
            }

            return [
                'success' => true,
                'data' => $jsonData,
                'storage_path' => 'tours/' . $tourId,
                'public_path' => '/tours/' . $tourId . '/index.html',
                'message' => 'Zip file processed successfully'
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
            
            // Skip hidden files
            if (strpos($filename, '__MACOSX') !== false || strpos($filename, '.DS_Store') !== false) {
                continue;
            }

            // Check for index.html in root
            if (strtolower(basename($filename)) === 'index.html' && !str_contains(dirname($filename), '/')) {
                $hasIndexHtml = true;
            }

            // Check for JSON file in root
            if (pathinfo($filename, PATHINFO_EXTENSION) === 'json' && !str_contains(dirname($filename), '/')) {
                $hasJsonFile = true;
            }

            // Check for required folders
            $parts = explode('/', $filename);
            if (count($parts) > 0) {
                $topFolder = strtolower($parts[0]);
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
    private function cleanupFailedExtraction($storagePath, $publicPath)
    {
        if (\File::exists($storagePath)) {
            \File::deleteDirectory($storagePath);
        }
        if (\File::exists($publicPath)) {
            \File::deleteDirectory($publicPath);
        }
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
