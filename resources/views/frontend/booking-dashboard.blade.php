@extends('frontend.layouts.base', ['title' => 'Booking Dashboard - PROP PIK'])

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400..800&family=Urbanist:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('frontend/css/plugins.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/style.css') }}">
@endsection

@section('content')
    <!-- Progress scroll totop -->
    <div class="progress-wrap cursor-pointer">
        <svg class="progress-circle svg-content" width="100%" height="100%" viewBox="-1 -1 102 102">
            <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98" />
        </svg>
    </div>
    <!-- Cursor -->
    <div class="cursor js-cursor"></div>
    <!-- Social Icons -->
    <div class="social-ico-block"> 
        <a href="https://duruthemes.com/demo/html/gloom/" target="_blank" class="social-ico"><i class="fa-brands fa-instagram"></i></a> 
        <a href="https://duruthemes.com/demo/html/gloom/" target="_blank" class="social-ico"><i class="fa-brands fa-x-twitter"></i></a> 
        <a href="https://duruthemes.com/demo/html/gloom/" target="_blank" class="social-ico"><i class="fa-brands fa-youtube"></i></a> 
        <a href="https://duruthemes.com/demo/html/gloom/" target="_blank" class="social-ico"><i class="fa-brands fa-tiktok"></i></a> 
        <a href="https://duruthemes.com/demo/html/gloom/" target="_blank" class="social-ico"><i class="fa-brands fa-flickr"></i></a> 
    </div>
    
    @include('frontend.layouts.partials.page-header', ['title' => 'Booking Dashboard'])
    
    <section class="page bg-light section-padding-bottom section-padding-top">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="dashboard-container">
                        <div class="dashboard-tabs">
                            <button class="tab-btn active" data-tab="bookings">My Bookings</button>
                            <button class="tab-btn" onclick="switchToNewBooking()">New Booking</button>
                        </div>

                        <div id="bookings-tab" class="tab-content active">
                            <div class="bookings-grid" id="bookingsList">
                                <!-- Bookings will be loaded here by JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Schedule Date Modal -->
    <div class="modal fade" id="scheduleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:12px;">
                <div class="modal-header">
                    <h5 class="modal-title">Schedule Virtual Tour</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="scheduleBookingId">
                    <div class="form-group mb-3">
                        <label for="scheduleDate" class="form-label">Preferred Date</label>
                        <input type="date" class="form-control" id="scheduleDate" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="scheduleTime" class="form-label">Preferred Time</label>
                        <input type="time" class="form-control" id="scheduleTime" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="scheduleNotes" class="form-label">Additional Notes (Optional)</label>
                        <textarea class="form-control" id="scheduleNotes" rows="3" placeholder="Any special instructions or requirements..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveSchedule()">Save Schedule</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:12px;">
                <div class="modal-body text-center p-4">
                    <div class="success-icon mb-3">
                        <i class="fa-solid fa-circle-check" style="font-size:48px;color:#28a745;"></i>
                    </div>
                    <h4 id="successTitle">Success!</h4>
                    <p id="successMessage" class="mb-3">Your action has been completed successfully.</p>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('frontend/js/plugins/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('frontend/js/plugins/jquery-migrate-3.5.0.min.js') }}"></script>
    <script src="{{ asset('frontend/js/plugins/popper.min.js') }}"></script>
    <script src="{{ asset('frontend/js/plugins/bootstrap.min.js') }}"></script>
    <script src="{{ asset('frontend/js/plugins/smooth-scroll.min.js') }}"></script>
    <script src="{{ asset('frontend/js/plugins/wow.js') }}"></script>
    <script src="{{ asset('frontend/js/custom.js') }}"></script>
    <script>
        // Booking Management System
        let bookings = @json($bookings ?? []);
        let bookingIdCounter = 1;
        console.log("booking :",bookings)
        // Convert Laravel bookings to frontend format
        if (bookings && bookings.length > 0) {
            bookings = bookings.map(booking => {
                return {
                    id: booking.id,
                    name: booking.user ? `${booking.user.firstname || ''} ${booking.user.lastname || ''}`.trim() : 'Property Booking',
                    phone: booking.user ? booking.user.mobile : '',
                    email: booking.user ? booking.user.email : '',
                    address: booking.full_address || booking.address_area || '',
                    mainType: booking.property_type ? booking.property_type.name : '',
                    propertyType: booking.property_type ? booking.property_type.name : '',
                    residential: booking.property_type && booking.property_type.name === 'Residential' ? {
                        bhk: booking.bhk ? booking.bhk.name : '',
                        subType: booking.property_sub_type ? booking.property_sub_type.name : '',
                        area: booking.area || ''
                    } : null,
                    commercial: booking.property_type && booking.property_type.name === 'Commercial' ? {
                        subType: booking.property_sub_type ? booking.property_sub_type.name : '',
                        area: booking.area || ''
                    } : null,
                    other: booking.property_type && booking.property_type.name === 'Other' ? {
                        area: booking.area || ''
                    } : null,
                    price: booking.price || 0,
                    status: booking.status || 'pending',
                    createdAt: booking.created_at || new Date().toISOString(),
                    scheduledDate: null,
                    scheduledTime: null
                };
            });
        }

        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            // Set minimum date to today for schedule modal
            const scheduleDateInput = document.getElementById('scheduleDate');
            if (scheduleDateInput) {
                const today = new Date().toISOString().split('T')[0];
                scheduleDateInput.setAttribute('min', today);
            }
            
            // Load bookings
            loadBookings();
        });

        // Calculate price based on area (same logic as setup.html)
        function calculatePrice(booking) {
            // If price is already set, use it
            if (booking.price) {
                return booking.price;
            }
            
            const baseArea = 1500;
            const basePrice = 599;
            const extraBlockPrice = 200;
            let areaValue = 0;
            
            // Get area from the appropriate property type
            if (booking.residential && booking.residential.area) {
                areaValue = Number(booking.residential.area) || 0;
            } else if (booking.commercial && booking.commercial.area) {
                areaValue = Number(booking.commercial.area) || 0;
            } else if (booking.other && booking.other.area) {
                areaValue = Number(booking.other.area) || 0;
            }
            
            if (!areaValue || areaValue <= 0) {
                return basePrice;
            }
            
            let price = basePrice;
            
            // If area is greater than base area, calculate extra blocks
            if (areaValue > baseArea) {
                const extra = areaValue - baseArea;
                const blocks = Math.ceil(extra / 500);
                price += blocks * extraBlockPrice;
            }
            
            return price;
        }

        // Switch to new booking tab (redirects to setup.html)
        function switchToNewBooking() {
            window.location.href = "{{ route('frontend.setup') }}";
        }

        // Load and display bookings
        function loadBookings() {
            const bookingsList = document.getElementById('bookingsList');
            
            if (!bookingsList) {
                console.error('bookingsList element not found!');
                return;
            }
            
            if (bookings.length === 0) {
                bookingsList.innerHTML = `
                    <div class="no-bookings">
                        <i class="fa-solid fa-calendar-xmark mb-3" style="font-size:48px;color:#ccc;"></i>
                        <h4>No Bookings Yet</h4>
                        <p>You haven't created any bookings. Click "New Booking" to get started!</p>
                    </div>
                `;
                return;
            }

            bookingsList.innerHTML = bookings.map(booking => {
                // Debug: log booking data
                console.log('Processing booking:', booking);
                
                if (!booking.createdAt) {
                    booking.createdAt = new Date().toISOString();
                }
                
                const createdDate = booking.createdAt ? new Date(booking.createdAt).toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'short', 
                    day: 'numeric' 
                }) : 'Date not available';
                const scheduledDate = booking.scheduledDate ? new Date(booking.scheduledDate).toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'short', 
                    day: 'numeric' 
                }) : null;
                const statusClass = booking.status === 'scheduled' ? 'success' : booking.status === 'completed' ? 'info' : 'warning';
                const statusText = booking.status ? booking.status.charAt(0).toUpperCase() + booking.status.slice(1) : 'Pending';
                
                // Calculate price
                const price = calculatePrice(booking);
                const formattedPrice = `₹${price.toLocaleString('en-IN')}`;
                
                // Get property details based on mainType
                let propertyType = '';
                let propertyDetails = '';
                
                if (booking.mainType === 'Residential' && booking.residential) {
                    propertyType = 'Residential';
                    const parts = [];
                    if (booking.residential.bhk) parts.push(booking.residential.bhk);
                    if (booking.residential.subType) parts.push(booking.residential.subType);
                    if (booking.residential.area) parts.push(booking.residential.area + ' sq ft');
                    propertyDetails = parts.join(' - ');
                } else if (booking.mainType === 'Commercial' && booking.commercial) {
                    propertyType = 'Commercial';
                    const parts = [];
                    if (booking.commercial.subType) parts.push(booking.commercial.subType);
                    if (booking.commercial.area) parts.push(booking.commercial.area + ' sq ft');
                    propertyDetails = parts.join(' - ');
                } else if (booking.mainType === 'Other' && booking.other) {
                    propertyType = 'Other';
                    propertyDetails = booking.other.area ? booking.other.area + ' sq ft' : '';
                } else if (booking.propertyType) {
                    propertyType = booking.propertyType;
                }

                return `
                    <div class="booking-card">
                        <div class="booking-header">
                            <h4>${escapeHtml(booking.name || 'Property Booking')}</h4>
                            <span class="badge bg-${statusClass}">${statusText}</span>
                        </div>
                        <div class="booking-details">
                            <p><strong>Property:</strong> ${escapeHtml(propertyType)}</p>
                            ${propertyDetails ? `<p><strong>Details:</strong> ${escapeHtml(propertyDetails)}</p>` : ''}
                            <p><strong>Address:</strong> ${escapeHtml(booking.address || 'N/A')}</p>
                            <p><strong>Phone:</strong> ${escapeHtml(booking.phone || 'N/A')}</p>
                            <p><strong>Email:</strong> ${escapeHtml(booking.email || 'N/A')}</p>
                            <p><strong>Created:</strong> ${createdDate}</p>
                            ${scheduledDate ? `<p><strong>Scheduled:</strong> ${scheduledDate} at ${booking.scheduledTime || ''}</p>` : ''}
                            <p><strong>Price:</strong> ${formattedPrice}</p>
                        </div>
                        <div class="booking-actions">
                            <button class="btn btn-sm btn-primary" onclick="openScheduleModal(${booking.id})">
                                <i class="fa-solid fa-calendar"></i> Schedule
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteBooking(${booking.id})">
                                <i class="fa-solid fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                `;
            }).join('');
        }

        // Open schedule modal
        function openScheduleModal(bookingId) {
            const booking = bookings.find(b => b.id === bookingId);
            if (!booking) return;

            document.getElementById('scheduleBookingId').value = bookingId;
            document.getElementById('scheduleDate').value = booking.scheduledDate || '';
            document.getElementById('scheduleTime').value = booking.scheduledTime || '';
            document.getElementById('scheduleNotes').value = '';

            const modal = new bootstrap.Modal(document.getElementById('scheduleModal'));
            modal.show();
        }

        // Save schedule
        function saveSchedule() {
            const bookingId = parseInt(document.getElementById('scheduleBookingId').value);
            const scheduleDate = document.getElementById('scheduleDate').value;
            const scheduleTime = document.getElementById('scheduleTime').value;

            if (!scheduleDate || !scheduleTime) {
                alert('Please select both date and time');
                return;
            }

            const booking = bookings.find(b => b.id === bookingId);
            if (booking) {
                // Update local booking data
                booking.scheduledDate = scheduleDate;
                booking.scheduledTime = scheduleTime;
                booking.status = 'scheduled';
                
                // Make AJAX request to update booking in database
                fetch(`/admin/bookings/${bookingId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        status: 'scheduled',
                        // You can add more fields here as needed
                    })
                })
                .then(response => response.json())
                .then(data => {
                    loadBookings();

                    const modal = bootstrap.Modal.getInstance(document.getElementById('scheduleModal'));
                    modal.hide();

                    document.getElementById('successTitle').textContent = 'Schedule Updated!';
                    document.getElementById('successMessage').textContent = 'Booking schedule has been updated successfully.';
                    const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                    successModal.show();
                })
                .catch(error => {
                    console.error('Error updating schedule:', error);
                    alert('Failed to update schedule. Please try again.');
                });
            }
        }

        // Delete booking
        function deleteBooking(bookingId) {
            if (confirm('Are you sure you want to delete this booking?')) {
                // Make AJAX request to delete booking from database
                fetch(`/admin/bookings/${bookingId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove booking from array
                        bookings = bookings.filter(b => b.id !== bookingId);
                        loadBookings();

                        document.getElementById('successTitle').textContent = 'Booking Deleted!';
                        document.getElementById('successMessage').textContent = 'The booking has been deleted successfully.';
                        const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                        successModal.show();
                    } else {
                        alert('Failed to delete booking: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error deleting booking:', error);
                    alert('Failed to delete booking. Please try again.');
                });
            }
        }

        // Save bookings to localStorage (deprecated - now using database)
        function saveBookings() {
            // This function is kept for compatibility but bookings are now saved in database
            console.log('Bookings are now managed in the database');
        }

        // Escape HTML to prevent XSS
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
@endsection
