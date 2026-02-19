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

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info("Starting S3 upload for folder: {$this->folderName}");

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
                        
                        // Get proper MIME type based on file extension
                        $mimeType = $this->getMimeType($fileData['local']);
                        
                        // Upload to S3
                        $uploaded = $s3Disk->put(
                            $fileData['s3'],
                            $fileContent,
                            ['ContentType' => $mimeType]
                        );
                        
                        if (!$uploaded) {
                            throw new \Exception("S3 upload returned false");
                        }
                        
                        // Set visibility (failure is not critical - file is still uploaded)
                        try {
                            $s3Disk->setVisibility($fileData['s3'], 'public');
                        } catch (\Exception $e) {
                            // Silently continue - visibility setting failure is not critical
                        }
                        
                        $totalSize += $fileData['size'];
                        $uploadedCount++;
                        
                    } catch (\Exception $e) {
                        $failedCount++;
                        Log::error("Failed to upload {$fileData['s3']}: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
                        
                        if ($failedCount > 50) {
                            Log::error("Too many upload failures ({$failedCount}). Stopping upload process in " . __FILE__ . ":" . __LINE__);
                            break 2;
                        }
                    }
                }
                
                // Progress logging removed to reduce log verbosity
            }
            
            $sizeMB = round($totalSize / 1024 / 1024, 2);
            
            if ($failedCount > 0) {
                Log::error("Upload completed with errors for {$this->folderName}: {$uploadedCount}/" . count($filesToUpload) . " files uploaded ({$sizeMB} MB), {$failedCount} failed in " . __FILE__ . ":" . __LINE__);
            }

            // Update booking status if all folders are done (optional)
            // You can add a status field to track upload progress
            
        } catch (\Exception $e) {
            Log::error("Background S3 upload job failed for {$this->folderName}: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Background S3 upload job permanently failed for folder: {$this->folderName}. Error: " . $exception->getMessage() . " in " . $exception->getFile() . ":" . $exception->getLine());
        
        // Optionally notify admin or update booking status
    }
}


