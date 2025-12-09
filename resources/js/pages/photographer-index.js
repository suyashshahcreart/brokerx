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

class CalendarSchedule {

    constructor() {
        this.body = document.body;
        this.modal = new Modal(document.getElementById('event-modal'), { backdrop: 'static' });
        this.calendar = document.getElementById('calendar');
        this.formEvent = document.getElementById('forms-event');
        this.btnNewEvent = document.getElementById('btn-new-event');
        this.btnDeleteEvent = document.getElementById('btn-delete-event');
        this.btnSaveEvent = document.getElementById('btn-save-event');
        this.modalTitle = document.getElementById('modal-title');
        this.calendarObj = null;
        this.selectedEvent = null;
        this.newEventData = null;
    }

    onEventClick(info) {
        this.formEvent?.reset();
        this.formEvent.classList.remove('was-validated');
        this.newEventData = null;
        this.btnDeleteEvent.style.display = "block";
        this.modalTitle.text = ('Edit Event');
        this.modal.show();
        this.selectedEvent = info.event;
        document.getElementById('event-title').value = this.selectedEvent.title;
        document.getElementById('event-category').value = (this.selectedEvent.classNames[0]);
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
                return result.data.map(booking => {
                    // Determine event color based on status
                    let className = 'bg-primary';
                    if (booking.status === 'confirmed') className = 'bg-success';
                    else if (booking.status === 'pending') className = 'bg-warning';
                    else if (booking.status === 'cancelled') className = 'bg-danger';
                    else if (booking.status === 'completed') className = 'bg-info';

                    // Format the event title with booking time if available
                    let title = '';
                    if (booking.booking_time) {
                        title += ` - ${booking.booking_time}`;
                    }
                    title += booking.firm_name || `Booking #${booking.id}`;
                    return {
                        id: booking.id,
                        title: title,
                        start: booking.booking_date,
                        className: className,
                        extendedProps: {
                            bookingId: booking.id,
                            status: booking.status,
                            paymentStatus: booking.payment_status,
                            price: booking.price,
                            address: booking.full_address,
                            tourCode: booking.tour_code,
                            bookingTime: booking.booking_time,
                            user: booking.user,
                            city: booking.city,
                            state: booking.state
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

        // save event
        self.formEvent?.addEventListener('submit', function (e) {
            e.preventDefault();
            const form = self.formEvent;

            // validation
            if (form.checkValidity()) {
                if (self.selectedEvent) {
                    self.selectedEvent.setProp('title', document.getElementById('event-title').value);
                    self.selectedEvent.setProp('classNames', document.getElementById('event-category').value)

                } else {
                    const eventData = {
                        title: document.getElementById('event-title').value,
                        start: self.newEventData.date,
                        allDay: self.newEventData.allDay,
                        className: document.getElementById('event-category').value
                    };
                    self.calendarObj.addEvent(eventData);
                }
                self.modal.hide();
            } else {
                e.stopPropagation();
                form.classList.add('was-validated');
            }
        });

        // delete event
        self.btnDeleteEvent.addEventListener('click', function (e) {
            if (self.selectedEvent) {
                self.selectedEvent.remove();
                self.selectedEvent = null;
                self.modal.hide();
            }
        });
    }

}
document.addEventListener('DOMContentLoaded', function (e) {
    new CalendarSchedule().init();
});