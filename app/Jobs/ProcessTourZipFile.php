<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Models\QR;
use App\Models\Tour;
use App\Services\TourAssetJsonPersistenceService;
use App\Services\TourService;
use App\Services\TourZipProgressService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Background processing of uploaded tour ZIP archives.
 *
 * Queue workers cache loaded PHP; after changing DB columns or related services, run `php artisan queue:restart`
 * (or stop and start `queue:work`) so jobs pick up the new code.
 */
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

    protected $tourService;

    protected ?TourZipProgressService $zipProgress = null;

    /**
     * Create a new job instance.
     */
    public function __construct($bookingId, $zipFilePath, $originalFilename, $slug, $location)
    {
        $this->tourService = app(TourService::class);
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
        $this->zipProgress = app(TourZipProgressService::class);

        try {
            set_time_limit(18000);
            ini_set('max_execution_time', '18000');
            ini_set('memory_limit', '9048M');

            $this->zipProgress->report($this->bookingId, 3.5, 'job_start', 'Job started', [], true);
            $this->workerLog('RUNNING', 4, 'Starting background ZIP processing');

            $booking = Booking::findOrFail($this->bookingId);
            $this->zipProgress->report($this->bookingId, 8.0, 'validate', 'Loaded booking', [], true);
            $tour = $booking->tours()->first();

            if (! $tour) {
                throw new \Exception('No tour found for this booking.');
            }
            $this->zipProgress->report($this->bookingId, 12.5, 'validate', 'Loaded tour', [], true);

            if (! file_exists($this->zipFilePath)) {
                throw new \Exception("ZIP file not found: {$this->zipFilePath}");
            }
            $fileSize = filesize($this->zipFilePath);
            $fileHash = md5_file($this->zipFilePath);
            $this->zipProgress->report($this->bookingId, 16.0, 'validate', 'ZIP file validated', [], true);

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
            $this->zipProgress->report($this->bookingId, 20.0, 'validate', 'Tour details updated', [], true);

            $qrCode = $booking->qr;
            if (! $qrCode) {
                $qrCode = QR::whereNull('booking_id')->first();
                if (! $qrCode) {
                    throw new \Exception('No available QR codes.');
                }
                $qrCode->booking_id = $booking->id;
                $qrCode->updated_by = auth()->id() ?? 1;
                $qrCode->save();
                $booking->tour_code = $qrCode->code;
                $booking->save();
            }
            $this->zipProgress->report($this->bookingId, 24.0, 'validate', 'QR code assigned', [], true);

            Log::info("Processing ZIP file '{$this->originalFilename}' (size: {$fileSize} bytes, hash: {$fileHash}) for booking #{$this->bookingId}");
            $this->workerLog('RUNNING', 26, "Processing ZIP '{$this->originalFilename}'");

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
                $qrCode->code,
                $this->zipProgress
            );

            if (! $result['success']) {
                throw new \Exception($result['message']);
            }

            $this->zipProgress->report($this->bookingId, 88.5, 'db_sync', 'Merging tour JSON and syncing database fields', [], true);

            $zipPayloadForHistory = TourAssetJsonPersistenceService::snapshotZipPayloadForHistory($result);

            $tourData = $result['data'];

            $uploadedFiles = [
                [
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
                    'processed_in_background' => true,
                ],
            ];

            $existingFiles = $tour->final_json['files'] ?? [];

            $fileAlreadyExists = false;
            $existingFileIndex = null;
            foreach ($existingFiles as $index => $existingFile) {
                if (isset($existingFile['name']) && $existingFile['name'] === $this->originalFilename) {
                    if (isset($existingFile['file_hash']) && isset($fileHash)) {
                        if (
                            $existingFile['file_hash'] === $fileHash &&
                            isset($existingFile['size']) && $existingFile['size'] === $fileSize
                        ) {
                            $fileAlreadyExists = true;
                            $existingFileIndex = $index;
                            break;
                        }
                    } else {
                        $fileAlreadyExists = true;
                        $existingFileIndex = $index;
                        break;
                    }
                }
            }

            if (! $fileAlreadyExists) {
                $existingFiles = array_merge($existingFiles, $uploadedFiles);
            } else {
                if ($existingFileIndex !== null) {
                    $existingFiles[$existingFileIndex] = array_merge($existingFiles[$existingFileIndex], $uploadedFiles[0]);
                }
            }

            $tour->final_json = array_merge(
                $tourData,
                [
                    'files' => $existingFiles,
                    'qr_code' => $qrCode->code,
                    'updated_at' => now()->toDateTimeString(),
                ]
            );

            $this->tourService->syncTourFieldsFromJson($tour, $tour->tour_data_json, [], true);
            $this->zipProgress->report($this->bookingId, 94.0, 'db_sync', 'Recording JSON history snapshot', [], true);

            app(TourAssetJsonPersistenceService::class)->recordFromZipResult(
                $tour,
                $zipPayloadForHistory,
                auth()->id() ?? 1
            );

            $booking->base_url = $result['s3_url'];
            $booking->save();

            Log::info("Successfully processed ZIP file for booking #{$this->bookingId}");
            $this->zipProgress->markDone($this->bookingId);
            $this->workerLog('DONE', 100, 'Successfully processed ZIP file');

            if (strpos($this->zipFilePath, 'chunks') !== false && file_exists($this->zipFilePath)) {
                @unlink($this->zipFilePath);
            }
        } catch (\Exception $e) {
            Log::error("Background ZIP processing failed for booking #{$this->bookingId}: ".$e->getMessage().' in '.$e->getFile().':'.$e->getLine());
            ($this->zipProgress ?? app(TourZipProgressService::class))->markFailed($this->bookingId, 'Processing failed: '.$e->getMessage());
            $this->workerLog('FAILED', 0, 'Processing failed: '.$e->getMessage());
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Background ZIP processing permanently failed for booking #{$this->bookingId}: ".$exception->getMessage().' in '.$exception->getFile().':'.$exception->getLine());
        app(TourZipProgressService::class)->markFailed($this->bookingId, 'Processing permanently failed: '.$exception->getMessage());
        $this->workerLog('FAILED', 0, 'Processing permanently failed: '.$exception->getMessage());

        try {
            Booking::find($this->bookingId);
        } catch (\Exception $e) {
            Log::error('Failed to update booking status: '.$e->getMessage().' in '.$e->getFile().':'.$e->getLine());
        }
    }

    private function workerLog(string $state, int $progress, string $message): void
    {
        try {
            $logger = Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/worker-tour.log'),
            ]);

            $logger->info(sprintf(
                '[BOOKING:%s] [%s] [%d%%] %s',
                $this->bookingId,
                strtoupper($state),
                max(0, min(100, $progress)),
                $message
            ));
        } catch (\Exception $e) {
        }
    }
}
