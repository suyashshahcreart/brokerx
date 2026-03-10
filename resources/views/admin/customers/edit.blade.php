@extends('admin.layouts.vertical', ['title' => 'Edit Customer', 'subTitle' => 'System'])

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
            <div>
                <nav aria-label="breadcrumb" class="mb-0">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="#">System</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.customer.index') }}">Customer</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit</li>
                    </ol>
                </nav>
                <h3 class="mb-0">Edit Customer</h3>
            </div>
            <div class="d-flex align-items-center gap-2">
                <x-admin.back-button :fallback="route('admin.customer.index')" :classes="['btn', 'btn-soft-secondary']" :merge="false" icon="ri-arrow-go-back-line" />
            </div>
        </div>

        <div class="card panel-card border-primary border-top" data-panel-card>
            <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h4 class="card-title mb-1">Customer Details</h4>
                    <p class="text-muted mb-0">Update account information</p>
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
                <!-- Customer details form -->
                <form method="POST" action="{{ route('admin.customer.update', $customer) }}" class="needs-validation" novalidate enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label for="firstname" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" name="firstname" id="firstname" value="{{ old('firstname',$customer->firstname) }}"
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
                                <input type="text" name="lastname" id="lastname" value="{{ old('lastname',$customer->lastname) }}"
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
                                <label for="base_mobile" class="form-label">Mobile <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select class="form-select @error('country_id') is-invalid @enderror" id="country_id" name="country_id" style="max-width: 140px;" required>
                                        <option value="">Country</option>
                                        @foreach($countries as $country)
                                            <option value="{{ $country->id }}" @selected($defaultCountryId == $country->id)>
                                                {{ $country->name }} ({{ $country->dial_code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="tel" name="base_mobile" id="base_mobile" value="{{ old('base_mobile', $customer->base_mobile ?? $customer->mobile) }}"
                                        class="form-control @error('base_mobile') is-invalid @enderror"
                                        required inputmode="numeric" pattern="[0-9]{6,15}" minlength="6" maxlength="15">
                                </div>
                                @error('country_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <div class="invalid-feedback">
                                    @error('base_mobile')
                                        {{ $message }}
                                    @else
                                        Mobile number must be between 6 and 15 digits.
                                    @enderror
                                </div>
                                @if(!$errors->has('base_mobile'))
                                    <div class="valid-feedback">Looks good!</div>
                                @endif
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="email" value="{{ old('email',$customer->email) }}"
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
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label for="slug" class="form-label">Slug </label>
                                <input type="text" name="slug" id="slug" value="{{ old('slug',$customer->slug) }}"
                                    class="form-control @error('slug') is-invalid @enderror" placeholder="unique-identifier">
                                <div class="invalid-feedback">
                                    @error('slug')
                                        {{ $message }}
                                    @else
                                        Slug must consist of letters, numbers, dashes or underscores and be unique.
                                    @enderror
                                </div>
                                @if(!$errors->has('slug'))
                                    <div class="valid-feedback">Looks good!</div>
                                @endif
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label for="company_name" class="form-label">Company Name</label>
                                <input type="text" name="company_name" id="company_name" value="{{ old('company_name',$customer->company_name) }}"
                                    class="form-control @error('company_name') is-invalid @enderror" maxlength="255">
                                <div class="invalid-feedback">
                                    @error('company_name')
                                        {{ $message }}
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label for="designation" class="form-label">Designation</label>
                                <input type="text" name="designation" id="designation" value="{{ old('designation',$customer->designation) }}"
                                    class="form-control @error('designation') is-invalid @enderror" maxlength="255">
                                <div class="invalid-feedback">
                                    @error('designation')
                                        {{ $message }}
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label for="company_website" class="form-label">Company Website</label>
                                <input type="url" name="company_website" id="company_website" value="{{ old('company_website',$customer->company_website) }}"
                                    class="form-control @error('company_website') is-invalid @enderror" maxlength="255">
                                <div class="invalid-feedback">
                                    @error('company_website')
                                        {{ $message }}
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label for="tag_line" class="form-label">Tag Line</label>
                                <input type="text" name="tag_line" id="tag_line" value="{{ old('tag_line',$customer->tag_line) }}"
                                    class="form-control @error('tag_line') is-invalid @enderror" maxlength="255">
                                <div class="invalid-feedback">
                                    @error('tag_line')
                                        {{ $message }}
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label for="social_link" class="form-label">Social Links (JSON)</label>
                                <textarea name="social_link" id="social_link" rows="2" class="form-control @error('social_link') is-invalid @enderror">{{ old('social_link',$customer->social_link ? json_encode($customer->social_link) : '') }}</textarea>
                                <div class="invalid-feedback">
                                    @error('social_link')
                                        {{ $message }}
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label for="profile_photo" class="form-label">Profile Photo</label>
                                <input type="file" name="profile_photo" id="profile_photo" class="form-control @error('profile_photo') is-invalid @enderror">
                                @if($customer->profile_photo)
                                    <img src="{{ Storage::disk('s3')->url($customer->profile_photo) }}" alt="Profile" class="img-thumbnail mt-1" style="max-height:80px;">
                                @endif
                                <div class="invalid-feedback">
                                    @error('profile_photo')
                                        {{ $message }}
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label for="cover_photo" class="form-label">Cover Photo</label>
                                <input type="file" name="cover_photo" id="cover_photo" class="form-control @error('cover_photo') is-invalid @enderror">
                                @if($customer->cover_photo)
                                    <img src="{{ Storage::disk('s3')->url($customer->cover_photo) }}" alt="Cover" class="img-thumbnail mt-1" style="max-height:80px;">
                                @endif
                                <div class="invalid-feedback">
                                    @error('cover_photo')
                                        {{ $message }}
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password <small class="text-muted">(leave blank to keep current)</small></label>
                        <div class="input-group">
                            <input type="password" name="password" id="password" 
                                class="form-control @error('password') is-invalid @enderror" 
                                minlength="6">
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword" tabindex="-1">
                                <i class="ri-eye-off-line" id="togglePasswordIcon"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback">
                            @error('password')
                                {{ $message }}
                            @else
                                Password must be at least 6 characters if provided.
                            @enderror
                        </div>
                        @if(!$errors->has('password'))
                            <div class="valid-feedback">Looks good!</div>
                        @endif
                    </div>
                    </div> <!-- end first card body -->
                </div> <!-- end first card -->

                <!-- end customer form -->
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" type="submit"><i class="ri-save-line me-1"></i> Update Customer</button>
                        <a href="{{ route('admin.customer.index') }}" class="btn btn-soft-secondary"><i class="ri-close-line me-1"></i> Cancel</a>
                    </div>
                </form>

                <!-- SEO settings form (separate) -->
                <form method="POST" action="{{ route('admin.customer.update-seo', $customer) }}" class="needs-validation mt-3" novalidate>
                    @csrf
                    @method('PATCH')
                <div class="card panel-card border-secondary border-top" data-panel-card>
                    <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
                        <div>
                            <h4 class="card-title mb-1">SEO Settings</h4>
                            <p class="text-muted mb-0">Optional meta tags and social information</p>
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
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="meta_title" class="form-label">Meta Title</label>
                                    <input type="text" name="meta_title" id="meta_title" value="{{ old('meta_title',$customer->meta_title) }}"
                                        class="form-control @error('meta_title') is-invalid @enderror" maxlength="255">
                                    <div class="invalid-feedback">
                                        @error('meta_title')
                                            {{ $message }}
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="meta_keywords" class="form-label">Meta Keywords</label>
                                    <input type="text" name="meta_keywords" id="meta_keywords" value="{{ old('meta_keywords',$customer->meta_keywords) }}"
                                        class="form-control @error('meta_keywords') is-invalid @enderror" maxlength="255">
                                    <div class="invalid-feedback">
                                        @error('meta_keywords')
                                            {{ $message }}
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="meta_description" class="form-label">Meta Description</label>
                            <textarea name="meta_description" id="meta_description" rows="3"
                                class="form-control @error('meta_description') is-invalid @enderror">{{ old('meta_description',$customer->meta_description) }}</textarea>
                            <div class="invalid-feedback">
                                @error('meta_description')
                                    {{ $message }}
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="canonical_url" class="form-label">Canonical URL</label>
                                    <input type="url" name="canonical_url" id="canonical_url" value="{{ old('canonical_url',$customer->canonical_url) }}"
                                        class="form-control @error('canonical_url') is-invalid @enderror" maxlength="2048">
                                    <div class="invalid-feedback">
                                        @error('canonical_url')
                                            {{ $message }}
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="meta_robots" class="form-label">Meta Robots</label>
                                    <input type="text" name="meta_robots" id="meta_robots" value="{{ old('meta_robots',$customer->meta_robots) }}"
                                        class="form-control @error('meta_robots') is-invalid @enderror">
                                    <div class="invalid-feedback">
                                        @error('meta_robots')
                                            {{ $message }}
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="twitter_title" class="form-label">Twitter Title</label>
                                    <input type="text" name="twitter_title" id="twitter_title" value="{{ old('twitter_title',$customer->twitter_title) }}"
                                        class="form-control @error('twitter_title') is-invalid @enderror" maxlength="255">
                                    <div class="invalid-feedback">
                                        @error('twitter_title')
                                            {{ $message }}
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="twitter_image" class="form-label">Twitter Image URL</label>
                                    <input type="text" name="twitter_image" id="twitter_image" value="{{ old('twitter_image',$customer->twitter_image) }}"
                                        class="form-control @error('twitter_image') is-invalid @enderror" maxlength="255">
                                    <div class="invalid-feedback">
                                        @error('twitter_image')
                                            {{ $message }}
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="twitter_description" class="form-label">Twitter Description</label>
                            <textarea name="twitter_description" id="twitter_description" rows="2"
                                class="form-control @error('twitter_description') is-invalid @enderror">{{ old('twitter_description',$customer->twitter_description) }}</textarea>
                            <div class="invalid-feedback">
                                @error('twitter_description')
                                    {{ $message }}
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="og_title" class="form-label">OG Title</label>
                                    <input type="text" name="og_title" id="og_title" value="{{ old('og_title',$customer->og_title) }}"
                                        class="form-control @error('og_title') is-invalid @enderror" maxlength="255">
                                    <div class="invalid-feedback">
                                        @error('og_title')
                                            {{ $message }}
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="og_description" class="form-label">OG Description</label>
                                    <textarea name="og_description" id="og_description" rows="2"
                                        class="form-control @error('og_description') is-invalid @enderror">{{ old('og_description',$customer->og_description) }}</textarea>
                                    <div class="invalid-feedback">
                                        @error('og_description')
                                            {{ $message }}
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="header_code" class="form-label">Header Code</label>
                            <textarea name="header_code" id="header_code" rows="3"
                                class="form-control @error('header_code') is-invalid @enderror">{{ old('header_code',$customer->header_code) }}</textarea>
                            <div class="invalid-feedback">
                                @error('header_code')
                                    {{ $message }}
                                @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="footer_code" class="form-label">Footer Code</label>
                            <textarea name="footer_code" id="footer_code" rows="3"
                                class="form-control @error('footer_code') is-invalid @enderror">{{ old('footer_code',$customer->footer_code) }}</textarea>
                            <div class="invalid-feedback">
                                @error('footer_code')
                                    {{ $message }}
                                @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="gtm_tag" class="form-label">GTM Tag</label>
                            <input type="text" name="gtm_tag" id="gtm_tag" value="{{ old('gtm_tag',$customer->gtm_tag) }}"
                                class="form-control @error('gtm_tag') is-invalid @enderror">
                            <div class="invalid-feedback">
                                @error('gtm_tag')
                                    {{ $message }}
                                @enderror
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary" type="submit"><i class="ri-save-line me-1"></i> Update SEO</button>
                            <a href="{{ route('admin.customer.index') }}" class="btn btn-soft-secondary"><i class="ri-close-line me-1"></i> Cancel</a>
                        </div>
                    </div>
                </div> <!-- end seo card -->
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
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
    // Show/hide password toggle
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const togglePasswordIcon = document.getElementById('togglePasswordIcon');
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.type === 'password' ? 'text' : 'password';
            passwordInput.type = type;
            togglePasswordIcon.className = type === 'password' ? 'ri-eye-off-line' : 'ri-eye-line';
        });
    }
    
    // Show success toast on page load
    @if(session('success'))
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '{{ session('success') }}',
                timer: 3000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        } else {
            // Fallback if Swal is not available
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed';
            alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            alertDiv.innerHTML = `
                <i class="ri-check-circle-line me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alertDiv);
            
            setTimeout(() => {
                alertDiv.remove();
            }, 3000);
        }
    @endif
})();
</script>
@endsection


