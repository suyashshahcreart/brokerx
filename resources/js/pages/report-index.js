console.log('Report Index Page Loaded');

document.addEventListener('DOMContentLoaded', function () {
    // Initialize panel cards
    const panelCards = document.querySelectorAll('[data-panel-card]');
    panelCards.forEach(card => {
        // Add your panel card initialization logic here
    });

    // Handle export button clicks
    const exportButtons = document.querySelectorAll('.export-btn');
    const exportModal = document.getElementById('exportModal');
    const exportModalLabel = document.getElementById('exportModalLabel');
    const exportReportTypeInput = document.getElementById('exportReportType');
    const exportUrlInput = document.getElementById('exportUrl');
    const exportDateRangeInput = document.getElementById('exportDateRange');
    const exportOwnerTypeInput = document.getElementById('exportOwnerType');
    const exportCustomerInput = document.getElementById('exportCustomer');
    const exportPropertyTypeInput = document.getElementById('exportPropertyType');
    const exportPropertySubTypeInput = document.getElementById('exportPropertySubType');
    const exportStateInput = document.getElementById('exportState');
    const exportCityInput = document.getElementById('exportCity');
    const exportPinCodeInput = document.getElementById('exportPinCode');
    const exportFromDateInput = document.getElementById('exportFromDate');
    const exportToDateInput = document.getElementById('exportToDate');
    const exportConfirmBtn = document.getElementById('exportConfirmBtn');

    // Set default dates
    const today = moment();
    const thirtyDaysAgo = moment().subtract(30, 'days');

    // Initialize date range picker
    $(exportDateRangeInput).daterangepicker({
        startDate: thirtyDaysAgo,
        endDate: today,
        opens: 'center',
        autoApply: false,
        locale: {
            format: 'DD/MM/YYYY'
        },
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            'Last 3 Months': [moment().subtract(3, 'months').startOf('month'), moment().endOf('month')],
            'Last 6 Months': [moment().subtract(6, 'months').startOf('month'), moment().endOf('month')],
            'This Year': [moment().startOf('year'), moment().endOf('year')],
            'Last Year': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')],
        }
    }, function(start, end, label) {
        exportFromDateInput.value = start.format('YYYY-MM-DD');
        exportToDateInput.value = end.format('YYYY-MM-DD');
    });

    // Update hidden inputs on picker initialization
    exportFromDateInput.value = thirtyDaysAgo.format('YYYY-MM-DD');
    exportToDateInput.value = today.format('YYYY-MM-DD');

    exportButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const reportType = this.dataset.reportType;
            const exportUrl = this.dataset.exportUrl;
            const reportName = this.textContent.trim();
            
            exportReportTypeInput.value = reportType;
            exportUrlInput.value = exportUrl;
            exportModalLabel.textContent = `Select Date Range - ${reportName}`;
        });
    });

    // Handle export confirmation
    exportConfirmBtn.addEventListener('click', function () {
        const exportUrl = exportUrlInput.value;
        const fromDate = exportFromDateInput.value;
        const toDate = exportToDateInput.value;
        const ownerType = exportOwnerTypeInput ? exportOwnerTypeInput.value : '';
        const customerId = exportCustomerInput ? exportCustomerInput.value : '';
        const propertyTypeId = exportPropertyTypeInput ? exportPropertyTypeInput.value : '';
        const propertySubTypeId = exportPropertySubTypeInput ? exportPropertySubTypeInput.value : '';
        const stateId = exportStateInput ? exportStateInput.value : '';
        const cityId = exportCityInput ? exportCityInput.value : '';
        const pinCode = exportPinCodeInput ? exportPinCodeInput.value : '';

        if (!exportUrl) {
            alert('Export URL is missing. Please try again.');
            return;
        }

        if (!fromDate || !toDate) {
            alert('Please select a date range');
            return;
        }

        if (new Date(fromDate) > new Date(toDate)) {
            alert('From date must be before To date');
            return;
        }

        // Create form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = exportUrl;
        form.style.display = 'none';

        const tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = '_token';
        tokenInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const fromInput = document.createElement('input');
        fromInput.type = 'hidden';
        fromInput.name = 'from';
        fromInput.value = fromDate;

        const toInput = document.createElement('input');
        toInput.type = 'hidden';
        toInput.name = 'to';
        toInput.value = toDate;

        if (ownerType) {
            const ownerTypeInput = document.createElement('input');
            ownerTypeInput.type = 'hidden';
            ownerTypeInput.name = 'owner_type';
            ownerTypeInput.value = ownerType;
            form.appendChild(ownerTypeInput);
        }

        if (customerId) {
            const customerInput = document.createElement('input');
            customerInput.type = 'hidden';
            customerInput.name = 'user_id';
            customerInput.value = customerId;
            form.appendChild(customerInput);
        }

        if (propertyTypeId) {
            const propertyTypeInput = document.createElement('input');
            propertyTypeInput.type = 'hidden';
            propertyTypeInput.name = 'property_type_id';
            propertyTypeInput.value = propertyTypeId;
            form.appendChild(propertyTypeInput);
        }

        if (propertySubTypeId) {
            const propertySubTypeInput = document.createElement('input');
            propertySubTypeInput.type = 'hidden';
            propertySubTypeInput.name = 'property_sub_type_id';
            propertySubTypeInput.value = propertySubTypeId;
            form.appendChild(propertySubTypeInput);
        }

        if (stateId) {
            const stateInput = document.createElement('input');
            stateInput.type = 'hidden';
            stateInput.name = 'state_id';
            stateInput.value = stateId;
            form.appendChild(stateInput);
        }

        if (cityId) {
            const cityInput = document.createElement('input');
            cityInput.type = 'hidden';
            cityInput.name = 'city_id';
            cityInput.value = cityId;
            form.appendChild(cityInput);
        }

        if (pinCode) {
            const pinCodeInput = document.createElement('input');
            pinCodeInput.type = 'hidden';
            pinCodeInput.name = 'pin_code';
            pinCodeInput.value = pinCode;
            form.appendChild(pinCodeInput);
        }

        form.appendChild(tokenInput);
        form.appendChild(fromInput);
        form.appendChild(toInput);
        document.body.appendChild(form);
        form.submit();

        // Close modal
        const modal = bootstrap.Modal.getInstance(exportModal);
        if (modal) {
            modal.hide();
        }
    });
});