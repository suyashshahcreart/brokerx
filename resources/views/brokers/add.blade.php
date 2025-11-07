@extends('layouts.vertical', ['title' => 'Add Broker', 'subTitle' => 'Real Estate'])

@section('content')

    <form action="{{ route('broker.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-xl-9 col-lg-8">
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
                                           placeholder="Enter License Number" value="{{ old('license_number') }}" required>
                                    @error('license_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="company_name" class="form-label">Company Name</label>
                                    <input type="text" id="company_name" name="company_name" class="form-control @error('company_name') is-invalid @enderror" 
                                           placeholder="Enter Company Name" value="{{ old('company_name') }}">
                                    @error('company_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="position_title" class="form-label">Position Title</label>
                                    <input type="text" id="position_title" name="position_title" class="form-control @error('position_title') is-invalid @enderror" 
                                           placeholder="e.g., Senior Broker, Real Estate Agent" value="{{ old('position_title') }}">
                                    @error('position_title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="years_of_experience" class="form-label">Years of Experience</label>
                                    <input type="number" id="years_of_experience" name="years_of_experience" class="form-control @error('years_of_experience') is-invalid @enderror" 
                                           placeholder="Enter Years" value="{{ old('years_of_experience') }}" min="0">
                                    @error('years_of_experience')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="mb-3">
                                    <label for="commission_rate" class="form-label">Commission Rate (%)</label>
                                    <input type="number" id="commission_rate" name="commission_rate" class="form-control @error('commission_rate') is-invalid @enderror" 
                                           placeholder="e.g., 2.5" value="{{ old('commission_rate') }}" min="0" max="100" step="0.01">
                                    @error('commission_rate')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="mb-3">
                                    <label for="bio" class="form-label">Bio / About</label>
                                    <textarea class="form-control @error('bio') is-invalid @enderror" id="bio" name="bio" rows="4" 
                                              placeholder="Tell us about yourself and your experience..." maxlength="1000">{{ old('bio') }}</textarea>
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
                                           placeholder="+1 234 567 8900" value="{{ old('phone_number') }}">
                                    @error('phone_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="whatsapp_number" class="form-label">WhatsApp Number</label>
                                    <input type="text" id="whatsapp_number" name="whatsapp_number" class="form-control @error('whatsapp_number') is-invalid @enderror" 
                                           placeholder="+1 234 567 8900" value="{{ old('whatsapp_number') }}">
                                    @error('whatsapp_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="2"
                                              placeholder="Enter full address">{{ old('address') }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <label for="city" class="form-label">City</label>
                                    <input type="text" id="city" name="city" class="form-control @error('city') is-invalid @enderror" 
                                           placeholder="Enter City" value="{{ old('city') }}">
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <label for="state" class="form-label">State</label>
                                    <input type="text" id="state" name="state" class="form-control @error('state') is-invalid @enderror" 
                                           placeholder="Enter State" value="{{ old('state') }}">
                                    @error('state')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <label for="country" class="form-label">Country</label>
                                    <input type="text" id="country" name="country" class="form-control @error('country') is-invalid @enderror" 
                                           placeholder="Enter Country" value="{{ old('country') }}">
                                    @error('country')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="mb-3">
                                    <label for="pin_code" class="form-label">Pin Code / Zip Code</label>
                                    <input type="text" id="pin_code" name="pin_code" class="form-control @error('pin_code') is-invalid @enderror" 
                                           placeholder="Enter Pin/Zip Code" value="{{ old('pin_code') }}">
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
                                           placeholder="https://facebook.com/username" value="{{ old('social_links.facebook') }}">
                                    @error('social_links.facebook')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="instagram_url" class="form-label">Instagram URL</label>
                                    <input type="url" id="instagram_url" name="social_links[instagram]" class="form-control @error('social_links.instagram') is-invalid @enderror" 
                                           placeholder="https://instagram.com/username" value="{{ old('social_links.instagram') }}">
                                    @error('social_links.instagram')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="twitter_url" class="form-label">Twitter/X URL</label>
                                    <input type="url" id="twitter_url" name="social_links[twitter]" class="form-control @error('social_links.twitter') is-invalid @enderror" 
                                           placeholder="https://twitter.com/username" value="{{ old('social_links.twitter') }}">
                                    @error('social_links.twitter')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="linkedin_url" class="form-label">LinkedIn URL</label>
                                    <input type="url" id="linkedin_url" name="social_links[linkedin]" class="form-control @error('social_links.linkedin') is-invalid @enderror" 
                                           placeholder="https://linkedin.com/in/username" value="{{ old('social_links.linkedin') }}">
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
                                    <input type="file" id="profile_image" name="profile_image" class="form-control @error('profile_image') is-invalid @enderror" 
                                           accept="image/jpeg,image/png,image/jpg,image/gif">
                                    <small class="text-muted">Max size: 2MB. Formats: JPEG, PNG, JPG, GIF</small>
                                    @error('profile_image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="cover_image" class="form-label">Cover Image</label>
                                    <input type="file" id="cover_image" name="cover_image" class="form-control @error('cover_image') is-invalid @enderror" 
                                           accept="image/jpeg,image/png,image/jpg,image/gif">
                                    <small class="text-muted">Max size: 2MB. Formats: JPEG, PNG, JPG, GIF</small>
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
                            <button type="submit" class="btn btn-primary w-100">Create Broker</button>
                        </div>
                        <div class="col-lg-2">
                            <a href="/" class="btn btn-danger w-100">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

@endsection