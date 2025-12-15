import $ from "jquery";
import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.min.css";
import 'datatables.net-bs5';
import moment from 'moment';

window.$ = $;
window.jQuery = $;
window.flatpickr = flatpickr;

// Additional custom logic can be added here if needed
document.addEventListener('DOMContentLoaded', function () {
	const $ = window.jQuery;
	if (!$) return;

	const table = $('#bookings-table');
	if (!table.length) return;

	// Initialize daterangepicker
	let dateRangePicker = null;
	if (typeof $.fn.daterangepicker === 'function') {
		initializeDateRangePicker();
	} else {
		setTimeout(() => {
			if (typeof $.fn.daterangepicker === 'function') {
				initializeDateRangePicker();
			}
		}, 500);
	}

	function initializeDateRangePicker() {
		const input = $('#filterDateRange');
		if (!input.length) return;
		
		dateRangePicker = input.daterangepicker({
			autoUpdateInput: false,
			locale: {
				cancelLabel: 'Clear',
				format: 'YYYY-MM-DD'
			}
		});

		input.on('apply.daterangepicker', function(ev, picker) {
			$(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
		});

		input.on('cancel.daterangepicker', function(ev, picker) {
			$(this).val('');
		});
	}

	const dataTable = table.DataTable({
		processing: true,
		serverSide: true,
		ajax: {
			url: window.bookingIndexUrl || '',
			type: 'GET',
			data: function (d) {
				// Add filter parameters
				d.state_id = $('#filterState').val() || '';
				d.city_id = $('#filterCity').val() || '';
				d.status = $('#filterStatus').val() || '';

				// Handle date range
				const dateRange = $('#filterDateRange').val();
				if (dateRange) {
					const dates = dateRange.split(' - ');
					if (dates.length === 2) {
						d.date_from = dates[0];
						d.date_to = dates[1];
					}
				}
			}
		},
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
			searchPlaceholder: 'Search bookings...',
			emptyTable: "No bookings found",
			processing: "Processing...",
			paginate: {
				next: '<i class="ri-arrow-right-s-line"></i>',
				previous: '<i class="ri-arrow-left-s-line"></i>'
			}
		},
		lengthMenu: [10, 25, 50, 100],
		responsive: true
	});

	// Bind filter buttons
	$('#applyFilters').on('click', function () {
		dataTable.draw();
	});

	$('#clearFilters').on('click', function () {
		$('#filterState').val('');
		$('#filterCity').val('');
		$('#filterStatus').val('');
		$('#filterDateRange').val('');
		dataTable.draw();
	});

	// Filter state - cascade cities
	$('#filterState').on('change', function () {
		const stateId = $(this).val();
		const citySelect = $('#filterCity');

		if (stateId) {
			citySelect.find('option').each(function () {
				const $option = $(this);
				if ($option.val() === '' || $option.data('state') == stateId) {
					$option.show();
				} else {
					$option.hide();
				}
			});
			citySelect.val('');
		} else {
			citySelect.find('option').show();
			citySelect.val('');
		}
	});

	let holidays = [];
	let flatpickrInstance = null;
	let lastSelectedDate = null;
	let lastDayLimit = 30;

	function fetchHolidaysAndInitPicker(selectedDate) {
		const apiUrl = (window.apiBaseUrl || '') + '/holidays';
		$.get(apiUrl, function (data) {
			// data.holidays is an array of {id, name, date}, data.day_limit is the setting object
			holidays = (data.holidays || []).map(h => h.date);
			let dayLimit = 30;
			if (data.day_limit && data.day_limit.value) {
				dayLimit = parseInt(data.day_limit.value, 10) || 30;
			}
			lastDayLimit = dayLimit;
			initFlatpickr(selectedDate, dayLimit);
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
		flatpickrInstance = window.flatpickr('#schedule-date', {
			dateFormat: 'Y-m-d',
			minDate: minDate,
			maxDate: maxDate,
			disable: disable,
			defaultDate: selectedDate || null,
			onChange: function (selectedDates, dateStr) {
				if (mode === 'default' && holidays.includes(dateStr)) {
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
			lastSelectedDate = selectedDate;
			// Always default to 'default' mode on open
			$('#schedule-mode-default').prop('checked', true);
			fetchHolidaysAndInitPicker(selectedDate);
			const modal = new bootstrap.Modal(document.getElementById('scheduleModal'));
			modal.show();
		}
	});

	// Make calendar icon clickable to open date picker
	$(document).on('click', '#calendar-icon-trigger', function() {
		if (flatpickrInstance) {
			flatpickrInstance.open();
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
		const baseUrl = window.appBaseUrl || '';
		$.ajax({
			url: `${baseUrl}/admin/bookings/${bookingId}/reschedule`,
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

	// Accept schedule from booking list
	window.acceptScheduleQuick = async function(bookingId) {
		// Get booking data from DataTable
		const dataTable = table.DataTable();
		const rowData = dataTable.rows().data().toArray().find(row => row.id === bookingId);
		
		const requestedDate = rowData?.booking_date || 'Not specified';
		const customerNotes = rowData?.booking_notes || '';
		const userName = rowData?.user || 'N/A';

		const htmlContent = `
			<div class="text-start mb-3">
				<div class="border-bottom pb-2 mb-2">
					<p class="mb-2"><strong class="text-muted">Customer:</strong> ${userName}</p>
					${requestedDate !== '-' && requestedDate !== 'Not specified' ? `
						<p class="mb-0"><strong class="text-muted">Requested Date:</strong> <span class="text-primary">${requestedDate}</span></p>
					` : ''}
				</div>
				${customerNotes ? `
					<div class="mb-3">
						<small class="text-muted d-block mb-1"><strong>Customer Notes:</strong></small>
						<div class="alert alert-info py-2 mb-0"><small>${customerNotes}</small></div>
					</div>
				` : ''}
				<div>
					<small class="text-muted d-block mb-1"><strong>Admin Notes (Optional):</strong></small>
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
				const response = await fetch(`${window.appBaseUrl}/admin/pending-schedules/${bookingId}/accept`, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-CSRF-TOKEN': window.bookingCsrfToken,
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
					table.DataTable().ajax.reload();
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
	};

	// Decline schedule from booking list
	window.declineScheduleQuick = async function(bookingId) {
		// Get booking data from DataTable
		const dataTable = table.DataTable();
		const rowData = dataTable.rows().data().toArray().find(row => row.id === bookingId);
		
		const requestedDate = rowData?.booking_date || 'Not specified';
		const customerNotes = rowData?.booking_notes || '';
		const userName = rowData?.user || 'N/A';

		const htmlContent = `
			<div class="text-start mb-3">
				<div class="border-bottom pb-2 mb-2">
					<p class="mb-2"><strong class="text-muted">Customer:</strong> ${userName}</p>
					${requestedDate !== '-' && requestedDate !== 'Not specified' ? `
						<p class="mb-0"><strong class="text-muted">Requested Date:</strong> <span class="text-primary">${requestedDate}</span></p>
					` : ''}
				</div>
				${customerNotes ? `
					<div class="mb-3">
						<small class="text-muted d-block mb-1"><strong>Customer Notes:</strong></small>
						<div class="alert alert-info py-2 mb-0"><small>${customerNotes}</small></div>
					</div>
				` : ''}
				<div>
					<small class="text-muted d-block mb-1"><strong>Reason for Decline:</strong> <span class="text-danger">*</span></small>
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
				const response = await fetch(`${window.appBaseUrl}/admin/pending-schedules/${bookingId}/decline`, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-CSRF-TOKEN': window.bookingCsrfToken,
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
					table.DataTable().ajax.reload();
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
	};

	// Delete booking
	window.deleteBooking = async function(bookingId) {
		const result = await Swal.fire({
			title: 'Delete Booking?',
			text: 'This action cannot be undone!',
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#dc3545',
			cancelButtonColor: '#6c757d',
			confirmButtonText: 'Yes, Delete',
			cancelButtonText: 'Cancel'
		});

		if (result.isConfirmed) {
			try {
				const response = await fetch(`${window.appBaseUrl}/admin/bookings/${bookingId}`, {
					method: 'DELETE',
					headers: {
						'X-CSRF-TOKEN': window.bookingCsrfToken,
						'Accept': 'application/json',
					}
				});

				const data = await response.json();

				if (response.ok) {
					await Swal.fire({
						icon: 'success',
						title: 'Deleted!',
						text: 'Booking deleted successfully',
						timer: 1500,
						showConfirmButton: false
					});
					table.DataTable().ajax.reload();
				} else {
					throw new Error(data.message || 'Failed to delete');
				}
			} catch (error) {
				Swal.fire({
					icon: 'error',
					title: 'Error',
					text: error.message || 'Failed to delete booking'
				});
			}
		}
	};
});
