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