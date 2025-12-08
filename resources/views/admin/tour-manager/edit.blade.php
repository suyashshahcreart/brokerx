@extends('admin.layouts.vertical', ['title' => 'Edit Tour'])

@section('content')
<div class="">
    <div class="row">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                <div>
                    <nav aria-label="breadcrumb" class="mb-0">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('root') }}">Home</a></li>
                            <li class="breadcrumb-item" aria-current="page"><a href="{{ route('admin.tour-manager.index') }}">Tour Management</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Edit</li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $tour->id }}</li>
                        </ol>
                    </nav>
                    <h3 class="mb-0">Tour Management</h3>
                </div>
                <a href="{{ route('admin.tour-manager.index', $tour->booking_id) }}" class="btn btn-secondary">
                        <i class="ri-arrow-left-line me-1"></i> Back to Booking
                </a>
            </div>
        </div>
    </div>
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <form action="{{ route('admin.tour-manager.update', $tour) }}" method="POST" enctype="multipart/form-data" id="tour-edit-form">
                @csrf
                @method('PUT')
                
                <!-- File Upload Card -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Upload Tour Files</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Upload Files <span class="text-danger">*</span></label>
                            <div class="dropzone" id="tour-dropzone">
                                <div class="dz-message needsclick">
                                    <i class="ri-upload-cloud-2-line fs-1 text-muted"></i>
                                    <h4>Drop tour ZIP file here or click to select</h4>
                                    <span class="text-muted">Upload a ZIP file containing tour assets (images, assets, gallery, tiles, index.html, data.json)</span>
                                    <span class="text-muted d-block mt-1"><small>Max 500MB per file | Required: index.html + JSON file + folders (images, assets, gallery, tiles)</small></span>
                                </div>
                            </div>
                            <div id="file-count-display" class="mt-2 text-muted" style="display: none;">
                                0 file(s) selected
                            </div>
                            @error('files')
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Existing Files -->
                        @if($tour->final_json && isset($tour->final_json['files']))
                        <div class="mb-3">
                            <label class="form-label">Existing Files</label>
                            <div class="list-group">
                                @foreach($tour->final_json['files'] as $file)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="ri-file-line me-2"></i>
                                        <span>{{ $file['name'] ?? 'File' }}</span>
                                    </div>
                                    <span class="badge bg-info">{{ $file['size'] ?? '' }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Basic Details Card -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Tour Details</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                   id="title" name="title" value="{{ old('title', $tour->title) }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="4">{{ old('description', $tour->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-select @error('status') is-invalid @enderror" 
                                            id="status" name="status" required>
                                        @foreach($statuses as $statusOption)
                                            <option value="{{ $statusOption }}" {{ old('status', $tour->status) == $statusOption ? 'selected' : '' }}>
                                                {{ ucfirst($statusOption) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="location" class="form-label">Location</label>
                                    <input type="text" class="form-control @error('location') is-invalid @enderror" 
                                           id="location" name="location" value="{{ old('location', $tour->location) }}">
                                    @error('location')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="ri-save-line me-1"></i> Update Tour
                            </button>
                            <a href="{{ route('admin.tour-manager.show', $tour->booking_id) }}" class="btn btn-secondary">
                                <i class="ri-close-line me-1"></i> Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Booking Info -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Booking Information</h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">Booking ID</label>
                        <p class="mb-0 fw-semibold">#{{ $tour->booking_id }}</p>
                    </div>
                    @if($tour->booking)
                        <div class="mb-3">
                            <label class="text-muted small">Customer</label>
                            <p class="mb-0 fw-semibold">{{ $tour->booking->user?->firstname }} {{ $tour->booking->user?->lastname }}</p>
                            <small class="text-muted">{{ $tour->booking->user?->email }}</small>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Property Type</label>
                            <p class="mb-0">{{ $tour->booking->propertyType?->name ?? 'N/A' }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Location</label>
                            <p class="mb-0">{{ $tour->booking->city?->name ?? 'N/A' }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Booking Date</label>
                            <p class="mb-0">{{ $tour->booking->booking_date ? \Carbon\Carbon::parse($tour->booking->booking_date)->format('d M Y') : 'N/A' }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Price</label>
                            <p class="mb-0 fw-semibold">₹{{ number_format($tour->booking->price, 2) }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Tour Info -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Tour Information</h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">Tour ID</label>
                        <p class="mb-0 fw-semibold">#{{ $tour->id }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Current Status</label>
                        <p class="mb-0">
                            @php
                                $statusBadge = [
                                    'draft' => 'secondary',
                                    'published' => 'success',
                                    'archived' => 'warning'
                                ];
                                $color = $statusBadge[$tour->status] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $color }}">{{ ucfirst($tour->status) }}</span>
                        </p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Created At</label>
                        <p class="mb-0">{{ $tour->created_at->format('d M Y, h:i A') }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Last Updated</label>
                        <p class="mb-0">{{ $tour->updated_at->format('d M Y, h:i A') }}</p>
                    </div>
                    @if($tour->revision)
                    <div class="mb-3">
                        <label class="text-muted small">Revision</label>
                        <p class="mb-0">#{{ $tour->revision }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="tour-loading-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); z-index: 9999; justify-content: center; align-items: center;">
    <div style="text-align: center; color: white; max-width: 500px; width: 90%;">
        <div class="spinner-border text-secondary me-3" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <h4 class="mt-3">Uploading and Processing Tour...</h4>
        <p class="text-muted">This may take a few moments. Please don't close this window.</p>
        
        <!-- Folder Processing Display -->
        <div id="folder-processing-container" class="mt-4">
            <div class="card bg-dark border-secondary" style="width: 100%;">
                <div class="card-body">
                    <h6 class="card-title text-white mb-3">Processing Folders</h6>
                    <div id="current-folder-name" class="text-center mb-3 fw-bold text-success" style="font-size: 1.2rem; min-height: 32px;">
                        <!-- Folder name will appear here -->
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div id="folder-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%"></div>
                    </div>
                    <div id="folder-status" class="mt-2 small text-muted text-center">
                        <!-- Status text will appear here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
@vite(['resources/js/pages/tour-manager-edit.js'])
@endsection
