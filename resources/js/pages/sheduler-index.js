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
        this.selectedEvent = info.event;

        // Check if this is an appointment from database
        if (this.selectedEvent.id && this.selectedEvent.extendedProps) {
            // Show appointment details
            const props = this.selectedEvent.extendedProps;
            const appointmentDetails = `
                    <div class="appointment-details">
                         <p><strong>Location:</strong> ${props.address}, ${props.city}, ${props.state}, ${props.country} - ${props.pin_code}</p>
                         <p><strong>Status:</strong> <span class="badge bg-${this.selectedEvent.classNames[0].replace('bg-', '')}">${props.status.toUpperCase()}</span></p>
                         <p><strong>Time:</strong> ${this.selectedEvent.start.toLocaleString()}</p>
                         ${this.selectedEvent.end ? `<p><strong>End Time:</strong> ${this.selectedEvent.end.toLocaleString()}</p>` : ''}
                    </div>
               `;

            // Use SweetAlert to show details
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: this.selectedEvent.title,
                    html: appointmentDetails,
                    icon: 'info',
                    confirmButtonText: 'Close'
                });
            } else {
                alert(this.selectedEvent.title + '\n\n' + props.address + ', ' + props.city + '\nStatus: ' + props.status);
            }
            return;
        }

        // Original event edit functionality for draggable events
        this.btnDeleteEvent.style.display = "block";
        this.modalTitle.text = ('Edit Event');
        this.modal.show();
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

        // Note: Default demo events removed - calendar now shows real appointments from database

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
            events: function (info, successCallback, failureCallback) {
                // Check if API URL is available
                if (window.appointmentsApiUrl) {
                    console.log('Fetching appointments from:', window.appointmentsApiUrl);
                    fetch(window.appointmentsApiUrl)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            console.log('Loaded appointments:', data);
                            console.log('Number of appointments:', data.length);
                            // Convert appointments to JS Date format
                            const formattedAppointments = data.map(item => {
                                // extract just the date portion (e.g. "2025-11-12")
                                const baseDate = item.date.split('T')[0];

                                // merge date + start/end times into full ISO timestamps
                                const startDateTime = new Date(`${baseDate}T${item.start}`);
                                const endDateTime = new Date(`${baseDate}T${item.end}`);

                                return {
                                    ...item,
                                    start: startDateTime,
                                    end: endDateTime
                                };
                            });
                            console.log('Formatted appointments:', formattedAppointments);
                            successCallback(formattedAppointments);
                        })
                        .catch(error => {
                            console.error('Error loading appointments:', error);
                            // Show empty calendar if API fails
                            successCallback([]);

                            // Show error notification if Swal is available
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error Loading Appointments',
                                    text: 'Could not load appointments. Please refresh the page.',
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                            }
                        });
                } else {
                    // Show empty calendar if no API URL
                    console.error('No appointments API URL provided');
                    successCallback([]);
                }
            },
            editable: false, // Disable drag-and-drop editing for database appointments
            droppable: false, // Disable dropping external events
            selectable: true,
            dateClick: function (info) {
                self.onSelect(info);
            },
            eventClick: function (info) {
                self.onEventClick(info);
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