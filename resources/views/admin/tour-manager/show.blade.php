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
                        <h3 class="mb-0">Tour Management ({{$booking->tour_code}})</h3>
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
                @if($booking->user)
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Customer Information</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Name</label>
                                        <p class="fw-semibold">{{ $booking->user->firstname }} {{ $booking->user->lastname }}
                                        </p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Email</label>
                                        <p>{{ $booking->user->email }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Mobile</label>
                                        <p>{{ $booking->user->mobile }}</p>
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
                                        $qrUrl = 'https://qr.proppik.com/' . $booking->qr->code;
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
                                        <a href="https://qr.proppik.com/{{ $booking->qr->code }}" target="_blank" class="text-break">
                                            <code>https://qr.proppik.com/{{ $booking->qr->code }}</code>
                                        </a>
                                    </p>
                                </div>
                                <div class="col-6 mb-2" id="tour-live-link-box" data-booking-id="{{ $booking->id }}">
                                    <label class="form-label fw-bold text-muted small">Tour Live Link</label>

                                    @php
                                        $tourZipStatus = $booking->tour_zip_status ?? 'pending';
                                        $tourZipProgress = (int)($booking->tour_zip_progress ?? 0);
                                        $tourZipMessage = $booking->tour_zip_message;
                                        $tourLiveUrl = $booking->getTourLiveUrl();
                                        $hasLiveLink = !empty($booking->qr?->qr_link) && $tourLiveUrl !== '#';
                                    @endphp

                                    <div id="tour-live-link-content">
                                        
                                        @if($tourZipStatus === 'processing')
                                            <p class="text-warning mb-1">
                                                Processing ZIP… {{ $tourZipMessage ? '(' . $tourZipMessage . ')' : '' }}
                                            </p>
                                            <div class="progress" style="height: 10px;">
                                                <div class="progress-bar progress-bar-striped progress-bar-animated"
                                                     role="progressbar"
                                                     style="width: {{ max(1, min(100, $tourZipProgress)) }}%;"
                                                     aria-valuenow="{{ $tourZipProgress }}" aria-valuemin="0" aria-valuemax="100">
                                                </div>
                                            </div>
                                            <small class="text-muted d-block mt-1">
                                                {{ $tourZipProgress }}%
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
                                                    <a href="{{ $tourLiveUrl }}" target="_blank" class="text-truncate d-block" style="max-width: 100%;">
                                                        {{ Str::limit($tourLiveUrl, 40) }}
                                                        <i class="ri-external-link-line ms-1"></i>
                                                    </a>
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

// Live ZIP processing status polling (Tour Live Link box)
(function() {
    const box = document.getElementById('tour-live-link-box');
    const content = document.getElementById('tour-live-link-content');
    if (!box || !content) return;

    const statusUrl = "{{ route('admin.tour-manager.status', $booking) }}";
    let startedAtMs = null;
    let isProcessing = false;
    let lastStatus = null;
    const initialStatus = "{{ $tourZipStatus }}"; // Get initial status from server
    
    // Initialize timer data if page loads with processing status
    @if($tourZipStatus === 'processing' && $booking->tour_zip_started_at)
        @php
            $startedAtTimestamp = $booking->tour_zip_started_at->timestamp * 1000; // Convert to milliseconds
        @endphp
        startedAtMs = {{ $startedAtTimestamp }};
        isProcessing = true;
    @endif

    function escapeHtml(str) {
        return String(str ?? '').replace(/[&<>"']/g, function(m) {
            return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]);
        });
    }

    function showToast(message, type) {
        try {
            const existing = document.getElementById('tour-status-toast');
            if (existing) existing.remove();

            const toast = document.createElement('div');
            toast.id = 'tour-status-toast';
            toast.className = 'position-fixed top-0 end-0 p-3';
            toast.style.zIndex = '1080';
            toast.innerHTML = `
                <div class="toast align-items-center text-bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            ${escapeHtml(message)}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            `;
            document.body.appendChild(toast);

            const btn = toast.querySelector('.btn-close');
            if (btn) {
                btn.addEventListener('click', () => toast.remove());
            }

            setTimeout(() => {
                if (toast.parentNode) toast.remove();
            }, 6000);
        } catch (e) {
            // Fallback
            alert(message);
        }
    }

    function render(data) {
        const status = data?.tour_zip_status ?? 'pending';
        const progress = Math.max(0, Math.min(100, parseInt(data?.tour_zip_progress ?? 0, 10)));
        const message = data?.tour_zip_message ?? '';
        const liveUrl = data?.tour_live_url ?? '#';
        const hasLive = data?.has_live_link === true;
        const startedAt = data?.tour_zip_started_at ? new Date(data.tour_zip_started_at) : null;
        isProcessing = status === 'processing';
        startedAtMs = (startedAt && !isNaN(startedAt.getTime())) ? startedAt.getTime() : startedAtMs;

        function formatElapsed() {
            if (!startedAtMs) return '';
            const now = new Date();
            const diffMs = Math.max(0, now.getTime() - startedAtMs);
            const totalSeconds = Math.floor(diffMs / 1000);
            const minutes = Math.floor(totalSeconds / 60);
            const seconds = totalSeconds % 60;
            if (minutes === 0) {
                return `${seconds}s`;
            }
            return `${minutes}m ${seconds.toString().padStart(2, '0')}s`;
        }

        if (status === 'processing') {
            const width = Math.max(1, progress);
            const elapsed = formatElapsed();
            content.innerHTML = `
                <p class="text-warning mb-1">Processing ZIP… ${message ? '(' + escapeHtml(message) + ')' : ''}</p>
                <div class="progress" style="height: 10px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated"
                         role="progressbar"
                         style="width: ${width}%;"
                         aria-valuenow="${progress}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <small class="text-muted d-block mt-1">
                    ${progress}%${elapsed ? ' · running for <span id="tour-zip-elapsed">' + escapeHtml(elapsed) + '</span>' : ''}
                </small>
            `;
            return;
        }

        if (status === 'failed') {
            content.innerHTML = `<p class="text-danger mb-0">Processing failed${message ? ': ' + escapeHtml(message) : '.'}</p>`;
            return;
        }

        if (status === 'done') {
            if (hasLive && liveUrl && liveUrl !== '#') {
                content.innerHTML = `
                    <p class="mb-0">
                        <a href="${escapeHtml(liveUrl)}" target="_blank" class="text-truncate d-block" style="max-width: 100%;">
                            ${escapeHtml(liveUrl.length > 40 ? liveUrl.slice(0, 40) + '…' : liveUrl)}
                            <i class="ri-external-link-line ms-1"></i>
                        </a>
                    </p>
                `;
            } else {
                content.innerHTML = `<p class="text-muted mb-0">Please upload a ZIP Again to generate the live link.</p>`;
            }
            return;
        }

        if (status === 'pending') {
            content.innerHTML = `<p class="text-muted mb-0">Please upload a ZIP to generate the live link.</p>`;
            return;
        }

        // fallback (unknown state)
        content.innerHTML = `<p class="text-muted mb-0">Please upload a ZIP Again to generate the live link.</p>`;
    }

    async function poll() {
        try {
            const res = await fetch(statusUrl, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) {
                // On HTTP error, retry if still processing
                if (lastStatus === 'processing') {
                    console.warn('Status API returned error:', res.status, '- retrying in 10s');
                    setTimeout(() => poll(), 10000);
                }
                return;
            }
            const data = await res.json();
            const status = data?.tour_zip_status ?? 'pending';
            
            console.log('Poll response - Status:', status, 'Progress:', data?.tour_zip_progress ?? 0);

            // Detect status change from processing -> something else (check BEFORE updating lastStatus)
            const wasProcessing = lastStatus === 'processing';
            const isNowDone = status === 'done';
            const isNowFailed = status === 'failed';
            const isNowNotProcessing = status !== 'processing';

            if (wasProcessing && isNowNotProcessing) {
                console.log('Status changed from processing to:', status);
                if (isNowDone && (data?.has_live_link ?? false)) {
                    showToast('✅ Tour processing completed! Live link is ready.', 'success');
                } else if (isNowFailed) {
                    showToast('❌ Tour processing failed. Please check logs.', 'error');
                } else {
                    showToast('ℹ️ Tour processing finished.', 'info');
                }
            }

            // Update lastStatus AFTER checking transitions
            lastStatus = status;
            render(data);

            // Only reload if status changed FROM processing TO done (transition detection)
            if (wasProcessing && isNowDone) {
                if (!poll._reloaded) {
                    poll._reloaded = true;
                    console.log('Status changed to done, preparing page reload...');
                    // Add URL parameter to show toast after reload
                    const url = new URL(window.location.href);
                    url.searchParams.set('completed', '1');
                    // Show toast for 2 seconds before reload
                    setTimeout(() => {
                        console.log('Reloading page with completed parameter...');
                        window.location.href = url.toString();
                    }, 2000);
                }
                return;
            }

            // Continue polling only while processing
            if (status === 'processing') {
                // Keep polling every 5 seconds while still processing
                console.log('Status is processing, scheduling next poll in 5s...');
                setTimeout(() => {
                    console.log('Executing scheduled poll...');
                    poll();
                }, 5000);
            } else {
                // Status changed from processing to something else - stop polling
                // (reload already handled above if it was processing -> done)
                console.log('Polling stopped. Status changed to:', status);
            }
        } catch (e) {
            // On error, only retry if we're still in processing state
            if (lastStatus === 'processing') {
                console.error('Poll error, retrying in 10s:', e);
                setTimeout(() => poll(), 10000);
            } else {
                console.error('Poll error and status not processing, stopping:', e);
            }
        }
    }

    // Smooth UI timer: update "running for ..." every 1s without extra AJAX calls
    setInterval(() => {
        const el = document.getElementById('tour-zip-elapsed');
        if (!el || !isProcessing || !startedAtMs) return;
        const diffMs = Math.max(0, Date.now() - startedAtMs);
        const totalSeconds = Math.floor(diffMs / 1000);
        const minutes = Math.floor(totalSeconds / 60);
        const seconds = totalSeconds % 60;
        el.textContent = minutes === 0 ? `${seconds}s` : `${minutes}m ${seconds.toString().padStart(2, '0')}s`;
    }, 1000);

    // Only start polling if initial status is 'processing'
    // If page loads with 'done'/'failed'/'pending', don't poll at all
    if (initialStatus === 'processing') {
        lastStatus = 'processing'; // Initialize so transition detection works
        console.log('Initial status is processing, starting polling...');
        // Start polling immediately, then continue every 5 seconds
        poll();
    } else {
        console.log('Initial status is', initialStatus, '- polling not started');
    }
})();
</script>
@endsection