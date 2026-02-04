    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        const bookingId = {{ $booking->id }};
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const baseUrl = '{{ url("/") }}';
        const apiBaseUrl = '{{ url("/api") }}';

        // Update Payment Status
        async function updatePaymentStatus(status) {
            const statusLabel = status.charAt(0).toUpperCase() + status.slice(1);

            const result = await Swal.fire({
                title: 'Update Payment Status?',
                html: `
                    <p>Change payment status to <strong class="text-primary">"${statusLabel}"</strong></p>
                    <div class="mb-3">
                        <label class="form-label text-start d-block">Add Notes (Optional):</label>
                        <textarea id="payment-status-notes" class="form-control" rows="3" placeholder="Enter notes about this payment status change..."></textarea>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Update',
                cancelButtonText: 'Cancel',
                preConfirm: () => {
                    return {
                        notes: document.getElementById('payment-status-notes').value
                    };
                }
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch(`${baseUrl}/${window.adminBasePath}/bookings/${bookingId}/update-ajax`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            payment_status: status,
                            notes: result.value.notes || `Payment status changed to ${statusLabel}`
                        })
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        await Swal.fire({
                            icon: 'success',
                            title: 'Updated!',
                            html: `<p class="mb-2">Payment status has been changed to <strong class="text-success">${statusLabel}</strong></p><p class="text-muted small">History entry created automatically</p>`,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        setTimeout(() => window.location.reload(), 2000);
                    } else {
                        throw new Error(data.message || 'Failed to update');
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'Failed to update payment status'
                    });
                    // Revert select
                    document.getElementById('quickPaymentStatus').value = '{{ $booking->payment_status }}';
                }
            } else {
                // Revert select
                document.getElementById('quickPaymentStatus').value = '{{ $booking->payment_status }}';
            }
        }

        // Update Booking Status
        async function updateBookingStatus(status) {
            const result = await Swal.fire({
                title: 'Update Booking Status?',
                html: `
                    <p>Change booking status to <strong>"${status.replace(/_/g, ' ').toUpperCase()}"</strong></p>
                    <div class="mb-3">
                        <label class="form-label text-start d-block">Add Notes (Optional):</label>
                        <textarea id="status-notes" class="form-control" rows="3" placeholder="Enter notes about this status change..."></textarea>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Update',
                cancelButtonText: 'Cancel',
                preConfirm: () => {
                    return {
                        notes: document.getElementById('status-notes').value
                    };
                }
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch(`${baseUrl}/${window.adminBasePath}/bookings/${bookingId}/change-status`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            status: status,
                            notes: result.value.notes || `Status changed to ${status.replace(/_/g, ' ')}`
                        })
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        await Swal.fire({
                            icon: 'success',
                            title: 'Updated!',
                            text: 'Booking status updated successfully',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        window.location.reload();
                    } else {
                        throw new Error(data.message || 'Failed to update');
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'Failed to update booking status'
                    });
                    // Revert select
                    document.getElementById('quickBookingStatus').value = '{{ $booking->status }}';
                }
            } else {
                // Revert select
                document.getElementById('quickBookingStatus').value = '{{ $booking->status }}';
            }
        }

        // Change Status with Note (for Quick Action Buttons)
        async function changeStatusWithNote(newStatus, defaultNote) {
            const statusLabel = newStatus.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());

            const result = await Swal.fire({
                title: `Change to ${statusLabel}?`,
                html: `
                    <div class="mb-3">
                        <p class="text-muted mb-3">You are about to change the booking status to <strong class="text-primary">${statusLabel}</strong></p>
                        <label class="form-label text-start d-block fw-semibold">Add Notes (Optional):</label>
                        <textarea id="quick-status-notes" class="form-control" rows="3" placeholder="Enter notes about this change...">${defaultNote}</textarea>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="ri-check-line me-1"></i> Confirm Change',
                cancelButtonText: '<i class="ri-close-line me-1"></i> Cancel',
                customClass: {
                    confirmButton: 'btn btn-primary',
                    cancelButton: 'btn btn-secondary'
                },
                preConfirm: () => {
                    return {
                        notes: document.getElementById('quick-status-notes').value || defaultNote
                    };
                }
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch(`${baseUrl}/${window.adminBasePath}/bookings/${bookingId}/change-status`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            status: newStatus,
                            notes: result.value.notes
                        })
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        await Swal.fire({
                            icon: 'success',
                            title: 'Status Updated!',
                            html: `<p class="mb-2">Booking status has been changed to <strong class="text-success">${statusLabel}</strong></p><p class="text-muted small">History entry created automatically</p>`,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        setTimeout(() => window.location.reload(), 2000);
                    } else {
                        throw new Error(data.message || 'Failed to update status');
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Update Failed',
                        text: error.message || 'Failed to update booking status',
                        confirmButtonText: 'OK'
                    });
                }
            }
        }

        // Schedule Date Modal with Flatpickr
        let holidays = [];
        let flatpickrInstance = null;
        let lastSelectedDate = '{{ optional($booking->booking_date)->format("Y-m-d") }}' || null;
        let lastDayLimit = 30;

        function fetchHolidaysAndInitPicker(selectedDate) {
            fetch(`${apiBaseUrl}/holidays`)
                .then(response => response.json())
                .then(data => {
                    holidays = (data.holidays || []).map(h => h.date);
                    let dayLimit = 30;
                    if (data.day_limit && data.day_limit.value) {
                        dayLimit = parseInt(data.day_limit.value, 10) || 30;
                    }
                    lastDayLimit = dayLimit;
                    initFlatpickr(selectedDate, dayLimit);
                })
                .catch(error => {
                    console.error('Failed to fetch holidays:', error);
                    initFlatpickr(selectedDate, 30);
                });
        }

        function initFlatpickr(selectedDate, dayLimit = 30, mode = 'default') {
            if (flatpickrInstance) flatpickrInstance.destroy();
            const today = new Date();
            const minDate = today.toISOString().split('T')[0];
            let maxDate = null;
            let disable = [];

            if (mode === 'default') {
                const max = new Date();
                max.setDate(today.getDate() + dayLimit);
                maxDate = max.toISOString().split('T')[0];
                disable = holidays;
            }

            flatpickrInstance = flatpickr('#schedule-date', {
                dateFormat: 'Y-m-d',
                minDate: minDate,
                maxDate: maxDate,
                disable: disable,
                defaultDate: selectedDate || null,
                onChange: function (selectedDates, dateStr) {
                    if (mode === 'default' && holidays.includes(dateStr)) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Holiday',
                            text: 'Selected date is a holiday. Please choose another date.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        flatpickrInstance.clear();
                    }
                }
            });
        }

        // Handle schedule mode change
        document.addEventListener('change', function (e) {
            if (e.target && e.target.name === 'schedule_mode') {
                const mode = e.target.value;
                if (mode === 'any') {
                    initFlatpickr(lastSelectedDate, 0, 'any');
                } else {
                    initFlatpickr(lastSelectedDate, lastDayLimit, 'default');
                }
            }
        });

        // Initialize Flatpickr when modal opens
        const scheduleModalEl = document.getElementById('scheduleModal');
        if (scheduleModalEl) {
            scheduleModalEl.addEventListener('show.bs.modal', function () {
                document.getElementById('schedule-mode-default').checked = true;
                fetchHolidaysAndInitPicker(lastSelectedDate);
            });
        }

        // Make calendar icon clickable to open date picker
        const calendarIconTrigger = document.getElementById('calendar-icon-trigger');
        if (calendarIconTrigger) {
            calendarIconTrigger.addEventListener('click', function () {
                if (flatpickrInstance) {
                    flatpickrInstance.open();
                }
            });
        }

        // Schedule submit button
        const scheduleSubmitBtn = document.getElementById('scheduleSubmitBtn');
        if (scheduleSubmitBtn) {
            scheduleSubmitBtn.addEventListener('click', function () {
                const date = document.getElementById('schedule-date').value;
                if (!date) {
                    document.getElementById('schedule-date').classList.add('is-invalid');
                    return;
                }
                document.getElementById('schedule-date').classList.remove('is-invalid');

                fetch(`${baseUrl}/${window.adminBasePath}/bookings/${bookingId}/reschedule`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ schedule_date: date })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const modal = bootstrap.Modal.getInstance(document.getElementById('scheduleModal'));
                            modal.hide();
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: 'Booking rescheduled successfully!',
                                timer: 2000,
                                showConfirmButton: false
                            });
                            setTimeout(() => window.location.reload(), 2000);
                        } else {
                            throw new Error(data.message || 'Failed to reschedule booking.');
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: error.message || 'Failed to reschedule booking.'
                        });
                    });
            });
        }

        // Assign QR Code
        async function assignQR() {
            Swal.fire({
                title: 'Assign QR Code',
                text: 'This feature will open the QR assignment interface',
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Go to QR Page',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirect to QR assignment page or open modal
                    window.location.href = `/${window.adminBasePath}/qrs?booking_id=` + bookingId;
                }
            });
        }

        // Delete Booking (Unused in quick actions but kept for consistency if needed)
        async function deleteBooking() {
             // ... existing delete logic ...
        }

        // Accept Schedule from Show Page
        async function acceptScheduleFromShow() {
            const requestedDate = '{{ $booking->booking_date ? $booking->booking_date->format("F j, Y") : "Not specified" }}';
            const customerNotes = '{{ addslashes($booking->booking_notes ?? "") }}';
            const customerName = '{{ $booking->user ? $booking->user->firstname . " " . $booking->user->lastname : "N/A" }}';

            // Escape HTML to prevent XSS
            const escapeHtml = (text) => {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            };

            const htmlContent = `
                <div class="text-start mb-3">
                    <div class="border-bottom pb-2 mb-2">
                        <p class="mb-2"><strong class="text-muted">Customer:</strong> ${customerName}</p>
                        <p class="mb-0"><strong class="text-muted">Requested Date:</strong> <span class="text-primary">${requestedDate}</span></p>
                    </div>
                    ${customerNotes && customerNotes.trim() ? `
                        <div class="mb-3">
                            <label class="form-label mb-2" style="font-weight: 600; color: #495057;"><strong>Customer Notes:</strong></label>
                            <div class="alert alert-info py-3 mb-0" style="background-color: #d1ecf1; border-left: 4px solid #0dcaf0;">
                                <div class="d-flex align-items-start">
                                    <i class="ri-message-3-line me-2 mt-1" style="color: #0dcaf0; font-size: 1.1rem;"></i>
                                    <div style="color: #055160; line-height: 1.6; white-space: pre-wrap;">${escapeHtml(customerNotes)}</div>
                                </div>
                            </div>
                        </div>
                    ` : ''}
                    <div>
                        <label class="form-label mb-2" style="font-weight: 600; color: #495057;"><strong>Admin Notes (Optional):</strong></label>
                    </div>
                </div>
            `;

            const result = await Swal.fire({
                title: 'Accept Schedule?',
                html: htmlContent,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Accept',
                cancelButtonText: 'Cancel',
                input: 'textarea',
                inputPlaceholder: 'Add admin notes (optional)...',
                inputAttributes: {
                    maxlength: 500
                },
                width: '600px'
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch(`${baseUrl}/${window.adminBasePath}/pending-schedules/${bookingId}/accept`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ notes: result.value || null })
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        await Swal.fire({
                            icon: 'success',
                            title: 'Accepted!',
                            text: data.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        window.location.reload();
                    } else {
                        throw new Error(data.message || 'Failed to accept schedule');
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'Failed to accept schedule'
                    });
                }
            }
        }

        // Decline Schedule from Show Page
        async function declineScheduleFromShow() {
            const requestedDate = '{{ $booking->booking_date ? $booking->booking_date->format("F j, Y") : "Not specified" }}';
            const customerNotes = '{{ addslashes($booking->booking_notes ?? "") }}';
            const customerName = '{{ $booking->user ? $booking->user->firstname . " " . $booking->user->lastname : "N/A" }}';

            // Escape HTML to prevent XSS
            const escapeHtml = (text) => {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            };

            const htmlContent = `
                <div class="text-start mb-3">
                    <div class="border-bottom pb-2 mb-2">
                        <p class="mb-2"><strong class="text-muted">Customer:</strong> ${customerName}</p>
                        <p class="mb-0"><strong class="text-muted">Requested Date:</strong> <span class="text-primary">${requestedDate}</span></p>
                    </div>
                    ${customerNotes && customerNotes.trim() ? `
                        <div class="mb-3">
                            <label class="form-label mb-2" style="font-weight: 600; color: #495057;"><strong>Customer Notes:</strong></label>
                            <div class="alert alert-info py-3 mb-0" style="background-color: #d1ecf1; border-left: 4px solid #0dcaf0;">
                                <div class="d-flex align-items-start">
                                    <i class="ri-message-3-line me-2 mt-1" style="color: #0dcaf0; font-size: 1.1rem;"></i>
                                    <div style="color: #055160; line-height: 1.6; white-space: pre-wrap;">${escapeHtml(customerNotes)}</div>
                                </div>
                            </div>
                        </div>
                    ` : ''}
                    <div>
                        <label class="form-label mb-2" style="font-weight: 600; color: #495057;"><strong>Reason for Decline:</strong> <span class="text-danger">*</span></label>
                    </div>
                </div>
            `;

            const result = await Swal.fire({
                title: 'Decline Schedule?',
                html: htmlContent,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Decline',
                cancelButtonText: 'Cancel',
                input: 'textarea',
                inputPlaceholder: 'Enter reason for declining...',
                inputAttributes: {
                    maxlength: 500,
                    required: true
                },
                inputValidator: (value) => {
                    if (!value) {
                        return 'You must provide a reason!'
                    }
                },
                width: '600px'
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch(`${baseUrl}/${window.adminBasePath}/pending-schedules/${bookingId}/decline`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ reason: result.value })
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        await Swal.fire({
                            icon: 'success',
                            title: 'Declined!',
                            text: data.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        window.location.reload();
                    } else {
                        throw new Error(data.message || 'Failed to decline schedule');
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'Failed to decline schedule'
                    });
                }
            }
        }

        // Handle assignment form submission
        document.addEventListener('DOMContentLoaded', function () {
            const assignForm = document.getElementById('assignBookingForm');
            if (assignForm) {
                assignForm.addEventListener('submit', function (e) {
                    e.preventDefault();

                    const bookingId = {{ $booking->id }};
                    const userId = document.getElementById('assignPhotographer').value;
                    const time = document.getElementById('assignTime').value;

                    if (!userId || !time) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Required Fields',
                            text: 'Please select a photographer and set a time'
                        });
                        return;
                    }

                    // Client-side double check: ensure selected time is within allowed range and aligned to slots
                    const assignTimeInput = document.getElementById('assignTime');
                    const modalEl = document.getElementById('assignBookingModal');
                    const availableFrom = modalEl?.dataset?.photographerFrom || '08:00';
                    const availableTo = modalEl?.dataset?.photographerTo || '21:00';
                    const workingDuration = parseInt(modalEl?.dataset?.photographerDuration || '60', 10);

                    function toMinutesLocal(t) {
                        const parts = (t || '').split(':'); if (parts.length < 2) return null; return parseInt(parts[0], 10) * 60 + parseInt(parts[1], 10);
                    }

                    const timeMins = toMinutesLocal(time);
                    const fromMins = toMinutesLocal(availableFrom);
                    const toMins = toMinutesLocal(availableTo);

                    if (timeMins === null) {
                        Swal.fire({ icon: 'warning', title: 'Invalid Time', text: 'Please choose a valid time.' });
                        return;
                    }

                    if (toMins < fromMins) {
                        Swal.fire({ icon: 'warning', title: 'No Available Slot', text: 'No available slots configured. Please update settings.' });
                        return;
                    }

                    if (timeMins < fromMins || timeMins > toMins) {
                        Swal.fire({ icon: 'warning', title: 'Time Not Allowed', text: `Selected time is outside allowed photographer availability (${availableFrom} — ${availableTo}).` });
                        return;
                    }

                    // Disable submit button to prevent double submission
                    const submitBtn = this.querySelector('button[type="submit"]');
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Assigning...';

                    const formData = new FormData();
                    formData.append('booking_id', bookingId);
                    formData.append('user_id', userId);
                    formData.append('time', time);
                    formData.append('_token', csrfToken);

                    const storeUrl = this.getAttribute('action');

                    fetch(storeUrl, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        body: formData
                    })
                        .then(response => {
                            if (!response.ok) {
                                return response.json().then(err => Promise.reject(err));
                            }
                            return response.json();
                        })
                        .then(data => {
                            // Close modal
                            const modalElement = document.getElementById('assignBookingModal');
                            const modalInstance = bootstrap.Modal.getInstance(modalElement);
                            if (modalInstance) {
                                modalInstance.hide();
                            }

                            // Show success message
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: data.message || 'Booking assigned successfully',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                // Reload page to show updated assignment
                                window.location.reload();
                            });
                        })
                        .catch(error => {
                            console.error('Error:', error);

                            let errorMessage = 'Failed to assign booking';
                            if (error.message) {
                                errorMessage = error.message;
                            } else if (error.errors) {
                                errorMessage = Object.values(error.errors).flat().join(', ');
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: errorMessage
                            });
                        })
                        .finally(() => {
                            // Re-enable submit button
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = 'Assign';
                        });
                });
            }

            // Assign Booking to Photographer Modal - Slot loading functionality
            function initializeAssignModal() {
                const assignPhotographerEl = document.getElementById('assignPhotographer');
                const assignTimeEl = document.getElementById('assignTime');
                const helper = document.getElementById('assignTimeHelper');
                const slotModeAvailable = document.getElementById('slotModeAvailable');
                const slotModeAny = document.getElementById('slotModeAny');

                const setHelper = (msg) => { if (helper) helper.textContent = msg; };
                const resetSelect = () => { assignTimeEl.disabled = true; assignTimeEl.innerHTML = '<option value="">Select a time</option>'; assignTimeEl.value = ''; };

                setHelper('Select a photographer first to see available slots from the API, or choose "Pick any" to ignore conflicts.');
                resetSelect();
                if (slotModeAvailable) slotModeAvailable.checked = true;
                if (slotModeAny) slotModeAny.checked = false;

                const modalEl = document.getElementById('assignBookingModal');
                const availableFrom = modalEl?.dataset?.photographerFrom || '08:00';
                const availableTo = modalEl?.dataset?.photographerTo || '21:00';
                const workingDuration = parseInt(modalEl?.dataset?.photographerDuration || '60', 10);

                function toMinutes(t) {
                    const parts = (t || '').split(':');
                    if (parts.length < 2) return null;
                    return parseInt(parts[0], 10) * 60 + parseInt(parts[1], 10);
                }

                function formatHM(m) {
                    const h = Math.floor(m / 60).toString().padStart(2, '0');
                    const mm = (m % 60).toString().padStart(2, '0');
                    return `${h}:${mm}`;
                }

                function formatDisplay(m) {
                    const hours = Math.floor(m / 60);
                    const minutes = m % 60;
                    const period = hours >= 12 ? 'PM' : 'AM';
                    let h12 = hours % 12;
                    if (h12 === 0) h12 = 12;
                    return `${h12}:${minutes.toString().padStart(2, '0')} ${period}`;
                }

                const fromM = toMinutes(availableFrom);
                const toM = toMinutes(availableTo);
                const slotStep = 15;

                const getSlotMode = () => (slotModeAny?.checked ? 'any' : 'available');

                function buildAllSlots() {
                    assignTimeEl.innerHTML = '<option value="">Select a time</option>';
                    if (toM < fromM) return;
                    for (let t = fromM; t <= toM; t += slotStep) {
                        const candidateEnd = t + (workingDuration || 60);
                        if (candidateEnd > toM) continue;
                        const opt = document.createElement('option');
                        opt.value = formatHM(t);
                        opt.textContent = formatDisplay(t);
                        assignTimeEl.appendChild(opt);
                    }
                    if (assignTimeEl.options.length > 1) {
                        assignTimeEl.disabled = false;
                        assignTimeEl.value = assignTimeEl.options[1].value;
                    } else {
                        assignTimeEl.disabled = true;
                    }
                }

                function loadSlots() {
                    if (!assignPhotographerEl.value) {
                        resetSelect();
                        setHelper('Select a photographer first to see available slots from the API, or choose "Pick any" to ignore conflicts.');
                        return;
                    }

                    if (toM < fromM) {
                        resetSelect();
                        setHelper('No available slots for photographers. Please update settings.');
                        return;
                    }

                    const dateVal = document.getElementById('modalDate').value;
                    if (!dateVal) {
                        resetSelect();
                        setHelper('Please select a booking date first.');
                        return;
                    }

                    if (getSlotMode() === 'any') {
                        buildAllSlots();
                        setHelper(`Pick any slot between ${formatDisplay(fromM)} — ${formatDisplay(toM)} (every ${slotStep} min)`);
                        return;
                    }

                    setHelper('Loading photographer slots...');
                    assignTimeEl.disabled = true;
                    assignTimeEl.innerHTML = '<option value="">Loading...</option>';

                    fetch(`${apiBaseUrl}/booking-assignees/slots?date=${encodeURIComponent(dateVal)}&user_id=${encodeURIComponent(assignPhotographerEl.value)}`, {
                        method: 'GET',
                        credentials: 'same-origin',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            if (response.status === 403) {
                                setHelper('Forbidden to view slots for selected user.');
                                resetSelect();
                                return Promise.reject({ message: 'Forbidden' });
                            }
                            return response.json().then(err => Promise.reject(err));
                        }
                        return response.json();
                    })
                    .then(json => {
                        if (!json || json.success === false) {
                            setHelper(json?.message || 'Failed to load slots');
                            resetSelect();
                            return;
                        }

                        const duration = workingDuration || 60;
                        const occupiedIntervals = [];

                        (json.data || []).forEach(s => {
                            if (!s.time) return;
                            const parts = s.time.split(':');
                            if (parts.length < 2) return;
                            const start = parseInt(parts[0], 10) * 60 + parseInt(parts[1], 10);
                            const end = start + duration;
                            occupiedIntervals.push({ start, end });
                        });

                        assignTimeEl.innerHTML = '<option value="">Select a time</option>';
                        for (let t = fromM; t <= toM; t += slotStep) {
                            const candidateStart = t;
                            const candidateEnd = t + duration;
                            if (candidateEnd > toM) continue;

                            let overlaps = false;
                            for (const occ of occupiedIntervals) {
                                if (candidateStart < occ.end && candidateEnd > occ.start) {
                                    overlaps = true;
                                    break;
                                }
                            }

                            if (overlaps) continue;

                            const opt = document.createElement('option');
                            opt.value = formatHM(t);
                            opt.textContent = formatDisplay(t);
                            assignTimeEl.appendChild(opt);
                        }

                        if (assignTimeEl.options.length <= 1) {
                            resetSelect();
                            setHelper('No available slots on this date for selected photographer.');
                        } else {
                            assignTimeEl.disabled = false;
                            if (!assignTimeEl.value && assignTimeEl.options.length > 1) {
                                assignTimeEl.value = assignTimeEl.options[1].value;
                            }
                            assignTimeEl.focus();
                            setHelper(`Available slots: ${formatDisplay(fromM)} — ${formatDisplay(toM)} (every ${slotStep} min)`);
                        }
                    })
                    .catch(err => {
                        console.error('Error loading slots:', err);
                        setHelper(err?.message || 'Failed to load slots.');
                        resetSelect();
                    });
                }

                assignPhotographerEl.onchange = loadSlots;
                if (slotModeAvailable) slotModeAvailable.onchange = loadSlots;
                if (slotModeAny) slotModeAny.onchange = loadSlots;
            }

            // Initialize when modal is shown
            const assignModalEl = document.getElementById('assignBookingModal');
            if (assignModalEl) {
                assignModalEl.addEventListener('show.bs.modal', function () {
                    // Populate booking details
                    document.getElementById('modalCustomer').textContent = '{{ $booking->user ? $booking->user->firstname . " " . $booking->user->lastname : "-" }}';
                    document.getElementById('modalPincode').textContent = '{{ $booking->pin_code ?? "-" }}';
                    document.getElementById('modalAddress').textContent = `{{ $booking->full_address ?? ($booking->house_no . ", " . $booking->building . ", " . ($booking->society_name ?? "") . ", " . ($booking->address_area ?? "")) }}`;
                    document.getElementById('modalCity').textContent = '{{ $booking->city?->name ?? "-" }}';
                    document.getElementById('modalState').textContent = '{{ $booking->state?->name ?? "-" }}';
                    
                    // Set booking date
                    const dateInput = document.getElementById('modalDate');
                    dateInput.value = '{{ $booking->booking_date ? $booking->booking_date->format("Y-m-d") : "" }}';
                    
                    initializeAssignModal();
                });
            }
        });
    </script>
