/**
 * Booking Assignee Index Page
 * Handles DataTables initialization and interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeDataTable();
});

/**
 * Initialize DataTable for booking assignees
 */
function initializeDataTable() {
    if (!document.getElementById('bookingAssigneesTable')) {
        return;
    }

    const table = $('#bookingAssigneesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: window.location.pathname,
            type: 'GET',
            data: function(d) {
                d._token = $('meta[name="csrf-token"]').attr('content');
            },
            error: function(xhr, status, error) {
                console.error('DataTable error:', error);
                showNotification('Error loading data', 'error');
            }
        },
        columns: [
            { 
                data: 'id', 
                name: 'id', 
                orderable: false, 
                searchable: false,
                width: '60px'
            },
            { 
                data: 'booking', 
                name: 'booking.id', 
                orderable: false, 
                searchable: false 
            },
            { 
                data: 'user', 
                name: 'user.name', 
                orderable: false, 
                searchable: false 
            },
            { 
                data: 'date', 
                name: 'date', 
                orderable: true, 
                searchable: false,
                width: '120px'
            },
            { 
                data: 'time', 
                name: 'time', 
                orderable: false, 
                searchable: false,
                width: '100px'
            },
            { 
                data: 'created_by', 
                name: 'createdBy.name', 
                orderable: false, 
                searchable: false 
            },
            { 
                data: 'created_at', 
                name: 'created_at', 
                orderable: true, 
                searchable: false,
                width: '150px'
            },
            { 
                data: 'actions', 
                name: 'actions', 
                orderable: false, 
                searchable: false,
                width: '80px'
            }
        ],
        order: [[6, 'desc']],
        pageLength: 15,
        lengthMenu: [[10, 15, 25, 50, -1], [10, 15, 25, 50, 'All']],
        language: {
            paginate: {
                next: '<i class="ri-arrow-right-s-line"></i>',
                previous: '<i class="ri-arrow-left-s-line"></i>'
            },
            emptyTable: 'No booking assignments found',
            loadingRecords: 'Loading...',
            processing: 'Processing...',
            zeroRecords: 'No matching records found'
        },
        dom: 'Blfrtip',
        drawCallback: function() {
            // Re-initialize dropdowns after each draw
            initializeDropdowns();
            // Bind delete handlers
            bindDeleteHandlers();
        }
    });

    // Search functionality
    $('#tableSearch').on('keyup', function() {
        table.search($(this).val()).draw();
    });

    return table;
}

/**
 * Initialize Bootstrap dropdowns
 */
function initializeDropdowns() {
    // Re-initialize Bootstrap dropdowns
    const dropdownElementList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'));
    dropdownElementList.map(function(dropdownToggleEl) {
        return new bootstrap.Dropdown(dropdownToggleEl);
    });
}

/**
 * Bind delete button handlers
 */
function bindDeleteHandlers() {
    document.querySelectorAll('form[data-delete-form]').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const deleteBtn = this.querySelector('button[type="submit"]');
            const assigneeId = this.getAttribute('data-assignee-id');
            
            if (!confirm('Are you sure you want to delete this assignment?')) {
                return;
            }

            // Disable button and show loading state
            deleteBtn.disabled = true;
            deleteBtn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Deleting...';

            // Submit the form
            this.submit();
        });
    });
}

/**
 * Show notification message
 * @param {string} message - The message to display
 * @param {string} type - 'success', 'error', 'warning', 'info'
 */
function showNotification(message, type = 'info') {
    const alertClass = {
        success: 'alert-success',
        error: 'alert-danger',
        warning: 'alert-warning',
        info: 'alert-info'
    };

    const alertHTML = `
        <div class="alert ${alertClass[type] || alertClass.info} alert-dismissible fade show" role="alert">
            <strong>${type.charAt(0).toUpperCase() + type.slice(1)}!</strong> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;

    const alertContainer = document.querySelector('[data-alert-container]');
    if (alertContainer) {
        alertContainer.insertAdjacentHTML('afterbegin', alertHTML);
    }
}

/**
 * Export DataTable to CSV
 */
function exportToCSV() {
    const table = $('#bookingAssigneesTable').DataTable();
    const data = table.rows({ search: 'applied' }).data();
    
    let csv = 'ID,Booking,User,Date,Time,Created By,Created At\n';
    
    data.each(function(value) {
        // Extract text from HTML
        const id = $(value.id).text();
        const booking = $(value.booking).text();
        const user = $(value.user).text();
        const date = $(value.date).text();
        const time = $(value.time).text();
        const createdBy = value.created_by;
        const createdAt = $(value.created_at).text();
        
        csv += `"${id}","${booking}","${user}","${date}","${time}","${createdBy}","${createdAt}"\n`;
    });

    downloadCSV(csv, 'booking-assignees.csv');
}

/**
 * Download CSV file
 * @param {string} csv - CSV content
 * @param {string} filename - Output filename
 */
function downloadCSV(csv, filename) {
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    link.setAttribute('href', url);
    link.setAttribute('download', filename);
    link.style.visibility = 'hidden';
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
