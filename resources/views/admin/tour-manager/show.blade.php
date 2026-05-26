@extends('admin.layouts.vertical', ['title' => 'Booking Details'])

@section('content')
    @if(request()->get('completed') == '1' && ($booking->tour_zip_status ?? 'pending') === 'done')
        <div id="tour-completion-toast" class="position-fixed top-0 end-0 p-3" style="z-index: 1080;">
            <div class="toast align-items-center text-bg-success border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="ri-check-line me-2"></i> Tour processing completed and live link is ready.
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>
        <script>
            (function() {
                const toast = document.getElementById('tour-completion-toast');
                if (toast) {
                    const btn = toast.querySelector('.btn-close');
                    if (btn) {
                        btn.addEventListener('click', () => {
                            toast.remove();
                            // Remove URL parameter after closing
                            const url = new URL(window.location.href);
                            url.searchParams.delete('completed');
                            window.history.replaceState({}, '', url.toString());
                        });
                    }
                    setTimeout(() => {
                        if (toast && toast.parentNode) {
                            toast.remove();
                            // Remove URL parameter after auto-hide
                            const url = new URL(window.location.href);
                            url.searchParams.delete('completed');
                            window.history.replaceState({}, '', url.toString());
                        }
                    }, 5000);
                }
            })();
        </script>
    @endif
    <div class="">
        <div class="row">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                    <div>
                        <nav aria-label="breadcrumb" class="mb-0">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
                                <li class="breadcrumb-item" aria-current="page"><a
                                        href="{{ route('admin.tour-manager.index') }}">Tour Management</a></li>
                                <li class="breadcrumb-item active" aria-current="page">{{ $booking->id }}</li>
                            </ol>
                        </nav>
                        <h3 class="mb-0">
                            Tour Management
                            (<span class="dblclick-copy" role="button" tabindex="0" title="Double click to copy"
                                data-copy-text="{{ $booking->tour_code }}">{{ $booking->tour_code }}</span>)
                        </h3>
                    </div>
                    <div>
                        <a href="{{ route('admin.tour-manager.index') }}" class="btn btn-soft-secondary" data-bs-toggle="tooltip" title="Back to Tour Management">
                            <iconify-icon icon="solar:arrow-left-broken" class="align-middle me-1"></iconify-icon> Back
                        </a>
                        @can('booking_edit')
                            <a href="{{ route('admin.bookings.edit', $booking->id) }}" class="btn btn-primary" data-bs-toggle="tooltip" title="Edit Booking Info">
                                <iconify-icon icon="solar:pen-new-square-broken" class="align-middle me-1"></iconify-icon> Edit Booking
                            </a>
                        @endcan
                        <a href="{{ route('admin.tour-manager.upload', $booking) }}" class="btn btn-primary" data-bs-toggle="tooltip" title="Upload & Manage Tour Assets">
                            <iconify-icon icon="solar:upload-minimalistic-broken" class="align-middle me-1"></iconify-icon> Upload Tour
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <!-- Booking Information -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Booking Information</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <!-- Upload Paths Information -->
                                @if($tour)
                                @php
                                        // Get S3 base URL
                                        $s3BaseUrl = config('filesystems.disks.s3.url') ?: 
                                            ('https://' . config('filesystems.disks.s3.bucket') . '.s3.' . 
                                            config('filesystems.disks.s3.region') . '.amazonaws.com');
                                        $s3FullPath = rtrim($s3BaseUrl, '/') . '/tours/' . ($booking->tour_code ?? 'N/A') . '/';
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
                                                <button type="button" class="btn btn-sm btn-outline-secondary copy-btn" data-copy-target="ftp-full-url-text" title="Copy FTP URL">
                                                    <i class="ri-file-copy-line me-1"></i> Copy
                                                </button>
                                            </div>
                                            <code class="d-block mt-1 small text-break p-2 bg-light rounded" id="ftp-full-url-text">
                                                {{ $tour->getTourLiveUrl() !== '#' ? $tour->getTourLiveUrl() : 'N/A' }}
                                            </code>
                                            <small class="text-muted d-block mt-1">The converted index.php file will be uploaded to this FTP URL.</small>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Status</label>
                                    <p>
                                        @php
                                            $badges = [
                                                'pending' => 'secondary',
                                                'confirmed' => 'primary',
                                                'scheduled' => 'info',
                                                'completed' => 'success',
                                                'cancelled' => 'danger'
                                            ];
                                            $color = $badges[$booking->status] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $color }}">{{ ucfirst($booking->status) }}</span>
                                    </p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-muted">Property Type</label>
                                    <p class="fw-semibold">{{ $booking->propertyType?->name ?? 'N/A' }}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-muted">Property Sub Type</label>
                                    <p>{{ $booking->propertySubType?->name ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">BHK</label>
                                    <p>{{ $booking->bhk?->name ?? 'N/A' }}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-muted">Area</label>
                                    <p>{{ $booking->area ? $booking->area . ' sq. ft.' : 'N/A' }}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-muted">Price</label>
                                    <p class="fw-semibold">₹{{ number_format($booking->price, 2) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted">Full Address</label>
                            <p>{{ $booking->full_address ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Customer Information -->
                @if($booking->customer)
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Customer Information</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Name</label>
                                        <p class="fw-semibold">{{ $booking->customer->firstname }} {{ $booking->customer->lastname }}
                                        </p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Email</label>
                                        <p>{{ $booking->customer->email }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Mobile</label>
                                        <p>{{ $booking->customer->mobile }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Tours -->
                @if($booking->tours->count() > 0)
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Tours</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Tour Date</th>
                                            <th>Status</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($booking->tours as $tour)
                                            <tr>
                                                <td>{{ $tour->tour_date ? \Carbon\Carbon::parse($tour->tour_date)->format('d M Y, h:i A') : 'N/A' }}
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">{{ ucfirst($tour->status ?? 'scheduled') }}</span>
                                                </td>
                                                <td>{{ $tour->notes ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

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
                                        $qrUrl = getQrLinkBase() . $booking->qr->code;
                                        $qrCodeSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(250)
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
                                        <div class="d-flex align-items-center gap-2">
                                            <a href="{{ getQrLinkBase() }}{{ $booking->qr->code }}" target="_blank" rel="noopener"
                                                class="text-truncate d-block flex-grow-1" title="{{ getQrLinkBase() }}{{ $booking->qr->code }}">
                                                <code>{{ getQrLinkBase() }}{{ $booking->qr->code }}</code>
                                            </a>
                                            <button type="button"
                                                class="btn btn-link btn-sm p-0 copy-link-btn"
                                                data-copy-text="{{ getQrLinkBase() }}{{ $booking->qr->code }}"
                                                title="Copy QR link" aria-label="Copy QR link">
                                                <i class="ri-file-copy-line"></i>
                                            </button>
                                        </div>
                                    </p>
                                </div>
                                @php
                                    $tourZipStatus = $booking->tour_zip_status ?? 'pending';
                                    $tourZipProgress = round((float) ($booking->tour_zip_progress ?? 0), 2);
                                    $tourZipMessage = $booking->tour_zip_message;
                                    $tourZipPhaseLabel = match ($booking->tour_zip_phase ?? '') {
                                        'queued' => 'Queued',
                                        'job_start' => 'Starting job',
                                        'validate' => 'Validating',
                                        'zip_to_s3' => 'Storing archive',
                                        's3_upload' => 'Uploading files',
                                        'index_local' => 'Building index',
                                        'ftp_upload' => 'Publishing',
                                        'db_sync' => 'Saving data',
                                        'finalize' => 'Finalizing',
                                        default => '',
                                    };
                                    $tourLiveUrl = $booking->getTourLiveUrl();
                                    $hasLiveLink = !empty($booking->qr?->qr_link) && $tourLiveUrl !== '#';
                                    $tourZipPctWidth = max(0.05, min(100, $tourZipProgress));
                                @endphp
                                <div class="col-6 mb-2" id="tour-live-link-box"
                                    data-booking-id="{{ $booking->id }}"
                                    data-status-url="{{ route('admin.tour-manager.status', $booking) }}"
                                    data-initial-status="{{ $tourZipStatus }}"
                                    data-started-at-ms="{{ ($tourZipStatus === 'processing' && $booking->tour_zip_started_at) ? $booking->tour_zip_started_at->timestamp * 1000 : '' }}">
                                    <label class="form-label fw-bold text-muted small">Tour Live Link</label>

                                    <div id="tour-live-link-content">

                                        @if($tourZipStatus === 'processing')
                                            <p class="text-warning mb-1 small">
                                                @if($tourZipPhaseLabel !== '')
                                                    <strong>{{ $tourZipPhaseLabel }}</strong> —
                                                @endif
                                                Processing ZIP… {{ $tourZipMessage ? '('.$tourZipMessage.')' : '' }}
                                            </p>
                                            <div class="progress" style="height: 10px;">
                                                <div class="progress-bar progress-bar-striped progress-bar-animated tour-zip-live-progress"
                                                     role="progressbar"
                                                     style="width: {{ $tourZipPctWidth }}%; transition: width 0.4s ease;"
                                                     aria-valuenow="{{ $tourZipProgress }}" aria-valuemin="0" aria-valuemax="100">
                                                </div>
                                            </div>
                                            <small class="text-muted d-block mt-1">
                                                {{ number_format($tourZipProgress, 2) }}%
                                                @if((int) ($booking->tour_zip_items_total ?? 0) > 0)
                                                    · {{ (int) ($booking->tour_zip_items_done ?? 0) }}/{{ (int) ($booking->tour_zip_items_total ?? 0) }} files
                                                @endif
                                                @if($booking->tour_zip_started_at)
                                                    · started {{ $booking->tour_zip_started_at->diffForHumans() }}
                                                @endif
                                            </small>
                                        @elseif($tourZipStatus === 'failed')
                                            <p class="text-danger mb-0">
                                                Processing failed{{ $tourZipMessage ? ': ' . $tourZipMessage : '.' }}
                                            </p>
                                        @elseif($tourZipStatus === 'done')
                                            @if($hasLiveLink)
                                                <p class="mb-0">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <a href="{{ $tourLiveUrl }}" target="_blank" rel="noopener"
                                                            class="text-truncate d-block flex-grow-1" style="max-width: 100%;" title="{{ $tourLiveUrl }}">
                                                            {{ Str::limit($tourLiveUrl, 40) }}
                                                        </a>
                                                        <button type="button"
                                                            class="btn btn-link btn-sm p-0 copy-link-btn"
                                                            data-copy-text="{{ $tourLiveUrl }}"
                                                            title="Copy live link" aria-label="Copy live link">
                                                            <i class="ri-file-copy-line"></i>
                                                        </button>
                                                    </div>
                                                </p>
                                            @else
                                                <p class="text-muted mb-0">Please upload a ZIP Again to generate the live link.</p>
                                            @endif
                                        @elseif($tourZipStatus === 'pending')
                                            <p class="text-muted mb-0">Please upload a ZIP to generate the live link.</p>
                                        @else
                                            <p class="text-muted mb-0">Please upload a ZIP Again to generate the live link.</p>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-6 mb-2">
                                    <label class="form-label fw-bold text-muted small">Tour Live Status</label>
                                    <p class="mb-0">{{ $booking->tour_zip_status ?? 'N/A' }}</p>
                                </div>
                                
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
                @endif

                <!-- Payment Information -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Payment Information</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label text-muted">Payment Status</label>
                            <p>
                                @php
                                    $paymentBadges = [
                                        'pending' => 'warning',
                                        'paid' => 'success',
                                        'failed' => 'danger',
                                        'refunded' => 'info'
                                    ];
                                    $paymentColor = $paymentBadges[$booking->payment_status] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $paymentColor }}">{{ ucfirst($booking->payment_status) }}</span>
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Amount</label>
                            <p class="fw-semibold fs-4">₹{{ number_format($booking->price, 2) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Timeline -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Timeline</h4>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-3">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <i class="ri-checkbox-circle-line text-secondary fs-5"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1">Created</h6>
                                        <p class="text-muted mb-0">{{ $booking->created_at->format('d M Y, h:i A') }}</p>
                                    </div>
                                </div>
                            </li>

                            @if($booking->booking_date)
                                <li class="mb-3">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <i class="ri-calendar-line text-info fs-5"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1">Scheduled</h6>
                                            <p class="text-muted mb-0">
                                                {{ \Carbon\Carbon::parse($booking->booking_date)->format('d M Y, h:i A') }}</p>
                                        </div>
                                    </div>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
@vite(['resources/js/pages/tour-zip-status-poll.js'])
<script>
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
                }, 2000);
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

// Copy QR/Tour links to clipboard
(function() {
    async function copyTextToClipboard(text) {
        try {
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(text);
                return true;
            }
        } catch (e) {
            // ignore and fallback
        }

        try {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.setAttribute('readonly', '');
            textarea.style.position = 'fixed';
            textarea.style.left = '-9999px';
            document.body.appendChild(textarea);
            textarea.select();
            const ok = document.execCommand('copy');
            document.body.removeChild(textarea);
            return ok;
        } catch (e) {
            return false;
        }
    }

    document.addEventListener('click', async function(e) {
        const btn = e.target.closest('.copy-link-btn');
        if (!btn) return;

        const text = btn.getAttribute('data-copy-text') || '';
        if (!text) return;

        if (!btn.dataset.originalHtml) {
            btn.dataset.originalHtml = btn.innerHTML;
        }

        const copied = await copyTextToClipboard(text);
        if (copied) {
            btn.innerHTML = '<i class="ri-check-line"></i>';
            const originalTitle = btn.getAttribute('title') || '';
            btn.setAttribute('title', 'Copied');
            setTimeout(function() {
                btn.innerHTML = btn.dataset.originalHtml || '<i class="ri-file-copy-line"></i>';
                btn.setAttribute('title', originalTitle || 'Copy link');
            }, 2000);
        } else {
            btn.innerHTML = '<i class="ri-close-line"></i>';
            setTimeout(function() {
                btn.innerHTML = btn.dataset.originalHtml || '<i class="ri-file-copy-line"></i>';
            }, 2000);
            alert('Failed to copy. Please copy manually.');
        }
    });
})();
</script>
@endsection