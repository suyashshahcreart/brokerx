/*
Template Name: Lahomes - Real Estate Admin Dashboard Template
Author: Techzaa
File: schedule js
*/
import { Draggable } from '@fullcalendar/interaction';
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import listPlugin from '@fullcalendar/list'
import { Modal } from 'bootstrap'
import { end } from '@popperjs/core';

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

        if (!this.isAdmin) {
            this.updatePhotographerDetails(props, formattedTime);
            return;
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

        if (bookingIdEl) bookingIdEl.textContent = String(props.bookingId || '—');
        if (customerEl) customerEl.textContent = `${user.firstname || ''} ${user.lastname || ''}`.trim() || '—';
        if (propertyEl) propertyEl.textContent = property || '—';
        if (addressEl) addressEl.textContent = props.address || '—';
        if (cityStateEl) cityStateEl.textContent = `${city.name || ''} / ${state.name || ''}`.trim() || '—';
        if (pincodeEl) pincodeEl.textContent = props.pincode || '—';
        if (schedDateEl) schedDateEl.textContent = formatDateOnly(this.selectedEvent.startStr || props.dateOnly || '');
        if (schedTimeEl) schedTimeEl.textContent = formattedTime;

        // Action buttons
        const btnContainer = document.getElementById('modal-buttons-container');
        const checkInLink = document.getElementById('modal-check-in-link');
        const checkOutLink = document.getElementById('modal-check-out-link');
        const viewBookingLink = document.getElementById('modal-view-booking-link');
        const completedLink = document.getElementById('modal-completed-link');

        // Hide all by default
        if (checkInLink) checkInLink.style.display = 'none';
        if (checkOutLink) checkOutLink.style.display = 'none';
        if (completedLink) completedLink.style.display = 'none';

        // View booking always available
        if (viewBookingLink && this.bookingShowRouteTpl) {
            viewBookingLink.href = this.bookingShowRouteTpl.replace(':id', String(props.bookingId));
            viewBookingLink.style.display = 'inline-block';
        }

        // Add/Update Assign button for admins when status accepted
        if (btnContainer) {
            // remove previous injected assign button if any
            const prevAssign = document.getElementById('modal-assign-btn');
            if (prevAssign && prevAssign.parentElement === btnContainer) {
                btnContainer.removeChild(prevAssign);
            }

            const status = (props.status || '').toLowerCase();
            const canAssign = this.isAdmin && (status === 'schedul_accepted' || status === 'reschedul_accepted');
            if (canAssign) {
                const assignBtn = document.createElement('button');
                assignBtn.id = 'modal-assign-btn';
                assignBtn.type = 'button';
                assignBtn.className = 'btn btn-primary';
                assignBtn.innerHTML = '<i class="ri-user-add-line me-1"></i> Assign Photographer';
                assignBtn.addEventListener('click', () => this.openAssignModal(props));
                btnContainer.appendChild(assignBtn);
            }
        }

        this.modal.show();
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
                    let status = (booking.status || '').toLowerCase();

                    switch (status) {
                        case 'schedul_assign':
                            className = 'bg-primary'; // Assigned → primary
                            break;
                        case 'reschedul_assign':
                            className = 'bg-success'; // Assigned → success
                            break;
                        case 'schedul_accepted':
                        case 'reschedul_accepted':
                            className = 'bg-warning'; // Accepted → warning
                            break;
                        case 'schedul_inprogress':
                            className = 'bg-info'; // In-progress → info
                            break;
                        case 'schedul_completed':
                            className = 'bg-success'; // Completed → success
                            break;
                        case 'cancelled':
                            className = 'bg-danger';
                            break;
                        case 'schedul_pending':
                            className = 'bg-secondary';
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
        const pinEl = document.getElementById('modalPincode');
        const addrEl = document.getElementById('modalAddress');
        const cityEl = document.getElementById('modalCity');
        const stateEl = document.getElementById('modalState');
        const dateEl = document.getElementById('modalDate');

        if (customerEl) customerEl.textContent = `${props?.user?.firstname || ''} ${props?.user?.lastname || ''}`.trim() || '-';
        if (pinEl) pinEl.textContent = props.pincode || '-';
        if (addrEl) addrEl.textContent = props.address || '-';
        if (cityEl) cityEl.textContent = props?.city?.name || '-';
        if (stateEl) stateEl.textContent = props?.state?.name || '-';
        if (dateEl) dateEl.value = (dateStr || '').split('T')[0] || '';

        // Reset selects
        const photographerSel = document.getElementById('assignPhotographer');
        const timeSel = document.getElementById('assignTime');
        const timeHelper = document.getElementById('assignTimeHelper');
        if (photographerSel) photographerSel.value = '';
        if (timeSel) {
            timeSel.innerHTML = '<option value="">Select a time</option>';
            timeSel.disabled = true;
        }
        if (timeHelper) timeHelper.textContent = 'Select a photographer first to see available slots.';

        // When photographer changes, populate available slots
        if (photographerSel) {
            photographerSel.onchange = () => {
                if (!timeSel) return;
                this.populateTimeSlots(timeSel);
                timeSel.disabled = false;
            };
        }

        this.assignModal.show();
    }

    // Populate time slots using settings from the modal's data attributes
    populateTimeSlots(selectEl) {
        const modalEl = document.getElementById('assignBookingModal');
        if (!modalEl || !selectEl) return;

        const from = modalEl.getAttribute('data-photographer-from') || '08:00';
        const to = modalEl.getAttribute('data-photographer-to') || '21:00';
        const durationMin = parseInt(modalEl.getAttribute('data-photographer-duration') || '60', 10);

        const [fromH, fromM] = from.split(':').map(Number);
        const [toH, toM] = to.split(':').map(Number);

        const cur = new Date();
        cur.setHours(fromH, fromM, 0, 0);
        const end = new Date();
        end.setHours(toH, toM, 0, 0);

        const options = ['<option value="">Select a time</option>'];
        while (cur < end) {
            const hh = String(cur.getHours()).padStart(2, '0');
            const mm = String(cur.getMinutes()).padStart(2, '0');
            const label = cur.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            options.push(`<option value="${hh}:${mm}">${label}</option>`);
            cur.setMinutes(cur.getMinutes() + durationMin);
        }
        selectEl.innerHTML = options.join('');
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