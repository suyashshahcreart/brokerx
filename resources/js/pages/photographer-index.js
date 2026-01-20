/*
Template Name: Lahomes - Real Estate Admin Dashboard Template
Author: Techzaa
File: schedule js
*/
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import listPlugin from '@fullcalendar/list'
import { Modal } from 'bootstrap';
import Swal from 'sweetalert2';

// ---------------------------
// Utilities & helpers
// ---------------------------

/** Map booking status to Bootstrap bg class */
function statusToClass(statusRaw) {
    const status = (statusRaw || '').toLowerCase();
    switch (status) {
        case 'schedul_assign':
        case 'reschedul_assign':
            return 'bg-success';
        case 'schedul_accepted':
        case 'reschedul_accepted':
            return 'bg-warning';
        case 'schedul_inprogress':
            return 'bg-info';
        case 'schedul_completed':
            return 'bg-success';
        case 'cancelled':
            return 'bg-danger';
        case 'pending':
        case 'schedul_pending':
            return 'bg-secondary';
        case 'confirmed':
        case 'scheduled':
            return 'bg-primary';
        default:
            return 'bg-secondary';
    }
}

/** Convert booking_date (assumed UTC string) into IST YYYY-MM-DD */
function toIstDateOnly(utcDateString) {
    const IST_OFFSET_MIN = 5 * 60 + 30; // +05:30
    const parsed = new Date(utcDateString);
    if (isNaN(parsed)) return (utcDateString || '').split('T')[0] || '';
    const istMs = parsed.getTime() + IST_OFFSET_MIN * 60 * 1000;
    const ist = new Date(istMs);
    const pad = (n) => String(n).padStart(2, '0');
    return `${ist.getFullYear()}-${pad(ist.getMonth() + 1)}-${pad(ist.getDate())}`;
}

/**
 * Compute event timing from booking date/time using IST conversion.
 * Returns { start, end, allDay, dateOnly }
 */
function computeEventTimes(bookingDate, bookingTime, durationMin = 120) {
    const dateOnly = toIstDateOnly(bookingDate);
    if (!bookingTime) {
        return { start: dateOnly, end: undefined, allDay: true, dateOnly };
    }

    const t = ('' + bookingTime).trim();
    const normalized = t.length === 5 ? `${t}:00` : t; // HH:MM -> HH:MM:SS
    const parts = normalized.split(':').map(Number);
    const hh = parts[0] || 0;
    const mm = parts[1] || 0;
    const ss = parts[2] || 0;

    const IST_OFFSET_MIN = 5 * 60 + 30;
    const [yStr, mStr, dStr] = dateOnly.split('-');
    const y = parseInt(yStr, 10);
    const m = parseInt(mStr, 10);
    const d = parseInt(dStr, 10);
    const utcMsForIstLocal = Date.UTC(y, m - 1, d, hh, mm, ss) - IST_OFFSET_MIN * 60 * 1000;
    const startDt = new Date(utcMsForIstLocal);
    const endDt = new Date(utcMsForIstLocal + durationMin * 60 * 1000);
    return { start: startDt.toISOString(), end: endDt.toISOString(), allDay: false, dateOnly };
}

/** Format event title from booking data */
function formatEventTitle(booking) {
    let title = '';
    const rawTime = booking.booking_time;
    if (rawTime) {
        const [h, m] = rawTime.split(':');
        const date = new Date();
        date.setHours(h, m, 0);
        const formattedTime = date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        title += `${formattedTime} `;
    }
    title += booking.firm_name || `Booking #${booking.id}`;
    return { title, rawTime };
}

/** Build extendedProps payload for calendar events */
function buildExtendedProps(booking, assignee, rawTime) {
    return {
        assigneeId: assignee?.id ?? null,
        bookingId: booking.id,
        status: booking.status,
        paymentStatus: booking.payment_status,
        price: booking.price,
        address: booking.full_address,
        tourCode: booking.tour_code,
        bookingTime: rawTime,
        propertyType: `${booking?.propertyType?.name || ''} / ${booking?.propertySubType?.name || ''}`,
        assignmentTime: assignee?.time ?? null,
        pincode: booking.pin_code,
        user: booking.user,
        city: booking.city,
        state: booking.state,
        photographer: assignee?.user ?? null,
    };
}

// date formate function formatDateTime(isoString, locale = 'en-IN') {
function formatDateOnly(isoString, locale = 'en-IN') {
    const d = new Date(isoString);

    if (isNaN(d)) return 'Invalid Date';

    return new Intl.DateTimeFormat(locale, {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    }).format(d);
}


class CalendarSchedule {

    constructor() {
        this.body = document.body;
        this.modal = new Modal(document.getElementById('event-modal'), { backdrop: 'static' });
        this.assignModal = new Modal(document.getElementById('assignBookingModal'), { backdrop: 'static' });
        this.calendar = document.getElementById('calendar');
        this.formEvent = document.getElementById('forms-event');
        this.btnNewEvent = document.getElementById('btn-new-event');
        this.btnDeleteEvent = document.getElementById('btn-delete-event'); // May not exist
        this.btnSaveEvent = document.getElementById('btn-save-event'); // May not exist
        this.modalTitle = document.getElementById('modal-title');
        this.calendarObj = null;
        this.selectedEvent = null;
        this.newEventData = null;
        this.isAdmin = (this.calendar?.getAttribute('data-is-admin') === '1');
        this.photoCard = document.getElementById('photographer-details-card');
        this.photoCardFields = {
            bookingId: document.getElementById('ph-booking-id'),
            customer: document.getElementById('ph-customer'),
            property: document.getElementById('ph-property'),
            address: document.getElementById('ph-address'),
            cityState: document.getElementById('ph-city-state'),
            pincode: document.getElementById('ph-pincode'),
            date: document.getElementById('ph-date'),
            time: document.getElementById('ph-time'),
            status: document.getElementById('ph-status'),
        };

        // Routes for actions
        this.checkInRouteTpl = this.calendar?.getAttribute('data-check-in-route') || '';
        this.checkOutRouteTpl = this.calendar?.getAttribute('data-check-out-route') || '';
        this.bookingShowRouteTpl = this.calendar?.getAttribute('data-booking-show-route') || '';
        // Accept/Decline routes from Blade (with :id placeholder)
        this.acceptRouteTpl = (window.PENDING_SCHEDULE_ACCEPT_URL || '').replace('%3Aid', ':id');
        this.declineRouteTpl = (window.PENDING_SCHEDULE_DECLINE_URL || '').replace('%3Aid', ':id');
    }

    // Event Clck {{ BOOKING SELECT AND mODEL OPEN }}
    onEventClick(info) {
        // Fetch bookings from API
        this.formEvent?.classList.remove('was-validated');
        this.newEventData = null;
        if (this.btnDeleteEvent) {
            this.btnDeleteEvent.style.display = "none";
        }
        this.modalTitle.textContent = 'Booking Assignment - Details';

        this.selectedEvent = info.event;
        const props = this.selectedEvent.extendedProps;

        // Safely format time
        let formattedTime = '—';
        if (props.bookingTime) {
            const [h, m] = props.bookingTime.split(':');
            const date = new Date();
            date.setHours(h, m, 0);
            formattedTime = date.toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit',
            });
        }

        // For photographers, also show inline card but do NOT return early
        if (!this.isAdmin) {
            this.updatePhotographerDetails(props, formattedTime);
        }
        // Fill modal details
        const user = props.user || {};
        const city = props.city || {};
        const state = props.state || {};
        const property = props.propertyType || '';

        const bookingIdEl = document.getElementById('modal-booking-id');
        const customerEl = document.getElementById('modal-booking-customer');
        const propertyEl = document.getElementById('modal-booking-property');
        const addressEl = document.getElementById('modal-booking-address');
        const cityStateEl = document.getElementById('modal-booking-city-state');
        const pincodeEl = document.getElementById('modal-booking-pincode');
        const schedDateEl = document.getElementById('modal-schedule-date');
        const schedTimeEl = document.getElementById('modal-schedule-time');
        const photographerNameEl = document.getElementById('modal-photographer-name');
        const photographerMobileEl = document.getElementById('modal-photographer-mobile');
        const assignedTimeEl = document.getElementById('modal-assigned-time');

        if (bookingIdEl) bookingIdEl.textContent = String(props.bookingId || '—');
        if (customerEl) customerEl.textContent = `${user.firstname || ''} ${user.lastname || ''}`.trim() || '—';
        if (propertyEl) propertyEl.textContent = property || '—';
        if (addressEl) addressEl.textContent = props.address || '—';
        if (cityStateEl) cityStateEl.textContent = `${city.name || ''} / ${state.name || ''}`.trim() || '—';
        if (pincodeEl) pincodeEl.textContent = props.pincode || '—';
        if (schedDateEl) schedDateEl.textContent = formatDateOnly(this.selectedEvent.startStr || props.dateOnly || '');
        if (schedTimeEl) schedTimeEl.textContent = formattedTime;

        // Photographer & assignment info (for admins visibility)
        const ph = props.photographer || null;
        if (photographerNameEl) photographerNameEl.textContent = ph ? `${ph.firstname || ''} ${ph.lastname || ''}`.trim() || '-' : '-';
        if (photographerMobileEl) photographerMobileEl.textContent = ph?.mobile || '-';
        if (assignedTimeEl) assignedTimeEl.textContent = props.assignmentTime || props.bookingTime || '-';

        // Determine which module to show based on status
        const status = (props.status || '').toLowerCase();
        const assigneeId = props.assigneeId;
        const bookingId = props.bookingId;

        // Hide all modules first
        const pendingModule = document.getElementById('modal-accept-decline-module');
        const assignModule = document.getElementById('modal-assign-photographer-module');
        const checkinModule = document.getElementById('modal-checkin-checkout-module');

        if (pendingModule) pendingModule.style.display = 'none';
        if (assignModule) assignModule.style.display = 'none';
        if (checkinModule) checkinModule.style.display = 'none';

        // Clear existing buttons
        const btnContainer = document.getElementById('modal-buttons-container');
        if (btnContainer) {
            const prevAssign = document.getElementById('modal-assign-btn');
            if (prevAssign && prevAssign.parentElement === btnContainer) {
                btnContainer.removeChild(prevAssign);
            }
        }

        // Show MODULE based on status (only for ADMIN)
        if (this.isAdmin) {
            if (status === 'schedul_pending' || status === 'pending') {
                // MODULE 1: Accept/Decline
                if (pendingModule) {
                    pendingModule.style.display = 'block';
                    const acceptDeclineButtons = document.getElementById('modal-accept-decline-buttons');
                    const viewPendingLink = document.getElementById('modal-view-booking-link-pending');

                    // Show action buttons
                    if (acceptDeclineButtons) acceptDeclineButtons.style.display = 'flex';
                    if (viewPendingLink && this.bookingShowRouteTpl) {
                        viewPendingLink.href = this.bookingShowRouteTpl.replace(':id', String(bookingId));
                    }
                }
            } else if (status === 'schedul_accepted' || status === 'reschedul_accepted') {
                // MODULE 2: Assign Photographer - Open directly
                this.openAssignModal(props);
                return; // Skip showing event-modal, go straight to assign modal
            } else if (status === 'schedul_assign' || status === 'reschedul_assign' || status === 'schedul_inprogress' || status === 'schedul_completed') {
                // Admin should NOT see check-in/out buttons, but can see details and view link
                if (checkinModule) {
                    checkinModule.style.display = 'block';
                    this.setupCheckInCheckOutButtons(props, assigneeId, /*forceHideActionsForAdmin=*/true);
                }
            }
        } else {
            // PHOTOGRAPHER VIEW: Only show Check-in/Check-out module if schedule_assigned
            if (status === 'schedul_assign' || status === 'reschedul_assign' || status === 'schedul_inprogress' || status === 'schedul_completed') {
                if (checkinModule) {
                    checkinModule.style.display = 'block';
                    this.setupCheckInCheckOutButtons(props, assigneeId);
                }
            }
        }

        this.modal.show();
    }

    // Setup Check-In / Check-Out buttons (reusable logic)
    setupCheckInCheckOutButtons(props, assigneeId, forceHideActionsForAdmin = false) {
        const checkInLink = document.getElementById('modal-check-in-link');
        const checkOutLink = document.getElementById('modal-check-out-link');
        const viewBookingLink = document.getElementById('modal-view-booking-link');
        const completedLink = document.getElementById('modal-completed-link');
        const status = (props.status || '').toLowerCase();

        // Hide all by default
        if (checkInLink) checkInLink.style.display = 'none';
        if (checkOutLink) checkOutLink.style.display = 'none';
        if (completedLink) completedLink.style.display = 'none';
        if (viewBookingLink) viewBookingLink.style.display = 'none';

        // Show appropriate action based on booking status
        if (assigneeId) {
            const canBuildCheckIn = !!this.checkInRouteTpl;
            const canBuildCheckOut = !!this.checkOutRouteTpl;

            if (!this.isAdmin || !forceHideActionsForAdmin) {
                if (status === 'schedul_inprogress') {
                    if (checkOutLink && canBuildCheckOut) {
                        checkOutLink.href = this.checkOutRouteTpl.replace(':id', String(assigneeId));
                        checkOutLink.style.display = 'inline-block';
                    }
                } else if (status === 'schedul_assign' || status === 'reschedul_assign') {
                    if (checkInLink && canBuildCheckIn) {
                        checkInLink.href = this.checkInRouteTpl.replace(':id', String(assigneeId));
                        checkInLink.style.display = 'inline-block';
                    }
                } else if (status === 'schedul_completed') {
                    if (completedLink) {
                        completedLink.style.display = 'inline-block';
                    }
                }
            }
        }

        // View booking link
        if (viewBookingLink && this.bookingShowRouteTpl) {
            viewBookingLink.href = this.bookingShowRouteTpl.replace(':id', String(props.bookingId));
            viewBookingLink.style.display = 'inline-block';
        }
    }

    // For photographers: show booking basics in the inline card
    updatePhotographerDetails(props, formattedTime) {
        if (!this.photoCard) return;

        const user = props.user || {};
        const city = props.city || {};
        const state = props.state || {};

        const setText = (el, value) => { if (el) el.textContent = value || '—'; };
        setText(this.photoCardFields.bookingId, props.bookingId ? String(props.bookingId) : '—');
        setText(this.photoCardFields.customer, `${user.firstname || ''} ${user.lastname || ''}`.trim() || '—');
        setText(this.photoCardFields.property, props.propertyType || '—');
        setText(this.photoCardFields.address, props.address || '—');
        setText(this.photoCardFields.cityState, `${city.name || ''} / ${state.name || ''}`.trim() || '—');
        setText(this.photoCardFields.pincode, props.pincode || '—');
        setText(this.photoCardFields.date, formatDateOnly(this.selectedEvent?.startStr || props.dateOnly || ''));
        setText(this.photoCardFields.time, formattedTime || '—');
        setText(this.photoCardFields.status, props.status || '—');

        this.photoCard.classList.remove('d-none');
    }

    // EVENT SELECT AND MODEL OPEN
    onSelect(info) {
        this.formEvent?.reset();
        this.formEvent?.classList.remove('was-validated');
        this.selectedEvent = null;
        this.newEventData = info;
        this.btnDeleteEvent.style.display = "none";
        this.modalTitle.textContent = 'Add New Event';
        this.modal.show();
        this.calendarObj.unselect();
    }

    // Fetch bookings from API
    async fetchBookings(fromDate, toDate) {
        try {
            // Get the API URL from the data attribute
            const apiBaseUrl = this.calendar.getAttribute('data-booking-api');
            // Read filter values from the DOM (if present)
            const photographer = document.getElementById('filterPhotographer')?.value;
            const statusFilter = document.getElementById('filterStatus')?.value;
            let url = `${apiBaseUrl}?from_date=${fromDate}&to_date=${toDate}`;
            if (photographer) url += `&photographer=${encodeURIComponent(photographer)}`;
            if (statusFilter) url += `&status=${encodeURIComponent(statusFilter)}`;

            const response = await fetch(url);

            if (!response.ok) {
                console.error('HTTP error:', response.status, response.statusText);
                return [];
            }

            const result = await response.json();

            if (result.success && result.data) {
                const events = [];

                // Process each booking item from API
                result.data.forEach(item => {
                    const booking = item.booking || {};
                    const assignees = item.assignees || [];

                    // Determine event color based on booking status
                    let className = '';
                    let status = booking.status.toLowerCase();

                    switch (status) {
                        case 'schedul_assign':
                            className = 'bg-primary'; // Assigned → primary
                            break;
                        case 'reschedul_assign':
                            className = 'bg-primary'; // Assigned → success
                            break;
                        case 'schedul_accepted':
                            className = 'bg-info'; // Accepted → warning
                            break;
                        case 'schedul_decline':
                            className = 'bg-danger'; // Accepted → warning
                            break;
                        case 'reschedul_accepted':
                            className = 'bg-info'; // Accepted → warning
                            break;
                        case 'schedul_inprogress':
                            className = 'bg-secondary'; // In-progress → info
                            break;
                        case 'schedul_completed':
                            className = 'bg-success'; // Completed → success
                            break;
                        case 'cancelled':
                            className = 'bg-danger';
                            break;
                        case 'schedul_pending':
                            className = 'bg-warning';
                            break;
                        default:
                            className = 'bg-secondary'; // fallback for unknown statuses
                    }
                    // Prefer booking_time
                    const rawTime = booking.booking_time;
                    let title = '';
                    if (rawTime) {
                        const [h, m] = rawTime.split(':');
                        const date = new Date();
                        date.setHours(h, m, 0);

                        const formattedTime = date.toLocaleTimeString([], {
                            hour: '2-digit',
                            minute: '2-digit',
                        });
                        title += `${formattedTime} `;
                    }
                    title += booking.firm_name || `Booking #${booking.id}`;

                    const working_Duration = 120; // minutes
                    const datePart = booking.booking_date;
                    const timePart = booking.booking_time;

                    // Parse booking_date as UTC, then shift to IST (+05:30)
                    const IST_OFFSET_MIN = 5 * 60 + 30; // 330 minutes
                    let dateOnly;
                    const parsedBookingUtc = new Date(datePart);
                    if (!isNaN(parsedBookingUtc)) {
                        const istMs = parsedBookingUtc.getTime() + IST_OFFSET_MIN * 60 * 1000;
                        const istDt = new Date(istMs);
                        const pad = (n) => String(n).padStart(2, '0');
                        dateOnly = `${istDt.getFullYear()}-${pad(istDt.getMonth() + 1)}-${pad(istDt.getDate())}`;
                    } else {
                        dateOnly = (datePart || '').split('T')[0];
                    }

                    // Default values
                    let eventStart = dateOnly;
                    let eventEnd = undefined;
                    let allDay = !timePart;

                    if (timePart) {
                        // Normalize time "HH:MM" -> "HH:MM:SS"
                        const t = ('' + timePart).trim();
                        const normalized = t.length === 5 ? `${t}:00` : t;

                        // Parse time components
                        const parts = normalized.split(':').map(Number);
                        const hh = parts[0] || 0;
                        const mm = parts[1] || 0;
                        const ss = parts[2] || 0;

                        // Compute UTC ms that corresponds to this IST local time
                        const [yStr, mStr, dStr] = dateOnly.split('-');
                        const y = parseInt(yStr, 10);
                        const m = parseInt(mStr, 10);
                        const d = parseInt(dStr, 10);

                        const utcMsForIstLocal = Date.UTC(y, m - 1, d, hh, mm, ss) - IST_OFFSET_MIN * 60 * 1000;
                        const startDt = new Date(utcMsForIstLocal);
                        const endDt = new Date(utcMsForIstLocal + working_Duration * 60 * 1000);

                        eventStart = startDt.toISOString();
                        eventEnd = endDt.toISOString();
                        allDay = false;
                    }

                    // If booking has assignees, create an event for each assignee
                    if (assignees.length > 0) {
                        assignees.forEach((assignee, index) => {
                            events.push({
                                id: `${booking.id}-${assignee.id}-${index}`,
                                title,
                                start: eventStart,
                                end: eventEnd,
                                allDay: allDay,
                                classNames: [className],
                                extendedProps: {
                                    assigneeId: assignee.id,
                                    bookingId: booking.id,
                                    status: booking.status,
                                    paymentStatus: booking.payment_status,
                                    price: booking.price,
                                    address: booking.full_address,
                                    tourCode: booking.tour_code,
                                    bookingTime: rawTime,
                                    propertyType: (booking.propertyType?.name || '') + " / " + (booking.propertySubType?.name || ''),
                                    assignmentTime: assignee.time,
                                    pincode: booking.pin_code,
                                    user: booking.user,
                                    city: booking.city,
                                    state: booking.state,
                                    photographer: assignee.user
                                }
                            });
                        });
                    } else {
                        // For bookings without assignees, create one event
                        events.push({
                            id: `booking-${booking.id}`,
                            title,
                            start: eventStart,
                            end: eventEnd,
                            allDay: allDay,
                            classNames: [className],
                            extendedProps: {
                                assigneeId: null,
                                bookingId: booking.id,
                                status: booking.status,
                                paymentStatus: booking.payment_status,
                                price: booking.price,
                                address: booking.full_address,
                                tourCode: booking.tour_code,
                                bookingTime: rawTime,
                                propertyType: (booking.propertyType?.name || '') + " / " + (booking.propertySubType?.name || ''),
                                assignmentTime: null,
                                pincode: booking.pin_code,
                                user: booking.user,
                                city: booking.city,
                                state: booking.state,
                                photographer: null
                            }
                        });
                    }
                });

                return events;
            }
            return [];
        } catch (error) {
            console.error('Error fetching bookings:', error);
            return [];
        }
    }

    // Open assignment modal and prefill details
    openAssignModal(props) {
        const modalEl = document.getElementById('assignBookingModal');
        if (!modalEl) return;

        // Hidden fields
        const bookingIdInput = document.getElementById('assignBookingId');
        const dateInputHidden = document.getElementById('assignDate');

        const dateStr = (this.selectedEvent && this.selectedEvent.startStr) ? this.selectedEvent.startStr : '';
        if (bookingIdInput) bookingIdInput.value = String(props.bookingId || '');
        if (dateInputHidden) dateInputHidden.value = (dateStr || '').split('T')[0] || '';

        // Visible booking details
        const customerEl = document.getElementById('modalCustomer');
        const customerMobileEl = document.getElementById('modalCustomerMobile');
        const propertyTypeEl = document.getElementById('modalPropertyType');
        const scheduledDateEl = document.getElementById('modalScheduledDate');
        const pinEl = document.getElementById('modalPincode');
        const addrEl = document.getElementById('modalAddress');
        const cityEl = document.getElementById('modalCity');
        const stateEl = document.getElementById('modalState');
        const dateEl = document.getElementById('modalDate');

        if (customerEl) customerEl.textContent = `${props?.user?.firstname || ''} ${props?.user?.lastname || ''}`.trim() || '-';
        if (customerMobileEl) customerMobileEl.textContent = props?.user?.mobile || '-';
        if (propertyTypeEl) propertyTypeEl.textContent = props.propertyType || '-';
        if (scheduledDateEl) scheduledDateEl.textContent = formatDateOnly(dateStr || props.dateOnly || '');
        if (pinEl) pinEl.textContent = props.pincode || '-';
        if (addrEl) addrEl.textContent = props.address || '-';
        if (cityEl) cityEl.textContent = props?.city?.name || '-';
        if (stateEl) stateEl.textContent = props?.state?.name || '-';
        if (dateEl) dateEl.value = (dateStr || '').split('T')[0] || '';

        // Reset selects and slot mode
        const photographerSel = document.getElementById('assignPhotographer');
        const timeSel = document.getElementById('assignTime');
        const timeHelper = document.getElementById('assignTimeHelper');
        const slotModeAvailable = document.getElementById('slotModeAvailable');
        const slotModeAny = document.getElementById('slotModeAny');

        if (photographerSel) photographerSel.value = '';
        if (timeSel) {
            timeSel.innerHTML = '<option value="">Select a time</option>';
            timeSel.disabled = true;
            timeSel.value = '';
        }
        if (timeHelper) timeHelper.textContent = 'Select a photographer first to see available slots from the API, or choose "Pick any" to ignore conflicts.';

        // Reset slot mode to "Available Slots Only"
        if (slotModeAvailable) slotModeAvailable.checked = true;
        if (slotModeAny) slotModeAny.checked = false;

        // When photographer or slot mode changes, load slots
        if (photographerSel) {
            photographerSel.onchange = () => this.loadSlotsForPhotographer(photographerSel, timeSel, timeHelper, dateEl, modalEl);
        }
        if (slotModeAvailable) {
            slotModeAvailable.onchange = () => this.loadSlotsForPhotographer(photographerSel, timeSel, timeHelper, dateEl, modalEl);
        }
        if (slotModeAny) {
            slotModeAny.onchange = () => this.loadSlotsForPhotographer(photographerSel, timeSel, timeHelper, dateEl, modalEl);
        }

        this.assignModal.show();
    }

    // Load slots for photographer based on selected mode (Available or Any)
    loadSlotsForPhotographer(photographerSel, timeSel, timeHelper, dateEl, modalEl) {
        if (!photographerSel || !timeSel || !timeHelper || !dateEl || !modalEl) return;

        // Read photographer availability settings
        const availableFrom = modalEl.getAttribute('data-photographer-from') || '08:00';
        const availableTo = modalEl.getAttribute('data-photographer-to') || '21:00';
        const workingDuration = parseInt(modalEl.getAttribute('data-photographer-duration') || '60', 10);
        const slotStep = 15; // minutes between options

        // Helper functions
        const toMinutes = (t) => {
            const parts = (t || '').split(':');
            if (parts.length < 2) return null;
            return parseInt(parts[0], 10) * 60 + parseInt(parts[1], 10);
        };

        const formatHM = (m) => {
            const h = Math.floor(m / 60).toString().padStart(2, '0');
            const mm = (m % 60).toString().padStart(2, '0');
            return `${h}:${mm}`;
        };

        // Display in 12-hour format with AM/PM for user-friendly labels
        const formatDisplay = (m) => {
            const hours = Math.floor(m / 60);
            const minutes = m % 60;
            const period = hours >= 12 ? 'PM' : 'AM';
            let h12 = hours % 12;
            if (h12 === 0) h12 = 12;
            return `${h12}:${minutes.toString().padStart(2, '0')} ${period}`;
        };

        const fromM = toMinutes(availableFrom);
        const toM = toMinutes(availableTo);

        const setHelper = (msg) => { if (timeHelper) timeHelper.textContent = msg; };
        const resetSelect = () => {
            timeSel.disabled = true;
            timeSel.innerHTML = '<option value="">Select a time</option>';
            timeSel.value = '';
        };

        // Get slot mode
        const slotModeAny = document.getElementById('slotModeAny');
        const getSlotMode = () => (slotModeAny?.checked ? 'any' : 'available');

        // Helper to check if time is in the past
        const isPastTime = (timeInMinutes, selectedDate) => {
            if (!selectedDate) return false;
            
            const now = new Date();
            // Parse selectedDate string (YYYY-MM-DD) to avoid timezone issues
            const [year, month, day] = selectedDate.split('-').map(Number);
            const selectedDateObj = new Date(year, month - 1, day);
            const todayDateObj = new Date(now.getFullYear(), now.getMonth(), now.getDate());
            
            // If selected date is not today, no filtering needed
            if (selectedDateObj.getTime() !== todayDateObj.getTime()) {
                return false;
            }
            
            // For today, check if the slot start time has passed
            const currentMinutes = now.getHours() * 60 + now.getMinutes();
            return timeInMinutes <= currentMinutes;
        };

        // Build all possible slots
        const buildAllSlots = () => {
            timeSel.innerHTML = '<option value="">Select a time</option>';
            if (toM < fromM) return;
            const dateVal = dateEl.value;
            for (let t = fromM; t <= toM; t += slotStep) {
                // Skip past times if date is today
                if (dateVal && isPastTime(t, dateVal)) continue;
                
                const opt = document.createElement('option');
                opt.value = formatHM(t);
                opt.textContent = formatDisplay(t);
                timeSel.appendChild(opt);
            }
            if (timeSel.options.length > 1) {
                timeSel.disabled = false;
                timeSel.value = timeSel.options[1].value;
            } else {
                timeSel.disabled = true;
            }
        };

        // Check if photographer is selected
        if (!photographerSel.value) {
            resetSelect();
            setHelper('Select a photographer first to see available slots from the API, or choose "Pick any" to ignore conflicts.');
            return;
        }

        // Check if time range is valid
        if (toM < fromM) {
            resetSelect();
            setHelper('No available slots for photographers. Please update settings.');
            return;
        }

        // Get booking date
        const dateVal = dateEl.value;
        if (!dateVal) {
            resetSelect();
            setHelper('Please select a booking date first.');
            return;
        }

        // Mode: pick any (ignore existing assignments)
        if (getSlotMode() === 'any') {
            buildAllSlots();
            setHelper(`Pick any slot between ${formatDisplay(fromM)} — ${formatDisplay(toM)} (every ${slotStep} min)`);
            return;
        }

        // Mode: available (default) -> fetch and filter
        setHelper('Loading photographer slots...');
        timeSel.disabled = true;
        timeSel.innerHTML = '<option value="">Loading...</option>';

        fetch(`/api/booking-assignees/slots?date=${encodeURIComponent(dateVal)}&user_id=${encodeURIComponent(photographerSel.value)}`, {
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
                        return response.json().then(err => {
                            throw new Error(err?.message || 'Access denied');
                        });
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

                const occupiedIntervals = [];

                (json.data || []).forEach(s => {
                    if (!s.time) return;
                    const parts = s.time.split(':');
                    if (parts.length < 2) return;
                    const start = parseInt(parts[0], 10) * 60 + parseInt(parts[1], 10);
                    // Add buffer time equal to working duration after the working duration
                    const bufferTime = workingDuration;
                    const end = start + workingDuration + bufferTime;
                    occupiedIntervals.push({ start, end });
                });

                timeSel.innerHTML = '<option value="">Select a time</option>';
                for (let t = fromM; t <= toM; t += slotStep) {
                    const candidateStart = t;
                    const candidateEnd = t + workingDuration;

                    // Skip past times if date is today
                    if (isPastTime(candidateStart, dateVal)) continue;

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
                    timeSel.appendChild(opt);
                }

                if (timeSel.options.length <= 1) {
                    resetSelect();
                    setHelper('No available slots on this date for selected photographer.');
                } else {
                    timeSel.disabled = false;
                    if (!timeSel.value && timeSel.options.length > 1) {
                        timeSel.value = timeSel.options[1].value;
                    }
                    timeSel.focus();
                    setHelper(`Available slots: ${formatDisplay(fromM)} — ${formatDisplay(toM)} (every ${slotStep} min)`);
                }
            })
            .catch(err => {
                console.error('Error loading slots:', err);
                setHelper(err?.message || 'Failed to load slots.');
                resetSelect();
            });
    }

    // Setup accept/decline form handlers
    setupAcceptDeclineForms() {
        const self = this;
        const acceptButton = document.getElementById('modal-accept-btn');
        const declineButton = document.getElementById('modal-decline-btn');
        const adminNotesField = document.getElementById('modal-accept-notes');
        const modal = this.modal;

        // Helper to get booking id from modal
        function getBookingId() {
            const el = document.getElementById('modal-booking-id');
            return el ? el.textContent.trim() : '';
        }

        // Accept booking
        acceptButton?.addEventListener('click', function (e) {
            const bookingId = getBookingId();
            const notes = adminNotesField?.value || '';
            if (!bookingId) {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Booking ID not found' });
                return;
            }
            acceptButton.disabled = true;
            acceptButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
            // Use route from Blade
            let url = self.acceptRouteTpl ? self.acceptRouteTpl.replace(':id', bookingId) : `/admin/pending-schedules/${bookingId}/accept`;
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({ notes })
            })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => Promise.reject(err));
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        modal.hide();
                        self.calendarObj.refetchEvents();
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message || 'Schedule accepted successfully',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to accept schedule' });
                    }
                })
                .catch(error => {
                    Swal.fire({ icon: 'error', title: 'Error', text: error.message || 'Failed to accept schedule' });
                })
                .finally(() => {
                    acceptButton.disabled = false;
                    acceptButton.innerHTML = '<i class="ri-check-line me-1"></i> Accept Schedule';
                });
        });

        // Decline booking
        declineButton?.addEventListener('click', function (e) {
            const bookingId = getBookingId();
            const notes = adminNotesField?.value || '';
            if (!bookingId) {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Booking ID not found' });
                return;
            }
            if (!notes.trim()) {
                Swal.fire({ icon: 'warning', title: 'Required', text: 'Please provide admin notes for declining.' });
                return;
            }
            declineButton.disabled = true;
            declineButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
            // Use route from Blade
            let url = self.declineRouteTpl ? self.declineRouteTpl.replace(':id', bookingId) : `/${window.adminBasePath}/pending-schedules/${bookingId}/decline`;
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({ reason: notes })
            })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => Promise.reject(err));
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        modal.hide();
                        self.calendarObj.refetchEvents();
                        Swal.fire({
                            icon: 'success',
                            title: 'Declined',
                            text: data.message || 'Schedule declined successfully',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to decline schedule' });
                    }
                })
                .catch(error => {
                    Swal.fire({ icon: 'error', title: 'Error', text: error.message || 'Failed to decline schedule' });
                })
                .finally(() => {
                    declineButton.disabled = false;
                    declineButton.innerHTML = '<i class="ri-close-line me-1"></i> Decline Schedule';
                });
        });
    }

    init() {
        /*  Initialize the calendar  */
        const today = new Date();
        const self = this;

        // cal - init
        self.calendarObj = new Calendar(self.calendar, {
            plugins: [dayGridPlugin, timeGridPlugin, listPlugin],
            slotMaxTime: '24:00:00',
            slotDuration: '00:30:00', /* If we want to split day time each 15minutes */
            themeSystem: 'bootstrap',
            bootstrapFontAwesome: false,
            buttonText: {
                today: 'Today',
                month: 'Month',
                week: 'Week',
                day: 'Day',
                list: 'List',
                prev: 'Prev',
                next: 'Next'
            },
            initialView: 'dayGridMonth',
            handleWindowResize: true,
            height: window.innerHeight - 200,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
            },
            events: async function (info, successCallback, failureCallback) {
                // Format dates to YYYY-MM-DD
                const fromDate = info.start.toISOString().split('T')[0];
                const toDate = info.end.toISOString().split('T')[0];
                const events = await self.fetchBookings(fromDate, toDate);
                successCallback(events);
            },
            editable: true,
            droppable: true, // this allows things to be dropped onto the calendar !!!
            dayMaxEventRows: false, // allow "more" link when too many events
            selectable: true,
            // dateClick: function (info) {
            //     self.onSelect(info);
            // },
            eventClick: function (info) {
                self.onEventClick(info);
            },
            datesSet: function (info) {
                console.log('Calendar view changed:', info);
            }
        });

        self.calendarObj.render();

        // Setup accept/decline form handlers
        self.setupAcceptDeclineForms();

        // Attach filter change listeners to refetch events when selection changes
        const fp = document.getElementById('filterPhotographer');
        const fs = document.getElementById('filterStatus');
        if (fp) fp.addEventListener('change', () => self.calendarObj.refetchEvents());
        if (fs) fs.addEventListener('change', () => self.calendarObj.refetchEvents());

        // Clear filters button: reset selects and refetch events
        const btnClear = document.getElementById('btnClearFilters');
        if (btnClear) btnClear.addEventListener('click', () => {
            if (fp) fp.value = '';
            if (fs) fs.value = '';
            // trigger change events for other handlers if needed
            if (typeof Event === 'function') {
                fp?.dispatchEvent(new Event('change'));
                fs?.dispatchEvent(new Event('change'));
            }
            self.calendarObj.refetchEvents();
        });
    }
}
document.addEventListener('DOMContentLoaded', function (e) {
    new CalendarSchedule().init();
});