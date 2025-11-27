@extends('admin.layouts.vertical', ['title' => 'Tour Details', 'subTitle' => 'Manage'])

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
            <div>
                <nav aria-label="breadcrumb" class="mb-0">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('root') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.tours.index') }}">Tours</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $tour->title }}</li>
                    </ol>
                </nav>
                <h3 class="mb-0">{{ $tour->title }}</h3>
            </div>
            <div class="d-flex align-items-center gap-2">
                <x-admin.back-button :fallback="route('admin.tours.index')" :classes="['btn', 'btn-soft-secondary']" :merge="false" icon="ri-arrow-go-back-line" />
                @can('tour_edit')
                <a href="{{ route('admin.tours.edit', $tour) }}" class="btn btn-primary">
                    <i class="ri-edit-line me-1"></i> Edit
                </a>
                @endcan
            </div>
        </div>

        <!-- Basic Information -->
        <div class="card panel-card border-primary border-top mb-3">
            <div class="card-header">
                <h4 class="card-title mb-0">Basic Information</h4>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-lg-6">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Title</dt>
                            <dd class="col-sm-8">{{ $tour->title }}</dd>

                            <dt class="col-sm-4">Slug</dt>
                            <dd class="col-sm-8"><code>{{ $tour->slug }}</code></dd>

                            <dt class="col-sm-4">Status</dt>
                            <dd class="col-sm-8">
                                @php
                                    $badges = ['draft' => 'bg-secondary', 'published' => 'bg-success', 'archived' => 'bg-warning'];
                                    $class = $badges[$tour->status] ?? 'bg-secondary';
                                @endphp
                                <span class="badge {{ $class }} text-uppercase">{{ $tour->status }}</span>
                            </dd>

                            <dt class="col-sm-4">Location</dt>
                            <dd class="col-sm-8">{{ $tour->location ?? '-' }}</dd>

                            <dt class="col-sm-4">Price</dt>
                            <dd class="col-sm-8">{{ $tour->formatted_price }}</dd>

                            <dt class="col-sm-4">Duration</dt>
                            <dd class="col-sm-8">{{ $tour->duration_text }}</dd>
                        </dl>
                    </div>
                    <div class="col-lg-6">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Start Date</dt>
                            <dd class="col-sm-8">{{ $tour->start_date ? $tour->start_date->format('d M Y') : '-' }}</dd>

                            <dt class="col-sm-4">End Date</dt>
                            <dd class="col-sm-8">{{ $tour->end_date ? $tour->end_date->format('d M Y') : '-' }}</dd>

                            <dt class="col-sm-4">Max Participants</dt>
                            <dd class="col-sm-8">{{ $tour->max_participants ? number_format($tour->max_participants) : '-' }}</dd>

                            <dt class="col-sm-4">Featured Image</dt>
                            <dd class="col-sm-8">
                                @if($tour->featured_image)
                                    <a href="{{ $tour->featured_image }}" target="_blank" class="text-primary">View Image</a>
                                @else
                                    -
                                @endif
                            </dd>

                            <dt class="col-sm-4">Created</dt>
                            <dd class="col-sm-8">{{ $tour->created_at->format('d M Y, h:i A') }}</dd>

                            <dt class="col-sm-4">Updated</dt>
                            <dd class="col-sm-8">{{ $tour->updated_at->format('d M Y, h:i A') }}</dd>
                        </dl>
                    </div>
                </div>

                @if($tour->description)
                <div class="mt-4">
                    <h5 class="mb-2">Short Description</h5>
                    <p class="text-muted">{{ $tour->description }}</p>
                </div>
                @endif

                @if($tour->content)
                <div class="mt-4">
                    <h5 class="mb-2">Full Content</h5>
                    <div class="border rounded p-3 bg-light">
                        {!! nl2br(e($tour->content)) !!}
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- SEO Information -->
        <div class="card panel-card border-success border-top mb-3">
            <div class="card-header">
                <h4 class="card-title mb-0">SEO Meta Tags</h4>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Meta Title</dt>
                    <dd class="col-sm-9">{{ $tour->meta_title ?? '-' }}</dd>

                    <dt class="col-sm-3">Meta Description</dt>
                    <dd class="col-sm-9">{{ $tour->meta_description ?? '-' }}</dd>

                    <dt class="col-sm-3">Meta Keywords</dt>
                    <dd class="col-sm-9">{{ $tour->meta_keywords ?? '-' }}</dd>

                    <dt class="col-sm-3">Canonical URL</dt>
                    <dd class="col-sm-9">
                        @if($tour->canonical_url)
                            <a href="{{ $tour->canonical_url }}" target="_blank" class="text-primary">{{ $tour->canonical_url }}</a>
                        @else
                            -
                        @endif
                    </dd>

                    <dt class="col-sm-3">Meta Robots</dt>
                    <dd class="col-sm-9"><code>{{ $tour->meta_robots ?? '-' }}</code></dd>
                </dl>
            </div>
        </div>

        <!-- Open Graph -->
        <div class="card panel-card border-info border-top mb-3">
            <div class="card-header">
                <h4 class="card-title mb-0">Open Graph / Social Media</h4>
            </div>
            <div class="card-body">
                <h5 class="mb-3">Open Graph Tags</h5>
                <dl class="row mb-4">
                    <dt class="col-sm-3">OG Title</dt>
                    <dd class="col-sm-9">{{ $tour->og_title ?? '-' }}</dd>

                    <dt class="col-sm-3">OG Description</dt>
                    <dd class="col-sm-9">{{ $tour->og_description ?? '-' }}</dd>

                    <dt class="col-sm-3">OG Image</dt>
                    <dd class="col-sm-9">
                        @if($tour->og_image)
                            <a href="{{ $tour->og_image }}" target="_blank" class="text-primary">View Image</a>
                        @else
                            -
                        @endif
                    </dd>
                </dl>

                <h5 class="mb-3">Twitter Card</h5>
                <dl class="row mb-0">
                    <dt class="col-sm-3">Twitter Title</dt>
                    <dd class="col-sm-9">{{ $tour->twitter_title ?? '-' }}</dd>

                    <dt class="col-sm-3">Twitter Description</dt>
                    <dd class="col-sm-9">{{ $tour->twitter_description ?? '-' }}</dd>

                    <dt class="col-sm-3">Twitter Image</dt>
                    <dd class="col-sm-9">
                        @if($tour->twitter_image)
                            <a href="{{ $tour->twitter_image }}" target="_blank" class="text-primary">View Image</a>
                        @else
                            -
                        @endif
                    </dd>
                </dl>
            </div>
        </div>

        <!-- Structured Data -->
        @if($tour->structured_data_type || $tour->structured_data)
        <div class="card panel-card border-warning border-top mb-3">
            <div class="card-header">
                <h4 class="card-title mb-0">Structured Data (JSON-LD)</h4>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-3">Data Type</dt>
                    <dd class="col-sm-9">{{ $tour->structured_data_type ?? '-' }}</dd>

                    @if($tour->structured_data)
                    <dt class="col-sm-3">JSON-LD Data</dt>
                    <dd class="col-sm-9">
                        <pre class="bg-light p-3 rounded"><code>{{ json_encode($tour->structured_data, JSON_PRETTY_PRINT) }}</code></pre>
                    </dd>
                    @endif
                </dl>
            </div>
        </div>
        @endif

        <!-- Custom Code -->
        @if($tour->header_code || $tour->footer_code)
        <div class="card panel-card border-danger border-top mb-3">
            <div class="card-header">
                <h4 class="card-title mb-0">Custom Code Injection</h4>
            </div>
            <div class="card-body">
                @if($tour->header_code)
                <div class="mb-4">
                    <h5 class="mb-2">Header Code</h5>
                    <pre class="bg-light p-3 rounded"><code>{{ $tour->header_code }}</code></pre>
                </div>
                @endif

                @if($tour->footer_code)
                <div>
                    <h5 class="mb-2">Footer Code</h5>
                    <pre class="bg-light p-3 rounded"><code>{{ $tour->footer_code }}</code></pre>
                </div>
                @endif
            </div>
        </div>
        @endif

    </div>
</div>
@endsection
