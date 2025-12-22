@extends('admin.layouts.vertical', ['title' => 'Edit Tour'])

@section('content')
<div class="">
    <div class="row">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                <div>
                    <nav aria-label="breadcrumb" class="mb-0">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('root') }}">Home</a></li>
                            <li class="breadcrumb-item" aria-current="page"><a href="{{ route('admin.tour-manager.index') }}">Tour Management</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Edit</li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $booking->id }}</li>
                        </ol>
                    </nav>
                    <h3 class="mb-0">Tour Management</h3>
                </div>
                <a href="{{ route('admin.tour-manager.show', $booking) }}" class="btn btn-secondary">
                        <i class="ri-arrow-left-line me-1"></i> Back to Booking
                </a>
            </div>
        </div>
    </div>
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <form action="{{ route('admin.tour-manager.update', $booking) }}" method="POST" enctype="multipart/form-data" id="tour-edit-form">
                @csrf
                @method('PUT')
                
                <!-- File Upload Card -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Upload Tour Files</h4>
                    </div>
                    <div class="card-body">
                        <!-- Tour Information Display -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label class="form-label fw-bold text-muted small">Tour Slug</label>
                                    <p class="mb-0 fw-semibold">{{ $tour->slug ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label class="form-label fw-bold text-muted small">Tour Location</label>
                                    <p class="mb-0 fw-semibold">
                                        @if($tour->location === 'creart_qr')
                                            creart_qr (http://creart.in/qr/)
                                        @elseif($tour->location)
                                            {{ $tour->location }} (https://{{ $tour->location }}.proppik.com)
                                        @else
                                            N/A
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Upload Paths Information -->
                        @if($tour->slug && $tour->location)
                        @php
                            // Get S3 base URL
                            $s3BaseUrl = config('filesystems.disks.s3.url') ?: 
                                ('https://' . config('filesystems.disks.s3.bucket') . '.s3.' . 
                                 config('filesystems.disks.s3.region') . '.amazonaws.com');
                            $s3FullPath = rtrim($s3BaseUrl, '/') . '/tours/' . ($booking->tour_code ?? 'N/A') . '/';
                            $s3RelativePath = 'tours/' . ($booking->tour_code ?? 'N/A') . '/';
                        @endphp
                        <div class="alert alert-info mb-3">
                            <h6 class="alert-heading mb-2"><i class="ri-information-line me-1"></i> Upload Paths</h6>
                            <div class="mb-2">
                                <strong>S3 Upload Path:</strong>
                                <code class="d-block mt-1 small">{{ $s3RelativePath }}</code>
                                <small class="text-muted">All tour assets (images, assets, gallery, tiles, etc.) will be uploaded to this S3 path.</small>
                                <div class="mt-1">
                                    <strong>Full S3 URL:</strong>
                                    <code class="d-block mt-1 small text-break">{{ $s3FullPath }}</code>
                                </div>
                            </div>
                            <div class="mb-0">
                                <strong>FTP Upload Path:</strong>
                                @if($tour->location === 'creart_qr')
                                    <code class="d-block mt-1 small">qr/{{ $tour->slug }}/index.php</code>
                                    <small class="text-muted">The converted index.php file will be uploaded to: <strong>http://creart.in/qr/{{ $tour->slug }}/index.php</strong></small>
                                @else
                                    <code class="d-block mt-1 small">{{ $tour->slug }}/index.php</code>
                                    <small class="text-muted">The converted index.php file will be uploaded to: <strong>https://{{ $tour->location }}.proppik.com/{{ $tour->slug }}/index.php</strong></small>
                                @endif
                            </div>
                        </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label">Upload ZIP File <span class="text-danger">*</span></label>
                            <div class="dropzone" id="tour-dropzone">
                                <div class="dz-message needsclick">
                                    <i class="ri-upload-cloud-2-line fs-1 text-muted"></i>
                                    <h4>Drop tour ZIP file here or click to select</h4>
                                    <span class="text-muted">Upload a single ZIP file containing tour assets (images, assets, gallery, tiles, index.html, data.json)</span>
                                    <span class="text-muted d-block mt-1"><small>Max 500MB | Single file only | Required: index.html + JSON file + folders (images, assets, gallery, tiles)</small></span>
                                </div>
                            </div>
                            <div id="file-count-display" class="mt-2 text-muted" style="display: none;">
                                0 file(s) selected
                            </div>
                            @error('files')
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Existing Files -->
                        @if($tour->final_json && isset($tour->final_json['files']))
                        <div class="mb-3">
                            <label class="form-label">Existing Files</label>
                            <div class="list-group">
                                @foreach($tour->final_json['files'] as $file)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="ri-file-line me-2"></i>
                                        <span>{{ $file['name'] ?? 'File' }}</span>
                                    </div>
                                    <span class="badge bg-info">{{ $file['size'] ?? '' }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <div class="d-grid gap-2 mt-3">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="ri-upload-cloud-2-line me-1"></i> Upload Tour Files
                            </button>
                            <a href="{{ route('admin.tour-manager.show', $booking) }}" class="btn btn-secondary">
                                <i class="ri-close-line me-1"></i> Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">

            <!-- QR Code Information -->
            @if($booking && $booking->qr)
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">QR Code</h4>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-3">
                            @if($booking->qr->image)
                                <img src="{{ asset('storage/' . $booking->qr->image) }}" alt="QR Code" class="img-fluid"
                                    style="max-width: 250px;">
                            @elseif($booking->qr->qr_link)
                                <div class="qr-code-container">
                                    {!! $booking->qr->qr_code_image !!}
                                </div>
                            @elseif($booking->qr->code)
                                @php
                                    // Generate QR code from code if qr_link doesn't exist
                                    $qrUrl = 'https://qr.proppik.com/' . $booking->qr->code;
                                    $qrCodeSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(300)
                                        ->format('svg')
                                        ->generate($qrUrl);
                                @endphp
                                <div class="qr-code-container">
                                    {!! $qrCodeSvg !!}
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="ri-qr-code-line fs-3"></i>
                                    <p class="mb-0 mt-2">QR Code not generated yet</p>
                                </div>
                            @endif
                        </div>
                        <div class="row text-start">
                            <div class="col-6 mb-2">
                                <label class="form-label fw-bold text-muted small">QR Name</label>
                                <p class="fw-semibold mb-0">{{ $booking->qr->name ?? 'N/A' }}</p>
                            </div>
                            <div class="col-6 mb-2">
                                <label class="form-label fw-bold text-muted small">QR Code</label>
                                <p class="mb-0 font-monospace">{{ $booking->qr->code }}</p>
                            </div>
                            @if($booking->qr->qr_link)
                                <div class="col-6 mb-2">
                                    <label class="form-label fw-bold text-muted small">QR Link</label>
                                    <p class="mb-0">
                                        <a href="{{ $booking->qr->qr_link }}" target="_blank" class="text-truncate d-block"
                                            style="max-width: 100%;">
                                            {{ Str::limit($booking->qr->qr_link, 40) }}
                                            <i class="ri-external-link-line ms-1"></i>
                                        </a>
                                    </p>
                                </div>
                            @endif
                            <div class="col-6 mb-2">
                                <label class="form-label fw-bold text-muted small">Created</label>
                                <p class="mb-0">{{ $booking->qr->created_at->format('d M Y, h:i A') }}</p>
                            </div>
                        </div>
                        @if($booking->qr->image)
                            <div class="col-6 mt-3">
                                <a href="{{ asset('storage/' . $booking->qr->image) }}" download class="btn btn-primary btn-sm">
                                    <i class="ri-download-line me-1"></i> Download QR
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
                @else
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">QR Code</h4>
                    </div>
                    <div class="card-body text-center">
                        <div class="alert alert-info">
                            <i class="ri-qr-code-line fs-3"></i>
                            <p class="mb-0 mt-2">QR Code not generated yet</p>

                        </div>
                        @php
                             // Try to find and assign an available QR code
                             $availableQr = \App\Models\QR::whereNull('booking_id')->first();
                         @endphp
                         @if($availableQr)
                             <div class="row text-start">
                                 <div class="col-6 mb-2">
                                     <label class="form-label fw-bold text-muted small">Auto Assigned QR Code</label>
                                     <h3><small class="badge bg-primary font-monospace">{{ $availableQr->code }}</small></h3>
                                     
                                 </div>
                                 <div class="col-12 mb-2">
                                     <div class="alert alert-warning mb-0">
                                         <i class="ri-qr-code-line me-1"></i>
                                         <small><strong><big> #{{ $availableQr->code }} </big></strong>  QR code will be automatically assigned to this booking when you upload and save the tour here.</small>
                                     </div>
                                 </div>
                             </div>
                         @else
                             <div class="alert alert-info">
                                 <i class="ri-qr-code-line fs-3"></i>
                                 <p class="mb-0 mt-2">No available QR codes. Please generate a new QR code first.</p>
                             </div>
                         @endif
                    </div>
                </div>
            @endif<!-- QR Code -->

                        

            <!-- Booking Info -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Booking Information</h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">Booking ID</label>
                        <p class="mb-0 fw-semibold">#{{ $booking->id }}</p>
                    </div>
                    @if($booking)
                        <div class="mb-3">
                            <label class="text-muted small">Customer</label>
                            <p class="mb-0 fw-semibold">{{ $booking->user?->firstname }} {{ $booking->user?->lastname }}</p>
                            <small class="text-muted">{{ $booking->user?->email }}</small>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Property Type</label>
                            <p class="mb-0">{{ $booking->propertyType?->name ?? 'N/A' }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Location</label>
                            <p class="mb-0">{{ $booking->city?->name ?? 'N/A' }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Booking Date</label>
                            <p class="mb-0">{{ $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') : 'N/A' }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Price</label>
                            <p class="mb-0 fw-semibold">₹{{ number_format($booking->price, 2) }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Tour Info -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Tour Information</h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">Tour ID</label>
                        <p class="mb-0 fw-semibold">#{{ $tour->id }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Current Status</label>
                        <p class="mb-0">
                            @php
                                $statusBadge = [
                                    'draft' => 'secondary',
                                    'published' => 'success',
                                    'archived' => 'warning'
                                ];
                                $color = $statusBadge[$tour->status] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $color }}">{{ ucfirst($tour->status) }}</span>
                        </p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Created At</label>
                        <p class="mb-0">{{ $tour->created_at->format('d M Y, h:i A') }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Last Updated</label>
                        <p class="mb-0">{{ $tour->updated_at->format('d M Y, h:i A') }}</p>
                    </div>
                    @if($tour->revision)
                    <div class="mb-3">
                        <label class="text-muted small">Revision</label>
                        <p class="mb-0">#{{ $tour->revision }}</p>
                    </div>
                    @endif
                </div>
            </div>

            
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="tour-loading-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); z-index: 9999; justify-content: center; align-items: center;">
    <div style="text-align: center; color: white; max-width: 500px; width: 90%;">
        <div class="spinner-border text-secondary me-3" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <h4 class="mt-3">Uploading and Processing Tour...</h4>
        <p class="text-muted">This may take a few moments. Please don't close this window.</p>
        
        <!-- Folder Processing Display -->
        <div id="folder-processing-container" class="mt-4">
            <div class="card bg-dark border-secondary" style="width: 100%;">
                <div class="card-body">
                    <h6 class="card-title text-white mb-3">Processing Folders</h6>
                    <div id="current-folder-name" class="text-center mb-3 fw-bold text-success" style="font-size: 1.2rem; min-height: 32px;">
                        <!-- Folder name will appear here -->
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div id="folder-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%"></div>
                    </div>
                    <div id="folder-status" class="mt-2 small text-muted text-center">
                        <!-- Status text will appear here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
@vite(['resources/js/pages/tour-manager-edit.js'])
@endsection
