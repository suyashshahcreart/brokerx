@extends('admin.layouts.vertical', ['title' => 'Booking Details'])

@section('content')
    <div class="">
        <div class="row">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                    <div>
                        <nav aria-label="breadcrumb" class="mb-0">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('root') }}">Home</a></li>
                                <li class="breadcrumb-item" aria-current="page"><a
                                        href="{{ route('admin.tour-manager.index') }}">Tour Management</a></li>
                                <li class="breadcrumb-item active" aria-current="page">{{ $booking->id }}</li>
                            </ol>
                        </nav>
                        <h3 class="mb-0">Tour Management</h3>
                    </div>
                    <div>
                        <a href="{{ route('admin.tour-manager.index') }}" class="btn btn-secondary">
                            <i class="ri-arrow-left-line me-1"></i> Back to Booking
                        </a>
                        <a href="{{ route('admin.tour-manager.edit', $booking) }}" class="btn btn-secondary">
                            <i class="ri-edit-line me-1"></i> Edit
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
                                @if($booking->qr->qr_link)
                                    <div class="col-6 mb-2">
                                        <label class="form-label fw-bold text-muted small">QR Link Redirect Link</label>
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
</script>
@endsection