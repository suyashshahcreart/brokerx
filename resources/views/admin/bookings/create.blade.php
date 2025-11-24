@extends('admin.layouts.vertical', ['title' => 'Create Booking', 'subTitle' => 'Property'])

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
            <div>
                <nav aria-label="breadcrumb" class="mb-0">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('root') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.bookings.index') }}">Bookings</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Create</li>
                    </ol>
                </nav>
                <h3 class="mb-0">Create Booking</h3>
            </div>
            <div class="d-flex align-items-center gap-2">
                <x-admin.back-button :fallback="route('admin.bookings.index')" :classes="['btn', 'btn-soft-secondary']" :merge="false" icon="ri-arrow-go-back-line" />
            </div>
        </div>

        <div class="card panel-card border-primary border-top" data-panel-card>
            <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h4 class="card-title mb-1">Booking Details</h4>
                    <p class="text-muted mb-0">Fill in property and user details</p>
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
                <form method="POST" action="{{ route('admin.bookings.store') }}" class="needs-validation" novalidate>
                    @csrf
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="mb-3">
                                <label class="form-label" for="user_id">User <span class="text-danger">*</span></label>
                                <select name="user_id" id="user_id" class="form-select" required>
                                    <option value="">Select user</option>
                                    @foreach($users as $u)
                                        <option value="{{ $u->id }}" @selected(old('user_id')==$u->id)>{{ $u->firstname }} {{ $u->lastname }} ({{ $u->email }})</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">Please select a user.</div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="mb-3">
                                <label class="form-label" for="property_type_id">Property Type <span class="text-danger">*</span></label>
                                <select name="property_type_id" id="property_type_id" class="form-select" required>
                                    <option value="">Select type</option>
                                    @foreach($propertyTypes as $pt)
                                        <option value="{{ $pt->id }}" @selected(old('property_type_id')==$pt->id)>{{ $pt->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">Please select a property type.</div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="mb-3">
                                <label class="form-label" for="property_sub_type_id">Property Subtype <span class="text-danger">*</span></label>
                                <select name="property_sub_type_id" id="property_sub_type_id" class="form-select" required>
                                    <option value="">Select subtype</option>
                                    @foreach($propertySubTypes as $pst)
                                        <option value="{{ $pst->id }}" data-property-type-id="{{ $pst->property_type_id }}" @selected(old('property_sub_type_id')==$pst->id)>{{ $pst->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">Please select a property subtype.</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-3">
                            <div class="mb-3">
                                <label class="form-label" for="bhk_id">BHK</label>
                                <select name="bhk_id" id="bhk_id" class="form-select">
                                    <option value="">Select BHK</option>
                                    @foreach($bhks as $b)
                                        <option value="{{ $b->id }}" @selected(old('bhk_id')==$b->id)>{{ $b->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="mb-3">
                                <label class="form-label" for="city_id">City</label>
                                <select name="city_id" id="city_id" class="form-select">
                                    <option value="">Select city</option>
                                    @foreach($cities as $c)
                                        <option value="{{ $c->id }}" data-state-id="{{ $c->state_id }}" @selected(old('city_id')==$c->id)>{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="mb-3">
                                <label class="form-label" for="state_id">State</label>
                                <select name="state_id" id="state_id" class="form-select">
                                    <option value="">Select state</option>
                                    @foreach($states as $s)
                                        <option value="{{ $s->id }}" @selected(old('state_id')==$s->id)>{{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="mb-3">
                                <label class="form-label" for="furniture_type">Furniture Type</label>
                                <select name="furniture_type" id="furniture_type" class="form-select">
                                    <option value="">Select furniture type</option>
                                    <option value="Furnished" @selected(old('furniture_type')=='Furnished')>Furnished</option>
                                    <option value="Semi-Furnished" @selected(old('furniture_type')=='Semi-Furnished')>Semi-Furnished</option>
                                    <option value="Unfurnished" @selected(old('furniture_type')=='Unfurnished')>Unfurnished</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-4">
                            <div class="mb-3">
                                <label class="form-label" for="area">Area (sq ft) <span class="text-danger">*</span></label>
                                <input type="number" name="area" id="area" class="form-control" value="{{ old('area') }}" required min="0">
                                <div class="invalid-feedback">Please enter a valid area.</div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="mb-3">
                                <label class="form-label" for="price">Price (₹) <span class="text-danger">*</span></label>
                                <input type="number" name="price" id="price" class="form-control" value="{{ old('price') }}" required min="0">
                                <div class="invalid-feedback">Please enter a valid price.</div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="mb-3">
                                <label class="form-label" for="booking_date">Booking Date</label>
                                <input type="date" name="booking_date" id="booking_date" class="form-control" value="{{ old('booking_date') }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-4">
                            <div class="mb-3">
                                <label class="form-label" for="payment_status">Payment Status <span class="text-danger">*</span></label>
                                <select name="payment_status" id="payment_status" class="form-select" required>
                                    @foreach($paymentStatuses as $ps)
                                        <option value="{{ $ps }}" @selected(old('payment_status')==$ps)>{{ ucfirst($ps) }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">Select a valid payment status.</div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="mb-3">
                                <label class="form-label" for="status">Status <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-select" required>
                                    @foreach($statuses as $st)
                                        <option value="{{ $st }}" @selected(old('status')==$st)>{{ ucfirst($st) }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">Select a valid status.</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-3">
                            <div class="mb-3">
                                <label class="form-label" for="house_no">House No</label>
                                <input type="text" name="house_no" id="house_no" class="form-control" value="{{ old('house_no') }}">
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="mb-3">
                                <label class="form-label" for="building">Building</label>
                                <input type="text" name="building" id="building" class="form-control" value="{{ old('building') }}">
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="mb-3">
                                <label class="form-label" for="society_name">Society Name</label>
                                <input type="text" name="society_name" id="society_name" class="form-control" value="{{ old('society_name') }}">
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="mb-3">
                                <label class="form-label" for="address_area">Area / Locality</label>
                                <input type="text" name="address_area" id="address_area" class="form-control" value="{{ old('address_area') }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label" for="landmark">Landmark</label>
                                <input type="text" name="landmark" id="landmark" class="form-control" value="{{ old('landmark') }}">
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="mb-3">
                                <label class="form-label" for="pin_code">PIN Code</label>
                                <input type="text" name="pin_code" id="pin_code" class="form-control" value="{{ old('pin_code') }}">
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="mb-3">
                                <label class="form-label" for="full_address">Full Address</label>
                                <textarea name="full_address" id="full_address" class="form-control" rows="3">{{ old('full_address') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" type="submit"><i class="ri-check-line me-1"></i> Save Booking</button>
                        <a href="{{ route('admin.bookings.index') }}" class="btn btn-soft-secondary"><i class="ri-close-line me-1"></i> Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
    @vite(['resources/js/pages/bookings-form.js'])
@endsection
