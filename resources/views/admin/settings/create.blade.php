@extends('admin.layouts.vertical', ['title' => 'Create Setting', 'subTitle' => 'System'])

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <div>
                <nav aria-label="breadcrumb" class="mb-1">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('root') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="#">System</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.settings.index') }}">Settings</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Create</li>
                    </ol>
                </nav>
                <h3 class="mb-0">Create New Setting</h3>
            </div>
            <div class="d-flex align-items-center gap-2">
                <x-admin.back-button :fallback="route('admin.settings.index')" :classes="['btn', 'btn-soft-secondary']" :merge="false" icon="ri-arrow-go-back-line" />
            </div>
        </div>

        <div class="card panel-card border-primary border-top" data-panel-card>
            <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h4 class="card-title mb-1">Setting Details</h4>
                    <p class="text-muted mb-0">Enter the setting name and value (e.g., holiday dates)</p>
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
                <form method="POST" action="{{ route('admin.settings.store') }}" class="needs-validation" novalidate>
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" 
                            class="form-control @error('name') is-invalid @enderror" 
                            placeholder="e.g., holiday_2025_01_01" required minlength="2" maxlength="255">
                        <div class="invalid-feedback">
                            @error('name')
                                {{ $message }}
                            @else
                                Please provide a valid setting name (minimum 2 characters).
                            @enderror
                        </div>
                        @if(!$errors->has('name'))
                            <div class="valid-feedback">Looks good!</div>
                        @endif
                        <small class="form-text text-muted">Unique identifier for the setting (e.g., holiday_2025_01_01, maintenance_mode)</small>
                    </div>
                    <div class="mb-3">
                        <label for="value" class="form-label">Value</label>
                        <textarea name="value" id="value" rows="5" 
                            class="form-control @error('value') is-invalid @enderror" 
                            placeholder="Enter setting value">{{ old('value') }}</textarea>
                        <div class="invalid-feedback">
                            @error('value')
                                {{ $message }}
                            @enderror
                        </div>
                        <small class="form-text text-muted">The value or description for this setting (e.g., "New Year's Day", "2025-01-01", JSON data, etc.)</small>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-line me-1"></i> Save Setting
                        </button>
                        <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-secondary">
                            <i class="ri-close-line me-1"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        });
    </script>
@endsection
