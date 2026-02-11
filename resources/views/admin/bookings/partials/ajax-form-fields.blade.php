{{-- AJAX Booking Form Fields - 2 Column Layout with Dynamic Property Selection --}}

<!-- Hidden Fields for Dynamic Data -->
<input type="hidden" id="choice_ownerType" name="owner_type" value="{{ $booking->owner_type ?? '' }}">
<input type="hidden" id="mainPropertyType" name="main_property_type" value="{{ $booking->propertyType->name ?? 'Residential' }}">
<input type="hidden" id="propertySubTypeId" name="property_sub_type_id" value="{{ $booking->property_sub_type_id ?? '' }}">

<!-- User Selection - Full Width -->
<div class="row">
    <div class="col-6">
        <div class="mb-1">
            <label class="form-label" for="user_id">Select Customer <span class="text-danger">*</span></label>
            <select name="user_id" id="user_id" data-choices class="form-select @error('user_id') is-invalid @enderror" required>
                <option value="">Choose a customer...</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" @selected($booking->user_id == $u->id)>
                        {{ $u->firstname }} {{ $u->lastname }} | {{ $u->mobile }}@if($u->email) | {{ $u->email }}@endif
                    </option>
                @endforeach
            </select>
            <div class="invalid-feedback">@error('user_id'){{ $message }}@else Please select a customer.@enderror</div>
        </div>
    </div>
    <!-- <div class="col-3">
        <div class="mb-1">
            <label class="form-label" for="status">Status <span class="text-danger">*</span></label>
            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                <option value="">Select status...</option>
                @foreach($statuses as $status)
                    <option value="{{ $status }}" @selected(old('status', $booking->status) == $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <div class="invalid-feedback">@error('status'){{ $message }}@else Please select a status.@enderror</div>
        </div>
    </div> -->
    <div class="col-3">
        <div class="mb-1">
            <label class="form-label" for="payment_status">Payment Status <span class="text-danger">*</span></label>
            <select name="payment_status" id="payment_status" class="form-select @error('payment_status') is-invalid @enderror" required>
                <option value="">Select payment status...</option>
                @foreach($paymentStatuses as $ps)
                    <option value="{{ $ps }}" @selected(old('payment_status', $booking->payment_status) == $ps)>{{ ucfirst($ps) }}</option>
                @endforeach
            </select>
            <div class="invalid-feedback">@error('payment_status'){{ $message }}@else Please select a payment status.@enderror</div>
        </div>
    </div>
</div>

<div class="row">
    <!-- LEFT COLUMN: Property Details -->
    <div class="col-lg-12">
        <div class="card border bg-light-subtle mb-3">
            <div class="card-header bg-primary-subtle border-primary">
                <h5 class="card-title mb-0"><i class="ri-building-line me-2"></i>Property Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-4">
                        <!-- Owner Type -->
                        <div class="mb-1">
                            <div class="section-title mb-0">Owner Type <span class="text-danger">*</span></div>
                            <div class="d-flex gap" id="ownerTypeContainer">
                                <div class="top-pill {{ ($booking->owner_type ?? '') == 'Owner' ? 'active' : '' }}" data-group="ownerType" data-value="Owner" onclick="topPillClick(this)">
                                    <i class="ri-user-line me-1"></i> Owner
                                </div>
                                <div class="top-pill {{ ($booking->owner_type ?? '') == 'Broker' ? 'active' : '' }}" data-group="ownerType" data-value="Broker" onclick="topPillClick(this)">
                                    <i class="ri-briefcase-line me-1"></i> Broker
                                </div>
                                <div class="top-pill d-none {{ ($booking->owner_type ?? '') == 'Other' ? 'active' : '' }}" data-group="ownerType" data-value="Other" onclick="topPillClick(this)">
                                    <i class="ri-shield-user-line me-1"></i> Other
                                </div>
                            </div>
                            <div id="err-ownerType" class="error">Owner Type is required.</div>
                        </div>
                    </div>
                    <div class="col-8">
                        <!-- Property Type Tabs -->
                        <div class="mb-1">
                            <div class="section-title mb-0">Property Type <span class="text-danger">*</span></div>
                            <div class="d-flex flex-wrap gap mb-0" id="propertyTypeContainer">
                                @php
                                    $currentPropertyTypeName = $booking->propertyType->name ?? 'Residential';
                                @endphp
                                
                                @foreach($propertyTypes as $pt)
                                    @php
                                        $isActive = ($booking->property_type_id == $pt->id);
                                    @endphp
                                    <div
                                        class="top-pill {{ $isActive ? 'active' : '' }}"
                                        id="pill{{ \Illuminate\Support\Str::studly($pt->name) }}"
                                        data-value="{{ $pt->name }}"
                                        data-type-id="{{ $pt->id }}"
                                        data-tab-connect="tab-{{ $pt->name }}"
                                        onclick="handlePropertyTabChange(this)"
                                    >
                                        <i class="{{ $pt->icon }}"></i>
                                        {{ $pt->name }}
                                    </div>
                                @endforeach
                            </div>
                            <div id="err-propertyType" class="error">Property Type is required.</div>
                        </div>
                    </div>
                </div>
                
                @php
                    $currentPropertyType = $booking->propertyType->name ?? 'Residential';
                    $currentPropertySubTypeId = $booking->property_sub_type_id;
                    $currentBhkId = $booking->bhk_id;
                    $currentFurnitureType = $booking->furniture_type;
                    // Normalize furniture type: handle both "Semi Furnished" (space) and "Semi-Furnished" (hyphen)
                    // Also handle "Fully Furnished" -> "Furnished"
                    $normalizedFurnitureType = $currentFurnitureType;
                    if ($currentFurnitureType == 'Semi Furnished') {
                        $normalizedFurnitureType = 'Semi-Furnished';
                    } elseif ($currentFurnitureType == 'Fully Furnished') {
                        $normalizedFurnitureType = 'Furnished';
                    }
                @endphp

                <!-- PROPERTY SUB TYPE AND OTHER DETAILS TAB -->
                <div id="propertySubTypetab" class="{{ $currentPropertyType ? '' : 'hidden' }}">
                    <div class="row">
                        <div class="col-6">
                            <!-- Property Sub Type -->
                            <div class="mb-1">
                                <div class="section-title mb-0">Property Sub Type <span class="text-danger">*</span></div>
                                @foreach($propertySubTypes as $typeId => $subTypes)
                                    <div class="d-wrap {{ $currentPropertyType == collect($propertyTypes)->firstWhere('id', $typeId)->name ? '' : 'hidden' }}" id="tab-{{ collect($propertyTypes)->firstWhere('id', $typeId)->name }}">
                                        @foreach ($subTypes as $subType)
                                            <div class="top-pill {{ $currentPropertyType == collect($propertyTypes)->firstWhere('id', $typeId)->name && $subType->id == $currentPropertySubTypeId ? 'active' : '' }}" data-group="resType" data-value="{{ $subType->id }}" data-subtype-name="{{ $subType->name }}" onclick="selectCard(this)">
                                                <i class="{{ $subType->icon }}"></i>
                                                {{ $subType->name }}
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                                <div id="err-resType" class="error">Property Sub Type is required.</div>
                            </div>
                        </div>
                        <div class="col-6" id="furnishRow">
                            <!-- Furnish Type -->
                            <div class="mb-1">
                                <div class="section-title mb-0">Furnish Type <span class="text-danger">*</span></div>
                                <div class="d-flex flex-wrap gap" id="resFurnishContainer">
                                    <div class="chip {{ $normalizedFurnitureType == 'Furnished' ? 'active' : '' }}" data-group="resFurnish" data-value="Furnished" onclick="selectChip(this)"><i class="ri-sofa-line"></i> Fully Furnished</div>
                                    <div class="chip {{ ($normalizedFurnitureType == 'Semi-Furnished' || $currentFurnitureType == 'Semi Furnished') ? 'active' : '' }}" data-group="resFurnish" data-value="Semi-Furnished" onclick="selectChip(this)"><i class="ri-lightbulb-line"></i> Semi Furnished</div>
                                    <div class="chip {{ $normalizedFurnitureType == 'Unfurnished' ? 'active' : '' }}" data-group="resFurnish" data-value="Unfurnished" onclick="selectChip(this)"><i class="ri-door-line"></i> Unfurnished</div>
                                </div>
                                <div id="err-resFurnish" class="error">Furnish Type is required.</div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12" id="sizeRow">
                            <!-- Size (BHK/RK) -->
                            <div class="mb-1">
                                <div class="section-title mb-0">Size (BHK / RK) <span class="text-danger">*</span></div>
                                <div class="d-flex flex-wrap gap" id="resSizeContainer">
                                    @foreach($bhks as $bhk)
                                        <div class="chip {{ $bhk->id == $currentBhkId ? 'active' : '' }}" data-group="resSize" data-value="{{ $bhk->id }}" onclick="selectChip(this)">{{ $bhk->name }}</div>
                                    @endforeach
                                </div>
                                <div id="err-resSize" class="error">Size (BHK / RK) is required.</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Other Option Details (Hidden by default, shown when "Other" sub type is selected) -->
                    <div class="row" id="otherDetailsRow" style="{{ ($booking->other_option_details) ? 'display: block;' : 'display: none;' }}">
                        <div class="col-12">
                            <div class="mb-1">
                                <div class="section-title mb-0">Other Option Details <span class="text-danger">*</span></div>
                                <textarea name="other_option_details" id="othDesc" class="form-control @error('other_option_details') is-invalid @enderror" rows="3" placeholder="Enter other option details">{{ old('other_option_details', $booking->other_option_details ?? '') }}</textarea>
                                <div id="err-othDesc" class="error @error('other_option_details') show @endif">@error('other_option_details'){{ $message }}@else Other Option Details is required.@enderror</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-6">
                        <!-- Area (Always Visible - Common Field) -->
                        <div class="mb-1">
                            <label class="form-label fw-semibold mb-0" for="area">Super Built-up Area (sq ft) <span class="text-danger">*</span></label>
                            <input type="number" name="area" id="area" class="form-control @error('area') is-invalid @enderror" value="{{ old('area', $booking->area) }}" placeholder="e.g., 1200" required min="1">
                            <div class="invalid-feedback">@error('area'){{ $message }}@else Please enter a valid area.@enderror</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <!-- Price, Dates, Status (Always Visible) -->
                        <div class="mb-1">
                            <label class="form-label fw-semibold mb-0" for="price">Price (₹) <span class="text-danger">*</span> <small class="text-muted">(Auto-calculated)</small></label>
                            <input type="number" name="price" id="price" class="form-control bg-light @error('price') is-invalid @enderror" value="{{ old('price', $booking->price) }}" placeholder="Enter area to calculate" required min="0">
                            <div class="invalid-feedback">@error('price'){{ $message }}@else Please enter a valid price.@enderror</div>
                        </div>
                    </div>
                </div>

                

                
                        
                <!-- Use Company Billing Details Checkbox -->
                <div class="mb-1">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="differentBillingName" name="different_billing_name" {{ ($booking->firm_name || $booking->gst_no) ? 'checked' : '' }}>
                        <label class="form-check-label" for="differentBillingName">
                            Use company billing details
                        </label>
                    </div>
                </div>

                <!-- Company Billing Fields (Hidden by default) -->
                <div id="billingDetailsRow" class="{{ ($booking->firm_name || $booking->gst_no) ? '' : 'hidden' }}">
                    <div class="row">
                        <div class="col-6">
                            <div class="m-0">
                                <div class="section-title m-0">Company Name <span class="text-danger">*</span></div>
                                <input type="text" name="firm_name" id="firmName" class="form-control @error('firm_name') is-invalid @enderror" placeholder="Enter Company Name" value="{{ old('firm_name', $booking->firm_name ?? '') }}">
                                <div id="err-firmName" class="error @error('firm_name') @else hidden @enderror">@error('firm_name'){{ $message }}@else Company Name is required.@enderror</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="m-0">
                                <div class="section-title m-0">GST No <span class="text-danger">*</span></div>
                                <input type="text" name="gst_no" id="gstNo" class="form-control @error('gst_no') is-invalid @enderror" placeholder="Enter GST number" value="{{ old('gst_no', $booking->gst_no ?? '') }}">
                                <div id="err-gstNo" class="error @error('gst_no') @else hidden @enderror">@error('gst_no'){{ $message }}@else GST No is required.@enderror</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- RIGHT COLUMN: Address Details -->
    <div class="col-lg-12">
        <div class="card border bg-light-subtle mb-3">
            <div class="card-header bg-success-subtle border-success">
                <h5 class="card-title mb-0"><i class="ri-map-pin-line me-2"></i>Address Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-4">
                        <!-- House No -->
                        <div class="mb-1">
                            <label class="form-label fw-semibold mb-0" for="house_no">House / Office No <span class="text-danger">*</span></label>
                            <input type="text" name="house_no" id="house_no" class="form-control" value="{{ $booking->house_no }}" placeholder="e.g., H-123, 12A" required>
                            <div class="invalid-feedback">House / Office No is required.</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <!-- Building -->
                        <div class="mb-1">
                            <label class="form-label fw-semibold mb-0" for="building">Society / Building Name <span class="text-danger">*</span></label>
                            <input type="text" name="building" id="building" class="form-control" value="{{ $booking->building }}" placeholder="Building or Tower name" required>
                            <div class="invalid-feedback">Society / Building Name is required.</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <!-- Society Name -->
                        <div class="mb-1">
                            <label class="form-label fw-semibold mb-0" for="society_name">Society / Complex Name</label>
                            <input type="text" name="society_name" id="society_name" class="form-control" value="{{ $booking->society_name }}" placeholder="Society or complex name">
                        </div>
                    </div>
                    <div class="col-4">
                        <!-- Area / Locality -->
                        <div class="mb-1">
                            <label class="form-label fw-semibold mb-0" for="address_area">Area / Locality</label>
                            <input type="text" name="address_area" id="address_area" class="form-control" value="{{ $booking->address_area }}" placeholder="e.g., Vastrapur">
                        </div>
                    </div>
                    <div class="col-4">
                        <!-- Landmark -->
                        <div class="mb-1">
                            <label class="form-label fw-semibold mb-0" for="landmark">Landmark</label>
                            <input type="text" name="landmark" id="landmark" class="form-control" value="{{ $booking->landmark }}" placeholder="Nearby landmark">
                        </div>
                    </div>
                    <div class="col-4">
                        <!-- PIN Code -->
                        <div class="mb-1">
                            <label class="form-label fw-semibold mb-0" for="pin_code">PIN Code <span class="text-danger">*</span></label>
                            <input type="text" name="pin_code" id="pin_code" class="form-control" value="{{ $booking->pin_code }}" placeholder="e.g., 380015" maxlength="6" required>
                            <div class="invalid-feedback">Valid 6-digit PIN Code is required.</div>
                        </div>

                    </div>
                </div>

                <div class="row">
                    <div class="col-4">
                        <div class="row">
                            <div class="col-12">
                                <!-- Country -->
                                <div class="mb-1">
                                    <label class="form-label fw-semibold mb-0" for="country_id">Country <span class="text-danger">*</span> </label>
                                    <select name="country_id" id="country_id" class="form-select" required>
                                        <option value="">Select country</option>
                                        @foreach($countries as $country)
                                            <option value="{{ $country->id }}" @selected($defaultCountryId == $country->id)>{{ $country->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <!-- State -->
                                <div class="mb-1">
                                    <label class="form-label fw-semibold mb-0" for="state_id">State <span class="text-danger">*</span> </label>
                                    <select name="state_id" id="state_id" class="form-select">
                                        <option value="">Select state</option>
                                        @foreach($states as $s)
                                            <option value="{{ $s->id }}" @selected($booking->state_id == $s->id)>{{ $s->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <!-- City -->
                                <div class="mb-1">
                                    <label class="form-label fw-semibold mb-0" for="city_id">City <span class="text-danger">*</span> </label>
                                    <select name="city_id" id="city_id" class="form-select">
                                        <option value="">Select city</option>
                                        @foreach($cities as $c)
                                            <option value="{{ $c->id }}" data-state-id="{{ $c->state_id }}" @selected($booking->city_id == $c->id)>{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>   
                    </div>

                    <div class="col-8">
                        <div class="row">
                            <div class="col-12">
                                <!-- Full Address -->
                                <div class="mb-0">
                                    <label class="form-label fw-semibold mb-0" for="full_address">Full Address <span class="text-danger">*</span></label>
                                    <textarea name="full_address" id="full_address" class="form-control" rows="4" placeholder="Complete address with street details..." required>{{ $booking->full_address }}</textarea>
                                    <div class="invalid-feedback">Full Address is required.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
