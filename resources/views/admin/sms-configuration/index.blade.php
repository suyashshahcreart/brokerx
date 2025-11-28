@extends('admin.layouts.vertical', ['title' => 'SMS Configuration', 'subTitle' => 'System'])

@section('css')
    
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                <div>
                    <nav aria-label="breadcrumb" class="mb-0">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('root') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="#">System</a></li>
                            <li class="breadcrumb-item active" aria-current="page">SMS Configuration</li>
                        </ol>
                    </nav>
                    <h3 class="mb-0">SMS Gateway Configuration</h3>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <x-admin.back-button :classes="['btn', 'btn-soft-secondary']" :merge="false"
                        icon="ri-arrow-go-back-line" />
                </div>
            </div>
            <div class="col-12">
                <div class="card panel-card border-primary border-top" data-panel-card>
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2 py-2 px-2">
                        <div>
                            <h4 class="card-title mb-0">SMS Gateway Settings</h4>
                            <p class="text-muted mb-0">Configure and manage your SMS gateway providers</p>
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
                    <div class="card-body py-3 px-3">
                        <div class="row g-4">
                            @foreach($gatewayInstances as $gatewayKey => $gateway)
                                <div class="col-md-6 col-lg-4">
                                    <form id="{{ $gatewayKey }}Form" action="{{ route('admin.sms-configuration.update') }}" method="POST"
                                        class="needs-validation" novalidate data-csrf="{{ csrf_token() }}">
                                        @csrf
                                        <div class="card h-100 border {{ $gateway['isActive'] ? 'border-success' : '' }}">
                                            <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="bg-primary text-white rounded p-2" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                                        <strong class="text-white">{{ strtoupper(substr($gateway['name'], 0, 2)) }}</strong>
                                                    </div>
                                                    <div>
                                                        <h5 class="mb-0">{{ $gateway['name'] }}</h5>
                                                        @if($gateway['isActive'])
                                                            <small class="text-success"><i class="ri-checkbox-circle-line"></i> Active</small>
                                                        @else
                                                            <small class="text-muted">Inactive</small>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input gateway-status-toggle" 
                                                        type="checkbox" 
                                                        id="{{ $gatewayKey }}_status" 
                                                        name="sms_gateway_{{ $gatewayKey }}_status" 
                                                        value="1" 
                                                        data-gateway="{{ $gatewayKey }}"
                                                        {{ $gateway['status'] ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="{{ $gatewayKey }}_status"></label>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                @if($gateway['isConfigured'])
                                                    <div class="alert alert-success mb-3 py-2">
                                                        <small><i class="ri-checkbox-circle-line me-1"></i> Gateway is configured and ready to use.</small>
                                                    </div>
                                                @else
                                                    <div class="alert alert-warning mb-3 py-2">
                                                        <small><i class="ri-alert-line me-1"></i> Gateway configuration is incomplete.</small>
                                                    </div>
                                                @endif

                                                @foreach($gateway['configFields'] as $field)
                                                    @php
                                                        $fieldValue = $settings[$field['key']] ?? ($field['default'] ?? '');
                                                        $fieldId = $field['key'];
                                                        $isRequired = $field['required'] ?? false;
                                                    @endphp
                                                    <div class="mb-3">
                                                        <label for="{{ $fieldId }}" class="form-label">
                                                            {{ $field['label'] }}
                                                            @if($isRequired)
                                                                <span class="text-danger {{ $gatewayKey }}-required">*</span>
                                                            @endif
                                                        </label>
                                                        @if($field['type'] === 'select')
                                                            <select name="{{ $field['key'] }}" 
                                                                id="{{ $fieldId }}" 
                                                                class="form-select {{ $isRequired ? 'required' : '' }}"
                                                                {{ $isRequired ? 'required' : '' }}>
                                                                @foreach($field['options'] ?? [] as $optionValue => $optionLabel)
                                                                    <option value="{{ $optionValue }}" {{ $fieldValue == $optionValue ? 'selected' : '' }}>
                                                                        {{ $optionLabel }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        @elseif($field['type'] === 'password')
                                                            <input type="password" 
                                                                name="{{ $field['key'] }}" 
                                                                id="{{ $fieldId }}" 
                                                                value="{{ $fieldValue }}" 
                                                                class="form-control {{ $isRequired ? 'required' : '' }}"
                                                                placeholder="{{ $field['placeholder'] ?? '' }}"
                                                                {{ $isRequired ? 'required' : '' }}>
                                                        @elseif($field['type'] === 'number')
                                                            <input type="number" 
                                                                name="{{ $field['key'] }}" 
                                                                id="{{ $fieldId }}" 
                                                                value="{{ $fieldValue }}" 
                                                                class="form-control {{ $isRequired ? 'required' : '' }}"
                                                                placeholder="{{ $field['placeholder'] ?? '' }}"
                                                                {{ $isRequired ? 'required' : '' }}
                                                                step="{{ $field['step'] ?? '1' }}"
                                                                min="{{ $field['min'] ?? '' }}">
                                                        @else
                                                            <input type="{{ $field['type'] ?? 'text' }}" 
                                                                name="{{ $field['key'] }}" 
                                                                id="{{ $fieldId }}" 
                                                                value="{{ $fieldValue }}" 
                                                                class="form-control {{ $isRequired ? 'required' : '' }}"
                                                                placeholder="{{ $field['placeholder'] ?? '' }}"
                                                                {{ $isRequired ? 'required' : '' }}>
                                                        @endif
                                                        @if(isset($field['help']))
                                                            <small class="form-text text-muted">{{ $field['help'] }}</small>
                                                        @endif
                                                    </div>
                                                @endforeach

                                                @if($gateway['isActive'])
                                                    <div class="alert alert-info mb-3 py-2">
                                                        <small><i class="ri-information-line me-1"></i> This is your active SMS gateway. All SMS will be sent through this gateway.</small>
                                                    </div>
                                                @endif

                                                <div class="d-flex justify-content-between align-items-center">
                                                    <button type="button" 
                                                        class="btn btn-sm btn-outline-primary set-active-gateway-btn"
                                                        data-gateway="{{ $gatewayKey }}"
                                                        {{ $gateway['isActive'] ? 'disabled' : '' }}>
                                                        <i class="ri-check-line me-1"></i> Set as Active
                                                    </button>
                                                    <button type="submit" class="btn btn-primary btn-sm" id="save{{ ucfirst($gatewayKey) }}Btn">
                                                        <i class="ri-save-line me-1"></i> Save
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        // Function to handle form submission
        function handleFormSubmit(form, submitBtn) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                
                // Validate form
                if (!form.checkValidity()) {
                    e.stopPropagation();
                    form.classList.add('was-validated');
                    return;
                }
                
                // Disable submit button and show loading state
                const originalBtnText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="ri-loader-4-line me-1 spin"></i> Saving...';
                
                // Get form data
                const formData = new FormData(form);
                const csrfToken = form.getAttribute('data-csrf');
                
                // Handle checkbox values - if unchecked, set to "0"
                const statusCheckbox = form.querySelector('.gateway-status-toggle');
                if (statusCheckbox) {
                    if (!statusCheckbox.checked) {
                        formData.set(statusCheckbox.name, '0');
                    } else {
                        formData.set(statusCheckbox.name, '1');
                    }
                }
                
                // Add CSRF token to form data if not already present
                if (!formData.has('_token')) {
                    formData.append('_token', csrfToken);
                }
                
                // Make AJAX request
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                })
                .then(async response => {
                    const contentType = response.headers.get('content-type');
                    let data;
                    
                    if (contentType && contentType.includes('application/json')) {
                        data = await response.json();
                    } else {
                        const text = await response.text();
                        throw { 
                            status: response.status, 
                            data: { message: text || 'An error occurred' } 
                        };
                    }
                    
                    if (!response.ok) {
                        throw { status: response.status, data: data };
                    }
                    return data;
                })
                .then(data => {
                    if (data.success) {
                        const gatewayName = form.id.replace('Form', '').toUpperCase();
                        
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: gatewayName + ' configuration updated successfully',
                                timer: 2000,
                                showConfirmButton: false,
                                timerProgressBar: true
                            });
                        } else {
                            alert(gatewayName + ' configuration updated successfully');
                        }
                        
                        // Reload the page after a short delay to show updated values
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        throw { data: data };
                    }
                })
                .catch(error => {
                    let errorMessage = 'An error occurred while updating configuration.';
                    
                    if (error instanceof TypeError && error.message.includes('fetch')) {
                        errorMessage = 'Network error. Please check your internet connection and try again.';
                    } else if (error.data) {
                        if (error.data.message) {
                            errorMessage = error.data.message;
                        } else if (error.data.errors) {
                            const errors = Object.values(error.data.errors).flat();
                            errorMessage = errors.join('<br>');
                        } else if (error.status === 422) {
                            errorMessage = 'Validation error. Please check your input.';
                        } else if (error.status === 500) {
                            errorMessage = 'Server error. Please try again later.';
                        } else if (error.status === 403 || error.status === 401) {
                            errorMessage = 'You do not have permission to perform this action.';
                        }
                    } else if (error.message) {
                        errorMessage = error.message;
                    }
                    
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            html: errorMessage,
                            confirmButtonText: 'OK'
                        });
                    } else {
                        alert(errorMessage);
                    }
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                });
            });
        }

        // Handle set active gateway button
        function handleSetActiveGateway(button) {
            const gatewayKey = button.getAttribute('data-gateway');
            const form = button.closest('form');
            const csrfToken = form.getAttribute('data-csrf');
            
            const formData = new FormData();
            formData.append('active_sms_gateway', gatewayKey);
            formData.append('_token', csrfToken);
            
            button.disabled = true;
            button.innerHTML = '<i class="ri-loader-4-line me-1 spin"></i> Setting...';
            
            fetch('{{ route("admin.sms-configuration.update") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            })
            .then(async response => {
                const data = await response.json();
                if (!response.ok) {
                    throw { status: response.status, data: data };
                }
                return data;
            })
            .then(data => {
                if (data.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: gatewayKey.toUpperCase() + ' is now your active SMS gateway',
                            timer: 2000,
                            showConfirmButton: false,
                            timerProgressBar: true
                        });
                    }
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    throw { data: data };
                }
            })
            .catch(error => {
                let errorMessage = 'Failed to set active gateway.';
                if (error.data && error.data.message) {
                    errorMessage = error.data.message;
                }
                
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: errorMessage,
                        confirmButtonText: 'OK'
                    });
                } else {
                    alert(errorMessage);
                }
                
                button.disabled = false;
                button.innerHTML = '<i class="ri-check-line me-1"></i> Set as Active';
            });
        }

        // Handle gateway status toggle
        function handleGatewayStatusToggle(checkbox) {
            const gatewayKey = checkbox.getAttribute('data-gateway');
            const form = checkbox.closest('form');
            const csrfToken = form.getAttribute('data-csrf');
            
            const formData = new FormData();
            formData.append(checkbox.name, checkbox.checked ? '1' : '0');
            formData.append('_token', csrfToken);
            
            fetch('{{ route("admin.sms-configuration.update") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            })
            .then(async response => {
                const data = await response.json();
                if (!response.ok) {
                    throw { status: response.status, data: data };
                }
                return data;
            })
            .then(data => {
                if (data.success) {
                    // Toggle required fields visibility
                    const requiredFields = form.querySelectorAll('.' + gatewayKey + '-required');
                    requiredFields.forEach(el => {
                        el.style.display = checkbox.checked ? 'inline' : 'none';
                    });
                    
                    const requiredInputs = form.querySelectorAll('.' + gatewayKey + '-required').forEach(span => {
                        const input = span.closest('.mb-3').querySelector('input, select');
                        if (input) {
                            input.required = checkbox.checked;
                        }
                    });
                }
            })
            .catch(error => {
                // Revert checkbox on error
                checkbox.checked = !checkbox.checked;
                
                let errorMessage = 'Failed to update gateway status.';
                if (error.data && error.data.message) {
                    errorMessage = error.data.message;
                }
                
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: errorMessage,
                        timer: 2000,
                        showConfirmButton: false,
                        timerProgressBar: true,
                        toast: true,
                        position: 'top-end'
                    });
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Initialize all gateway forms
            document.querySelectorAll('form[id$="Form"]').forEach(form => {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    handleFormSubmit(form, submitBtn);
                }
            });
            
            // Handle set active gateway buttons
            document.querySelectorAll('.set-active-gateway-btn').forEach(button => {
                button.addEventListener('click', function() {
                    handleSetActiveGateway(this);
                });
            });
            
            // Handle gateway status toggles
            document.querySelectorAll('.gateway-status-toggle').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    handleGatewayStatusToggle(this);
                });
                
                // Initialize required fields visibility
                const gatewayKey = checkbox.getAttribute('data-gateway');
                const requiredFields = checkbox.closest('form').querySelectorAll('.' + gatewayKey + '-required');
                requiredFields.forEach(el => {
                    el.style.display = checkbox.checked ? 'inline' : 'none';
                });
            });
        });
    </script>
    
    <style>
        .spin {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }
        
        .card.border-success {
            border-width: 2px !important;
        }
    </style>
@endsection

