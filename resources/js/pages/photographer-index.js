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
        this.calendar = document.getElementById('calendar');
        this.formEvent = document.getElementById('forms-event');
        this.btnNewEvent = document.getElementById('btn-new-event');
        this.btnDeleteEvent = document.getElementById('btn-delete-event'); // May not exist
        this.btnSaveEvent = document.getElementById('btn-save-event'); // May not exist
        this.modalTitle = document.getElementById('modal-title');
        this.calendarObj = null;
        this.selectedEvent = null;
        this.newEventData = null;
    }

    onEventClick(info) {
        this.formEvent?.reset();
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

        // Populate modal fields with booking data
        document.getElementById('modal-booking-id').textContent = `#${props.bookingId || '—'}`;
        document.getElementById('modal-booking-customer').textContent = props.user ? (props.user.firstname + " " + props.user.lastname) : '—';
        document.getElementById('modal-booking-property').textContent = props.propertyType || '—';
        document.getElementById('modal-booking-address').textContent = props.address || '—';
        document.getElementById('modal-booking-city-state').textContent =
            `${props.city?.name || '—'}, ${props.state?.name || '—'}`;
        document.getElementById('modal-booking-pincode').textContent = props.pincode || '—';
        document.getElementById('modal-schedule-date').textContent =
            this.selectedEvent.start ? formatDateOnly(this.selectedEvent.start.toISOString()) : '—';
        document.getElementById('modal-schedule-time').textContent = formattedTime || props.assignmentTime || '—';

        // Get route templates from data attributes
        const checkInRouteTemplate = this.calendar.getAttribute('data-check-in-route');
        const checkOutRouteTemplate = this.calendar.getAttribute('data-check-out-route');
        const bookingShowRouteTemplate = this.calendar.getAttribute('data-booking-show-route');
        
        // Get IDs
        const assigneeId = props.assigneeId;
        const bookingId = props.bookingId;
        
        // Build URLs by replacing :id placeholder
        const checkInUrl = checkInRouteTemplate ? checkInRouteTemplate.replace(':id', assigneeId) : `./booking-assignees/${assigneeId}/check-in`;
        const checkOutUrl = checkOutRouteTemplate ? checkOutRouteTemplate.replace(':id', assigneeId) : `./booking-assignees/${assigneeId}/check-out`;
        const bookingShowUrl = bookingShowRouteTemplate ? bookingShowRouteTemplate.replace(':id', bookingId) : `./bookings/${bookingId}`;
        
        // Get all button elements
        const checkInBtn = document.getElementById('modal-check-in-link');
        const checkOutBtn = document.getElementById('modal-check-out-link');
        const viewBookingBtn = document.getElementById('modal-view-booking-link');
        const completedBtn = document.getElementById('modal-completed-link');
        
        // Normalize and evaluate booking status for UI logic
        const status = (props.status || '').toLowerCase();
        console.log('Booking status:', status, 'Assignee ID:', assigneeId, 'Booking ID:', bookingId);
        
        // Hide all buttons first
        if (checkInBtn) checkInBtn.style.display = 'none';
        if (checkOutBtn) checkOutBtn.style.display = 'none';
        if (viewBookingBtn) viewBookingBtn.style.display = 'none';
        if (completedBtn) completedBtn.style.display = 'none';
        
        // Show buttons based on status
        if (status === 'schedul_completed' || status === 'tour_completed') {
            // Completed - show completed button (disabled) and view booking
            if (completedBtn) {
                completedBtn.style.display = 'inline-block';
                completedBtn.disabled = true;
            }
            if (viewBookingBtn && bookingId) {
                viewBookingBtn.style.display = 'inline-block';
                viewBookingBtn.href = bookingShowUrl;
            }
        } else if (status === 'schedul_inprogress' || status === 'tour_pending' || status === 'tour_live') {
            // In progress - show check-out button and view booking
            if (checkOutBtn && assigneeId) {
                checkOutBtn.style.display = 'inline-block';
                checkOutBtn.href = checkOutUrl;
            }
            if (viewBookingBtn && bookingId) {
                viewBookingBtn.style.display = 'inline-block';
                viewBookingBtn.href = bookingShowUrl;
            }
        } else if (status === 'schedul_assign' || status === 'reschedul_assign') {
            // Assigned but not started - show check-in button and view booking
            if (checkInBtn && assigneeId) {
                checkInBtn.style.display = 'inline-block';
                checkInBtn.href = checkInUrl;
            }
            if (viewBookingBtn && bookingId) {
                viewBookingBtn.style.display = 'inline-block';
                viewBookingBtn.href = bookingShowUrl;
            }
        } else {
            // Other statuses - show view booking button only
            if (viewBookingBtn && bookingId) {
                viewBookingBtn.style.display = 'inline-block';
                viewBookingBtn.href = bookingShowUrl;
            }
        }

        this.modal.show();
    }

    onSelect(info) {
        this.formEvent?.reset();
        this.formEvent?.classList.remove('was-validated');
        this.selectedEvent = null;
        this.newEventData = info;
        this.btnDeleteEvent.style.display = "none";
        this.modalTitle.text = ('Add New Event');
        this.modal.show();
        this.calendarObj.unselect();
    }

    async fetchBookings(fromDate, toDate) {
        try {
            // Get the API URL from the data attribute
            const apiBaseUrl = this.calendar.getAttribute('data-booking-api');
            const url = `${apiBaseUrl}?from_date=${fromDate}&to_date=${toDate}`;

            const response = await fetch(url);

            if (!response.ok) {
                console.error('HTTP error:', response.status, response.statusText);
                return [];
            }

            const result = await response.json();

            if (result.success && result.data) {
                return result.data.map(assignee => {
                    const booking = assignee.booking || {};

                    // Determine event color based on booking status
                    let className = '';
                    let status = booking.status;
                    switch (status) {
                        case 'schedul_assign':
                            className = 'bg-primary';
                            break;

                        case 'reschedul_assign':
                            className = 'bg-primary';
                            break;

                        case 'schedul_inprogress':
                            className = 'bg-info'; // Yellow for in-progress
                            break;

                        case 'schedul_completed':
                            className = 'bg-success'; // Green for completed
                            break;

                        default:
                            className = 'bg-secondary'; // fallback for unknown statuses
                    }


                    // Prefer booking_time, fallback to assignee time
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
                    title += booking.firm_name || `Booking #${booking.id || assignee.booking_id}`;

                    // Use assignment date (assignee.date) first, fall back to booking date
                    const startDate = assignee.date || booking.booking_date;

                    return {
                        id: assignee.id,
                        title,
                        start: startDate,
                        className,
                        extendedProps: {
                            assigneeId: assignee.id,
                            bookingId: booking.id || assignee.booking_id,
                            status: booking.status,
                            paymentStatus: booking.payment_status,
                            price: booking.price,
                            address: booking.full_address,
                            tourCode: booking.tour_code,
                            bookingTime: rawTime,
                            propertyType: booking.property_type.name + " / " + booking.property_sub_type.name,
                            assignmentTime: assignee.time,
                            pincode: booking.pin_code,
                            user: booking.user,
                            city: booking.city,
                            state: booking.state,
                            photographer: assignee.user
                        }
                    };
                });
            }
            return [];
        } catch (error) {
            console.error('Error fetching bookings:', error);
            return [];
        }
    }

    init() {
        /*  Initialize the calendar  */
        const today = new Date();
        const self = this;
        const externalEventContainerEl = document.getElementById('external-events');

        new Draggable(externalEventContainerEl, {
            itemSelector: '.external-event',
            eventData: function (eventEl) {
                return {
                    title: eventEl.innerText,
                    classNames: eventEl.getAttribute('data-class')
                };
            }
        });

        // cal - init
        self.calendarObj = new Calendar(self.calendar, {

            plugins: [dayGridPlugin, timeGridPlugin, listPlugin],
            slotDuration: '00:30:00', /* If we want to split day time each 15minutes */
            slotMinTime: '07:00:00',
            slotMaxTime: '19:00:00',
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
            // dayMaxEventRows: false, // allow "more" link when too many events
            selectable: true,
            dateClick: function (info) {
                self.onSelect(info);
            },
            eventClick: function (info) {
                self.onEventClick(info);
            },
            datesSet: function (info) {
                console.log('Calendar view changed:', {
                    startDate: info.start,
                    endDate: info.end,
                    currentView: info.view.type
                });
            }
        });

        self.calendarObj.render();

        // on new event button click
        self.btnNewEvent.addEventListener('click', function (e) {
            self.onSelect({
                date: new Date(),
                allDay: true
            });
        });

        // save event (disabled for booking view)
        if (self.formEvent && self.btnSaveEvent) {
            self.formEvent.addEventListener('submit', function (e) {
                e.preventDefault();
                // Form submission disabled for booking details view
                self.modal.hide();
            });
        }

        // delete event
        if (self.btnDeleteEvent) {
            self.btnDeleteEvent.addEventListener('click', function (e) {
                if (self.selectedEvent) {
                    self.selectedEvent.remove();
                    self.selectedEvent = null;
                    self.modal.hide();
                }
            });
        }
    }

}
document.addEventListener('DOMContentLoaded', function (e) {
    new CalendarSchedule().init();
});