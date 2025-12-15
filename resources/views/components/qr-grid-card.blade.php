@props(['qr', 'isSelected' => false])

<div class="col-lg-3 col-md-6 mb-0" data-qr-id="{{ $qr->id }}">
    <div class="card overflow-hidden qr-grid-card border-0" data-qr-id="{{ $qr->id }}">
        <div class="position-relative">
            @if($qr->qr_code_svg)
                <div class="qr-code-container d-flex justify-content-center align-items-center bg-light" style=" padding: 10px;">
                    {!! $qr->qr_code_svg !!}
                </div>
            @elseif($qr->image)
                <img src="/storage/{{ $qr->image }}" alt="QR Code" class="img-fluid rounded-top" style="height: 200px; object-fit: cover; width: 100%;">
            @else
                <div class="d-flex justify-content-center align-items-center bg-light-subtle" style="height: 200px;">
                    <div class="text-center text-muted">
                        <i class="ri-qr-code-line" style="font-size: 80px; opacity: 0.3;"></i>
                        <div class="mt-2 small">No QR Code</div>
                    </div>
                </div>
            @endif
            
            <!-- Checkbox (top-left) -->
            <span class="position-absolute top-0 start-0 p-1">
                <div class="form-check">
                    <input class="form-check-input qr-checkbox-grid" type="checkbox" value="{{ $qr->id }}" data-qr-id="{{ $qr->id }}" id="grid-check-{{ $qr->id }}">
                    <label class="form-check-label" for="grid-check-{{ $qr->id }}"></label>
                </div>
            </span>
            
            <!-- Status Badge (top-right) -->
            <span class="position-absolute top-0 end-0 p-1">
                @if($qr->booking_id)
                    <span class="badge bg-success text-white fs-13">
                        <i class="ri-checkbox-circle-fill me-1"></i>Active
                    </span>
                @else
                    <span class="badge bg-warning text-white fs-13">
                        <i class="ri-close-circle-fill me-1"></i>Inactive
                    </span>
                @endif
            </span>
        </div>
        
        <div class="card-body p-2">
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="avatar bg-light rounded">
                    <i class="ri-qr-code-line fs-24 text-primary avatar-title"></i>
                </div>
                <div class="flex-grow-1">
                    <a href="{{ route('admin.qr.show', $qr->id) }}" class="text-dark fw-medium fs-16 d-block">
                        {{ $qr->name ?: 'QR Code #' . $qr->id }}
                    </a>
                    <h4 class="text-muted mb-0">
                        <span class="badge bg-primary">{{ $qr->code }}</span>
                    </h4>
                </div>
            </div>
            
            <div class="row g-2">
                @if($qr->booking_id)
                    <div class="col-6">
                        <span class="badge bg-light-subtle text-muted border fs-12">
                            <i class="ri-bookmark-line fs-16 align-middle"></i>
                            <span class="ms-1">Booking #{{ $qr->booking_id }}</span>
                        </span>
                    </div>
                @else
                    <div class="col-6">
                        <span class="badge bg-light-subtle text-muted border fs-12">
                            <i class="ri-links-line fs-16 align-middle"></i>
                            <span class="ms-1">Unassigned</span>
                        </span>
                    </div>
                @endif
                
                <div class="col-6">
                    <span class="badge bg-light-subtle text-muted border fs-12">
                        <i class="ri-calendar-line fs-16 align-middle"></i>
                        <span class="ms-1">{{ $qr->created_at->format('M Y') }}</span>
                    </span>
                </div>
                
            </div>
        </div>
        
        <div class="card-footer bg-light-subtle d-flex justify-content-between align-items-center border-top p-2">
            <div class="d-flex gap-1">
                <a href="{{ route('admin.qr.download', $qr->id) }}" class="btn btn-soft-success btn-sm" title="Download QR Code" data-bs-toggle="tooltip" data-bs-placement="top">
                    <i class="ri-download-2-line"></i>
                </a>
                <button class="btn btn-outline-primary btn-sm assign-booking-btn" 
                    data-qr-id="{{ $qr->id }}" 
                    data-qr-name="{{ $qr->name }}" 
                    data-qr-code="{{ $qr->code }}" 
                    data-qr-image="{{ $qr->image }}" 
                    data-booking-id="{{ $qr->booking_id ?: '' }}"
                    title="{{ $qr->booking_id ? 'View Booking Details' : 'Assign QR to Booking' }}" 
                    data-bs-toggle="tooltip" 
                    data-bs-placement="top">
                    <i class="ri-link"></i>
                </button>
            </div>
            <div class="d-flex gap-1">
                <a href="{{ route('admin.qr.show', $qr->id) }}" class="btn btn-light btn-sm border" title="View QR Code Details" data-bs-toggle="tooltip" data-bs-placement="top">
                    <i class="ri-eye-line"></i>
                </a>
                <a href="{{ route('admin.qr.edit', $qr->id) }}" class="btn btn-soft-primary btn-sm border" title="Edit QR Code" data-bs-toggle="tooltip" data-bs-placement="top">
                    <i class="ri-edit-line"></i>
                </a>
                <form action="{{ route('admin.qr.destroy', $qr->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-soft-danger btn-sm border" onclick="return confirm('Delete this QR code?')" title="Delete QR Code" data-bs-toggle="tooltip" data-bs-placement="top">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>




