<?php

namespace App\Http\Controllers\Admin\Api;


use App\Models\Booking;
use App\Models\Customer;
use App\Models\Tour;
use App\Models\User;
use App\Models\Setting;
use App\Models\FtpConfiguration;
use App\Jobs\ProcessTourZipFile;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use ZipArchive;

class TourManagerController extends Controller
{
    /**
     * Handle the login request for tour manager (static response for now)
     */
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $data['email'])->first();
        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Generate a token (using Laravel Sanctum if available)
        if (method_exists($user, 'createToken')) {
            $fullToken = $user->createToken('tour_manager_api')->plainTextToken;
            // Extract only the token part (remove the ID prefix)
            $token = explode('|', $fullToken, 2)[1] ?? $fullToken;
        } else {
            $token = base64_encode(bin2hex(random_bytes(32)));
        }

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->hasRole('admin') ? 'Admin' : ($user->hasRole('tour_manager') ? 'Tour Manager' : 'User')   ,
                'token' => $token,
            ]
        ]);
    }

    /**
     * Get all users with role 'customer'
     */
    public function getCustomers(Request $request)
    {
        $customers = Customer::query()->get(['id', 'firstname', 'lastname', 'email', 'mobile']);
        return response()->json([
            'success' => true,
            'customers' => $customers
        ]);
    }

    /**
     * Get all tours for a given customer (user_id) via bookings
     */
    public function getToursByCustomer(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'nullable|integer|exists:customers,id',
            'user_id' => 'nullable|integer|exists:users,id',
        ]);

        $customerId = $data['customer_id'] ?? null;
        $userId = $data['user_id'] ?? null;

        if (!$customerId && !$userId) {
            return response()->json([
                'success' => false,
                'message' => 'customer_id or user_id is required'
            ], 422);
        }

        $bookingQuery = Booking::query();
        if ($customerId) {
            $bookingQuery->where('customer_id', $customerId);
        } else {
            $bookingQuery->where('user_id', $userId);
        }

        $bookingIds = $bookingQuery->pluck('id');
        // Get API, QR, and S3 base URLs from settings
        $apiBaseUrl = getApiBaseUrl();
        $qrLinkBase = getQrLinkBase();
        $s3LinkBase = getS3LinkBase();
        
        // Get tours for these bookings
        $tours = Tour::whereIn('booking_id', $bookingIds)->with('booking')->get();

        // Map tours to include full logo URLs
        $tours = $tours->map(function ($tour) use ($apiBaseUrl, $qrLinkBase, $s3LinkBase) {

            $tour->footer_brand_logo = $tour->footer_brand_logo ? $s3LinkBase . $tour->footer_brand_logo : null;

            $tour->footer_logo = $tour->footer_logo ? $tour->footer_logo : null;
            $tour->sidebar_logo = $tour->sidebar_logo ? $s3LinkBase . $tour->sidebar_logo : null;

            // QR Code
            $tour->qr_code = $tour->booking ? $tour->booking->tour_code : null;
            $tour->tour_code = $tour->booking ? $tour->booking->tour_code : null;
            $tour->qr_link = $tour->booking ? $tour->booking->tour_code ? $qrLinkBase . $tour->qr_code : null : null;
            $tour->s3_link = $tour->booking ? $tour->booking->tour_code ? $s3LinkBase . 'tours/' . $tour->qr_code . "/" : null : null;
            
            $tour->top_image = $tour->footer_logo ? $tour->footer_logo : null;
            $tour->top_number  = $tour->footer_mobile;
            $tour->top_title  = $tour->footer_name;
            $tour->top_email  = $tour->footer_email;
            $tour->top_sub_title  = $tour->footer_subtitle;
            $tour->top_description  = $tour->footer_decription;

            $tour->is_hosted = $tour->is_hosted ?? false;
            $tour->hosted_link = $tour->hosted_link ?? null;
            $tour->api_link = $apiBaseUrl;
            
            
            $tour->makeHidden(['booking']);
            $tour->makeVisible(['qr_code']);
            $tourArr = $tour->toArray();
            // Add full URLs for custom logos
            $tourArr['custom_logo_sidebar_url'] = $tour->custom_logo_sidebar ? Storage::disk('s3')->url($tour->custom_logo_sidebar) : null;
            $tourArr['custom_logo_footer_url'] = $tour->custom_logo_footer ? Storage::disk('s3')->url($tour->custom_logo_footer) : null;
            return $tourArr;
        });
        return response()->json([
            'success' => true,
            'tours' => $tours
        ]);
    }

    /**
     * Get details for a specific tour via tour_code
     */
    public function getTourDetails(Request $request, $tour_code)
    {
        $booking = Booking::where('tour_code', $tour_code)->first();
        
        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Tour code not found'
            ], 404);
        }

        $tour = $booking->tours()->latest()->first();
        
        if (!$tour) {
            return response()->json([
                'success' => false,
                'message' => 'Tour configuration not found for this code'
            ], 404);
        }

        // Re-attach booking for mapping logic compatibility
        $tour->setRelation('booking', $booking);

        // Get API, QR, and S3 base URLs from settings
        $apiBaseUrl = getApiBaseUrl();
        $qrLinkBase = getQrLinkBase();
        $s3LinkBase = getS3LinkBase();

        // Format tour details (matching mapping logic in getToursByCustomer)
        $tour->footer_brand_logo = $tour->footer_brand_logo ? $s3LinkBase . $tour->footer_brand_logo : null;
        $tour->footer_logo = $tour->footer_logo ? $s3LinkBase . $tour->footer_logo : null;
        $tour->sidebar_logo = $tour->sidebar_logo ? $s3LinkBase . $tour->sidebar_logo : null;

        $tour->qr_code = $tour->booking ? $tour->booking->tour_code : null;
        $tour->qr_link = $tour->booking ? $tour->booking->tour_code ? $qrLinkBase . $tour->qr_code : null : null;
        $tour->s3_link = $tour->booking ? $tour->booking->tour_code ? $s3LinkBase . 'tours/' . $tour->qr_code . "/" : null : null;
        
        $tour->top_image = $tour->footer_logo ? $s3LinkBase . $tour->footer_logo : null;
        $tour->top_number  = $tour->footer_mobile;
        $tour->top_title  = $tour->footer_name;
        $tour->top_email  = $tour->footer_email;
        $tour->top_sub_title  = $tour->footer_subtitle;
        $tour->top_description  = $tour->footer_decription;

        $tour->is_hosted = $tour->is_hosted ?? false;
        $tour->hosted_link = $tour->hosted_link ?? null;
        $tour->api_link = $apiBaseUrl;

        $tour->makeHidden(['booking']);
        $tour->makeVisible(['qr_code']);
        
        $tourData = $tour->toArray();
        // Add full URLs for custom logos
        $tourData['custom_logo_sidebar_url'] = $tour->custom_logo_sidebar ? Storage::disk('s3')->url($tour->custom_logo_sidebar) : null;
        $tourData['custom_logo_footer_url'] = $tour->custom_logo_footer ? Storage::disk('s3')->url($tour->custom_logo_footer) : null;

        return response()->json([
            'success' => true,
            'tour' => $tourData
        ]);
    }

    /**
     * Get all available tour locations (FTP configurations)
     */
    public function getTourLocations(Request $request)
    {
        $locations = FtpConfiguration::active()
            ->ordered()
            ->get(['id', 'category_name', 'display_name', 'main_url'])
            ->map(function ($config) {
                return [
                    'id' => $config->id,
                    'category_name' => $config->category_name,
                    'display_name' => $config->display_name,
                    'main_url' => $config->main_url,
                ];
            });

        return response()->json([
            'success' => true,
            'locations' => $locations
        ]);
    }

    /**
     * Update working_json field for a specific tour via tour_code (stores as JSON)
     */
    public function updateWorkingJson(Request $request, $tour_code)
    {
        // Read raw content and parse JSON
        $rawContent = $request->getContent();
        $workingJson = null;
        $userId = null;
        
        if (!empty($rawContent)) {
            $parsedContent = json_decode($rawContent, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($parsedContent)) {
                $workingJson = $parsedContent['working_json'] ?? null;
                $userId = $parsedContent['working_json_last_update_user'] ?? null;
            }
        }
        
        // Fallback: try regular input
        if ($workingJson === null) {
            $workingJson = $request->input('working_json');
        }
        if ($userId === null) {
            $userId = $request->input('working_json_last_update_user');
        }
        
        // Validate both fields are required
        $errors = [];
        if ($workingJson === null || $workingJson === '') {
            $errors['working_json'] = ['The working json field is required.'];
        }
        if ($userId === null || $userId === '') {
            $errors['working_json_last_update_user'] = ['The working json last update user field is required.'];
        }
        
        if (!empty($errors)) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $errors
            ], 422);
        }
        
        // Validate user exists
        $user = User::find($userId);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
                'errors' => [
                    'working_json_last_update_user' => ['The specified user does not exist.']
                ]
            ], 422);
        }
        // Find booking first to get the tour
        $booking = Booking::where('tour_code', $tour_code)->first();
        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Tour code not found'
            ], 404);
        }

        // Find latest tour for this booking
        $tour = $booking->tours()->latest()->first();
        if (!$tour) {
            return response()->json([
                'success' => false,
                'message' => 'Tour not found'
            ], 404);
        }
        
        // If working_json is a string, try to decode it as JSON
        if (is_string($workingJson)) {
            $decoded = json_decode($workingJson, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $workingJson = $decoded;
            }
        }
        
        // Update both fields
        $tour->working_json = $workingJson;
        $tour->working_json_last_update_user = $userId;
        $tour->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Working JSON updated successfully',
            'tour' => [
                'id' => $tour->id,
                'working_json' => $tour->working_json,
                'working_json_last_update_user' => $tour->working_json_last_update_user
            ]
        ]);
    }

    /**
     * Smart upload file - automatically handles simple or chunked upload based on file size
     * Single POST API endpoint that manages everything based on ZIP file size
     * Files < 100MB: Simple upload
     * Files >= 100MB: Chunked upload (handled internally)
     * Requires: tour_code, slug, location, and file
     */
    public function uploadFile(Request $request)
    {
        // Increase execution time limit for large file processing
        set_time_limit(18000);
        ini_set('max_execution_time', '18000');
        
        // Get valid location values from FTP configurations
        $validLocations = FtpConfiguration::active()->pluck('category_name')->toArray();
        
        // File size threshold: 100MB (104857600 bytes)
        $fileSizeThreshold = 100 * 1024 * 1024; // 100MB
        
        $request->validate([
            'tour_code' => 'required|string|exists:bookings,tour_code',
            'slug' => 'required|string|max:255|regex:/^[a-zA-Z0-9\/\-_]+$/',
            'location' => ['required', 'string', Rule::in($validLocations)],
            'file' => 'required|file|max:1024000|mimes:zip', // 1GB max, ZIP files only
        ]);

        try {
            // Get booking by tour_code
            $booking = Booking::where('tour_code', $request->input('tour_code'))->first();
            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tour code not found'
                ], 404);
            }

            // Get the tour for this booking
            $tour = $booking->tours()->first();
            if (!$tour) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tour found for this booking.'
                ], 404);
            }

            // Update tour slug and location
            $slug = $request->input('slug');
            $location = $request->input('location');
            
            $tour->slug = $slug;
            $tour->location = $location;
            $tour->updated_by = auth()->id();
            $tour->save();

            // Process the ZIP file
            $file = $request->file('file');
            $fileSize = $file->getSize();
            $filename = $file->getClientOriginalName();
            
            // Validate it's a ZIP file
            $extension = strtolower($file->getClientOriginalExtension());
            if ($extension !== 'zip') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only ZIP files are allowed. Please upload a ZIP file.'
                ], 422);
            }

            // Determine upload method based on file size
            $useChunkedUpload = $fileSize >= $fileSizeThreshold;
            $uploadMethod = $useChunkedUpload ? 'chunked' : 'simple';

            \Log::info("ZIP file upload via API. Booking ID: {$booking->id}, File size: {$fileSize} bytes ({$uploadMethod})");

            if ($useChunkedUpload) {
                // For large files: Use chunked upload process internally
                return $this->handleChunkedUploadInternal($file, $booking, $tour, $slug, $location, $filename, $fileSize);
            } else {
                // For small files: Use simple upload process
                return $this->handleSimpleUploadInternal($file, $booking, $tour, $slug, $location, $filename, $fileSize);
            }

        } catch (\Exception $e) {
            \Log::error('File upload error via API: ' . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
            
            return response()->json([
                'success' => false,
                'message' => 'File upload error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle simple upload for files < 100MB
     */
    private function handleSimpleUploadInternal($file, $booking, $tour, $slug, $location, $filename, $fileSize)
    {
        // Save file temporarily and dispatch background job
        $tempPath = $file->storeAs('temp_uploads', 'tour_' . $booking->id . '_' . time() . '.zip', 'local');
        $fullTempPath = storage_path('app/' . $tempPath);
        
        // Track status for UI
        $booking->tour_zip_status = 'processing';
        $booking->tour_zip_progress = 0;
        $booking->tour_zip_message = 'Queued for background processing (simple upload)';
        $booking->tour_zip_started_at = now();
        $booking->tour_zip_finished_at = null;
        $booking->save();
        
        // Dispatch background job to process the ZIP file
        $jobUniqueId = 'tour-processing-' . $booking->id . '-' . md5($filename . $fullTempPath);
        ProcessTourZipFile::dispatch(
            $booking->id,
            $fullTempPath,
            $filename,
            $slug,
            $location
        )->onQueue('tour-processing');
        
        \Log::info("Simple upload processing queued. Booking ID: {$booking->id}, Job ID: {$jobUniqueId}");

        return response()->json([
            'success' => true,
            'message' => 'ZIP file uploaded successfully! Processing will continue in the background.',
            'upload_method' => 'simple',
            'file_size' => $fileSize,
            'file_size_mb' => round($fileSize / 1024 / 1024, 2),
            'booking_id' => $booking->id,
            'tour_code' => $booking->tour_code,
            'processing' => true,
            'tour_zip_status' => $booking->tour_zip_status,
            'tour_zip_progress' => $booking->tour_zip_progress,
            'tour_zip_message' => $booking->tour_zip_message,
            'tour' => [
                'id' => $tour->id,
                'slug' => $tour->slug,
                'location' => $tour->location,
                'tour_code' => $booking->tour_code,
            ]
        ]);
    }

    /**
     * Handle chunked upload for files >= 100MB
     * Internally splits the file and processes it
     */
    private function handleChunkedUploadInternal($file, $booking, $tour, $slug, $location, $filename, $fileSize)
    {
        // Save the full file temporarily first
        $tempPath = $file->storeAs('temp_uploads', 'tour_' . $booking->id . '_' . time() . '.zip', 'local');
        $fullTempPath = storage_path('app/' . $tempPath);
        
        // Initialize chunked upload metadata
        $uploadId = md5($filename . $fileSize . $booking->id . time());
        $chunkSize = 10 * 1024 * 1024; // 10MB chunks
        $totalChunks = ceil($fileSize / $chunkSize);
        
        // Create temporary directory for chunks
        $chunkDir = storage_path('app/chunks/' . $uploadId);
        
        // Ensure parent chunks directory exists
        $chunksBaseDir = storage_path('app/chunks');
        if (!is_dir($chunksBaseDir)) {
            if (!mkdir($chunksBaseDir, 0775, true)) {
                \Log::error("Failed to create chunks base directory: {$chunksBaseDir}");
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create upload directory. Please check server permissions.'
                ], 500);
            }
            @chmod($chunksBaseDir, 0775);
        }
        
        // Create upload-specific directory
        if (!is_dir($chunkDir)) {
            if (!mkdir($chunkDir, 0775, true)) {
                \Log::error("Failed to create chunk directory: {$chunkDir}");
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create upload directory. Please check server permissions.'
                ], 500);
            }
            @chmod($chunkDir, 0775);
        }
        
        try {
            // Split file into chunks and save them
            $fileHandle = fopen($fullTempPath, 'rb');
            if (!$fileHandle) {
                throw new \Exception('Failed to open uploaded file for chunking.');
            }
            
            for ($chunkNumber = 0; $chunkNumber < $totalChunks; $chunkNumber++) {
                $chunkPath = $chunkDir . '/chunk_' . $chunkNumber;
                $chunkHandle = fopen($chunkPath, 'wb');
                
                if (!$chunkHandle) {
                    fclose($fileHandle);
                    throw new \Exception("Failed to create chunk file: {$chunkNumber}");
                }
                
                // Read chunk from original file
                $bytesRead = 0;
                while ($bytesRead < $chunkSize && !feof($fileHandle)) {
                    $data = fread($fileHandle, min($chunkSize - $bytesRead, 8192)); // Read in 8KB blocks
                    if ($data === false) break;
                    fwrite($chunkHandle, $data);
                    $bytesRead += strlen($data);
                }
                
                fclose($chunkHandle);
            }
            
            fclose($fileHandle);
            
            // Store upload metadata
            $metadata = [
                'upload_id' => $uploadId,
                'filename' => $filename,
                'total_size' => $fileSize,
                'booking_id' => $booking->id,
                'tour_code' => $booking->tour_code,
                'slug' => $slug,
                'location' => $location,
                'chunks_uploaded' => $totalChunks,
                'total_chunks' => $totalChunks,
                'created_at' => now()->toDateTimeString(),
            ];
            
            file_put_contents($chunkDir . '/metadata.json', json_encode($metadata));
            
            // Now combine chunks and process
            $finalPath = $chunkDir . '/final.zip';
            $finalFile = fopen($finalPath, 'wb');
            
            if (!$finalFile) {
                throw new \Exception('Failed to create final file.');
            }
            
            // Combine all chunks
            for ($i = 0; $i < $totalChunks; $i++) {
                $chunkPath = $chunkDir . '/chunk_' . $i;
                if (file_exists($chunkPath)) {
                    $chunkContent = file_get_contents($chunkPath);
                    fwrite($finalFile, $chunkContent);
                    unlink($chunkPath); // Clean up chunk
                }
            }
            
            fclose($finalFile);
            
            // Clean up original temp file
            if (file_exists($fullTempPath)) {
                @unlink($fullTempPath);
            }
            
            // Track status for UI
            $booking->tour_zip_status = 'processing';
            $booking->tour_zip_progress = 0;
            $booking->tour_zip_message = 'Queued for background processing (chunked upload)';
            $booking->tour_zip_started_at = now();
            $booking->tour_zip_finished_at = null;
            $booking->save();
            
            // Dispatch background job to process the ZIP file
            $jobUniqueId = 'tour-processing-' . $booking->id . '-' . md5($filename . $finalPath);
            ProcessTourZipFile::dispatch(
                $booking->id,
                $finalPath,
                $filename,
                $slug,
                $location
            )->onQueue('tour-processing');
            
            \Log::info("Chunked upload processing queued. Booking ID: {$booking->id}, Job ID: {$jobUniqueId}, Chunks: {$totalChunks}");
            
            // Clean up metadata file (keep final.zip for the job)
            unlink($chunkDir . '/metadata.json');
            
            return response()->json([
                'success' => true,
                'message' => 'Large ZIP file uploaded successfully! Processing will continue in the background.',
                'upload_method' => 'chunked',
                'file_size' => $fileSize,
                'file_size_mb' => round($fileSize / 1024 / 1024, 2),
                'total_chunks' => $totalChunks,
                'chunk_size_mb' => round($chunkSize / 1024 / 1024, 2),
                'booking_id' => $booking->id,
                'tour_code' => $booking->tour_code,
                'processing' => true,
                'tour_zip_status' => $booking->tour_zip_status,
                'tour_zip_progress' => $booking->tour_zip_progress,
                'tour_zip_message' => $booking->tour_zip_message,
                'tour' => [
                    'id' => $tour->id,
                    'slug' => $tour->slug,
                    'location' => $tour->location,
                    'tour_code' => $booking->tour_code,
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Chunked upload error: ' . $e->getMessage());
            
            // Clean up on error
            if (is_dir($chunkDir)) {
                @array_map('unlink', glob($chunkDir . '/*'));
                @rmdir($chunkDir);
            }
            if (file_exists($fullTempPath)) {
                @unlink($fullTempPath);
            }
            
            // Track error for UI
            try {
                $booking->tour_zip_status = 'failed';
                $booking->tour_zip_progress = 0;
                $booking->tour_zip_message = 'Failed to process chunks: ' . $e->getMessage();
                $booking->tour_zip_finished_at = now();
                $booking->save();
            } catch (\Exception $inner) {
                // ignore status update failure
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to process chunked upload: ' . $e->getMessage()
            ], 500);
        }
    }

}
