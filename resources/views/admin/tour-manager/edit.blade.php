@extends('admin.layouts.vertical', ['title' => 'Upload Tour'])

@section('content')
<div class="">
    <div class="row">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                <div>
                    <nav aria-label="breadcrumb" class="mb-0">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
                            <li class="breadcrumb-item" aria-current="page"><a href="{{ route('admin.tour-manager.index') }}">Tour Management</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Upload</li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $booking->id }}</li>
                        </ol>
                    </nav>
                    <h3 class="mb-0">Tour Management</h3>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.tour-manager.index') }}" class="btn btn-secondary" data-bs-toggle="tooltip" title="Back to Tour Management">
                        <i class="ri-arrow-left-line me-1"></i> Back
                    </a>
                    @can('booking_edit')
                        <a href="{{ route('admin.bookings.edit', $booking->id) }}" class="btn btn-info" data-bs-toggle="tooltip" title="Edit Booking Info">
                            <i class="ri-edit-box-line me-1"></i> Edit Booking
                        </a>
                    @endcan
                    <a href="{{ route('admin.tour-manager.show', $booking) }}" class="btn btn-primary" data-bs-toggle="tooltip" title="View Tour Public Page">
                        <i class="ri-eye-line me-1"></i> View
                    </a>
                </div>
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
                                    <label class="form-label fw-bold">Tour Slug <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           name="slug" 
                                           id="tour_slug" 
                                           class="form-control" 
                                           value="{{ old('slug', $tour->slug ?? '') }}" 
                                           required
                                           placeholder="tour-slug-name">
                                    @error('slug')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">URL-friendly identifier for the tour</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label class="form-label fw-bold">Tour Location <span class="text-danger">*</span></label>
                                    <select name="location" id="tour_location" class="form-select" required>
                                        <option value="">Select Location</option>
                                        @php
                                            $ftpConfigs = \App\Models\FtpConfiguration::active()->ordered()->get();
                                        @endphp
                                        @foreach($ftpConfigs as $ftpConfig)
                                            <option value="{{ $ftpConfig->category_name }}" 
                                                @selected(old('location', $tour->location) == $ftpConfig->category_name)>
                                                {{ $ftpConfig->display_name }} ({{ $ftpConfig->main_url }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('location')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">FTP server location for index.php upload</small>
                                </div>
                            </div>
                        </div>

                        <!-- Upload Paths Information -->
                        @php
                            // Get S3 base URL
                            $s3BaseUrl = config('filesystems.disks.s3.url') ?: 
                                ('https://' . config('filesystems.disks.s3.bucket') . '.s3.' . 
                                 config('filesystems.disks.s3.region') . '.amazonaws.com');
                            $s3FullPath = rtrim($s3BaseUrl, '/') . '/tours/' . ($booking->tour_code ?? 'N/A') . '/';
                            $s3RelativePath = 'tours/' . ($booking->tour_code ?? 'N/A') . '/';
                            
                            // Get customer_id for FTP URL generation
                            $customerId = $booking->user_id ?? null;
                            
                            // Get FTP configuration if location is set
                            $ftpConfig = null;
                            $ftpUrl = 'N/A';
                            if ($tour->location && $customerId && $tour->slug) {
                                $ftpConfig = \App\Models\FtpConfiguration::where('category_name', $tour->location)->first();
                                if ($ftpConfig) {
                                    $ftpUrl = $ftpConfig->getUrlForTour($tour->slug, $customerId);
                                }
                            }
                        @endphp
                        <div class="alert alert-info mb-3" id="upload-paths-info">
                            <h6 class="alert-heading mb-2"><i class="ri-information-line me-1"></i> Upload Paths</h6>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong>Full S3 UPLOAD URL ( qr code based generated )</strong>
                                    <button type="button" class="btn btn-sm btn-outline-secondary copy-btn" data-copy-target="s3-full-path" title="Copy S3 URL">
                                        <i class="ri-file-copy-line me-1"></i> Copy
                                    </button>
                                </div>
                                <code class="d-block mt-1 small text-break p-2 bg-light rounded" id="s3-full-path">{{ $s3FullPath }}</code>
                            </div>
                           
                            <div class="mb-0">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong>FTP Full URL ( Tour Slug And Location Based Generated ) :</strong>
                                    <button type="button" class="btn btn-sm btn-outline-secondary copy-btn" data-copy-target="ftp-full-url-text-2" title="Copy FTP URL">
                                        <i class="ri-file-copy-line me-1"></i> Copy
                                    </button>
                                </div>
                                <code class="d-block mt-1 small text-break p-2 bg-light rounded" id="ftp-full-url-text-2">
                                    {{ $ftpUrl }}
                                </code>
                                <small class="text-muted d-block mt-1">The converted index.php file will be uploaded to this FTP URL.</small>
                            </div>
                        </div>

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
                            <div class="col-6 mb-2">
                                <label class="form-label fw-bold text-muted small">QR Link</label>
                                <p class="mb-0">
                                    <a href="https://qr.proppik.com/{{ $booking->qr->code }}" target="_blank" class="text-break">
                                        <code>https://qr.proppik.com/{{ $booking->qr->code }}</code>
                                    </a>
                                </p>
                            </div>
                            @if($booking->qr->qr_link)
                                <div class="col-6 mb-2">
                                    <label class="form-label fw-bold text-muted small">QR Live Link</label>
                                    <p class="mb-0">
                                        <a href="{{ $booking->getTourLiveUrl() }}" target="_blank" class="text-truncate d-block"
                                            style="max-width: 100%;">
                                            {{ Str::limit($booking->getTourLiveUrl(), 40) }}
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

<script>
// Inline script to ensure real-time path updates work
(function() {
    // Customer ID from server
    const customerId = {{ $booking->user_id ?? 'null' }};
    
    // FTP configurations data for URL generation
    @php
        $ftpConfigsData = [];
        foreach(\App\Models\FtpConfiguration::all() as $config) {
            $ftpConfigsData[$config->category_name] = [
                'category_name' => $config->category_name,
                'main_url' => $config->main_url,
                'remote_path_pattern' => $config->remote_path_pattern ?? '{customer_id}/{slug}/index.php',
                'url_pattern' => $config->url_pattern ?? 'https://{main_url}/{remote_path}',
            ];
        }
    @endphp
    const ftpConfigs = @json($ftpConfigsData);
    
    function updatePaths() {
        const slugInput = document.getElementById('tour_slug');
        const locationSelect = document.getElementById('tour_location');
        const ftpFullUrlText = document.getElementById('ftp-full-url-text-2');
        
        if (!slugInput || !locationSelect || !ftpFullUrlText) {
            return;
        }
        
        const slug = slugInput.value.trim();
        const location = locationSelect.value;
        
        // Update FTP Full URL using FTP configuration data
        if (location && slug && customerId && ftpConfigs[location]) {
            const config = ftpConfigs[location];
            const remotePathPattern = config.remote_path_pattern || '{customer_id}/{slug}/index.php';
            const urlPattern = config.url_pattern || 'https://{main_url}/{remote_path}';
            
            // Replace placeholders in remote path
            let remotePath = remotePathPattern
                .replace(/{customer_id}/g, customerId)
                .replace(/{slug}/g, slug);
            
            // Replace placeholders in URL pattern
            const ftpUrl = urlPattern
                .replace(/{main_url}/g, config.main_url)
                .replace(/{remote_path}/g, remotePath);
            
            ftpFullUrlText.textContent = ftpUrl;
        } else {
            ftpFullUrlText.textContent = 'N/A';
        }
    }
    
    function initPathUpdates() {
        const slugInput = document.getElementById('tour_slug');
        const locationSelect = document.getElementById('tour_location');
        
        if (slugInput && locationSelect) {
            // Use oninput for real-time updates as user types
            slugInput.addEventListener('input', updatePaths);
            slugInput.addEventListener('keyup', updatePaths);
            slugInput.addEventListener('change', updatePaths);
            slugInput.addEventListener('paste', function() {
                setTimeout(updatePaths, 10);
            });
            
            locationSelect.addEventListener('change', updatePaths);
            locationSelect.addEventListener('input', updatePaths);
            
            // Initial update
            updatePaths();
        } else {
            // Retry if elements not ready
            setTimeout(initPathUpdates, 100);
        }
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPathUpdates);
    } else {
        initPathUpdates();
    }
    
    // Also try after a delay to catch any late-loading elements
    setTimeout(initPathUpdates, 200);
})();

// Copy to clipboard functionality
(function() {
    // Store original HTML for each button
    const buttonOriginals = new Map();
    
    function initCopyButtons() {
        const copyButtons = document.querySelectorAll('.copy-btn');
        
        // Store original HTML for each button
        copyButtons.forEach(function(button) {
            if (!buttonOriginals.has(button)) {
                buttonOriginals.set(button, button.innerHTML);
            }
        });
        
        copyButtons.forEach(function(button) {
            // Remove any existing listeners by cloning
            const newButton = button.cloneNode(true);
            button.parentNode.replaceChild(newButton, button);
            
            // Get fresh reference
            const freshButton = document.querySelector('[data-copy-target="' + newButton.getAttribute('data-copy-target') + '"]');
            
            freshButton.addEventListener('click', function() {
                const targetId = this.getAttribute('data-copy-target');
                const targetElement = document.getElementById(targetId);
                
                if (!targetElement) {
                    return;
                }
                
                // Get text content (remove HTML tags if any)
                let textToCopy = targetElement.textContent || targetElement.innerText;
                textToCopy = textToCopy.trim();
                
                // Get original HTML for this button
                const originalHTML = buttonOriginals.get(freshButton) || '<i class="ri-file-copy-line me-1"></i> Copy';
                
                // Clear any existing timeout
                if (freshButton._copyTimeout) {
                    clearTimeout(freshButton._copyTimeout);
                }
                
                // Try modern clipboard API first
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(textToCopy).then(function() {
                        // Show success feedback
                        freshButton.innerHTML = '<i class="ri-check-line me-1"></i> Copied!';
                        freshButton.classList.remove('btn-outline-secondary');
                        freshButton.classList.add('btn-success');
                        
                        // Reset after 2 seconds
                        freshButton._copyTimeout = setTimeout(function() {
                            freshButton.innerHTML = originalHTML;
                            freshButton.classList.remove('btn-success');
                            freshButton.classList.add('btn-outline-secondary');
                            freshButton._copyTimeout = null;
                        }, 2000);
                    }).catch(function(err) {
                        console.error('Failed to copy:', err);
                        // Fallback to old method
                        fallbackCopy(textToCopy, freshButton, originalHTML);
                    });
                } else {
                    // Fallback for older browsers
                    fallbackCopy(textToCopy, freshButton, originalHTML);
                }
            });
        });
    }
    
    function fallbackCopy(text, button, originalHTML) {
        // Create temporary textarea
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        textarea.setSelectionRange(0, 99999); // For mobile devices
        
        try {
            const successful = document.execCommand('copy');
            if (successful) {
                // Clear any existing timeout
                if (button._copyTimeout) {
                    clearTimeout(button._copyTimeout);
                }
                
                button.innerHTML = '<i class="ri-check-line me-1"></i> Copied!';
                button.classList.remove('btn-outline-secondary');
                button.classList.add('btn-success');
                
                // Reset after 2 seconds
                button._copyTimeout = setTimeout(function() {
                    button.innerHTML = originalHTML;
                    button.classList.remove('btn-success');
                    button.classList.add('btn-outline-secondary');
                    button._copyTimeout = null;
                }, 1000);
            }
        } catch (err) {
            console.error('Fallback copy failed:', err);
            alert('Failed to copy. Please copy manually: ' + text);
        }
        
        document.body.removeChild(textarea);
    }
    
    // Initialize copy buttons when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCopyButtons);
    } else {
        initCopyButtons();
    }
    
    // Also try after a delay
    setTimeout(initCopyButtons, 200);
})();
</script>
@endsection
