@extends('admin.layouts.vertical', ['title' => 'Customers', 'subTitle' => 'Show Customer, Tour, Bookings Details'])
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                <div>
                    <nav aria-label="breadcrumb" class="mb-0">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.customer.index') }}">Customer</a></li>
                            <li class="breadcrumb-item active" aria-current="page">#{{ $customer->id }}</li>
                        </ol>
                    </nav>
                    <h3 class="mb-0">Customer #{{ $customer->id }}</h3>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <x-admin.back-button :fallback="route('admin.customer.index')" :classes="['btn', 'btn-soft-secondary']"
                        :merge="false" icon="ri-arrow-go-back-line" />
                    <a href="{{ route('admin.customer.edit', $customer) }}" class="btn btn-primary"><i
                            class="ri-edit-line me-1"></i> Edit Customer</a>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-3">
        <!-- Property Details -->
        <div class="col-md-9 mb-3">
            <div class="card border bg-light-subtle h-100">
                <div class="card-header bg-primary-subtle border-primary">
                    <h5 class="card-title mb-0"><i class="ri-id-card-line me-2"></i>Customer Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-2">
                            <div class="flex-shrink-0">
                                <div
                                    class="avatar-lg bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="ri-user-line fs-3 text-primary"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-10">
                            <div class="row">
                                <div class="col-6">
                                    <P class="mb-1"><strong>Id:</strong>{{ $customer->id }}</P>
                                </div>
                                <div class="col-6">
                                    <p class="mb-1"><strong>Name:</strong> {{ $customer->firstname }}
                                        {{ $customer->lastname }}</p>
                                </div>
                                <div class="col-6">
                                    <p class="mb-1"><strong>Email:</strong> {{ $customer->email }}</p>
                                </div>
                                <div class="col-6">
                                    <p class="mb-1"><strong>Mobile:</strong> {{ $customer->mobile }}</p>
                                </div>
                                <div class="col-6">
                                    <p class="mb-1"><strong>Slug:</strong> {{ $customer->slug ?? '-' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-3">
            <!-- Top Statistics Section -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card text-center">
                        <div class="card-body">
                            <h6 class="text-muted">Total Bookings</h6>
                            <h3 class="mb-0">{{ $totalBookings ?? 0 }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="card text-center">
                        <div class="card-body">
                            <h6 class="text-muted">Total Tours</h6>
                            <h3 class="mb-0">{{ $totalTours ?? 0 }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Add more stat cards as needed -->
        </div>
    </div>

    <!-- SEO Metadata Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-secondary">
                <div class="card-header bg-secondary-subtle border-secondary">
                    <h5 class="card-title mb-0"><i class="ri-shield-check-line me-2"></i>SEO Metadata</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach([
                            'meta_title' => 'Meta Title',
                            'meta_description' => 'Meta Description',
                            'meta_keywords' => 'Meta Keywords',
                            'canonical_url' => 'Canonical URL',
                            'meta_robots' => 'Meta Robots',
                            'twitter_title' => 'Twitter Title',
                            'twitter_description' => 'Twitter Description',
                            'twitter_image' => 'Twitter Image',
                            'og_title' => 'OG Title',
                            'og_description' => 'OG Description',
                            'header_code' => 'Header Code',
                            'footer_code' => 'Footer Code',
                            'gtm_tag' => 'GTM Tag',
                        ] as $field => $label)
                            @if(!blank($customer->{$field}))
                                <div class="col-md-6 mb-2">
                                    <p class="mb-1"><strong>{{ $label }}:</strong>
                                        @if(in_array($field, ['meta_description','twitter_description','og_description','header_code','footer_code']))
                                            <br><small class="text-muted">{!! nl2br(e($customer->{$field})) !!}</small>
                                        @elseif($field === 'twitter_image')
                                            <a href="{{ $customer->{$field} }}" target="_blank">View image</a>
                                        @else
                                            {{ $customer->{$field} }}
                                        @endif
                                    </p>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Details Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-info-subtle border-info">
                    <h5 class="card-title mb-0"><i class="ri-calendar-check-line me-2"></i>Booking Details</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                         <table id="customer-bookings-table" class="table table-hover">
                           <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Customer</th>
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
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    @vite(['resources/js/pages/customer-show.js'])
@endsection