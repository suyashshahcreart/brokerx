@extends('admin.layouts.vertical', ['title' => 'Settings', 'subTitle' => 'System'])

@section('css')
    <style>
        #holidayContainer {
            min-height: 100px;
            max-height: 400px;
            overflow-y: auto;
        }

        .holiday-card {
            transition: all 0.2s ease;
        }

        .holiday-card:hover {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .holiday-card .btn-danger {
            opacity: 0.7;
            transition: opacity 0.2s ease;
        }

        .holiday-card:hover .btn-danger {
            opacity: 1;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                <div>
                    <nav aria-label="breadcrumb" class="mb-1">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('root') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="#">System</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Settings</li>
                        </ol>
                    </nav>
                    <h3 class="mb-0">Settings Management</h3>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <x-admin.back-button :classes="['btn', 'btn-soft-secondary']" :merge="false"
                        icon="ri-arrow-go-back-line" />
                    @if(!empty($canCreate) && $canCreate)
                        <a href="{{ route('admin.settings.create') }}" class="btn btn-primary" title="Add Setting"
                            data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Add Setting">
                            <i class="ri-add-line me-1"></i> New Setting
                        </a>
                    @endif
                </div>
            </div>
            <div class="col-12">
                <div class="card panel-card border-primary border-top" data-panel-card>
                    <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
                        <div>
                            <h4 class="card-title mb-1">Setting Details</h4>
                            <p class="text-muted mb-0">Enter the setting name and value (e.g., holiday dates)</p>
                        </div>
                        <div class="panel-actions d-flex gap-2">
                            <button type="button" class="btn btn-light border" data-panel-action="collapse"
                                title="Collapse">
                                <i class="ri-arrow-up-s-line"></i>
                            </button>
                            <button type="button" class="btn btn-light border" data-panel-action="fullscreen"
                                title="Fullscreen">
                                <i class="ri-fullscreen-line"></i>
                            </button>
                            <button type="button" class="btn btn-light border" data-panel-action="close" title="Close">
                                <i class="ri-close-line"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="settingsForm" action="{{ route('api.settings.update') }}" method="POST"
                            class="needs-validation" novalidate data-csrf="{{ csrf_token() }}">
                            @csrf
                            <!-- AVALIABLE DAY -->
                            <div class="mb-3">
                                <label for="avaliable_days" class="form-label"> Avaliable Day <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="avaliable_days" id="avaliable_days"
                                    value="{{ $settings['avaliable_days'] ?? '' }}" class="form-control"
                                    placeholder="e.g., 7" required minlength="1" maxlength="255">
                                <small class="form-text text-muted">Number of available days for booking</small>
                            </div>
                            <!-- HOLIDAY DATES -->
                            <div class="mb-3">
                                <label for="avaliable_days" class="form-label"> Holiday <span
                                        class="text-danger">*</span></label>
                                <input type="date" name="holiday" id="holiday"
                                    value="{{ $settings['holiday'] ?? '' }}" class="form-control"
                                    placeholder="e.g., 7" required minlength="1" maxlength="255">
                                <small class="form-text text-muted">Number of available days for booking</small>
                            </div>
                            <!-- // submit buttons -->
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary" id="updateSettingsBtn">
                                    <i class="ri-save-line me-1"></i> Update Settings
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
    </div>
@endsection
@vite(['resources/js/pages/setting-index-calendar.js'])