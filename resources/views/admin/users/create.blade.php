@extends('admin.layouts.vertical', ['title' => 'Create User', 'subTitle' => 'System'])

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
            <div>
                <nav aria-label="breadcrumb" class="mb-0">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="#">System</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Create</li>
                    </ol>
                </nav>
                <h3 class="mb-0">Create New User</h3>
            </div>
            <div class="d-flex align-items-center gap-2">
                <x-admin.back-button :fallback="route('admin.users.index')" :classes="['btn', 'btn-soft-secondary']" :merge="false" icon="ri-arrow-go-back-line" />
            </div>
        </div>

        <div class="card panel-card border-primary border-top" data-panel-card>
            <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h4 class="card-title mb-1">User Details</h4>
                    <p class="text-muted mb-0">Provide basic information and assign roles</p>
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
                <form method="POST" action="{{ route('admin.users.store') }}" class="needs-validation" novalidate>
                    @csrf
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label for="firstname" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" name="firstname" id="firstname" value="{{ old('firstname') }}"
                                    placeholder="e.g, Deepesh"
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
                                <input type="text" name="lastname" id="lastname" value="{{ old('lastname') }}"
                                    placeholder="e.g, Singh"
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
                                <label for="mobile" class="form-label">Mobile <span class="text-danger">*</span></label>
                                <input type="tel" name="mobile" id="mobile" value="{{ old('mobile') }}"
                                    class="form-control @error('mobile') is-invalid @enderror" placeholder="e.g, 9876543210"
                                    required inputmode="numeric" pattern="[0-9]{10}" minlength="10" maxlength="10">
                                <div class="invalid-feedback">
                                    @error('mobile')
                                        {{ $message }}
                                    @else
                                        Mobile number must be exactly 10 digits.
                                    @enderror
                                </div>
                                @if(!$errors->has('mobile'))
                                    <div class="valid-feedback">Looks good!</div>
                                @endif
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="email" value="{{ old('email') }}"
                                    class="form-control @error('email') is-invalid @enderror" placeholder="e.g, example@email.com"
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
                    <div class="mb-3">
                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" id="password" 
                            class="form-control @error('password') is-invalid @enderror" placeholder="e.g, Pass@123#"
                            required minlength="6">
                        <div class="invalid-feedback">
                            @error('password')
                                {{ $message }}
                            @else
                                Please provide a password (minimum 6 characters).
                            @enderror
                        </div>
                        @if(!$errors->has('password'))
                            <div class="valid-feedback">Looks good!</div>
                        @endif
                    </div>
                    @if($canManageRoles)
                    <div class="mb-3">
                        <label class="form-label">Roles:<span class="mt-1 fs-6 text-muted" >Assigne Role to the User.</span></label>
                        <div class="row g-2">
                            @forelse($roles as $role)
                                <div class="col-lg-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->name }}" id="role_{{ $role->id }}">
                                        <label class="form-check-label" for="role_{{ $role->id }}">{{ $role->name }}</label>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <p class="text-muted mb-0">No roles available.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                    @else
                    <div class="mb-3">
                        <label class="form-label">Roles</label>
                        <div class="alert alert-info mb-0">
                            You do not have permission to manage user roles. Roles will be assigned by an administrator.
                        </div>
                    </div>
                    @endif
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" type="submit"><i class="ri-check-line me-1"></i> Save User</button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-soft-secondary"><i class="ri-close-line me-1"></i> Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
@section('script')
<script>
(function() {
    'use strict';
    const form = document.querySelector('.needs-validation');
    if (form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    }
})();
</script>
@endsection


