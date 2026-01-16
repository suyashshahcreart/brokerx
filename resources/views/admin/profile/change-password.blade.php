@extends('admin.layouts.vertical', ['title' => 'Change Password', 'subTitle' => 'Profile'])

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
            <div>
                <nav aria-label="breadcrumb" class="mb-0">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.profile.index') }}">Profile</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Change Password</li>
                    </ol>
                </nav>
                <h3 class="mb-0">Change Password</h3>
            </div>
            <div class="d-flex align-items-center gap-2">
                <x-admin.back-button :fallback="route('admin.profile.index')" :classes="['btn', 'btn-soft-secondary']" :merge="false" icon="ri-arrow-go-back-line" />
            </div>
        </div>

        <div class="card panel-card border-primary border-top" data-panel-card>
            <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h4 class="card-title mb-1">Change Password</h4>
                    <p class="text-muted mb-0">Update your account password</p>
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
                <form method="POST" action="{{ route('admin.profile.update-password') }}" class="needs-validation" novalidate>
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Current Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" name="current_password" id="current_password"
                                        class="form-control @error('current_password') is-invalid @enderror"
                                        required>
                                    <button class="btn btn-outline-secondary" type="button" id="toggleCurrentPassword" tabindex="-1">
                                        <i class="ri-eye-off-line" id="toggleCurrentPasswordIcon"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback">
                                    @error('current_password')
                                        {{ $message }}
                                    @else
                                        Please enter your current password.
                                    @enderror
                                </div>
                                @if(!$errors->has('current_password'))
                                    <div class="valid-feedback">Looks good!</div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="mb-3">
                                <label for="password" class="form-label">New Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" name="password" id="password"
                                        class="form-control @error('password') is-invalid @enderror"
                                        required minlength="8">
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword" tabindex="-1">
                                        <i class="ri-eye-off-line" id="togglePasswordIcon"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback">
                                    @error('password')
                                        {{ $message }}
                                    @else
                                        Password must be at least 8 characters.
                                    @enderror
                                </div>
                                @if(!$errors->has('password'))
                                    <div class="valid-feedback">Looks good!</div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" name="password_confirmation" id="password_confirmation"
                                        class="form-control @error('password_confirmation') is-invalid @enderror"
                                        required minlength="8">
                                    <button class="btn btn-outline-secondary" type="button" id="togglePasswordConfirmation" tabindex="-1">
                                        <i class="ri-eye-off-line" id="togglePasswordConfirmationIcon"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback">
                                    @error('password_confirmation')
                                        {{ $message }}
                                    @else
                                        Please confirm your new password.
                                    @enderror
                                </div>
                                @if(!$errors->has('password_confirmation'))
                                    <div class="valid-feedback">Looks good!</div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.profile.index') }}" class="btn btn-secondary">
                            <i class="ri-close-line me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-line me-1"></i> Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Toggle password visibility
    function setupPasswordToggle(toggleBtnId, inputId, iconId) {
        const toggleBtn = document.getElementById(toggleBtnId);
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        
        if (toggleBtn && input && icon) {
            toggleBtn.addEventListener('click', function() {
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                icon.classList.toggle('ri-eye-off-line');
                icon.classList.toggle('ri-eye-line');
            });
        }
    }

    // Setup all password toggles
    setupPasswordToggle('toggleCurrentPassword', 'current_password', 'toggleCurrentPasswordIcon');
    setupPasswordToggle('togglePassword', 'password', 'togglePasswordIcon');
    setupPasswordToggle('togglePasswordConfirmation', 'password_confirmation', 'togglePasswordConfirmationIcon');
</script>
@endpush
@endsection

