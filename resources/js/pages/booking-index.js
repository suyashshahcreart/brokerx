import $ from "jquery";
import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.min.css";

window.$ = $;
window.jQuery = $;
window.flatpickr = flatpickr;

// Additional custom logic can be added here if needed
document.addEventListener('DOMContentLoaded', function () {
	const $ = window.jQuery;
	if (!$) return;

	const table = $('#bookings-table');
	if (!table.length) return;

	const dataTable = table.DataTable({
		processing: true,
		serverSide: true,
		ajax: window.bookingIndexUrl || '',
		order: [[0, 'desc']],
		columns: [
			{ data: 'id', name: 'id' },
			{ data: 'user', name: 'user.firstname', orderable: false, searchable: false },
			{ data: 'type_subtype', name: 'propertyType.name', orderable: false, searchable: false },
			{ data: 'bhk', name: 'bhk.name', orderable: false, searchable: false },
			{ data: 'city_state', name: 'city.name', orderable: false, searchable: false },
			{ data: 'area', name: 'area' },
			{ data: 'price', name: 'price' },
			{ data: 'booking_date', name: 'booking_date' },
			{ data: 'status', name: 'status', orderable: false, searchable: false },
			{ data: 'payment_status', name: 'payment_status', orderable: false, searchable: false },
			{ data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' },
		],
		language: {
			search: '_INPUT_',
			searchPlaceholder: 'Search bookings...'
		},
		lengthMenu: [10, 25, 50, 100],
		responsive: true
	});

	let holidays = [];
	let flatpickrInstance = null;

	function fetchHolidaysAndInitPicker(selectedDate) {
		$.get('/api/holidays', function (data) {
			// data.holidays is an array of {id, name, date}, data.day_limit is the setting object
			holidays = (data.holidays || []).map(h => h.date);
			let dayLimit = 30;
			if (data.day_limit && data.day_limit.value) {
				dayLimit = parseInt(data.day_limit.value, 10) || 30;
			}
			initFlatpickr(selectedDate, dayLimit);
		});
	}

	function initFlatpickr(selectedDate, dayLimit = 30) {
		if (flatpickrInstance) flatpickrInstance.destroy();
		const today = new Date();
		const minDate = today.toISOString().split('T')[0];
		const max = new Date();
		max.setDate(today.getDate() + dayLimit);
		const maxDate = max.toISOString().split('T')[0];
		flatpickrInstance = window.flatpickr('#schedule-date', {
			dateFormat: 'Y-m-d',
			minDate: minDate,
			maxDate: maxDate,
			disable: holidays,
			defaultDate: selectedDate || null,
			onChange: function (selectedDates, dateStr) {
				if (holidays.includes(dateStr)) {
					if (typeof Swal !== 'undefined') {
						Swal.fire({
							icon: 'warning',
							title: 'Holiday',
							text: 'Selected date is a holiday. Please choose another date.',
							timer: 2000,
							showConfirmButton: false
						});
					}
					flatpickrInstance.clear();
				}
			}
		});
	}

	table.on('click', '.btn-soft-warning', function (e) {
		e.preventDefault();
		const row = $(this).closest('tr');
		const rowData = dataTable.row(row).data();
		if (rowData && rowData.id) {
			$('#schedule-booking-id').val(rowData.id);
			let selectedDate = '';
			if (rowData.booking_date && rowData.booking_date !== '-') {
				selectedDate = rowData.booking_date;
				$('#current-booking-date').text(rowData.booking_date);
			} else {
				$('#current-booking-date').text('Not set');
			}
			fetchHolidaysAndInitPicker(selectedDate);
			const modal = new bootstrap.Modal(document.getElementById('scheduleModal'));
			modal.show();
		}
	});

	// Schedule submit button
	$('#scheduleSubmitBtn').on('click', function () {
		const bookingId = $('#schedule-booking-id').val();
		const date = $('#schedule-date').val();
		if (!date) {
			$('#schedule-date').addClass('is-invalid');
			return;
		}
		$('#schedule-date').removeClass('is-invalid');
		// Use correct route for reschedule
		$.ajax({
			url: `/admin/bookings/${bookingId}/reschedule`,
			method: 'POST',
			data: {
				schedule_date: date,
				_token: window.bookingCsrfToken || ''
			},
			success: function (response) {
				if (response.success) {
					$('#scheduleModal').modal('hide');
					table.DataTable().ajax.reload();
					if (typeof Swal !== 'undefined') {
						Swal.fire({
							icon: 'success',
							title: 'Success',
							text: 'Booking rescheduled successfully!',
							timer: 2000,
							showConfirmButton: false
						});
					}
				} else {
					if (typeof Swal !== 'undefined') {
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: 'Failed to reschedule booking.'
						});
					} else {
						alert('Failed to reschedule booking.');
					}
				}
			},
			error: function () {
				if (typeof Swal !== 'undefined') {
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: 'Failed to reschedule booking.'
					});
				} else {
					alert('Failed to reschedule booking.');
				}
			}
		});
	});
});
