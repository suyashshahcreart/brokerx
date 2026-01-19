@extends('admin.layouts.vertical', ['title' => 'Change Password', 'subTitle' => 'Profile'])
@section('styles')
<style>
    .password-requirements {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    .requirement {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #6c757d;
    }
    .requirement i {
        font-size: 0.875rem;
    }
    .requirement.met {
        color: #28a745;
    }
    .requirement.met i {
        color: #28a745;
    }
</style>
@endsection
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
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="ri-check-line me-1"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <div class="d-flex align-items-start gap-2">
                            <i class="ri-error-warning-line fs-5 mt-1"></i>
                            <div>
                                <strong>Please fix the issues below:</strong>
                                <ul class="mb-0 ps-3">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

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
                                <div class="invalid-feedback" id="passwordErrorFeedback">
                                    @error('password')
                                        {{ $message }}
                                    @else
                                        Password must be at least 8 characters.
                                    @enderror
                                </div>
                                @if(!$errors->has('password'))
                                    <div class="valid-feedback" id="passwordValidFeedback">Looks good!</div>
                                @endif
                                
                                <!-- Password Strength Checker -->
                                <div class="mt-2">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="small text-muted">Strength:</span>
                                        <div class="password-strength-bar" style="width: 100px; height: 6px; background: #e9ecef; border-radius: 3px; overflow: hidden;">
                                            <div id="strengthIndicator" style="width: 0%; height: 100%; background: #6c757d; transition: all 0.3s ease; border-radius: 3px;"></div>
                                        </div>
                                        <span id="strengthText" class="small text-muted"></span>
                                    </div>
                                    <div class="password-requirements small">
                                        <div class="requirement" id="reqLength">
                                            <i class="ri-close-circle-line text-danger"></i>
                                            <span>At least 8 characters</span>
                                        </div>
                                        <div class="requirement" id="reqUppercase">
                                            <i class="ri-close-circle-line text-danger"></i>
                                            <span>At least one uppercase letter</span>
                                        </div>
                                        <div class="requirement" id="reqLowercase">
                                            <i class="ri-close-circle-line text-danger"></i>
                                            <span>At least one lowercase letter</span>
                                        </div>
                                        <div class="requirement" id="reqNumber">
                                            <i class="ri-close-circle-line text-danger"></i>
                                            <span>At least one number</span>
                                        </div>
                                        <div class="requirement" id="reqSpecial">
                                            <i class="ri-close-circle-line text-danger"></i>
                                            <span>At least one special character (!@#$%^&*)</span>
                                        </div>
                                    </div>
                                </div>
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
                                <div class="invalid-feedback" id="confirmPasswordErrorFeedback">
                                    @error('password_confirmation')
                                        {{ $message }}
                                    @else
                                        Passwords do not match.
                                    @enderror
                                </div>
                                @if(!$errors->has('password_confirmation'))
                                    <div class="valid-feedback" id="confirmPasswordValidFeedback">Passwords match!</div>
                                @endif
                                <div class="mt-2">
                                    <small id="confirmPasswordMatch" class="text-muted"></small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.profile.index') }}" class="btn btn-secondary">
                            <i class="ri-close-line me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary" id="updatePasswordBtn">
                            <i class="ri-save-line me-1"></i> Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    console.log('loaded');
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

    // Password Strength Checker
    function checkPasswordStrength(password) {
        let strength = 0;
        const checks = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /\d/.test(password),
            special: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)
        };

        // Update requirement indicators
        updateRequirement('reqLength', checks.length);
        updateRequirement('reqUppercase', checks.uppercase);
        updateRequirement('reqLowercase', checks.lowercase);
        updateRequirement('reqNumber', checks.number);
        updateRequirement('reqSpecial', checks.special);

        // Calculate strength
        Object.values(checks).forEach(check => {
            if (check) strength++;
        });

        // Update strength bar and text
        const strengthIndicator = document.getElementById('strengthIndicator');
        const strengthText = document.getElementById('strengthText');
        
        const strengthLevels = [
            { width: 0, text: '', color: '#6c757d' },
            { width: 20, text: 'Very Weak', color: '#dc3545' },
            { width: 40, text: 'Weak', color: '#fd7e14' },
            { width: 60, text: 'Fair', color: '#ffc107' },
            { width: 80, text: 'Good', color: '#17a2b8' },
            { width: 100, text: 'Strong', color: '#28a745' }
        ];

        const level = strengthLevels[strength];
        strengthIndicator.style.width = level.width + '%';
        strengthIndicator.style.background = level.color;
        strengthText.textContent = level.text;
        strengthText.style.color = level.color;

        return strength === 5;
    }

    function updateRequirement(reqId, met) {
        const req = document.getElementById(reqId);
        if (req) {
            const icon = req.querySelector('i');
            if (met) {
                req.classList.add('met');
                icon.classList.remove('ri-close-circle-line');
                icon.classList.add('ri-check-circle-line');
            } else {
                req.classList.remove('met');
                icon.classList.remove('ri-check-circle-line');
                icon.classList.add('ri-close-circle-line');
            }
        }
    }

    // Enable/disable submit button based on match state
    function updateSubmitButton(isMatch) {
        const btn = document.getElementById('updatePasswordBtn');
        if (!btn) return;
        const password = document.getElementById('password').value;
        const confirmation = document.getElementById('password_confirmation').value;
        const shouldDisable = !(isMatch && password.length > 0 && confirmation.length > 0);
        btn.disabled = shouldDisable;
        btn.classList.toggle('disabled', shouldDisable);
    }

    // Validate password confirmation
    function validatePasswordConfirmation() {
        const password = document.getElementById('password').value;
        const confirmation = document.getElementById('password_confirmation').value;
        const confirmField = document.getElementById('password_confirmation');
        const matchText = document.getElementById('confirmPasswordMatch');

        if (confirmation.length === 0) {
            matchText.textContent = '';
            confirmField.classList.remove('is-invalid', 'is-valid');
            updateSubmitButton(false);
            return false;
        }

        const isMatch = password === confirmation;
        if (isMatch) {
            confirmField.classList.remove('is-invalid');
            confirmField.classList.add('is-valid');
            matchText.innerHTML = '<i class="ri-check-circle-line text-success me-1"></i> <span class="text-success">Passwords match!</span>';
        } else {
            confirmField.classList.remove('is-valid');
            confirmField.classList.add('is-invalid');
            matchText.innerHTML = '<i class="ri-close-circle-line text-danger me-1"></i> <span class="text-danger">Passwords do not match!</span>';
        }

        updateSubmitButton(isMatch);
        return isMatch;
    }

    // Setup all password toggles
    setupPasswordToggle('toggleCurrentPassword', 'current_password', 'toggleCurrentPasswordIcon');
    setupPasswordToggle('togglePassword', 'password', 'togglePasswordIcon');
    setupPasswordToggle('togglePasswordConfirmation', 'password_confirmation', 'togglePasswordConfirmationIcon');

    // Add event listeners for real-time validation
    document.getElementById('password').addEventListener('input', function() {
        checkPasswordStrength(this.value);
        validatePasswordConfirmation();
    });

    document.getElementById('password_confirmation').addEventListener('input', function() {
        validatePasswordConfirmation();
    });

    // Initial check on page load
    document.addEventListener('DOMContentLoaded', function() {
        const password = document.getElementById('password').value;
        updateSubmitButton(false);
        if (password) {
            checkPasswordStrength(password);
            validatePasswordConfirmation();
        }
    });
</script>
@endsection