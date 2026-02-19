<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Booking;
use App\Models\Tour;
use App\Models\QR;
use ZipArchive;

class ProcessTourZipFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 18000; // 5 hours for large ZIP processing
    public $tries = 3; // Retry a few times (DB retry_after can re-attempt long jobs)
    public $backoff = 900; // Wait 15 minutes before retry

    protected $bookingId;
    protected $zipFilePath;
    protected $originalFilename;
    protected $slug;
    protected $location;

    /**
     * Create a new job instance.
     */
    public function __construct($bookingId, $zipFilePath, $originalFilename, $slug, $location)
    {
        $this->bookingId = $bookingId;
        $this->zipFilePath = $zipFilePath;
        $this->originalFilename = $originalFilename;
        $this->slug = $slug;
        $this->location = $location;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Increase execution time and memory for large ZIP processing
            set_time_limit(18000);
            ini_set('max_execution_time', '18000');
            ini_set('memory_limit', '2048M');
            
            $this->updateBookingStatus('processing', 5, 'Job started');
            $this->workerLog('RUNNING', 5, 'Starting background ZIP processing');
            
            // Get booking and tour first for idempotency check
            $booking = Booking::findOrFail($this->bookingId);
            $this->updateBookingStatus('processing', 10, 'Loaded booking');
            $tour = $booking->tours()->first();
            
            if (!$tour) {
                throw new \Exception('No tour found for this booking.');
            }
            $this->updateBookingStatus('processing', 15, 'Loaded tour');
            
            // Get file size and hash immediately (file might be deleted during processing)
            $fileSize = 0;
            $fileHash = null;
            if (file_exists($this->zipFilePath)) {
                $fileSize = filesize($this->zipFilePath);
                $fileHash = md5_file($this->zipFilePath);
                
                // IDEMPOTENCY CHECK: Prevent duplicate processing of the SAME file
                // Only skip if the exact same file (by hash and size) was already processed successfully
                // This allows re-uploading the same filename (new version) to be processed
                if (isset($tour->final_json['files']) && is_array($tour->final_json['files'])) {
                    $existingFiles = $tour->final_json['files'];
                    foreach ($existingFiles as $file) {
                        // Check if same file (by hash and size) was already processed
                        if (isset($file['name']) && $file['name'] === $this->originalFilename 
                            && isset($file['processed']) && $file['processed'] === true
                            && isset($file['file_hash']) && $file['file_hash'] === $fileHash
                            && isset($file['size']) && $file['size'] === $fileSize) {
                            Log::info("ZIP file '{$this->originalFilename}' (hash: {$fileHash}) already processed for booking #{$this->bookingId}. Skipping duplicate processing.");
                            $this->updateBookingStatus('done', 100, 'Already processed (duplicate upload)');
                            $this->workerLog('DONE', 100, 'Already processed (duplicate upload)');
                            return; // Exit early - same file already processed successfully
                        }
                    }
                }
            } else {
                // File doesn't exist - check if it was already processed
                if (isset($tour->final_json['files']) && is_array($tour->final_json['files'])) {
                    $existingFiles = $tour->final_json['files'];
                    foreach ($existingFiles as $file) {
                        if (isset($file['name']) && $file['name'] === $this->originalFilename 
                            && isset($file['processed']) && $file['processed'] === true
                            && isset($file['file_hash']) && isset($file['size'])) {
                            // If we have hash info, only skip if it matches (same file)
                            // Otherwise, it's a new file with same name - process it
                            Log::info("ZIP file '{$this->originalFilename}' already processed for booking #{$this->bookingId}. File deleted but processing was successful. Skipping.");
                            $this->updateBookingStatus('done', 100, 'Already processed (file cleaned)');
                            $this->workerLog('DONE', 100, 'Already processed (file cleaned)');
                            return; // Exit early - already processed, file was cleaned up
                        }
                    }
                }
                throw new \Exception("ZIP file not found: {$this->zipFilePath}");
            }
            $this->updateBookingStatus('processing', 20, 'ZIP file validated');
            
            // Update tour slug and location if provided
            $tourUpdated = false;
            if ($this->slug && $tour->slug !== $this->slug) {
                $tour->slug = $this->slug;
                $tourUpdated = true;
            }
            if ($this->location && $tour->location !== $this->location) {
                $tour->location = $this->location;
                $tourUpdated = true;
            }
            
            if ($tourUpdated) {
                $tour->updated_by = auth()->id() ?? 1;
                $tour->save();
            }
            
            $tour->refresh();
            $this->updateBookingStatus('processing', 25, 'Tour details updated');
            
            // Get or assign QR code
            $qrCode = $booking->qr;
            if (!$qrCode) {
                $qrCode = QR::whereNull('booking_id')->first();
                if (!$qrCode) {
                    throw new \Exception('No available QR codes.');
                }
                $qrCode->booking_id = $booking->id;
                $qrCode->updated_by = auth()->id() ?? 1;
                $qrCode->save();
                $booking->tour_code = $qrCode->code;
                $booking->save();
            }
            $this->updateBookingStatus('processing', 30, 'QR code assigned');
            
            // Process the ZIP file using the controller's method
            Log::info("Processing ZIP file '{$this->originalFilename}' (size: {$fileSize} bytes, hash: {$fileHash}) for booking #{$this->bookingId}");
            $this->workerLog('RUNNING', 40, "Processing ZIP '{$this->originalFilename}'");
            $this->updateBookingStatus('processing', 40, 'Processing ZIP contents');
            $controller = app(\App\Http\Controllers\Admin\TourManagerController::class);
            $result = $controller->processZipFile(
                new \Illuminate\Http\UploadedFile(
                    $this->zipFilePath,
                    $this->originalFilename,
                    mime_content_type($this->zipFilePath),
                    null,
                    true
                ),
                $tour,
                $qrCode->code
            );
            
            if (!$result['success']) {
                throw new \Exception($result['message']);
            }
            $this->updateBookingStatus('processing', 80, 'ZIP processed, saving results');
            
            // Update tour data
            $tourData = $result['data'];
            
            $uploadedFiles = [[
                'name' => $this->originalFilename,
                'type' => 'zip',
                'processed' => true,
                'tour_path' => $result['tour_path'],
                'tour_url' => $result['tour_url'],
                's3_path' => $result['s3_path'],
                's3_url' => $result['s3_url'],
                'size' => $fileSize,
                'file_hash' => $fileHash,
                'uploaded_at' => now()->toDateTimeString(),
                'processed_at' => now()->toDateTimeString(),
                'processed_in_background' => true
            ]];
            
            $existingFiles = $tour->final_json['files'] ?? [];
            $existingTourData = is_array($tour->final_json) ? $tour->final_json : [];
            
            // Prevent duplicate file entries - check if exact same file already exists (by hash)
            $fileAlreadyExists = false;
            $existingFileIndex = null;
            foreach ($existingFiles as $index => $existingFile) {
                // Check if same file (by hash) or same name without hash (old records)
                if (isset($existingFile['name']) && $existingFile['name'] === $this->originalFilename) {
                    // If both have hash, compare by hash; otherwise treat as same if name matches
                    if (isset($existingFile['file_hash']) && isset($fileHash)) {
                        if ($existingFile['file_hash'] === $fileHash && 
                            isset($existingFile['size']) && $existingFile['size'] === $fileSize) {
                            $fileAlreadyExists = true;
                            $existingFileIndex = $index;
                            break;
                        }
                    } else {
                        // Old record without hash - update it
                        $fileAlreadyExists = true;
                        $existingFileIndex = $index;
                        break;
                    }
                }
            }
            
            // Only add file if it doesn't already exist (by hash)
            if (!$fileAlreadyExists) {
                $existingFiles = array_merge($existingFiles, $uploadedFiles);
            } else {
                // Update existing file entry
                if ($existingFileIndex !== null) {
                    $existingFiles[$existingFileIndex] = array_merge($existingFiles[$existingFileIndex], $uploadedFiles[0]);
                }
            }
            
            $tour->final_json = array_merge(
                $existingTourData,
                $tourData,
                [
                    'files' => $existingFiles,
                    'qr_code' => $qrCode->code,
                    'updated_at' => now()->toDateTimeString()
                ]
            );
            
            $booking->base_url = $result['s3_url'];
            $booking->save();
            
            $tour->updated_by = auth()->id() ?? 1;
            $tour->save();
            
            Log::info("Successfully processed ZIP file for booking #{$this->bookingId}");
            $this->updateBookingStatus('done', 100, 'Processing completed');
            $this->workerLog('DONE', 100, 'Successfully processed ZIP file');
            
            // Clean up the temporary ZIP file if it's in chunks directory
            if (strpos($this->zipFilePath, 'chunks') !== false && file_exists($this->zipFilePath)) {
                @unlink($this->zipFilePath);
            }
            
        } catch (\Exception $e) {
            Log::error("Background ZIP processing failed for booking #{$this->bookingId}: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
            $this->updateBookingStatus('failed', 0, 'Processing failed: ' . $e->getMessage());
            $this->workerLog('FAILED', 0, 'Processing failed: ' . $e->getMessage());
            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Background ZIP processing permanently failed for booking #{$this->bookingId}: " . $exception->getMessage() . " in " . $exception->getFile() . ":" . $exception->getLine());
        $this->updateBookingStatus('failed', 0, 'Processing permanently failed: ' . $exception->getMessage(), true);
        $this->workerLog('FAILED', 0, 'Processing permanently failed: ' . $exception->getMessage());
        
        // Optionally notify admin or update booking status
        try {
            $booking = Booking::find($this->bookingId);
            if ($booking) {
                // You can add a status field to track processing errors
            }
        } catch (\Exception $e) {
            Log::error("Failed to update booking status: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
        }
    }

    private function updateBookingStatus(string $status, int $progress, string $message, bool $finished = false): void
    {
        try {
            $booking = Booking::find($this->bookingId);
            if (!$booking) {
                return;
            }

            $booking->tour_zip_status = $status;
            $booking->tour_zip_progress = max(0, min(100, $progress));
            $booking->tour_zip_message = $message;

            if ($status === 'processing' && !$booking->tour_zip_started_at) {
                $booking->tour_zip_started_at = now();
            }

            if ($finished || in_array($status, ['done', 'failed'], true)) {
                $booking->tour_zip_finished_at = now();
            }

            $booking->save();
        } catch (\Exception $e) {
            // avoid breaking the job due to status update issues
            Log::warning("Failed to update booking #{$this->bookingId} tour_zip_status: " . $e->getMessage());
        }
    }

    private function workerLog(string $state, int $progress, string $message): void
    {
        try {
            $logger = Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/worker-tour.log'),
            ]);

            // Example line:
            // [BOOKING:6] [RUNNING] [40%] Processing ZIP 'file.zip'
            $logger->info(sprintf(
                '[BOOKING:%s] [%s] [%d%%] %s',
                $this->bookingId,
                strtoupper($state),
                max(0, min(100, $progress)),
                $message
            ));
        } catch (\Exception $e) {
            // ignore worker log failures
        }
    }
}
