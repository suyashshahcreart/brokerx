@extends('admin.layouts.vertical', ['title' => 'Create Tour', 'subTitle' => 'Manage'])

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
            <div>
                <nav aria-label="breadcrumb" class="mb-0">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.tours.index') }}">Tours</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Create</li>
                    </ol>
                </nav>
                <h3 class="mb-0">Create Tour</h3>
            </div>
            <div class="d-flex align-items-center gap-2">
                <x-admin.back-button :fallback="route('admin.tours.index')" :classes="['btn', 'btn-soft-secondary']" :merge="false" icon="ri-arrow-go-back-line" />
            </div>
        </div>

        <form method="POST" action="{{ route('admin.tours.store') }}" class="needs-validation" novalidate>
            @csrf
            
            <!-- Basic Information -->
            <div class="card panel-card border-primary border-top mb-3" data-panel-card>
                <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <h4 class="card-title mb-1">Basic Information</h4>
                        <p class="text-muted mb-0">Enter tour details</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="mb-3">
                                <label class="form-label" for="name">Tour Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
                                <div class="invalid-feedback">Please enter tour name.</div>
                                @error('name')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="mb-3">
                                <label class="form-label" for="title">Tour Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" id="title" class="form-control" value="{{ old('title') }}" required>
                                <div class="invalid-feedback">Please enter tour title.</div>
                                @error('title')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="mb-3">
                                <label class="form-label" for="slug">Slug</label>
                                <input type="text" name="slug" id="slug" class="form-control" value="{{ old('slug') }}" placeholder="Auto-generated from title">
                                @error('slug')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="sidebar_logo">Sidebar Logo</label>
                                <input type="file" name="sidebar_logo" id="sidebar_logo" class="form-control" accept="image/*" onchange="previewImage(event, 'sidebar_logo_preview')">
                                <div class="mt-2">
                                    <img id="sidebar_logo_preview" src="" alt="Sidebar Logo" style="max-width: 150px; max-height: 80px; border:1px solid #ddd; background:#fff; padding:2px; display:none;">
                                </div>
                                @error('sidebar_logo')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-4">
                            <div class="mb-3">
                                <label class="form-label" for="location">Location</label>
                                <input type="text" name="location" id="location" class="form-control" value="{{ old('location') }}">
                                @error('location')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="mb-3">
                                <label class="form-label" for="status">Status <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-select" required>
                                    @foreach($statuses as $s)
                                        <option value="{{ $s }}" @selected(old('status', 'draft')==$s)>{{ ucfirst($s) }}</option>
                                    @endforeach
                                </select>
                                @error('status')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="mb-3">
                                <label class="form-label" for="revision">Revision</label>
                                <input type="text" name="revision" id="revision" class="form-control" value="{{ old('revision') }}" placeholder="e.g. v1.0">
                                @error('revision')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="description">Short Description</label>
                        <textarea name="description" id="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                        @error('description')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="content">Full Content</label>
                        <textarea name="content" id="content" class="form-control" rows="6">{{ old('content') }}</textarea>
                        @error('content')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>

                    <div class="row">
                        <div class="col-lg-3">
                            <div class="mb-3">
                                <label class="form-label" for="price">Price (₹)</label>
                                <input type="number" name="price" id="price" class="form-control" value="{{ old('price') }}" step="0.01" min="0">
                                @error('price')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="mb-3">
                                <label class="form-label" for="duration_days">Duration (Days)</label>
                                <input type="number" name="duration_days" id="duration_days" class="form-control" value="{{ old('duration_days') }}" min="1">
                                @error('duration_days')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="mb-3">
                                <label class="form-label" for="start_date">Start Date</label>
                                <input type="date" name="start_date" id="start_date" class="form-control" value="{{ old('start_date') }}">
                                @error('start_date')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="mb-3">
                                <label class="form-label" for="end_date">End Date</label>
                                <input type="date" name="end_date" id="end_date" class="form-control" value="{{ old('end_date') }}">
                                @error('end_date')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label" for="max_participants">Max Participants</label>
                                <input type="number" name="max_participants" id="max_participants" class="form-control" value="{{ old('max_participants') }}" min="1">
                                @error('max_participants')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label" for="featured_image">Featured Image URL</label>
                                <input type="text" name="featured_image" id="featured_image" class="form-control" value="{{ old('featured_image') }}">
                                @error('featured_image')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="final_json">Final JSON Data</label>
                        <textarea name="final_json" id="final_json" class="form-control font-monospace" rows="5" placeholder='{"key": "value"}'>{!! old('final_json') !!}</textarea>
                        <small class="text-muted">Enter valid JSON data for tour configuration</small>
                        @error('final_json')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <!-- SEO Meta Tags -->
            <div class="card panel-card border-success border-top mb-3" data-panel-card>
                <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <h4 class="card-title mb-1">SEO Meta Tags</h4>
                        <p class="text-muted mb-0">Optimize for search engines</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label" for="meta_title">Meta Title</label>
                                <input type="text" name="meta_title" id="meta_title" class="form-control" value="{{ old('meta_title') }}">
                                @error('meta_title')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label" for="meta_keywords">Meta Keywords</label>
                                <input type="text" name="meta_keywords" id="meta_keywords" class="form-control" value="{{ old('meta_keywords') }}">
                                @error('meta_keywords')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="meta_description">Meta Description</label>
                        <textarea name="meta_description" id="meta_description" class="form-control" rows="2">{{ old('meta_description') }}</textarea>
                        @error('meta_description')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label" for="canonical_url">Canonical URL</label>
                                <input type="url" name="canonical_url" id="canonical_url" class="form-control" value="{{ old('canonical_url') }}">
                                @error('canonical_url')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label" for="meta_robots">Meta Robots</label>
                                <input type="text" name="meta_robots" id="meta_robots" class="form-control" value="{{ old('meta_robots', 'index, follow') }}" placeholder="index, follow">
                                @error('meta_robots')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Open Graph / Social Media -->
            <div class="card panel-card border-info border-top mb-3" data-panel-card>
                <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <h4 class="card-title mb-1">Open Graph / Social Media</h4>
                        <p class="text-muted mb-0">Configure social sharing preview</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label" for="og_title">OG Title</label>
                                <input type="text" name="og_title" id="og_title" class="form-control" value="{{ old('og_title') }}">
                                @error('og_title')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label" for="og_image">OG Image URL</label>
                                <input type="text" name="og_image" id="og_image" class="form-control" value="{{ old('og_image') }}">
                                @error('og_image')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="og_description">OG Description</label>
                        <textarea name="og_description" id="og_description" class="form-control" rows="2">{{ old('og_description') }}</textarea>
                        @error('og_description')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>

                    <h5 class="mt-4 mb-3">Twitter Card</h5>
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label" for="twitter_title">Twitter Title</label>
                                <input type="text" name="twitter_title" id="twitter_title" class="form-control" value="{{ old('twitter_title') }}">
                                @error('twitter_title')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label" for="twitter_image">Twitter Image URL</label>
                                <input type="text" name="twitter_image" id="twitter_image" class="form-control" value="{{ old('twitter_image') }}">
                                @error('twitter_image')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="twitter_description">Twitter Description</label>
                        <textarea name="twitter_description" id="twitter_description" class="form-control" rows="2">{{ old('twitter_description') }}</textarea>
                        @error('twitter_description')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <!-- Structured Data -->
            <div class="card panel-card border-warning border-top mb-3" data-panel-card>
                <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <h4 class="card-title mb-1">Structured Data (JSON-LD)</h4>
                        <p class="text-muted mb-0">Rich snippets for search results</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label" for="structured_data_type">Structured Data Type</label>
                                <select name="structured_data_type" id="structured_data_type" class="form-select">
                                    <option value="">Select type</option>
                                    @foreach($structuredDataTypes as $type)
                                        <option value="{{ $type }}" @selected(old('structured_data_type')==$type)>{{ $type }}</option>
                                    @endforeach
                                </select>
                                @error('structured_data_type')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="structured_data">Structured Data JSON</label>
                        <textarea name="structured_data" id="structured_data" class="form-control font-monospace" rows="5" placeholder='{"@context": "https://schema.org", "@type": "TouristAttraction"}'>{{ old('structured_data') }}</textarea>
                        <small class="text-muted">Enter valid JSON-LD structured data</small>
                        @error('structured_data')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <!-- Custom Code -->
            <div class="card panel-card border-danger border-top mb-3" data-panel-card>
                <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <h4 class="card-title mb-1">Custom Code Injection</h4>
                        <p class="text-muted mb-0">Add custom HTML, CSS, or JavaScript</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label" for="header_code">Header Code (before &lt;/head&gt;)</label>
                        <textarea name="header_code" id="header_code" class="form-control font-monospace" rows="4">{{ old('header_code') }}</textarea>
                        <small class="text-muted">Custom HTML, CSS, or scripts to inject in the header</small>
                        @error('header_code')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="footer_code">Footer Code (before &lt;/body&gt;)</label>
                        <textarea name="footer_code" id="footer_code" class="form-control font-monospace" rows="4">{{ old('footer_code') }}</textarea>
                        <small class="text-muted">Custom HTML, CSS, or scripts to inject in the footer</small>
                        @error('footer_code')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.tours.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-line me-1"></i> Create Tour
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-generate slug from title
    document.getElementById('title').addEventListener('input', function() {
        const title = this.value;
        const slugField = document.getElementById('slug');
        if (!slugField.dataset.userModified) {
            slugField.value = title
                .toLowerCase()
                .replace(/[^\w\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim();
        }
    });

    document.getElementById('slug').addEventListener('input', function() {
        this.dataset.userModified = 'true';
    });

    // Form validation
    (function() {
        'use strict'
        var forms = document.querySelectorAll('.needs-validation')
        Array.prototype.slice.call(forms).forEach(function(form) {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
    })()
</script>
@endpush
