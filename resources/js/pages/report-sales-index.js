document.addEventListener('DOMContentLoaded', function() {
    const $dateRange = $('#dateRange');
    const fromInput = document.getElementById('from');
    const toInput = document.getElementById('to');
    const resetBtn = document.getElementById('resetFilters');
    let table;

    // Set initial date range display
    if (fromInput.value && toInput.value) {
        const fromDate = moment(fromInput.value);
        const toDate = moment(toInput.value);
        $dateRange.val(fromDate.format('MM/DD/YYYY') + ' - ' + toDate.format('MM/DD/YYYY'));
    }

    // Initialize DataTable
    function initializeDataTable() {
        if (table) {
            table.destroy();
        }
        let dataUrl = $('#salesDataTable').data('url-featch') || 'sales/'; 
        console.log('Data URL for DataTable:', dataUrl);    
        table = $('#salesDataTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: dataUrl,
                type: 'GET',
                data: function(d) {
                    d.from = fromInput.value;
                    d.to = toInput.value;
                    d.ajax = 1;
                },
                error: function(xhr, error, thrown) {
                    console.error('DataTables AJAX Error:', error, xhr);
                    alert('Error loading data. Please try again.');
                }
            },
            columns: [
                { data: 'customer', name: 'customer', width: '20%', orderable: false },
                { data: 'booking_info', name: 'booking_info', width: '20%', orderable: false },
                { data: 'payment_amount', name: 'cashfree_payment_amount', className: 'text-end', width: '15%' },
                { data: 'booking_price', name: 'booking_price', className: 'text-end', width: '15%' },
                { data: 'booking_date', name: 'booking_date', width: '15%' },
                { data: 'created_at', name: 'created_at', width: '15%' }
            ],
            columnDefs: [
                {
                    targets: [0, 1],
                    render: function(data) {
                        return data;
                    },
                    createdCell: function(td) {
                        $(td).html($(td).text());
                    }
                }
            ],
            order: [[0, 'asc']],
            pageLength: 10,
            language: {
                emptyTable: 'No sales found for the selected period.'
            },
            drawCallback: function() {
                updateSummaryCards();
            }
        });
    }

    // Update summary cards
    function updateSummaryCards() {
        const from = fromInput.value;
        const to = toInput.value;

        if (!from || !to) return;

        const dataUrl = $('#salesDataTable').data('url-featch') || 'sales/';
        $.ajax({
            url: dataUrl,
            type: 'GET',
            data: {
                from: from,
                to: to,
                summary: 'true',
                ajax: 1
            },
            dataType: 'json',
            success: function(data) {
                const formatRupees = (amount) => '₹' + (Math.round(amount) / 100).toLocaleString('en-IN', { 
                    minimumFractionDigits: 2, 
                    maximumFractionDigits: 2 
                });

                document.getElementById('totalSalesDisplay').textContent = formatRupees(data.totalSales || 0);
                document.getElementById('totalBookingsDisplay').textContent = (data.totalBookings || 0).toLocaleString();
                
                const avg = (data.totalBookings || 0) > 0 ? data.totalSales / data.totalBookings : 0;
                document.getElementById('avgTicketDisplay').textContent = formatRupees(avg);
            },
            error: function(xhr, error, thrown) {
                console.error('Error updating summary:', error, xhr);
            }
        });
    }

    // Initialize DateRangePicker
    if (typeof $.fn.daterangepicker === 'function' && typeof moment === 'function') {
        $dateRange.daterangepicker({
            autoUpdateInput: true,
            alwaysShowCalendars: true,
            opens: 'left',
            startDate: moment(fromInput.value || moment().subtract(6, 'days')),
            endDate: moment(toInput.value || moment()),
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                'This Year': [moment().startOf('year'), moment().endOf('year')],
                'Last Year': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')]
            },
            locale: {
                cancelLabel: 'Clear',
                format: 'MM/DD/YYYY'
            }
        }, function(start, end) {
            // Update hidden inputs
            fromInput.value = start.format('YYYY-MM-DD');
            toInput.value = end.format('YYYY-MM-DD');
            // Reload table
            if (table) {
                table.ajax.reload();
            }
        });

        $dateRange.on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
            fromInput.value = picker.startDate.format('YYYY-MM-DD');
            toInput.value = picker.endDate.format('YYYY-MM-DD');
            if (table) {
                table.ajax.reload();
            }
        });

        $dateRange.on('cancel.daterangepicker', function() {
            $(this).val('');
            fromInput.value = '';
            toInput.value = '';
            if (table) {
                table.ajax.reload();
            }
        });
    } else {
        console.warn('DateRangePicker not available');
    }

    // Reset filters
    resetBtn.addEventListener('click', function() {
        const today = moment();
        const sixDaysAgo = moment().subtract(6, 'days');
        
        fromInput.value = sixDaysAgo.format('YYYY-MM-DD');
        toInput.value = today.format('YYYY-MM-DD');
        
        if ($dateRange.data('daterangepicker')) {
            $dateRange.data('daterangepicker').setStartDate(sixDaysAgo);
            $dateRange.data('daterangepicker').setEndDate(today);
            $dateRange.val(sixDaysAgo.format('MM/DD/YYYY') + ' - ' + today.format('MM/DD/YYYY'));
        }
        
        if (table) {
            table.ajax.reload();
        }
    });

    // Initialize on page load
    initializeDataTable();
});
