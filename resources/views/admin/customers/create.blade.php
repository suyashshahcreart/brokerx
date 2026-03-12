@extends('admin.layouts.vertical', ['title' => 'Create Customer', 'subTitle' => 'System'])

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
                        <li class="breadcrumb-item active" aria-current="page">Create</li>
                    </ol>
                </nav>
                <h3 class="mb-0">Create New Customer</h3>
            </div>
            <div class="d-flex align-items-center gap-2">
                <x-admin.back-button :fallback="route('admin.customer.index')" :classes="['btn', 'btn-soft-secondary']" :merge="false" icon="ri-arrow-go-back-line" />
            </div>
        </div>

        <div class="card panel-card border-primary border-top" data-panel-card>
            <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h4 class="card-title mb-1">Customer Details</h4>
                    <p class="text-muted mb-0">Provide basic customer information</p>
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
                <form method="POST" action="{{ route('admin.customer.store') }}" class="needs-validation" novalidate enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-4 col-6">
                            <div class="mb-1">
                                <label for="firstname" class="form-label mb-0">First Name <span class="text-danger">*</span></label>
                                <input type="text" name="firstname" id="firstname" value="{{ old('firstname') }}"
                                     class="form-control @error('firstname') is-invalid @enderror" placeholder="e.g, Sanjay"
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
                        <div class="col-md-4 col-6">
                            <div class="mb-1">
                                <label for="lastname" class="form-label mb-0">Last Name <span class="text-danger">*</span></label>
                                <input type="text" name="lastname" id="lastname" value="{{ old('lastname') }}"
                                    class="form-control @error('lastname') is-invalid @enderror" placeholder="e.g, Singh"
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
                        <div class="col-md-4 col-6">
                            <div class="mb-1">
                                <label for="email" class="form-label mb-0">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="email" value="{{ old('email') }}"
                                    class="form-control @error('email') is-invalid @enderror" placeholder="e.g, exmple@email.com"
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
                    <div class="row">
                        <div class="col-md-4 col-6">
                            <div class="mb-1">
                                <label for="base_mobile" class="form-label mb-0">Mobile <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select class="form-select @error('country_id') is-invalid @enderror" id="country_id" name="country_id" style="max-width: 140px;" required>
                                        <option value="">Country</option>
                                        @foreach($countries as $country)
                                            <option value="{{ $country->id }}" @selected($defaultCountryId == $country->id)>
                                                ({{ $country->dial_code }}) {{ $country->name }} 
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="tel" name="base_mobile" id="base_mobile" value="{{ old('base_mobile') }}"
                                        class="form-control @error('base_mobile') is-invalid @enderror" placeholder="e.g, 9876543120"
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
                        <div class="col-md-4 col-6">
                            <div class="mb-1">
                                <label for="company_name" class="form-label mb-0">Company Name</label>
                                <input type="text" name="company_name" id="company_name" value="{{ old('company_name') }}"
                                    class="form-control @error('company_name') is-invalid @enderror" placeholder="e.g, Prop Pik Global" maxlength="255">
                                <div class="invalid-feedback">
                                    @error('company_name')
                                        {{ $message }}
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-6">
                            <div class="mb-1">
                                <label for="slug" class="form-label mb-0">Slug </label>
                                <input type="text" name="slug" id="slug" value="{{ old('slug') }}"
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
                        
                    </div>
                    <div class="row">
                        <div class="col-md-4 col-6">
                            <div class="mb-1">
                                <label for="company_website" class="form-label mb-0">Company Website</label>
                                <input type="url" name="company_website" id="company_website" value="{{ old('company_website') }}"
                                    class="form-control @error('company_website') is-invalid @enderror" placeholder="e.g, https://www.proppik.com" maxlength="255">
                                <div class="invalid-feedback">
                                    @error('company_website')
                                        {{ $message }}
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-6">
                            <div class="mb-1">
                                <label for="tag_line" class="form-label mb-0">Tag Line</label>
                                <input type="text" name="tag_line" id="tag_line" value="{{ old('tag_line') }}"
                                    class="form-control @error('tag_line') is-invalid @enderror" placeholder="e.g, We build tours, not just trips" maxlength="255">
                                <div class="invalid-feedback">
                                    @error('tag_line')
                                        {{ $message }}
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-6">
                            <div class="mb-1">
                                <label for="designation" class="form-label mb-0">Designation</label>
                                <input type="text" name="designation" id="designation" value="{{ old('designation') }}"
                                    class="form-control @error('designation') is-invalid @enderror" placeholder="e.g, Agent, Broker" maxlength="255">
                                <div class="invalid-feedback">
                                    @error('designation')
                                        {{ $message }}
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 col-6">
                            <div class="mb-1">
                                <label for="profile_photo" class="form-label mb-0">Profile Photo</label>
                                <input type="file" name="profile_photo" id="profile_photo" class="form-control @error('profile_photo') is-invalid @enderror" accept="image/*">
                                <div id="profile_photo_preview" class="mt-2 d-none position-relative d-inline-block">
                                    <img id="profile_photo_preview_img" src="" alt="Profile preview" class="img-thumbnail" style="max-height: 120px; max-width: 100%; object-fit: cover; display: block;">
                                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 rounded-circle p-1 photo-remove-btn" id="profile_photo_remove" title="Remove" style="width: 24px; height: 24px; font-size: 12px; line-height: 1;"><i class="ri-close-line"></i></button>
                                </div>
                                <div class="invalid-feedback">
                                    @error('profile_photo')
                                        {{ $message }}
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-6">
                            <div class="mb-1">
                                <label for="cover_photo" class="form-label mb-0">Cover Photo</label>
                                <input type="file" name="cover_photo" id="cover_photo" class="form-control @error('cover_photo') is-invalid @enderror" accept="image/*">
                                <div id="cover_photo_preview" class="mt-2 d-none position-relative d-inline-block">
                                    <img id="cover_photo_preview_img" src="" alt="Cover preview" class="img-thumbnail" style="max-height: 120px; max-width: 100%; object-fit: cover; display: block;">
                                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 rounded-circle p-1 photo-remove-btn" id="cover_photo_remove" title="Remove" style="width: 24px; height: 24px; font-size: 12px; line-height: 1;"><i class="ri-close-line"></i></button>
                                </div>
                                <div class="invalid-feedback">
                                    @error('cover_photo')
                                        {{ $message }}
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-6">
                            <div class="mb-1">
                                <label class="form-label mb-0">Social Links</label>
                                <div id="social-links-container" data-links='{{ json_encode(old('social_link', [])) }}'>
                                    <!-- dynamic rows inserted by JS -->
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="add-social-pair">Add link</button>
                                <div class="invalid-feedback d-block">
                                    @error('social_link')
                                        {{ $message }}
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mt-3">
                            <div class="d-flex gap-2 justify-content-center">
                                <a href="{{ route('admin.customer.index') }}" class="btn btn-soft-secondary"><i class="ri-close-line me-1"></i> Cancel</a>
                                <button class="btn btn-primary" type="submit"><i class="ri-check-line me-1"></i> Save Customer</button>
                            </div>
                        </div>
                    </div>

                    

                    

            </div> <!-- end first card body -->
        </div> <!-- end first card -->

                <!-- SEO settings card -->
                <div class="card panel-card border-secondary border-top mt-3" data-panel-card>
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
                            <div class="col-md-3 col-6">
                                <div class="mb-1">
                                    <label for="meta_title" class="form-label mb-0">Meta Title</label>
                                    <input type="text" name="meta_title" id="meta_title" value="{{ old('meta_title') }}"
                                        class="form-control @error('meta_title') is-invalid @enderror" placeholder="e.g, Meta Title" maxlength="255">
                                    <div class="invalid-feedback">
                                        @error('meta_title')
                                            {{ $message }}
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="mb-1">
                                    <label for="meta_keywords" class="form-label mb-0">Meta Keywords</label>
                                    <input type="text" name="meta_keywords" id="meta_keywords" value="{{ old('meta_keywords') }}"
                                        class="form-control @error('meta_keywords') is-invalid @enderror" placeholder="e.g, keyword1, keyword2, keyword3" maxlength="255">
                                    <div class="invalid-feedback">
                                        @error('meta_keywords')
                                            {{ $message }}
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="mb-1">
                                    <label for="meta_image" class="form-label mb-0">Meta Image URL</label>
                                    <input type="url" name="meta_image" id="meta_image" value="{{ old('meta_image') }}"
                                        placeholder="e.g, https://example.com/image.jpg" class="form-control @error('meta_image') is-invalid @enderror" maxlength="2048">
                                    <div class="invalid-feedback">
                                        @error('meta_image')
                                            {{ $message }}
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="mb-1">
                                    <label for="meta_description" class="form-label mb-0">Meta Description</label>
                                    <textarea name="meta_description" id="meta_description" rows="2" placeholder="e.g, Meta Description"
                                        class="form-control @error('meta_description') is-invalid @enderror">{{ old('meta_description') }}</textarea>
                                    <div class="invalid-feedback">
                                        @error('meta_description')
                                            {{ $message }}
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-9 col-6">
                                <div class="row">
                                    <div class="col-md-6 col-6">
                                        <div class="mb-1">
                                            <label for="og_title" class="form-label mb-0">OG Title</label>
                                            <input type="text" name="og_title" id="og_title" value="{{ old('og_title') }}"
                                                class="form-control @error('og_title') is-invalid @enderror" placeholder="e.g, OG Title" maxlength="255">
                                            <div class="invalid-feedback">
                                                @error('og_title')
                                                    {{ $message }}
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-6">
                                        <div class="mb-1">
                                            <label for="og_image" class="form-label mb-0">OG Image URL</label>
                                            <input type="url" name="og_image" id="og_image" value="{{ old('og_image') }}"
                                                placeholder="e.g, https://example.com/og-image.jpg" class="form-control @error('og_image') is-invalid @enderror" maxlength="2048">
                                            <div class="invalid-feedback">
                                                @error('og_image')
                                                    {{ $message }}
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-6">
                                        <div class="mb-1">
                                            <label for="og_type" class="form-label mb-0">OG Type</label>
                                            <input type="text" name="og_type" id="og_type" value="{{ old('og_type') }}"
                                                placeholder="e.g, website" class="form-control @error('og_type') is-invalid @enderror" maxlength="64">
                                            <small class="text-muted">e.g. website, article</small>
                                            <div class="invalid-feedback">
                                                @error('og_type')
                                                    {{ $message }}
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-6">
                                        <div class="mb-1">
                                            <label for="og_url" class="form-label mb-0">OG URL</label>
                                            <input type="url" name="og_url" id="og_url" value="{{ old('og_url') }}"
                                                placeholder="e.g, https://www.example.com/customer-page" class="form-control @error('og_url') is-invalid @enderror" maxlength="2048">
                                            <div class="invalid-feedback">
                                                @error('og_url')
                                                    {{ $message }}
                                                @enderror
                                            </div>
                                        </div>

                                        
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="mb-1">
                                    <label for="og_description" class="form-label mb-0">OG Description</label>
                                    <textarea name="og_description" id="og_description" rows="4" placeholder="e.g, OG Description"
                                        class="form-control @error('og_description') is-invalid @enderror">{{ old('og_description') }}</textarea>
                                    <div class="invalid-feedback">
                                        @error('og_description')
                                            {{ $message }}
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3 col-6">
                                <div class="mb-1">
                                    <label for="twitter_title" class="form-label mb-0">Twitter Title</label>
                                    <input type="text" name="twitter_title" id="twitter_title" value="{{ old('twitter_title') }}"
                                        class="form-control @error('twitter_title') is-invalid @enderror" placeholder="e.g, Twitter title" maxlength="255">
                                    <div class="invalid-feedback">
                                        @error('twitter_title')
                                            {{ $message }}
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="mb-1">
                                    <label for="twitter_image" class="form-label mb-0">Twitter Image URL</label>
                                    <input type="url" name="twitter_image" id="twitter_image" value="{{ old('twitter_image') }}"
                                        placeholder="e.g, https://example.com/twitter.jpg" class="form-control @error('twitter_image') is-invalid @enderror" maxlength="2048">
                                    <div class="invalid-feedback">
                                        @error('twitter_image')
                                            {{ $message }}
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="mb-1">
                                    <label for="twitter_card" class="form-label mb-0">Twitter Card Type</label>
                                    <input type="text" name="twitter_card" id="twitter_card" value="{{ old('twitter_card') }}"
                                        placeholder="e.g, summary_large_image" class="form-control @error('twitter_card') is-invalid @enderror" maxlength="64">
                                    <small class="text-muted">e.g. summary, summary_large_image, app, player</small>
                                    <div class="invalid-feedback">
                                        @error('twitter_card')
                                            {{ $message }}
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="mb-1">
                                    <label for="twitter_description" class="form-label mb-0">Twitter Description</label>
                                    <textarea name="twitter_description" id="twitter_description" rows="2" placeholder="e.g, Twitter Description"
                                        class="form-control @error('twitter_description') is-invalid @enderror">{{ old('twitter_description') }}</textarea>
                                    <div class="invalid-feedback">
                                        @error('twitter_description')
                                            {{ $message }}
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 col-6">
                                <div class="mb-1">
                                    <label for="canonical_url" class="form-label mb-0">Canonical URL</label>
                                    <input type="url" name="canonical_url" id="canonical_url" value="{{ old('canonical_url') }}"
                                        class="form-control @error('canonical_url') is-invalid @enderror" placeholder="e.g, https://www.proppik.com/" maxlength="2048">
                                    <div class="invalid-feedback">
                                        @error('canonical_url')
                                            {{ $message }}
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="mb-1">
                                    <label for="meta_robots" class="form-label mb-0">Meta Robots</label>
                                    <input type="text" name="meta_robots" id="meta_robots" value="{{ old('meta_robots') }}"
                                        class="form-control @error('meta_robots') is-invalid @enderror" placeholder="e.g, index, follow" maxlength="255">
                                    <div class="invalid-feedback">
                                        @error('meta_robots')
                                            {{ $message }}
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="mb-1">
                                    <label for="gtm_tag" class="form-label mb-0">GTM Tag</label>
                                    <input type="text" name="gtm_tag" id="gtm_tag" value="{{ old('gtm_tag') }}"
                                        class="form-control @error('gtm_tag') is-invalid @enderror" placeholder="e.g, GTM-010129394102" maxlength="64">
                                    <div class="invalid-feedback">
                                        @error('gtm_tag')
                                            {{ $message }}
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 col-6">
                                <div class="mb-1">
                                    <label for="header_code" class="form-label mb-0">Header Code</label>
                                    <textarea name="header_code" id="header_code" rows="3" placeholder="e.g, <meta name='custom' content='value'>"
                                        class="form-control @error('header_code') is-invalid @enderror">{{ old('header_code') }}</textarea>
                                    <div class="invalid-feedback">
                                        @error('header_code')
                                            {{ $message }}
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-6">
                                <div class="mb-1">
                                    <label for="footer_code" class="form-label mb-0">Footer Code</label>
                                    <textarea name="footer_code" id="footer_code" rows="3" placeholder="e.g, <script>console.log('Hello')</script>"
                                        class="form-control @error('footer_code') is-invalid @enderror">{{ old('footer_code') }}</textarea>
                                    <div class="invalid-feedback">
                                        @error('footer_code')
                                            {{ $message }}
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        
                        
                       
                        
                    </div>
                </div> <!-- end seo card -->
                </form>
    </div>
</div>
@endsection
@section('script')
@vite(['resources/js/pages/customer-edit.js'])
<style>
.photo-remove-btn { opacity: 0.9; transition: opacity 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.2); }
.photo-remove-btn:hover { opacity: 1; }
.photo-remove-btn::after { content: 'Remove'; position: absolute; left: 100%; top: 50%; transform: translateY(-50%); margin-left: 6px; background: var(--bs-danger); color: #fff; padding: 3px 8px; border-radius: 4px; font-size: 11px; white-space: nowrap; opacity: 0; pointer-events: none; transition: opacity 0.2s; }
.photo-remove-btn:hover::after { opacity: 1; }
</style>
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

    // Profile Photo preview
    const profilePhotoInput = document.getElementById('profile_photo');
    const profilePhotoPreview = document.getElementById('profile_photo_preview');
    const profilePhotoPreviewImg = document.getElementById('profile_photo_preview_img');
    const profilePhotoRemove = document.getElementById('profile_photo_remove');
    if (profilePhotoInput && profilePhotoPreview) {
        profilePhotoInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    profilePhotoPreviewImg.src = e.target.result;
                    profilePhotoPreview.classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            } else {
                profilePhotoPreview.classList.add('d-none');
                profilePhotoPreviewImg.src = '';
            }
        });
        profilePhotoRemove.addEventListener('click', function() {
            profilePhotoInput.value = '';
            profilePhotoPreviewImg.src = '';
            profilePhotoPreview.classList.add('d-none');
        });
    }

    // Cover Photo preview
    const coverPhotoInput = document.getElementById('cover_photo');
    const coverPhotoPreview = document.getElementById('cover_photo_preview');
    const coverPhotoPreviewImg = document.getElementById('cover_photo_preview_img');
    const coverPhotoRemove = document.getElementById('cover_photo_remove');
    if (coverPhotoInput && coverPhotoPreview) {
        coverPhotoInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    coverPhotoPreviewImg.src = e.target.result;
                    coverPhotoPreview.classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            } else {
                coverPhotoPreview.classList.add('d-none');
                coverPhotoPreviewImg.src = '';
            }
        });
        coverPhotoRemove.addEventListener('click', function() {
            coverPhotoInput.value = '';
            coverPhotoPreviewImg.src = '';
            coverPhotoPreview.classList.add('d-none');
        });
    }
})();
</script>
@endsection


