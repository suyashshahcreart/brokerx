@extends('layouts.vertical', ['title' => 'Edit Broker', 'subTitle' => 'Real Estate'])

@section('content')
    <div class="row">
        <div class="col-xl-9 col-lg-8">
            <!-- User Information Form -->
            <form id="user-form" action="{{ route('user.update', $broker->user->id) }}" method="POST"
                  data-otp-send="{{ route('otp.send') }}"
                  data-otp-verify="{{ route('otp.verify') }}"
                  data-email-otp-send="{{ route('email_otp.send') }}"
                  data-email-otp-verify="{{ route('email_otp.verify') }}">
                @csrf
                @method('PUT')
                
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">User Information</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="firstname" class="form-label">First Name <span class="text-danger">*</span></label>
                                    <input type="text" id="firstname" name="firstname" class="form-control @error('firstname') is-invalid @enderror" 
                                           placeholder="Enter First Name" value="{{ old('firstname', $broker->user->firstname ?? '') }}" required>
                                    @error('firstname')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="lastname" class="form-label">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" id="lastname" name="lastname" class="form-control @error('lastname') is-invalid @enderror" 
                                           placeholder="Enter Last Name" value="{{ old('lastname', $broker->user->lastname ?? '') }}" required>
                                    @error('lastname')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <div class="input-group" id="email-input-group">
                                        <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                               placeholder="Enter Email" value="{{ old('email', $broker->user->email ?? '') }}" 
                                               {{ $broker->user->email_verified_at ? 'readonly' : '' }} required>
                                        @if($broker->user->email_verified_at)
                                            <span class="input-group-text bg-success text-white" id="email-verified-badge">
                                                <i class='bx bx-check-circle me-1'></i>Verified
                                            </span>
                                        @else
                                            <button type="button" class="btn btn-outline-primary" id="btn-verify-email">Verify</button>
                                        @endif
                                    </div>
                                    @error('email')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <div id="email-verify-text" class="form-text d-flex align-items-center gap-1 mt-1"></div>
                                    
                                    @if($broker->user->email_verified_at)
                                        <div id="email-change-after" class="mt-2">
                                            <a href="javascript:void(0)" id="btn-change-email-after" class="text-primary small">
                                                <i class='bx bx-edit'></i> Change Email
                                            </a>
                                        </div>
                                    @endif
                                    
                                    <!-- Email OTP Block (Hidden by default) -->
                                    <div id="email-otp-block" style="display: none;" class="mt-3">
                                        <div class="alert alert-info">
                                            <p class="mb-2"><strong>Enter the 6-digit code sent to:</strong></p>
                                            <p class="mb-2"><strong id="otp-email-display"></strong></p>
                                            <input type="text" id="email-otp-code" class="form-control mb-2" placeholder="Enter 6-digit code" maxlength="6">
                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn btn-sm btn-primary" id="btn-submit-email-otp">Submit OTP</button>
                                                <button type="button" class="btn btn-sm btn-secondary" id="btn-change-email">Change Email</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="mobile" class="form-label">Mobile <span class="text-danger">*</span></label>
                                    <div class="input-group" id="mobile-input-group">
                                        <input type="text" id="mobile" name="mobile" class="form-control @error('mobile') is-invalid @enderror" 
                                               placeholder="Enter Mobile Number" value="{{ old('mobile', $broker->user->mobile ?? '') }}" 
                                               {{ $broker->user->mobile_verified_at ? 'readonly' : '' }} required>
                                        @if($broker->user->mobile_verified_at)
                                            <span class="input-group-text bg-success text-white" id="mobile-verified-badge">
                                                <i class='bx bx-check-circle me-1'></i>Verified
                                            </span>
                                        @else
                                            <button type="button" class="btn btn-outline-primary" id="btn-verify-mobile">Verify</button>
                                        @endif
                                    </div>
                                    @error('mobile')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <div id="mobile-verify-text" class="form-text d-flex align-items-center gap-1 mt-1"></div>
                                    
                                    @if($broker->user->mobile_verified_at)
                                        <div id="mobile-change-after" class="mt-2">
                                            <a href="javascript:void(0)" id="btn-change-mobile-after" class="text-primary small">
                                                <i class='bx bx-edit'></i> Change Mobile
                                            </a>
                                        </div>
                                    @endif
                                    
                                    <!-- Mobile OTP Block (Hidden by default) -->
                                    <div id="otp-block" style="display: none;" class="mt-3">
                                        <div class="alert alert-info">
                                            <p class="mb-2"><strong>Enter the 6-digit code sent to:</strong></p>
                                            <p class="mb-2"><strong id="otp-mobile-display"></strong></p>
                                            <input type="text" id="otp-code" class="form-control mb-2" placeholder="Enter 6-digit code" maxlength="6">
                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn btn-sm btn-primary" id="btn-submit-otp">Submit OTP</button>
                                                <button type="button" class="btn btn-sm btn-secondary" id="btn-change-mobile">Change Mobile</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-primary">Update User Info</button>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Broker Information Form -->
            <form id="broker-form" action="{{ route('broker.update', $broker->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Professional Information -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Professional Information</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="license_number" class="form-label">License Number <span class="text-danger">*</span></label>
                                    <input type="text" id="license_number" name="license_number" class="form-control @error('license_number') is-invalid @enderror" 
                                           placeholder="Enter License Number" value="{{ old('license_number', $broker->license_number) }}" required>
                                    @error('license_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="company_name" class="form-label">Company Name</label>
                                    <input type="text" id="company_name" name="company_name" class="form-control @error('company_name') is-invalid @enderror" 
                                           placeholder="Enter Company Name" value="{{ old('company_name', $broker->company_name) }}">
                                    @error('company_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="position_title" class="form-label">Position Title</label>
                                    <input type="text" id="position_title" name="position_title" class="form-control @error('position_title') is-invalid @enderror" 
                                           placeholder="e.g., Senior Broker, Real Estate Agent" value="{{ old('position_title', $broker->position_title) }}">
                                    @error('position_title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="years_of_experience" class="form-label">Years of Experience</label>
                                    <input type="number" id="years_of_experience" name="years_of_experience" class="form-control @error('years_of_experience') is-invalid @enderror" 
                                           placeholder="Enter Years" value="{{ old('years_of_experience', $broker->years_of_experience) }}" min="0">
                                    @error('years_of_experience')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="mb-3">
                                    <label for="commission_rate" class="form-label">Commission Rate (%)</label>
                                    <input type="number" id="commission_rate" name="commission_rate" class="form-control @error('commission_rate') is-invalid @enderror" 
                                           placeholder="e.g., 2.5" value="{{ old('commission_rate', $broker->commission_rate) }}" min="0" max="100" step="0.01">
                                    @error('commission_rate')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="mb-3">
                                    <label for="bio" class="form-label">Bio / About</label>
                                    <textarea class="form-control @error('bio') is-invalid @enderror" id="bio" name="bio" rows="4" 
                                              placeholder="Tell us about yourself and your experience..." maxlength="1000">{{ old('bio', $broker->bio) }}</textarea>
                                    @error('bio')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Contact Information</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="phone_number" class="form-label">Phone Number</label>
                                    <input type="text" id="phone_number" name="phone_number" class="form-control @error('phone_number') is-invalid @enderror" 
                                           placeholder="+1 234 567 8900" value="{{ old('phone_number', $broker->phone_number) }}">
                                    @error('phone_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="whatsapp_number" class="form-label">WhatsApp Number</label>
                                    <input type="text" id="whatsapp_number" name="whatsapp_number" class="form-control @error('whatsapp_number') is-invalid @enderror" 
                                           placeholder="+1 234 567 8900" value="{{ old('whatsapp_number', $broker->whatsapp_number) }}">
                                    @error('whatsapp_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="2"
                                              placeholder="Enter full address">{{ old('address', $broker->address) }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <label for="city" class="form-label">City</label>
                                    <input type="text" id="city" name="city" class="form-control @error('city') is-invalid @enderror" 
                                           placeholder="Enter City" value="{{ old('city', $broker->city) }}">
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <label for="state" class="form-label">State</label>
                                    <input type="text" id="state" name="state" class="form-control @error('state') is-invalid @enderror" 
                                           placeholder="Enter State" value="{{ old('state', $broker->state) }}">
                                    @error('state')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <label for="country" class="form-label">Country</label>
                                    <input type="text" id="country" name="country" class="form-control @error('country') is-invalid @enderror" 
                                           placeholder="Enter Country" value="{{ old('country', $broker->country) }}">
                                    @error('country')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="mb-3">
                                    <label for="pin_code" class="form-label">Pin Code / Zip Code</label>
                                    <input type="text" id="pin_code" name="pin_code" class="form-control @error('pin_code') is-invalid @enderror" 
                                           placeholder="Enter Pin/Zip Code" value="{{ old('pin_code', $broker->pin_code) }}">
                                    @error('pin_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Social Media Links -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Social Media Links (Optional)</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="facebook_url" class="form-label">Facebook URL</label>
                                    <input type="url" id="facebook_url" name="social_links[facebook]" class="form-control @error('social_links.facebook') is-invalid @enderror" 
                                           placeholder="https://facebook.com/username" value="{{ old('social_links.facebook', $broker->social_links['facebook'] ?? '') }}">
                                    @error('social_links.facebook')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="instagram_url" class="form-label">Instagram URL</label>
                                    <input type="url" id="instagram_url" name="social_links[instagram]" class="form-control @error('social_links.instagram') is-invalid @enderror" 
                                           placeholder="https://instagram.com/username" value="{{ old('social_links.instagram', $broker->social_links['instagram'] ?? '') }}">
                                    @error('social_links.instagram')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="twitter_url" class="form-label">Twitter/X URL</label>
                                    <input type="url" id="twitter_url" name="social_links[twitter]" class="form-control @error('social_links.twitter') is-invalid @enderror" 
                                           placeholder="https://twitter.com/username" value="{{ old('social_links.twitter', $broker->social_links['twitter'] ?? '') }}">
                                    @error('social_links.twitter')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="linkedin_url" class="form-label">LinkedIn URL</label>
                                    <input type="url" id="linkedin_url" name="social_links[linkedin]" class="form-control @error('social_links.linkedin') is-invalid @enderror" 
                                           placeholder="https://linkedin.com/in/username" value="{{ old('social_links.linkedin', $broker->social_links['linkedin'] ?? '') }}">
                                    @error('social_links.linkedin')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Images Upload -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Profile & Cover Images</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="profile_image" class="form-label">Profile Image</label>
                                    @if($broker->profile_image)
                                        <div class="mb-2">
                                            <img src="{{ asset('storage/' . $broker->profile_image) }}" alt="Profile" class="img-thumbnail" style="max-width:150px">
                                        </div>
                                    @endif
                                    <input type="file" id="profile_image" name="profile_image" class="form-control @error('profile_image') is-invalid @enderror" 
                                           accept="image/jpeg,image/png,image/jpg,image/gif">
                                    <small class="text-muted">Leave empty to keep existing. Max size: 2MB. Formats: JPEG, PNG, JPG, GIF</small>
                                    @error('profile_image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="cover_image" class="form-label">Cover Image</label>
                                    @if($broker->cover_image)
                                        <div class="mb-2">
                                            <img src="{{ asset('storage/' . $broker->cover_image) }}" alt="Cover" class="img-thumbnail" style="max-width:250px">
                                        </div>
                                    @endif
                                    <input type="file" id="cover_image" name="cover_image" class="form-control @error('cover_image') is-invalid @enderror" 
                                           accept="image/jpeg,image/png,image/jpg,image/gif">
                                    <small class="text-muted">Leave empty to keep existing. Max size: 2MB. Formats: JPEG, PNG, JPG, GIF</small>
                                    @error('cover_image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="mb-3 rounded">
                    <div class="row justify-content-end g-2">
                        <div class="col-lg-2">
                            <button type="submit" class="btn btn-primary w-100">Update Broker</button>
                        </div>
                        <div class="col-lg-2">
                            <a href="{{ route('root') }}" class="btn btn-danger w-100">Cancel</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('js/broker-edit.js') }}"></script>
@endsection