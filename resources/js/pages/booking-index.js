import $ from "../jquery-select2-setup.js";
import "datatables.net-bs5";
import { initMobileFiltersToggle } from "../utils/mobile-filters-toggle.js";

import "select2-bootstrap-5-theme/dist/select2-bootstrap-5-theme.min.css";

import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.min.css";

window.flatpickr = window.flatpickr || flatpickr;

function whenPluginsReady(callback, attempt = 0) {
	const ready =
		typeof $.fn.select2 === "function" &&
		typeof $.fn.DataTable === "function";

	if (ready) {
		const start = () => callback($);
		if (document.readyState === "loading") {
			document.addEventListener("DOMContentLoaded", start);
		} else {
			start();
		}
		return;
	}

	if (attempt >= 100) {
		console.error(
			"Select2/DataTables failed to initialize on the bookings page."
		);
		return;
	}

	setTimeout(() => whenPluginsReady(callback, attempt + 1), 50);
}

whenPluginsReady(initBookingsIndexPage);

function initBookingsIndexPage($) {

	const table = $('#bookings-table');
	if (!table.length) return;

	const $filterCustomer = $('#filterCustomer');
	const $filterCountry = $('#filterCountry');
	const $filterState = $('#filterState');
	const $filterCity = $('#filterCity');
	const $filterStatus = $('#filterStatus');
	const $filterPropertyType = $('#filterPropertyType');
	const $filterPropertySubType = $('#filterPropertySubType');
	const $filterFurnish = $('#filterFurnish');
	const $filterBhk = $('#filterBhk');
	const $filterFurnishWrap = $('#filterFurnishWrap');
	const $filterBhkWrap = $('#filterBhkWrap');
	const defaultCountryId = window.defaultCountryId ? String(window.defaultCountryId) : '';
	const propertyTypeMeta = window.propertyTypeMeta || {};
	let bhkOptionsLoaded = false;

	function destroyFilterSelect2($element) {
		if ($element.hasClass('select2-hidden-accessible')) {
			$element.select2('destroy');
		}
	}

	function filterAllOptionHtml($element) {
		const label = $element.data("placeholder") || "All";
		return `<option value="">${label}</option>`;
	}

	function ensureFilterEmptyOption($element, placeholder) {
		let $empty = $element.find('option[value=""]').first();
		if (!$empty.length) {
			$element.prepend(filterAllOptionHtml($element));
			return;
		}
		if (placeholder && !$empty.text().trim()) {
			$empty.text(placeholder);
		}
	}

	function initFilterSelect2($element, options = {}) {
		destroyFilterSelect2($element);

		const placeholder = options.placeholder || $element.data("placeholder") || "";
		const disabled = $element.prop("disabled");
		const allowClear =
			options.allowClear !== undefined ? options.allowClear : !disabled;

		if (allowClear && placeholder && !options.multiple && !$element.prop("multiple")) {
			ensureFilterEmptyOption($element, placeholder);
		}

		const config = {
			theme: "bootstrap-5",
			width: "100%",
			minimumResultsForSearch: 0,
			placeholder,
			dropdownParent: $("#filtersSection"),
			allowClear,
			...options,
			placeholder: options.placeholder || placeholder,
			allowClear:
				options.allowClear !== undefined ? options.allowClear : !disabled,
		};

		$element.select2(config);

		if (!$element.val()) {
			$element.val(null).trigger("change.select2");
		}
	}

	function refreshFilterSelect2Layout() {
		$("#filtersSection .booking-filter-select").each(function () {
			const $el = $(this);
			if ($el.data("select2")) {
				$el.trigger("change.select2");
			}
		});
	}

	function initBookingFilterSelects() {
		initFilterSelect2($filterCountry);
		initFilterSelect2($filterState);
		initFilterSelect2($filterStatus, {
			multiple: true,
			placeholder: "All Status",
			allowClear: true,
			closeOnSelect: false,
		});
		initFilterSelect2($filterCity);
		initFilterSelect2($filterPropertyType);
		initFilterSelect2($filterPropertySubType);
		initFilterSelect2($filterFurnish);
		initFilterSelect2($filterBhk);
		initFilterSelect2($filterCustomer, {
			allowClear: true,
			placeholder: 'All Customers',
			ajax: {
				url: window.customersOptionsUrl || `${window.appBaseUrl}/${window.adminBasePath}/api/customers/options`,
				dataType: 'json',
				delay: 300,
				data: function (params) {
					return { q: params.term || '' };
				},
				processResults: function (data) {
					return { results: data.results || [] };
				},
				cache: true,
			},
			minimumInputLength: 0,
		});
	}

	initBookingFilterSelects();

	initMobileFiltersToggle({ onExpand: refreshFilterSelect2Layout });

	let dataTable = null;

	function reloadTable() {
		if (dataTable) {
			dataTable.draw();
		}
	}

	// Initialize daterangepicker
	let dateRangePicker = null;

	// Wait for moment and daterangepicker to be available
	const initDateRangePicker = () => {
		if (typeof window.moment === 'undefined' || typeof $.fn.daterangepicker === 'undefined') {
			setTimeout(initDateRangePicker, 100);
			return;
		}
		initializeDateRangePicker();
	};

	function initializeDateRangePicker() {
		const input = $('#filterDateRange');
		if (!input.length || input.length === 0) {
			// Element doesn't exist yet, try again later
			setTimeout(initializeDateRangePicker, 200);
			return;
		}

		// Ensure moment is available
		if (typeof window.moment === 'undefined') {
			console.error('Moment.js is not available');
			return;
		}

		// Ensure daterangepicker is available
		if (typeof $.fn.daterangepicker === 'undefined') {
			console.error('Daterangepicker is not available');
			return;
		}

		// Check if already initialized
		if (input.data('daterangepicker')) {
			return; // Already initialized
		}

		try {
			// Ensure the element is in the DOM
			if (!input.is(':visible') && !document.body.contains(input[0])) {
				setTimeout(initializeDateRangePicker, 200);
				return;
			}

			// Initialize daterangepicker with proper configuration and preset ranges
			// Don't specify parentEl - let daterangepicker use default (appends to body)
			dateRangePicker = input.daterangepicker({
				autoUpdateInput: false,
				locale: {
					cancelLabel: 'Clear',
					format: 'YYYY-MM-DD'
				},
				opens: 'left',
				ranges: {
					'Today': [window.moment(), window.moment()],
					'Yesterday': [window.moment().subtract(1, 'days'), window.moment().subtract(1, 'days')],
					'Last 7 Days': [window.moment().subtract(6, 'days'), window.moment()],
					'Last 30 Days': [window.moment().subtract(29, 'days'), window.moment()],
					'This Month': [window.moment().startOf('month'), window.moment().endOf('month')],
					'Last Month': [window.moment().subtract(1, 'month').startOf('month'), window.moment().subtract(1, 'month').endOf('month')],
					'This Year': [window.moment().startOf('year'), window.moment().endOf('year')],
					'Last Year': [window.moment().subtract(1, 'year').startOf('year'), window.moment().subtract(1, 'year').endOf('year')]
				},
				alwaysShowCalendars: true,
				showCustomRangeLabel: true
			});

			input.on('apply.daterangepicker', function (ev, picker) {
				$(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
				reloadTable();
			});

			input.on('cancel.daterangepicker', function () {
				$(this).val('');
				reloadTable();
			});
		} catch (error) {
			console.error('Error initializing daterangepicker:', error);
			// Don't retry on error to avoid infinite loop
		}
	}

	// Start initialization after a short delay to ensure DOM is ready
	setTimeout(() => {
		initDateRangePicker();
	}, 300);

	dataTable = table.DataTable({
		processing: true,
		serverSide: true,
		deferRender: true,
		pageLength: 10,
		lengthMenu: [10, 25, 50],
		searchDelay: 500,
		stateSave: true,
		ajax: {
			url: window.bookingIndexUrl || '',
			type: 'GET',
			data: function (d) {
				d.country_id = $filterCountry.val() || '';
				d.customer_id = $filterCustomer.val() || '';
				d.state_id = $filterState.val() || '';
				d.city_id = $filterCity.val() || '';
				const statusVal = $filterStatus.val();
				d.status =
					Array.isArray(statusVal) && statusVal.length
						? statusVal
						: statusVal || "";
				d.property_type_id = $filterPropertyType.val() || '';
				d.property_sub_type_id = $filterPropertySubType.val() || '';
				d.furniture_type =
					$filterFurnishWrap.is(":visible") && !$filterFurnish.prop("disabled")
						? $filterFurnish.val() || ""
						: "";
				d.bhk_id =
					$filterBhkWrap.is(":visible") && !$filterBhk.prop("disabled")
						? $filterBhk.val() || ""
						: "";

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
			{ data: 'id', name: 'bookings.id', searchable: false },
			{ data: 'customer', name: 'customer', searchable: true },
			{ data: 'type_subtype', name: 'type_subtype', orderable: false, searchable: false },
			{ data: 'qr_code', name: 'qr_code', orderable: false, searchable: true },
			{ data: 'city_state', name: 'city_state', orderable: false, searchable: true },
			{ data: 'area', name: 'bookings.area', searchable: false },
			{ data: 'price', name: 'bookings.price', searchable: false },
			{ data: 'booking_date', name: 'bookings.created_at', searchable: false },
			{ data: 'status', name: 'bookings.status', orderable: false, searchable: false },
			{ data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' },
		],
		language: {
			search: '_INPUT_',
			searchPlaceholder: 'Search bookings...',
			emptyTable: "No bookings found",
			processing: '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Loading...',
			paginate: {
				next: '<i class="ri-arrow-right-s-line"></i>',
				previous: '<i class="ri-arrow-left-s-line"></i>'
			}
		},
		responsive: false,
	});

	table.on('click', '.booking-delete-btn', function (e) {
		e.preventDefault();
		const bookingId = $(this).data('booking-id');
		if (bookingId) {
			window.deleteBooking(bookingId);
		}
	});

	$('#clearFilters').on('click', function () {
		$filterCustomer.val(null).trigger('change.select2');
		$filterStatus.val(null).trigger('change.select2');
		$('#filterDateRange').val('');
		$filterPropertyType.val(null).trigger('change.select2');
		updatePropertyDependentFilters(null);

		if (defaultCountryId && $filterCountry.val() !== defaultCountryId) {
			$filterCountry.val(defaultCountryId).trigger('change');
			return;
		}

		$filterState.val(null).trigger('change.select2');
		resetCityFilter();
		reloadTable();
	});

	$filterCountry.on('change', function () {
		const countryId = $(this).val();
		$filterState.val(null).trigger('change.select2');
		resetCityFilter();
		loadStatesForCountry(countryId, reloadTable);
	});

	$filterState.on('change', function () {
		loadCitiesForState($(this).val());
		reloadTable();
	});

	$filterCity.on('change', reloadTable);
	$filterStatus.on('change', reloadTable);
	$filterCustomer.on('change', reloadTable);
	$filterPropertyType.on('change', function () {
		updatePropertyDependentFilters($(this).val());
		reloadTable();
	});
	$filterPropertySubType.on('change', reloadTable);
	$filterFurnish.on('change', reloadTable);
	$filterBhk.on('change', reloadTable);

	function getPropertyTypeMeta(typeId) {
		if (!typeId) {
			return {};
		}

		return propertyTypeMeta[String(typeId)] || propertyTypeMeta[typeId] || {};
	}

	function resetPropertySubTypeFilter() {
		destroyFilterSelect2($filterPropertySubType);
		$filterPropertySubType.html(filterAllOptionHtml($filterPropertySubType));
		$filterPropertySubType.prop('disabled', true);
		$filterPropertySubType.data('placeholder', 'Select property type first');
		initFilterSelect2($filterPropertySubType);
	}

	function resetFurnishFilter() {
		hideFurnishFilter(true);
	}

	function resetBhkFilter() {
		bhkOptionsLoaded = false;
		destroyFilterSelect2($filterBhk);
		$filterBhk.html(filterAllOptionHtml($filterBhk));
		setFilterDisabledState($filterBhk, true, "All Sizes");
	}

	function loadPropertySubTypes(propertyTypeId, onComplete) {
		if (!propertyTypeId) {
			resetPropertySubTypeFilter();
			if (typeof onComplete === 'function') {
				onComplete();
			}
			return;
		}

		destroyFilterSelect2($filterPropertySubType);
		$filterPropertySubType.html(filterAllOptionHtml($filterPropertySubType)).prop('disabled', true);
		$filterPropertySubType.data('placeholder', 'Loading sub types...');
		initFilterSelect2($filterPropertySubType);

		const subTypesUrl = window.propertySubTypesOptionsUrl || `${window.appBaseUrl}/${window.adminBasePath}/api/property-sub-types/options`;

		$.get(subTypesUrl, { property_type_id: propertyTypeId })
			.done(function (subTypes) {
				destroyFilterSelect2($filterPropertySubType);
				$filterPropertySubType.html(filterAllOptionHtml($filterPropertySubType));
				(subTypes || []).forEach(function (subType) {
					$filterPropertySubType.append(`<option value="${subType.id}">${subType.name}</option>`);
				});
				$filterPropertySubType.prop('disabled', false);
				$filterPropertySubType.data('placeholder', 'All Sub Types');
				initFilterSelect2($filterPropertySubType);
				if (typeof onComplete === 'function') {
					onComplete();
				}
			})
			.fail(function () {
				destroyFilterSelect2($filterPropertySubType);
				$filterPropertySubType.html(filterAllOptionHtml($filterPropertySubType));
				$filterPropertySubType.prop('disabled', true);
				$filterPropertySubType.data('placeholder', 'Failed to load sub types');
				initFilterSelect2($filterPropertySubType);
				if (typeof onComplete === 'function') {
					onComplete();
				}
			});
	}

	function loadBhkOptions(onComplete) {
		if (bhkOptionsLoaded) {
			setFilterDisabledState($filterBhk, false, "All Sizes");
			if (typeof onComplete === "function") {
				onComplete();
			}
			return;
		}

		destroyFilterSelect2($filterBhk);
		$filterBhk.html(filterAllOptionHtml($filterBhk)).prop('disabled', true);
		$filterBhk.data('placeholder', 'Loading sizes...');
		initFilterSelect2($filterBhk);

		const bhkUrl = window.bhkOptionsUrl || `${window.appBaseUrl}/${window.adminBasePath}/api/bhk/options`;

		$.get(bhkUrl)
			.done(function (bhks) {
				destroyFilterSelect2($filterBhk);
				$filterBhk.html(filterAllOptionHtml($filterBhk));
				(bhks || []).forEach(function (bhk) {
					$filterBhk.append(`<option value="${bhk.id}">${bhk.name}</option>`);
				});
				$filterBhk.prop('disabled', false);
				$filterBhk.data('placeholder', 'All Sizes');
				initFilterSelect2($filterBhk);
				bhkOptionsLoaded = true;
				if (typeof onComplete === 'function') {
					onComplete();
				}
			})
			.fail(function () {
				destroyFilterSelect2($filterBhk);
				$filterBhk.html(filterAllOptionHtml($filterBhk));
				$filterBhk.prop('disabled', true);
				$filterBhk.data('placeholder', 'Failed to load sizes');
				initFilterSelect2($filterBhk);
				if (typeof onComplete === 'function') {
					onComplete();
				}
			});
	}

	function hideFurnishFilter(reset = true) {
		$filterFurnishWrap.addClass("d-none");
		if (reset) {
			setFilterDisabledState($filterFurnish, true, "All Furnish Types");
		}
	}

	function showFurnishFilter() {
		$filterFurnishWrap.removeClass("d-none");
		setFilterDisabledState($filterFurnish, false, "All Furnish Types");
	}

	function hideBhkFilter(reset = true) {
		$filterBhkWrap.addClass("d-none");
		if (reset) {
			resetBhkFilter();
		}
	}

	function showBhkFilter() {
		$filterBhkWrap.removeClass("d-none");
		loadBhkOptions();
	}

	function setFilterDisabledState($element, disabled, placeholder) {
		destroyFilterSelect2($element);
		$element.val(null);
		$element.prop("disabled", disabled);
		$element.data("placeholder", placeholder);
		initFilterSelect2($element, { allowClear: !disabled });
	}

	function updatePropertyDependentFilters(propertyTypeId, resetValues = true) {
		const meta = getPropertyTypeMeta(propertyTypeId);

		if (resetValues) {
			resetPropertySubTypeFilter();
			hideFurnishFilter(true);
			hideBhkFilter(true);
		}

		if (!propertyTypeId) {
			return;
		}

		loadPropertySubTypes(propertyTypeId);

		if (meta.is_residential || meta.is_commercial) {
			showFurnishFilter();
		} else {
			hideFurnishFilter(true);
		}

		if (meta.is_residential) {
			showBhkFilter();
		} else {
			hideBhkFilter(true);
		}

		setTimeout(refreshFilterSelect2Layout, 50);
	}

	setTimeout(refreshFilterSelect2Layout, 150);

	function resetCityFilter() {
		destroyFilterSelect2($filterCity);
		$filterCity.html(filterAllOptionHtml($filterCity));
		$filterCity.prop('disabled', true);
		$filterCity.data('placeholder', 'Select state first');
		initFilterSelect2($filterCity);
	}

	function loadStatesForCountry(countryId, onComplete) {
		if (!countryId) {
			destroyFilterSelect2($filterState);
			$filterState.html(filterAllOptionHtml($filterState));
			$filterState.prop('disabled', true);
			$filterState.data('placeholder', 'Select country first');
			initFilterSelect2($filterState);
			if (typeof onComplete === 'function') {
				onComplete();
			}
			return;
		}

		destroyFilterSelect2($filterState);
		$filterState.html(filterAllOptionHtml($filterState)).prop('disabled', true);
		$filterState.data('placeholder', 'Loading states...');
		initFilterSelect2($filterState);

		const statesUrl = window.statesOptionsUrl || `${window.appBaseUrl}/${window.adminBasePath}/api/states/options`;

		$.get(statesUrl, { country_id: countryId })
			.done(function (states) {
				destroyFilterSelect2($filterState);
				$filterState.html(filterAllOptionHtml($filterState));
				(states || []).forEach(function (state) {
					$filterState.append(`<option value="${state.id}">${state.name}</option>`);
				});
				$filterState.prop('disabled', false);
				$filterState.data('placeholder', 'All States');
				initFilterSelect2($filterState);
				if (typeof onComplete === 'function') {
					onComplete();
				}
			})
			.fail(function () {
				destroyFilterSelect2($filterState);
				$filterState.html(filterAllOptionHtml($filterState));
				$filterState.prop('disabled', true);
				$filterState.data('placeholder', 'Failed to load states');
				initFilterSelect2($filterState);
				if (typeof onComplete === 'function') {
					onComplete();
				}
			});
	}

	function loadCitiesForState(stateId) {
		if (!stateId) {
			resetCityFilter();
			return;
		}

		destroyFilterSelect2($filterCity);
		$filterCity.html(filterAllOptionHtml($filterCity)).prop('disabled', true);
		$filterCity.data('placeholder', 'Loading cities...');
		initFilterSelect2($filterCity);

		const citiesUrl = window.citiesOptionsUrl || `${window.appBaseUrl}/${window.adminBasePath}/api/cities/options`;

		$.get(citiesUrl, { state_id: stateId })
			.done(function (cities) {
				destroyFilterSelect2($filterCity);
				$filterCity.html(filterAllOptionHtml($filterCity));
				(cities || []).forEach(function (city) {
					$filterCity.append(`<option value="${city.id}">${city.name}</option>`);
				});
				$filterCity.prop('disabled', false);
				$filterCity.data('placeholder', 'All Cities');
				initFilterSelect2($filterCity);
			})
			.fail(function () {
				destroyFilterSelect2($filterCity);
				$filterCity.html(filterAllOptionHtml($filterCity));
				$filterCity.prop('disabled', true);
				$filterCity.data('placeholder', 'Failed to load cities');
				initFilterSelect2($filterCity);
			});
	}

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

	// Handle schedule booking button clicks (both .btn-soft-warning and .schedule-booking-btn)
	table.on('click', '.btn-soft-warning.schedule-booking-btn, .schedule-booking-btn', function (e) {
		e.preventDefault();
		const $btn = $(this);
		const bookingId = $btn.data('booking-id');
		const bookingDate = $btn.data('booking-date');

		if (bookingId) {
			$('#schedule-booking-id').val(bookingId);
			let selectedDate = '';
			if (bookingDate && bookingDate !== '' && bookingDate !== '-') {
				selectedDate = bookingDate;
				$('#current-booking-date').text(bookingDate);
			} else {
				$('#current-booking-date').text('Not set');
			}
			lastSelectedDate = selectedDate;
			// Always default to 'default' mode on open
			$('#schedule-mode-default').prop('checked', true);
			fetchHolidaysAndInitPicker(selectedDate);
			const modal = new bootstrap.Modal(document.getElementById('scheduleModal'));
			modal.show();
		} else {
			// Fallback to row data if data attributes not available
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
				$('#schedule-mode-default').prop('checked', true);
				fetchHolidaysAndInitPicker(selectedDate);
				const modal = new bootstrap.Modal(document.getElementById('scheduleModal'));
				modal.show();
			}
		}
	});

	// Make calendar icon clickable to open date picker
	$(document).on('click', '#calendar-icon-trigger', function () {
		if (flatpickrInstance) {
			flatpickrInstance.open();
		}
	});

	// Schedule submit button
	$('#scheduleSubmitBtn').on('click', async function () {
		const bookingId = $('#schedule-booking-id').val();
		const date = $('#schedule-date').val();
		const currentDateText = $('#current-booking-date').text();

		if (!date) {
			$('#schedule-date').addClass('is-invalid');
			return;
		}
		$('#schedule-date').removeClass('is-invalid');

		// Check if date has changed - normalize dates for comparison
		let dateChanged = false;
		let currentDateFormatted = null;
		if (currentDateText && currentDateText !== 'Not set') {
			// currentDateText might be in YYYY-MM-DD format
			currentDateFormatted = currentDateText.trim();
			// Normalize both dates to YYYY-MM-DD for comparison
			const newDateFormatted = date.trim();
			dateChanged = currentDateFormatted !== newDateFormatted;
		} else {
			// If no current date, this is a new schedule (not a change)
			dateChanged = false;
		}

		// Show confirmation if date changed
		if (dateChanged && typeof Swal !== 'undefined') {
			const oldDateFormatted = new Date(currentDateFormatted).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
			const newDateFormatted = new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });

			const result = await Swal.fire({
				icon: 'warning',
				title: 'Confirm Date Change',
				html: `
					<p>You are changing the booking date from <strong>${oldDateFormatted}</strong> to <strong>${newDateFormatted}</strong>.</p>
					<p class="text-danger"><strong>Warning:</strong> This will remove any existing photographer assignments and clear the booking time.</p>
					<p><strong>Are you sure you want to proceed?</strong></p>
				`,
				showCancelButton: true,
				confirmButtonColor: '#dc3545',
				cancelButtonColor: '#6c757d',
				confirmButtonText: 'Yes, Change Date',
				cancelButtonText: 'Cancel'
			});

			if (!result.isConfirmed) {
				return; // User cancelled
			}
		}

		// Use correct route for reschedule
		const baseUrl = window.appBaseUrl || '';
		$.ajax({
			url: `${baseUrl}/${window.adminBasePath}/bookings/${bookingId}/reschedule`,
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
							text: dateChanged ? 'Booking date changed successfully! Photographer assignments have been removed.' : 'Booking scheduled successfully!',
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
	window.acceptScheduleQuick = async function (bookingId) {
		// Get booking data from DataTable
		const dataTable = table.DataTable();
		const rowData = dataTable.rows().data().toArray().find(row => row.id === bookingId);

		const requestedDate = rowData?.booking_date || 'Not specified';
		const customerNotes = rowData?.booking_notes || '';
		const userName = rowData?.user || rowData?.customer || 'N/A';

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
				const response = await fetch(`${window.appBaseUrl}/${window.adminBasePath}/pending-schedules/${bookingId}/accept`, {
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
	window.declineScheduleQuick = async function (bookingId) {
		// Get booking data from DataTable
		const dataTable = table.DataTable();
		const rowData = dataTable.rows().data().toArray().find(row => row.id === bookingId);

		const requestedDate = rowData?.booking_date || 'Not specified';
		const customerNotes = rowData?.booking_notes || '';
		const userName = rowData?.user || rowData?.customer || 'N/A';

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
				const response = await fetch(`${window.appBaseUrl}/${window.adminBasePath}/pending-schedules/${bookingId}/decline`, {
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
	window.deleteBooking = async function (bookingId) {
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
				const response = await fetch(`${window.appBaseUrl}/${window.adminBasePath}/bookings/${bookingId}`, {
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
					dataTable.ajax.reload(null, false);
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
}
