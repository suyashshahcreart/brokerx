@extends('frontend.layouts.base', ['title' => 'My Profile - PROP PIK'])

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Syne:wght@400..800&family=Urbanist:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('frontend/css/plugins.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/style.css') }}">
    
    <style>
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
        }
        
        .profile-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
            font-weight: bold;
            margin: 0 auto 1rem;
        }
        
        .info-item {
            padding: 5px 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #495057;
        }
        
        .info-value {
            color: #6c757d;
        }
        
        .badge-verified {
            background-color: #28a745;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            margin-left: 0.5rem;
        }
        
        .badge-unverified {
            background-color: #ffc107;
            color: #333;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            margin-left: 0.5rem;
        }
        
        .edit-btn {
            font-size: 0.875rem;
            padding: 0.25rem 0.75rem;
            border-radius: 6px;
        }
        
        .info-item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .edit-form-group {
            display: none;
        }
        
        .edit-form-group.active {
            display: block;
        }
        
        .view-mode {
            display: block;
        }
        
        .view-mode.hidden {
            display: none;
        }
        
        .form-control-edit {
            border: 2px solid #667eea;
            border-radius: 8px;
            padding: 0.5rem;
            width: 100%;
        }
        
        .btn-group-edit {
            margin-top: 0.5rem;
            display: flex;
            gap: 0.5rem;
        }
        
        .error-message {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: none;
        }
        
        .error-message.show {
            display: block;
        }
        
        .alert-success {
            display: none;
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
            border-radius: 8px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-success.show {
            display: block;
        }
    </style>
@endsection

@section('content')
<div class="profile-header">
    <div class="container">
        <div class="text-center">
            <div class="profile-avatar">
                {{ strtoupper(substr($user->firstname, 0, 1) . substr($user->lastname, 0, 1)) }}
            </div>
            <h2 class="mb-2">{{ $user->firstname }} {{ $user->lastname }}</h2>
            <p class="mb-0 opacity-75">Member since {{ $user->created_at->format('F Y') }}</p>
        </div>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <!-- Personal Information Card -->
            <div class="profile-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0">
                        <i class="ri-user-line me-2"></i>Personal Information
                    </h4>
                    <button type="button" class="btn btn-sm btn-outline-primary edit-btn" id="editProfileBtn">
                        <i class="ri-edit-line me-1"></i>Edit
                    </button>
                </div>
                
                <div class="alert-success" id="successAlert">
                    <i class="ri-check-line me-1"></i>Profile updated successfully!
                </div>
                
                <!-- First Name and Last Name in Single Row -->
                <div class="info-item">
                    <div class="info-item-header">
                        <div class="info-label">Full Name</div>
                    </div>
                    <div class="view-mode" id="nameView">
                        <div class="info-value">{{ $user->firstname }} {{ $user->lastname }}</div>
                    </div>
                    <div class="edit-form-group" id="nameEdit">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="firstNameInput" class="form-label small text-muted mb-1">First Name</label>
                                <input type="text" class="form-control form-control-edit" id="firstNameInput" value="{{ $user->firstname }}" placeholder="Enter first name">
                                <div class="error-message" id="firstNameError"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="lastNameInput" class="form-label small text-muted mb-1">Last Name</label>
                                <input type="text" class="form-control form-control-edit" id="lastNameInput" value="{{ $user->lastname }}" placeholder="Enter last name">
                                <div class="error-message" id="lastNameError"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Email Address -->
                <div class="info-item">
                    <div class="info-item-header">
                        <div class="info-label">Email Address</div>
                    </div>
                    <div class="view-mode" id="emailView">
                        <div class="info-value">
                            {{ $user->email }}
                            @if($user->email_verified_at)
                                <span class="badge-verified">Verified</span>
                            @else
                                <span class="badge-unverified">Not Verified</span>
                            @endif
                        </div>
                    </div>
                    <div class="edit-form-group" id="emailEdit">
                        <input type="email" class="form-control form-control-edit" id="emailInput" value="{{ $user->email }}" placeholder="Enter email address">
                        <div class="error-message" id="emailError"></div>
                    </div>
                </div>
                
                <!-- Mobile Number (Read-only) -->
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
                
                <!-- Save/Cancel Buttons (Hidden by default) -->
                <div class="btn-group-edit" id="saveCancelButtons" style="display: none;">
                    <button type="button" class="btn btn-primary btn-sm" id="saveProfileBtn">
                        <i class="ri-save-line me-1"></i>Save Changes
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" id="cancelEditBtn">
                        <i class="ri-close-line me-1"></i>Cancel
                    </button>
                </div>
            </div>
            
            <!-- Booking Statistics Card -->
            <div class="profile-card">
                <h4 class="mb-4">
                    <i class="ri-calendar-line me-2"></i>Booking Statistics
                </h4>
                
                <div class="row text-center">
                    <div class="col-md-6 mb-3">
                        <div class="p-3 bg-light rounded">
                            <h3 class="mb-1 text-primary">{{ $bookingCount }}</h3>
                            <p class="mb-0 text-muted">Total Bookings</p>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="p-3 bg-light rounded">
                            <h3 class="mb-1 text-success">{{ $bookings->where('payment_status', 'paid')->count() }}</h3>
                            <p class="mb-0 text-muted">Paid Bookings</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Bookings Card -->
            @if($bookings->count() > 0)
            <div class="profile-card">
                <h4 class="mb-4">
                    <i class="ri-file-list-line me-2"></i>Recent Bookings
                </h4>
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Property</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bookings->take(5) as $booking)
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
                                    <span class="badge bg-{{ $booking->payment_status === 'paid' ? 'success' : 'warning' }}">
                                        {{ ucfirst($booking->payment_status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('frontend.booking.show', $booking->id) }}" class="btn btn-sm btn-primary">
                                        View
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                @if($bookings->count() > 5)
                <div class="text-center mt-3">
                    <a href="{{ route('frontend.booking-dashboard') }}" class="btn btn-outline-primary">
                        View All Bookings
                    </a>
                </div>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const editBtn = document.getElementById('editProfileBtn');
        const saveBtn = document.getElementById('saveProfileBtn');
        const cancelBtn = document.getElementById('cancelEditBtn');
        const successAlert = document.getElementById('successAlert');
        const saveCancelButtons = document.getElementById('saveCancelButtons');
        
        let isEditMode = false;
        let originalValues = {
            firstname: '{{ $user->firstname }}',
            lastname: '{{ $user->lastname }}',
            email: '{{ $user->email }}'
        };
        
        // Toggle edit mode
        function toggleEditMode() {
            isEditMode = !isEditMode;
            
            if (isEditMode) {
                // Enter edit mode
                document.querySelectorAll('.view-mode').forEach(el => el.classList.add('hidden'));
                document.querySelectorAll('.edit-form-group').forEach(el => el.classList.add('active'));
                saveCancelButtons.style.display = 'flex';
                editBtn.style.display = 'none';
                
                // Clear any previous errors
                document.querySelectorAll('.error-message').forEach(el => {
                    el.classList.remove('show');
                    el.textContent = '';
                });
                document.querySelectorAll('.form-control-edit').forEach(el => {
                    el.classList.remove('is-invalid');
                });
            } else {
                // Exit edit mode
                document.querySelectorAll('.view-mode').forEach(el => el.classList.remove('hidden'));
                document.querySelectorAll('.edit-form-group').forEach(el => el.classList.remove('active'));
                saveCancelButtons.style.display = 'none';
                editBtn.style.display = 'block';
                
                // Reset values to original
                document.getElementById('firstNameInput').value = originalValues.firstname;
                document.getElementById('lastNameInput').value = originalValues.lastname;
                document.getElementById('emailInput').value = originalValues.email;
                
                // Hide success alert
                successAlert.classList.remove('show');
            }
        }
        
        // Edit button click
        editBtn.addEventListener('click', toggleEditMode);
        
        // Cancel button click
        cancelBtn.addEventListener('click', function() {
            toggleEditMode();
        });
        
        // Save button click
        saveBtn.addEventListener('click', async function() {
            const firstName = document.getElementById('firstNameInput').value.trim();
            const lastName = document.getElementById('lastNameInput').value.trim();
            const email = document.getElementById('emailInput').value.trim();
            
            // Clear previous errors
            document.querySelectorAll('.error-message').forEach(el => {
                el.classList.remove('show');
                el.textContent = '';
            });
            document.querySelectorAll('.form-control-edit').forEach(el => {
                el.classList.remove('is-invalid');
            });
            
            // Validation
            let hasError = false;
            
            if (!firstName) {
                showError('firstNameError', 'First name is required');
                document.getElementById('firstNameInput').classList.add('is-invalid');
                hasError = true;
            }
            
            if (!lastName) {
                showError('lastNameError', 'Last name is required');
                document.getElementById('lastNameInput').classList.add('is-invalid');
                hasError = true;
            }
            
            if (!email) {
                showError('emailError', 'Email is required');
                document.getElementById('emailInput').classList.add('is-invalid');
                hasError = true;
            } else if (!isValidEmail(email)) {
                showError('emailError', 'Please enter a valid email address');
                document.getElementById('emailInput').classList.add('is-invalid');
                hasError = true;
            }
            
            if (hasError) {
                return;
            }
            
            // Disable save button
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="ri-loader-4-line me-1"></i>Saving...';
            
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                const response = await fetch('{{ route("user.update", Auth::id()) }}', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        firstname: firstName,
                        lastname: lastName,
                        email: email
                    })
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    // Update original values
                    originalValues = {
                        firstname: firstName,
                        lastname: lastName,
                        email: email
                    };
                    
                    // Update view mode values
                    document.getElementById('nameView').querySelector('.info-value').textContent = firstName + ' ' + lastName;
                    const emailViewValue = document.getElementById('emailView').querySelector('.info-value');
                    const badge = emailViewValue.querySelector('.badge-verified, .badge-unverified');
                    emailViewValue.innerHTML = email + (badge ? badge.outerHTML : '');
                    
                    // Update profile header name
                    const profileHeaderName = document.querySelector('.profile-header h2');
                    if (profileHeaderName) {
                        profileHeaderName.textContent = firstName + ' ' + lastName;
                    }
                    
                    // Update profile avatar initials
                    const profileAvatar = document.querySelector('.profile-avatar');
                    if (profileAvatar) {
                        profileAvatar.textContent = (firstName.charAt(0) + lastName.charAt(0)).toUpperCase();
                    }
                    
                    // Show success message
                    successAlert.classList.add('show');
                    
                    // Exit edit mode after a short delay
                    setTimeout(() => {
                        toggleEditMode();
                        successAlert.classList.remove('show');
                    }, 2000);
                    
                    // Show success notification
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Profile updated successfully',
                        timer: 2000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                } else {
                    // Handle validation errors
                    if (data.errors) {
                        if (data.errors.firstname) {
                            showError('firstNameError', data.errors.firstname[0]);
                            document.getElementById('firstNameInput').classList.add('is-invalid');
                        }
                        if (data.errors.lastname) {
                            showError('lastNameError', data.errors.lastname[0]);
                            document.getElementById('lastNameInput').classList.add('is-invalid');
                        }
                        if (data.errors.email) {
                            showError('emailError', data.errors.email[0]);
                            document.getElementById('emailInput').classList.add('is-invalid');
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Failed to update profile'
                        });
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while updating your profile'
                });
            } finally {
                // Re-enable save button
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="ri-save-line me-1"></i>Save Changes';
            }
        });
        
        function showError(elementId, message) {
            const errorEl = document.getElementById(elementId);
            errorEl.textContent = message;
            errorEl.classList.add('show');
        }
        
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
    });
</script>
@endsection

