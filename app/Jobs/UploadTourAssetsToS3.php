<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Booking;

class UploadTourAssetsToS3 implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // 1 hour timeout for large uploads
    public $tries = 3; // Retry 3 times on failure

    protected $localPath;
    protected $s3Path;
    protected $bookingId;
    protected $folderName;

    /**
     * Create a new job instance.
     */
    public function __construct($localPath, $s3Path, $bookingId, $folderName)
    {
        $this->localPath = $localPath;
        $this->s3Path = $s3Path;
        $this->bookingId = $bookingId;
        $this->folderName = $folderName;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info("Starting background S3 upload for folder: {$this->folderName} from {$this->localPath} to {$this->s3Path}");

            if (!is_dir($this->localPath)) {
                Log::error("Directory not found for S3 upload: {$this->localPath}");
                return;
            }

            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($this->localPath, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );

            $filesToUpload = [];
            
            // Collect all files
            foreach ($files as $file) {
                if (!$file->isFile()) {
                    continue;
                }

                $filePath = $file->getPathname();
                $relativePath = str_replace($this->localPath . DIRECTORY_SEPARATOR, '', $filePath);
                $relativePath = str_replace('\\', '/', $relativePath);

                // Skip hidden files and system files
                $fileName = basename($relativePath);
                if (strpos($fileName, '.') === 0 || 
                    strpos($relativePath, '__MACOSX') !== false || 
                    strpos($relativePath, '.DS_Store') !== false) {
                    continue;
                }

                $s3FilePath = ltrim($this->s3Path . '/' . $relativePath, '/');
                $filesToUpload[] = [
                    'local' => $filePath,
                    's3' => $s3FilePath,
                    'size' => filesize($filePath)
                ];
            }

            if (empty($filesToUpload)) {
                Log::warning("No files found to upload in directory: {$this->localPath}");
                return;
            }

            Log::info("Found " . count($filesToUpload) . " files to upload for folder: {$this->folderName}");
            
            // Upload files in larger batches for better performance
            $batchSize = 20; // Increased from 5 to 20
            $totalSize = 0;
            $uploadedCount = 0;
            $failedCount = 0;
            
            $s3Disk = Storage::disk('s3');
            
            foreach (array_chunk($filesToUpload, $batchSize) as $batchIndex => $batch) {
                foreach ($batch as $fileData) {
                    try {
                        if (!file_exists($fileData['local'])) {
                            throw new \Exception("Local file not found");
                        }
                        
                        $fileContent = file_get_contents($fileData['local']);
                        if ($fileContent === false) {
                            throw new \Exception("Failed to read file content");
                        }
                        
                        $mimeType = mime_content_type($fileData['local']) ?: 'application/octet-stream';
                        
                        // Upload to S3
                        $uploaded = $s3Disk->put(
                            $fileData['s3'],
                            $fileContent,
                            ['ContentType' => $mimeType]
                        );
                        
                        if (!$uploaded) {
                            throw new \Exception("S3 upload returned false");
                        }
                        
                        // Set visibility
                        try {
                            $s3Disk->setVisibility($fileData['s3'], 'public');
                        } catch (\Exception $e) {
                            // Visibility setting failure is not critical
                        }
                        
                        $totalSize += $fileData['size'];
                        $uploadedCount++;
                        
                    } catch (\Exception $e) {
                        $failedCount++;
                        Log::error("Failed to upload {$fileData['s3']}: " . $e->getMessage());
                        
                        if ($failedCount > 50) {
                            Log::error("Too many upload failures ({$failedCount}). Stopping upload process.");
                            break 2;
                        }
                    }
                }
                
                // Log progress every 5 batches
                if (($batchIndex + 1) % 5 === 0) {
                    $progress = round(($uploadedCount / count($filesToUpload)) * 100, 2);
                    Log::info("Upload progress for {$this->folderName}: {$uploadedCount}/" . count($filesToUpload) . " files ({$progress}%)");
                }
            }
            
            $sizeMB = round($totalSize / 1024 / 1024, 2);
            
            if ($failedCount > 0) {
                Log::warning("Upload completed with errors for {$this->folderName}: {$uploadedCount}/" . count($filesToUpload) . " files uploaded ({$sizeMB} MB), {$failedCount} failed");
            } else {
                Log::info("Successfully uploaded all {$uploadedCount} files ({$sizeMB} MB) for folder: {$this->folderName}");
            }

            // Update booking status if all folders are done (optional)
            // You can add a status field to track upload progress
            
        } catch (\Exception $e) {
            Log::error("Background S3 upload job failed for {$this->folderName}: " . $e->getMessage());
            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Background S3 upload job permanently failed for folder: {$this->folderName}. Error: " . $exception->getMessage());
        
        // Optionally notify admin or update booking status
    }
}


