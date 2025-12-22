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
                @if($booking->qr)
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