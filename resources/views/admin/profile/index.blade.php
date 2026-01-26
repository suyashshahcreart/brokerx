@extends('admin.layouts.vertical', ['title' => 'My Profile', 'subTitle' => 'Profile'])

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
            <div>
                <nav aria-label="breadcrumb" class="mb-0">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Profile</li>
                    </ol>
                </nav>
                <h3 class="mb-0">My Profile</h3>
            </div>
            <div class="d-flex align-items-center gap-2">
                <x-admin.back-button :fallback="route('admin.index')" :classes="['btn', 'btn-soft-secondary']" :merge="false" icon="ri-arrow-go-back-line" />
            </div>
        </div>

        <div class="card panel-card border-primary border-top" data-panel-card>
            <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h4 class="card-title mb-1">Profile Information</h4>
                    <p class="text-muted mb-0">Update your account information</p>
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
                <form method="POST" action="{{ route('admin.profile.update') }}" class="needs-validation" novalidate>
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label for="firstname" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" name="firstname" id="firstname" value="{{ old('firstname', $user->firstname) }}"
                                    class="form-control @error('firstname') is-invalid @enderror"
                                    required minlength="2" maxlength="255">
                                <div class="invalid-feedback">
                                    @error('firstname')
                                        {{ $message }}
                                    @else
                                        Please provide a valid first name (minimum 2 characters).
                                    @enderror
                                </div>
                                @if(!$errors->has('firstname'))
                                    <div class="valid-feedback">Looks good!</div>
                                @endif
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label for="lastname" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" name="lastname" id="lastname" value="{{ old('lastname', $user->lastname) }}"
                                    class="form-control @error('lastname') is-invalid @enderror"
                                    required minlength="2" maxlength="255">
                                <div class="invalid-feedback">
                                    @error('lastname')
                                        {{ $message }}
                                    @else
                                        Please provide a valid last name (minimum 2 characters).
                                    @enderror
                                </div>
                                @if(!$errors->has('lastname'))
                                    <div class="valid-feedback">Looks good!</div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name <span class="text-danger">*</span> <small class="text-muted">(Auto-generated)</small></label>
                                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                                    class="form-control @error('name') is-invalid @enderror bg-light"
                                    required minlength="2" maxlength="255" readonly>
                                <div class="invalid-feedback">
                                    @error('name')
                                        {{ $message }}
                                    @else
                                        Please provide a valid full name (minimum 2 characters).
                                    @enderror
                                </div>
                                @if(!$errors->has('name'))
                                    <div class="valid-feedback">Looks good!</div>
                                @endif
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label for="mobile" class="form-label">Mobile</label>
                                <input type="number"  name="mobile" id="mobile" value="{{ old('mobile', $user->mobile) }}"
                                    class="form-control @error('mobile') is-invalid @enderror"
                                    inputmode="numeric" pattern="[0-9]{10}" minlength="10" maxlength="20">
                                <div class="invalid-feedback">
                                    @error('mobile')
                                        {{ $message }}
                                    @else
                                        Please provide a valid mobile number.
                                    @enderror
                                </div>
                                @if(!$errors->has('mobile'))
                                    <div class="valid-feedback">Looks good!</div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                                    class="form-control @error('email') is-invalid @enderror"
                                    required maxlength="255">
                                <div class="invalid-feedback">
                                    @error('email')
                                        {{ $message }}
                                    @else
                                        Please provide a valid email address.
                                    @enderror
                                </div>
                                @if(!$errors->has('email'))
                                    <div class="valid-feedback">Looks good!</div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.profile.change-password') }}" class="btn btn-outline-warning">
                            <i class="ri-lock-password-line me-1"></i> Change Password
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-line me-1"></i> Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const firstNameInput = document.getElementById('firstname');
        const lastNameInput = document.getElementById('lastname');
        const fullNameInput = document.getElementById('name');

        function updateFullName() {
            const firstName = firstNameInput.value.trim();
            const lastName = lastNameInput.value.trim();
            const fullName = (firstName + ' ' + lastName).trim();
            fullNameInput.value = fullName;
            
            // Update validation state
            if (fullName.length >= 2) {
                fullNameInput.classList.remove('is-invalid');
                fullNameInput.classList.add('is-valid');
            } else {
                fullNameInput.classList.remove('is-valid');
            }
        }

        // Update full name when first name or last name changes
        firstNameInput.addEventListener('input', updateFullName);
        lastNameInput.addEventListener('input', updateFullName);

        // Initial update
        updateFullName();
    });
</script>
@endpush
@endsection

