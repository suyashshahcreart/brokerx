@extends('frontend.layouts.base', ['title' => 'My Profile - PROP PIK'])

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('proppik/assets/css/profile_page.css') }}">
@endsection

@section('content')
    <!-- Profile Header (dashboard style) -->
    <section class="py-5 bg-primary text-white mt-5">
        <div class="container pt-5 pb-2">
            <div class="row align-items-center g-3">
                <div class="col-lg-8">
                    <p class="text-uppercase fw-bold small mb-2">My Account</p>
                    <h1 class="display-6 fw-bold mb-2" id="profileHeroName">{{ $user->firstname }} {{ $user->lastname }}</h1>
                    <p class="mb-0 opacity-75">Manage your profile details and view your recent bookings.</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <div class="d-flex gap-2 justify-content-lg-end flex-wrap">
                        <a href="{{ route('frontend.booking-dashboard') }}" class="btn btn-light fw-semibold">
                            <i class="fa-solid fa-table-cells me-2"></i>My Bookings
                        </a>
                        <a href="{{ route('frontend.setup') }}" class="btn btn-outline-light fw-semibold">
                            <i class="fa-solid fa-plus me-2"></i>New Booking
                        </a>
                    </div>
                    <div class="mt-2 small opacity-75"> Since {{ $user->created_at->format('F Y') }}</div>
                </div>
            </div>
        </div>
    </section>

    <div class="page bg-setup-form py-4 container pp-profile-wrap">
        <div class="pp-profile-shell">
            <!-- Left: Summary -->
            <div class="profile-card pp-side-card">
                <div class="text-center">
                    <div class="pp-profile-avatar">
                        {{ strtoupper(substr($user->firstname, 0, 1) . substr($user->lastname, 0, 1)) }}
                    </div>
                    <div class="fw-bold" style="font-family: var(--font-heading); font-size: 1.2rem;">
                        {{ $user->firstname }} {{ $user->lastname }}
                    </div>
                    <div class="text-muted small">Member since {{ $user->created_at->format('F Y') }}</div>

                    <div class="d-flex flex-wrap gap-2 justify-content-center mt-3">
                        <span class="pp-chip">
                            <i class="fa-solid fa-envelope"></i>
                            @if(empty($user->email))
                                Email Not Added
                            @else
                                {{ $user->email_verified_at ? 'Email Verified' : 'Email Not Verified' }}
                            @endif
                        </span>
                        <span class="pp-chip">
                            <i class="fa-solid fa-phone"></i>
                            {{ $user->mobile_verified_at ? 'Mobile Verified' : 'Mobile Not Verified' }}
                        </span>
                    </div>
                </div>

                <hr class="my-4" style="border-color: rgba(31,57,90,0.14);">

                <div class="pp-stat-grid">
                    <div class="pp-stat">
                        <div class="k">{{ $bookingCount }}</div>
                        <div class="l">Total Bookings</div>
                    </div>
                    <div class="pp-stat">
                        <div class="k">{{ $bookings->where('payment_status', 'paid')->count() }}</div>
                        <div class="l">Paid Bookings</div>
                    </div>
                </div>

                <div class="mt-3 d-grid gap-2">
                    <a href="{{ route('frontend.booking-dashboard') }}" class="btn btn-primary">
                        <i class="fa-solid fa-table-cells me-2"></i>My Bookings
                    </a>
                    <a href="{{ route('frontend.setup') }}" class="btn btn-outline-primary">
                        <i class="fa-solid fa-plus me-2"></i>New Booking
                    </a>
                </div>
            </div>

            <!-- Right: Tabs -->
            <div>
                <div class="profile-card">
                    <ul class="nav nav-pills gap-2 pp-tabs" id="profileTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-profile" data-bs-toggle="pill" data-bs-target="#pane-profile" type="button" role="tab">
                                <i class="fa-regular fa-user me-2"></i>Profile Details
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-bookings" data-bs-toggle="pill" data-bs-target="#pane-bookings" type="button" role="tab">
                                <i class="fa-regular fa-rectangle-list me-2"></i>Recent Bookings
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content mt-4">
                        <!-- Profile Details -->
                        <div class="tab-pane fade show active" id="pane-profile" role="tabpanel" aria-labelledby="tab-profile" tabindex="0">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="mb-0">
                                    <i class="fa-regular fa-id-card me-2"></i>Personal Information
                                </h4>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="editProfileBtn">
                                    <i class="fa-regular fa-pen-to-square me-1"></i>Edit
                                </button>
                            </div>

                            <div class="alert-success" id="successAlert">
                                <i class="fa-solid fa-circle-check me-1"></i>Profile updated successfully!
                            </div>

                            <div class="info-item">
                                <div class="info-item-header d-flex justify-content-between align-items-center">
                                    <div class="info-label">Full Name</div>
                                </div>
                                <div class="view-mode" id="nameView">
                                    <div class="info-value">{{ $user->firstname }} {{ $user->lastname }}</div>
                                </div>
                                <div class="edit-form-group d-none" id="nameEdit">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="firstNameInput" class="form-label small text-muted mb-1">First Name</label>
                                            <input type="text" class="form-control form-control-edit" id="firstNameInput"
                                                   value="{{ $user->firstname }}" placeholder="Enter first name">
                                            <div class="error-message" id="firstNameError"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="lastNameInput" class="form-label small text-muted mb-1">Last Name</label>
                                            <input type="text" class="form-control form-control-edit" id="lastNameInput"
                                                   value="{{ $user->lastname }}" placeholder="Enter last name">
                                            <div class="error-message" id="lastNameError"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="info-item">
                                <div class="info-item-header d-flex justify-content-between align-items-center">
                                    <div class="info-label">Email Address</div>
                                </div>
                                <div class="view-mode" id="emailView">
                                    <div class="info-value">
                                        {{ $user->email }}
                                        @if(empty($user->email))
                                            <span class="badge-unverified">Email Not Added</span>
                                        @else
                                            @if($user->email_verified_at)
                                                <span class="badge-verified">Verified</span>
                                            @else
                                                <span class="badge-unverified">Not Verified</span>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                                <div class="edit-form-group d-none" id="emailEdit">
                                    <input type="email" class="form-control form-control-edit" id="emailInput"
                                           value="{{ $user->email }}" placeholder="Enter email address">
                                    <div class="error-message" id="emailError"></div>
                                </div>
                            </div>

                            <div class="info-item">
                                <div class="info-label">Mobile Number</div>
                                <div class="info-value">
                                    {{ $user->mobile ?? 'Not provided' }}
                                    @if($user->mobile_verified_at)
                                        <span class="badge-verified">Verified</span>
                                    @elseif($user->mobile)
                                        <span class="badge-unverified">Not Verified</span>
                                    @endif
                                </div>
                            </div>

                            <div class="btn-group-edit d-flex gap-2 justify-content-end mt-3 d-none" id="saveCancelButtons">
                                <button type="button" class="btn btn-primary btn-sm" id="saveProfileBtn">
                                    <i class="fa-regular fa-floppy-disk me-1"></i>Save Changes
                                </button>
                                <button type="button" class="btn btn-secondary btn-sm" id="cancelEditBtn">
                                    <i class="fa-solid fa-xmark me-1"></i>Cancel
                                </button>
                            </div>
                        </div>

                        <!-- Recent Bookings -->
                        <div class="tab-pane fade" id="pane-bookings" role="tabpanel" aria-labelledby="tab-bookings" tabindex="0">
                            @if($bookings->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle pp-soft-table">
                                        <thead>
                                        <tr>
                                            <th>Booking ID</th>
                                            <th>Property</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($bookings->take(8) as $booking)
                                            <tr>
                                                <td>#{{ $booking->id }}</td>
                                                <td>
                                                    {{ $booking->propertyType?->name ?? 'N/A' }}
                                                    @if($booking->propertySubType)
                                                        <small class="text-muted d-block">{{ $booking->propertySubType->name }}</small>
                                                    @endif
                                                </td>
                                                <td>{{ $booking->created_at->format('M d, Y') }}</td>
                                                <td>
                                                    <span class="badge bg-{{ ($booking->payment_status ?? 'pending') === 'paid' ? 'success' : 'warning' }}">
                                                        {{ ucfirst($booking->payment_status ?? 'pending') }}
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <a href="{{ route('frontend.booking.show', $booking->id) }}" class="btn btn-sm btn-primary">
                                                        View
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
                                    <div class="text-muted small">Showing latest {{ min(8, $bookings->count()) }} bookings</div>
                                    <a href="{{ route('frontend.booking-dashboard') }}" class="btn btn-outline-primary btn-sm">
                                        View All Bookings
                                    </a>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <div class="mb-3" style="font-size: 42px; color: rgba(0,0,0,0.25);">
                                        <i class="fa-regular fa-calendar-xmark"></i>
                                    </div>
                                    <div class="fw-semibold mb-1">No bookings yet</div>
                                    <div class="text-muted mb-3">Start your first booking to see it here.</div>
                                    <a href="{{ route('frontend.setup') }}" class="btn btn-primary">
                                        <i class="fa-solid fa-plus me-2"></i>Create Booking
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Bootstrap 5 bundle (required for tabs) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const editBtn = document.getElementById('editProfileBtn');
            const saveBtn = document.getElementById('saveProfileBtn');
            const cancelBtn = document.getElementById('cancelEditBtn');
            const successAlert = document.getElementById('successAlert');
            const saveCancelButtons = document.getElementById('saveCancelButtons');

            const nameView = document.getElementById('nameView');
            const emailView = document.getElementById('emailView');
            const nameEdit = document.getElementById('nameEdit');
            const emailEdit = document.getElementById('emailEdit');

            let isEditMode = false;
            let originalValues = {
                firstname: @json($user->firstname),
                lastname: @json($user->lastname),
                email: @json($user->email),
            };

            function setEditMode(enabled) {
                isEditMode = enabled;

                if (enabled) {
                    nameView?.classList.add('d-none');
                    emailView?.classList.add('d-none');
                    nameEdit?.classList.remove('d-none');
                    emailEdit?.classList.remove('d-none');
                    saveCancelButtons?.classList.remove('d-none');
                    editBtn?.classList.add('d-none');

                    document.querySelectorAll('.error-message').forEach(el => {
                        el.classList.remove('show');
                        el.textContent = '';
                    });
                    document.querySelectorAll('.form-control-edit').forEach(el => el.classList.remove('is-invalid'));
                } else {
                    nameView?.classList.remove('d-none');
                    emailView?.classList.remove('d-none');
                    nameEdit?.classList.add('d-none');
                    emailEdit?.classList.add('d-none');
                    saveCancelButtons?.classList.add('d-none');
                    editBtn?.classList.remove('d-none');

                    document.getElementById('firstNameInput').value = originalValues.firstname;
                    document.getElementById('lastNameInput').value = originalValues.lastname;
                    document.getElementById('emailInput').value = originalValues.email;

                    successAlert?.classList.remove('show');
                }
            }

            editBtn?.addEventListener('click', () => setEditMode(true));
            cancelBtn?.addEventListener('click', () => setEditMode(false));

            function showError(elementId, message) {
                const errorEl = document.getElementById(elementId);
                if (!errorEl) return;
                errorEl.textContent = message;
                errorEl.classList.add('show');
            }

            function isValidEmail(email) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(email);
            }

            saveBtn?.addEventListener('click', async function () {
                const firstName = document.getElementById('firstNameInput').value.trim();
                const lastName = document.getElementById('lastNameInput').value.trim();
                const email = document.getElementById('emailInput').value.trim();

                document.querySelectorAll('.error-message').forEach(el => {
                    el.classList.remove('show');
                    el.textContent = '';
                });
                document.querySelectorAll('.form-control-edit').forEach(el => el.classList.remove('is-invalid'));

                let hasError = false;
                // First/Last name are optional (like email): no required validation here
                // Email is optional: validate only if provided
                if (email && !isValidEmail(email)) {
                    showError('emailError', 'Please enter a valid email address');
                    document.getElementById('emailInput').classList.add('is-invalid');
                    hasError = true;
                }
                if (hasError) return;

                saveBtn.disabled = true;
                saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Saving...';

                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                    const response = await fetch(@json(route('user.update', Auth::id())), {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            firstname: firstName || null,
                            lastname: lastName || null,
                            email: email || null
                        })
                    });

                    const data = await response.json();

                    if (response.ok) {
                        originalValues = { firstname: firstName, lastname: lastName, email: email };

                        const safeFirst = (firstName || '').trim();
                        const safeLast = (lastName || '').trim();
                        const displayName = (safeFirst || safeLast) ? `${safeFirst} ${safeLast}`.trim() : '—';
                        document.getElementById('nameView').querySelector('.info-value').textContent = displayName;
                        const emailViewValue = document.getElementById('emailView').querySelector('.info-value');
                        if (!email) {
                            emailViewValue.innerHTML = '<span class="badge-unverified">Email Not Added</span>';
                        } else {
                            // Keep existing verification badge from the DOM (server-rendered)
                            const badge = emailViewValue.querySelector('.badge-verified, .badge-unverified');
                            emailViewValue.innerHTML = email + (badge ? badge.outerHTML : '');
                        }

                        const profileHeroName = document.getElementById('profileHeroName');
                        if (profileHeroName) profileHeroName.textContent = displayName;
                        const profileAvatar = document.getElementById('profileAvatarInitials');
                        if (profileAvatar) {
                            const initials = ((safeFirst[0] || '') + (safeLast[0] || '')).toUpperCase() || '—';
                            profileAvatar.textContent = initials;
                        }

                        successAlert?.classList.add('show');

                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Profile updated successfully',
                            timer: 2000,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-end'
                        });

                        setTimeout(() => {
                            setEditMode(false);
                            successAlert?.classList.remove('show');
                        }, 1800);
                    } else {
                        if (data.errors) {
                            if (data.errors.firstname) { showError('firstNameError', data.errors.firstname[0]); document.getElementById('firstNameInput').classList.add('is-invalid'); }
                            if (data.errors.lastname) { showError('lastNameError', data.errors.lastname[0]); document.getElementById('lastNameInput').classList.add('is-invalid'); }
                            if (data.errors.email) { showError('emailError', data.errors.email[0]); document.getElementById('emailInput').classList.add('is-invalid'); }
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to update profile' });
                        }
                    }
                } catch (error) {
                    console.error('Error:', error);
                    Swal.fire({ icon: 'error', title: 'Error', text: 'An error occurred while updating your profile' });
                } finally {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<i class="fa-regular fa-floppy-disk me-1"></i>Save Changes';
                }
            });
        });
    </script>
@endsection
