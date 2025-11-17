@extends('admin.layouts.vertical', ['title' => 'Bookings', 'subTitle' => 'Property'])

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <div>
                <nav aria-label="breadcrumb" class="mb-1">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('root') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="#">Property</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Bookings</li>
                    </ol>
                </nav>
                <h3 class="mb-0">Bookings</h3>
            </div>
            <div class="d-flex align-items-center gap-2">
                <x-admin.back-button :classes="['btn', 'btn-soft-secondary']" :merge="false" icon="ri-arrow-go-back-line" />
                <a href="{{ route('admin.bookings.create') }}" class="btn btn-primary">
                    <i class="ri-add-line me-1"></i> New Booking
                </a>
            </div>
        </div>

        <div class="card panel-card border-primary border-top" data-panel-card>
            <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h4 class="card-title mb-1">Bookings List</h4>
                    <p class="text-muted mb-0">Manage customer property bookings</p>
                </div>
                <div class="panel-actions d-flex gap-2">
                    <button type="button" class="btn btn-light border" data-panel-action="refresh" title="Refresh">
                        <i class="ri-refresh-line"></i>
                    </button>
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
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Type / Subtype</th>
                                <th>BHK</th>
                                <th>City / State</th>
                                <th>Area</th>
                                <th>Price</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($bookings as $booking)
                            <tr>
                                <td>#{{ $booking->id }}</td>
                                <td>{{ $booking->user?->firstname }} {{ $booking->user?->lastname }}</td>
                                <td>
                                    {{ $booking->propertyType?->name }}
                                    <div class="text-muted small">{{ $booking->propertySubType?->name }}</div>
                                </td>
                                <td>{{ $booking->bhk?->name ?? '-' }}</td>
                                <td>
                                    {{ $booking->city?->name ?? '-' }}
                                    <div class="text-muted small">{{ $booking->state?->name ?? '-' }}</div>
                                </td>
                                <td>{{ number_format($booking->area) }}</td>
                                <td>₹ {{ number_format($booking->price) }}</td>
                                <td>{{ optional($booking->booking_date)->format('Y-m-d') ?? '-' }}</td>
                                <td><span class="badge bg-secondary text-uppercase">{{ $booking->status }}</span></td>
                                <td><span class="badge bg-info text-uppercase">{{ $booking->payment_status }}</span></td>
                                <td class="text-end">
                                    <a href="{{ route('admin.bookings.show', $booking) }}" class="btn btn-light btn-sm border" title="View"><i class="ri-eye-line"></i></a>
                                    <a href="{{ route('admin.bookings.edit', $booking) }}" class="btn btn-soft-primary btn-sm" title="Edit"><i class="ri-edit-line"></i></a>
                                    <form action="{{ route('admin.bookings.destroy', $booking) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-soft-danger btn-sm" onclick="return confirm('Delete this booking?')"><i class="ri-delete-bin-line"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted">No bookings found.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $bookings->links() }}
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection
