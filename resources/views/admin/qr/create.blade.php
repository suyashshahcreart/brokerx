@extends('admin.layouts.vertical', ['title' => 'Create QR Code', 'subTitle' => 'System'])

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <div>
                <nav aria-label="breadcrumb" class="mb-1">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="#">System</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.qr.index') }}">QR Codes</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Create</li>
                    </ol>
                </nav>
                <h3 class="mb-0">Create New QR Code</h3>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#multipleGenerateModal">
                    <i class="ri-add-circle-line me-1"></i> Multiple Generate
                </button>
                <x-admin.back-button :fallback="route('admin.qr.index')" :classes="['btn', 'btn-soft-secondary']" :merge="false" icon="ri-arrow-go-back-line" />
            </div>
        </div>

        <div class="card panel-card border-primary border-top" data-panel-card>
            <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h4 class="card-title mb-1">QR Code Details</h4>
                    <p class="text-muted mb-0">Specify the QR code details and upload an image if needed</p>
                </div>
                <div class="panel-actions d-flex gap-2">
                    <button type="button" class="btn btn-light border" data-panel-action="collapse" title="Collapse">
                        <i class="ri-arrow-up-s-line"></i>
                    </button>
                    <button type="button" class="btn btn-light border" data-panel-action="fullscreen" title="Fullscreen">
                        <i class="ri-fullscreen-line"></i>
                    </button>
                    <button type="button" class="btn btn-light border" data-panel-action="close" title="Close">
                        <i class="ri-close-line"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.qr.store') }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate id="qrCreateForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Code <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" name="code" id="code" class="form-control" required maxlength="8" minlength="8" pattern="[A-Za-z0-9]{8}" placeholder="Auto-generated code" autocomplete="off" value="{{ $defaultCode ?? '' }}">
                            <button type="button" class="btn btn-outline-secondary" id="refreshCodeBtn" title="Regenerate Code">
                                <i class="ri-refresh-line"></i>
                            </button>
                        </div>
                        <div class="form-text">Code must be exactly 8 characters (A-Z, a-z, 0-9 only)</div>
                        <div class="invalid-feedback">Please enter a valid 8-character code (A-Z, a-z, 0-9 only)</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" id="name" class="form-control" placeholder="Optional name (any text)" autocomplete="off" value="">
                        <div class="form-text">Name is optional and can be any text</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Booking</label>
                        <select name="booking_id" class="form-control">
                            <option value="">Select Booking</option>
                            @foreach($bookings as $booking)
                                @php
                                    // Build customer name
                                    $customerName = trim(($booking->user->firstname ?? '') . ' ' . ($booking->user->lastname ?? ''));
                                    if (empty($customerName)) {
                                        $customerName = 'N/A';
                                    }
                                    
                                    // Build property details
                                    $propertyDetails = [];
                                    if ($booking->propertyType) {
                                        $propertyDetails[] = $booking->propertyType->name;
                                    }
                                    if ($booking->propertySubType) {
                                        $propertyDetails[] = $booking->propertySubType->name;
                                    }
                                    if ($booking->bhk) {
                                        $propertyDetails[] = $booking->bhk->name;
                                    }
                                    if ($booking->furniture_type) {
                                        $propertyDetails[] = $booking->furniture_type;
                                    }
                                    if ($booking->area) {
                                        $propertyDetails[] = number_format($booking->area) . ' sq.ft';
                                    }
                                    $propertyText = !empty($propertyDetails) ? implode(' | ', $propertyDetails) : 'N/A';
                                    
                                    // Build address
                                    $addressParts = [];
                                    if ($booking->house_no) {
                                        $addressParts[] = $booking->house_no;
                                    }
                                    if ($booking->building) {
                                        $addressParts[] = $booking->building;
                                    }
                                    if ($booking->city) {
                                        $addressParts[] = $booking->city->name;
                                    }
                                    if ($booking->state) {
                                        $addressParts[] = $booking->state->name;
                                    }
                                    if ($booking->pin_code) {
                                        $addressParts[] = $booking->pin_code;
                                    }
                                    $addressText = !empty($addressParts) ? implode(', ', $addressParts) : 'N/A';
                                    
                                    // Build display text
                                    $displayParts = [
                                        'ID: ' . $booking->id,
                                        $customerName,
                                        $booking->user->mobile ?? 'N/A',
                                        ($booking->user->email ? $booking->user->email : ''),
                                        $propertyText,
                                        $addressText
                                    ];
                                    $displayText = implode(' | ', array_filter($displayParts));
                                @endphp
                                <option value="{{ $booking->id }}">{{ $displayText }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- now just comment this here  --}}
                    {{-- <div class="mb-3">
                        <label class="form-label">Image</label>
                        <input type="file" name="image" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">QR Link</label>
                        <input type="text" name="qr_link" class="form-control">
                    </div> --}}
                    <button type="submit" class="btn btn-primary">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Multiple Generate Modal -->
<div class="modal fade" id="multipleGenerateModal" tabindex="-1" aria-labelledby="multipleGenerateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="multipleGenerateModalLabel">Generate Multiple QR Codes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="multipleGenerateForm">
                    <div class="mb-3">
                        <label for="quantity" class="form-label">How many QR codes to generate? <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="quantity" name="quantity" min="1" max="1000" required placeholder="Enter quantity">
                        <div class="form-text">Enter a number between 1 and 1000</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Quick Select:</label>
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="button" class="btn btn-outline-primary quick-quantity-btn" data-quantity="25">25</button>
                            <button type="button" class="btn btn-outline-primary quick-quantity-btn" data-quantity="50">50</button>
                            <button type="button" class="btn btn-outline-primary quick-quantity-btn" data-quantity="75">75</button>
                            <button type="button" class="btn btn-outline-primary quick-quantity-btn" data-quantity="100">100</button>
                        </div>
                    </div>
                    
                    <div id="generateStatus" class="alert d-none" role="alert"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="generateMultipleBtn">
                    <i class="ri-play-line me-1"></i> Generate
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
    // Function to generate random 8-character code/name (A-Za-z0-9)
    function generateRandomCode() {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        let result = '';
        for (let i = 0; i < 8; i++) {
            result += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return result;
    }

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        const codeInput = document.getElementById('code');
        const nameInput = document.getElementById('name');
        const refreshCodeBtn = document.getElementById('refreshCodeBtn');
        const form = document.getElementById('qrCreateForm');

        // Generate initial code on page load
        if (codeInput) {
            if (!codeInput.value || codeInput.value.trim() === '') {
                codeInput.value = generateRandomCode();
            }
        }

        // Refresh code button click handler
        if (refreshCodeBtn) {
            refreshCodeBtn.addEventListener('click', function() {
                if (codeInput) {
                    codeInput.value = generateRandomCode();
                    codeInput.classList.remove('is-invalid');
                    codeInput.focus();
                }
            });
        }

        // Custom validation for code field
        if (codeInput) {
            codeInput.addEventListener('input', function() {
                const value = this.value;
                const pattern = /^[A-Za-z0-9]{8}$/;
                
                if (value.length > 0 && !pattern.test(value)) {
                    // Only allow A-Za-z0-9 characters
                    this.value = value.replace(/[^A-Za-z0-9]/g, '');
                }
                
                // Limit to 8 characters
                if (this.value.length > 8) {
                    this.value = this.value.substring(0, 8);
                }
            });
        }

        // Form validation
        if (form) {
            form.addEventListener('submit', function(event) {
                const codeValue = codeInput ? codeInput.value.trim() : '';
                const pattern = /^[A-Za-z0-9]{8}$/;
                
                // Code is required
                if (!pattern.test(codeValue)) {
                    event.preventDefault();
                    event.stopPropagation();
                    if (codeInput) {
                        codeInput.classList.add('is-invalid');
                        codeInput.focus();
                    }
                } else {
                    if (codeInput) {
                        codeInput.classList.remove('is-invalid');
                    }
                }
                
                // Name is optional, no validation needed
                
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                
                form.classList.add('was-validated');
            });
        }

        // Multiple Generate Modal functionality
        const multipleGenerateModal = document.getElementById('multipleGenerateModal');
        const quantityInput = document.getElementById('quantity');
        const quickQuantityBtns = document.querySelectorAll('.quick-quantity-btn');
        const generateMultipleBtn = document.getElementById('generateMultipleBtn');
        const generateStatus = document.getElementById('generateStatus');

        // Function to generate QR codes
        function generateQRCodes(quantity) {
            if (!quantity || quantity < 1 || quantity > 1000) {
                generateStatus.className = 'alert alert-danger';
                generateStatus.textContent = 'Please enter a valid quantity between 1 and 1000';
                generateStatus.classList.remove('d-none');
                return;
            }

            // Disable all buttons and show loading
            generateMultipleBtn.disabled = true;
            quickQuantityBtns.forEach(b => b.disabled = true);
            generateMultipleBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Generating...';
            generateStatus.classList.add('d-none');

            // Make AJAX request
            fetch('{{ route("admin.qr.bulk-generate") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    quantity: quantity
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    generateStatus.className = 'alert alert-success';
                    generateStatus.textContent = `Successfully generated ${data.count} QR code(s)!`;
                    generateStatus.classList.remove('d-none');
                    
                    // Reset form after 2 seconds and close modal
                    setTimeout(() => {
                        quantityInput.value = '';
                        quickQuantityBtns.forEach(b => {
                            b.classList.remove('active');
                            b.disabled = false;
                        });
                        generateStatus.classList.add('d-none');
                        // Close modal using Bootstrap 5
                        const modalElement = bootstrap?.Modal?.getInstance(multipleGenerateModal);
                        if (modalElement) {
                            modalElement.hide();
                        } else if (window.bootstrap) {
                            const modal = window.bootstrap.Modal.getInstance(multipleGenerateModal);
                            if (modal) {
                                modal.hide();
                            }
                        }
                        // Redirect to index page after 1 more second
                        setTimeout(() => {
                            window.location.href = '{{ route("admin.qr.index") }}';
                        }, 1000);
                    }, 2000);
                } else {
                    generateStatus.className = 'alert alert-danger';
                    generateStatus.textContent = data.message || 'Failed to generate QR codes';
                    generateStatus.classList.remove('d-none');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                generateStatus.className = 'alert alert-danger';
                generateStatus.textContent = 'An error occurred while generating QR codes';
                generateStatus.classList.remove('d-none');
            })
            .finally(() => {
                generateMultipleBtn.disabled = false;
                quickQuantityBtns.forEach(b => b.disabled = false);
                generateMultipleBtn.innerHTML = '<i class="ri-play-line me-1"></i> Generate';
            });
        }

        // Quick quantity buttons - directly generate on click
        quickQuantityBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const quantity = parseInt(this.getAttribute('data-quantity'));
                quantityInput.value = quantity;
                // Remove active class from all buttons
                quickQuantityBtns.forEach(b => b.classList.remove('active'));
                // Add active class to clicked button
                this.classList.add('active');
                // Directly generate QR codes
                generateQRCodes(quantity);
            });
        });

        // Generate button click handler
        if (generateMultipleBtn) {
            generateMultipleBtn.addEventListener('click', function() {
                const quantity = parseInt(quantityInput.value);
                generateQRCodes(quantity);
            });
        }

        // Reset modal when closed
        if (multipleGenerateModal) {
            multipleGenerateModal.addEventListener('hidden.bs.modal', function() {
                quantityInput.value = '';
                quickQuantityBtns.forEach(b => b.classList.remove('active'));
                generateStatus.classList.add('d-none');
            });
        }
    });
</script>
@endsection
