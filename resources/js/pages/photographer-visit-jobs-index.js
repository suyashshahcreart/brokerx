/**
 * Photographer Visit Jobs Index - DataTable Handler
 * 
 * This module handles the photographer visit jobs listing page with DataTables.
 * 
 * Features:
 * - Server-side DataTable with pagination and sorting
 * - Real-time filtering by status, priority, photographer, and date
 * - Statistics cards showing job counts by status
 * - AJAX-based CRUD operations (view, edit, delete)
 * - Responsive design with Bootstrap 5
 * - Notification system for user feedback
 * 
 * Dependencies:
 * - jQuery
 * - DataTables (with Bootstrap 5 styling)
 * - Bootstrap 5
 * - SweetAlert2 (optional, for better confirmations)
 * 
 * @author BrokerX Development Team
 * @version 1.0.0
 * @since 2025-11-27
 */

document.addEventListener('DOMContentLoaded', function () {
    const $table = window.jQuery ? window.jQuery('#jobs-table') : null;
    
    if (!$table || !$table.length) {
        console.error('Jobs table not found');
        return;
    }

    const ajaxUrl = window.photographerJobsRoutes?.index || '/admin/photographer-visit-jobs';
    const csrfToken = window.jQuery('meta[name="csrf-token"]').attr('content');

    // Initialize DataTable
    const dataTable = $table.DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: ajaxUrl,
            data: function(d) {
                d.status = window.jQuery('#statusFilter').val();
                d.priority = window.jQuery('#priorityFilter').val();
                d.photographer_id = window.jQuery('#photographerFilter').val();
                d.scheduled_date = window.jQuery('#dateFilter').val();
            }
        },
        order: [[3, 'desc']], // Order by scheduled date descending
        columns: [
            { 
                data: 'job_code', 
                name: 'job_code',
                render: function(data, type, row) {
                    return data; // HTML already formatted from server
                }
            },
            { 
                data: 'booking', 
                name: 'booking_id'
            },
            { 
                data: 'photographer', 
                name: 'photographer.name'
            },
            { 
                data: 'scheduled_date', 
                name: 'scheduled_date'
            },
            { 
                data: 'priority', 
                name: 'priority', 
                className: 'text-center'
            },
            { 
                data: 'status', 
                name: 'status', 
                className: 'text-center'
            },
            { 
                data: 'action', 
                name: 'action', 
                orderable: false, 
                searchable: false, 
                className: 'text-end'
            }
        ],
        lengthMenu: [10, 25, 50, 100],
        language: {
            search: '_INPUT_',
            searchPlaceholder: 'Search jobs...',
            emptyTable: "No photographer visit jobs available",
            zeroRecords: "No matching jobs found",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ jobs",
            infoEmpty: "Showing 0 to 0 of 0 jobs",
            infoFiltered: "(filtered from _MAX_ total jobs)",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        },
        drawCallback: function () {
            // Initialize Bootstrap tooltips
            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                    new bootstrap.Tooltip(el);
                });
            }
            
            // Update statistics after table is drawn
            updateStatistics();
        }
    });

    // Filter handlers
    window.jQuery('#statusFilter, #priorityFilter, #photographerFilter, #dateFilter').on('change', function() {
        dataTable.draw();
    });

    // Clear filters button
    window.jQuery('#clearFilters').on('click', function() {
        window.jQuery('#statusFilter').val('');
        window.jQuery('#priorityFilter').val('');
        window.jQuery('#photographerFilter').val('');
        window.jQuery('#dateFilter').val('');
        dataTable.draw();
    });

    // Delete handler with SweetAlert2 or native confirm
    $table.on('click', '.delete-btn', function (event) {
        event.preventDefault();

        const button = window.jQuery(this);
        const jobId = button.data('id');
        const jobCode = button.data('code') || 'this job';

        const deleteJob = () => {
            window.jQuery.ajax({
                url: `${ajaxUrl}/${jobId}`,
                type: 'DELETE',
                data: {
                    _token: csrfToken
                },
                beforeSend: function() {
                    // Show loading state
                    button.prop('disabled', true);
                },
                success: function(response) {
                    button.prop('disabled', false);
                    
                    // Reload table without resetting pagination
                    dataTable.ajax.reload(null, false);
                    
                    // Show success notification
                    showNotification('success', response.message || 'Job deleted successfully');
                },
                error: function(xhr) {
                    button.prop('disabled', false);
                    
                    const message = xhr.responseJSON?.message || 'Error deleting job. Please try again.';
                    showNotification('error', message);
                }
            });
        };

        // Use SweetAlert2 if available, otherwise native confirm
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Delete Job',
                text: `Are you sure you want to delete ${jobCode}? This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel',
                customClass: {
                    confirmButton: 'btn btn-danger me-2',
                    cancelButton: 'btn btn-outline-secondary'
                },
                buttonsStyling: false
            }).then(result => {
                if (result.isConfirmed) {
                    deleteJob();
                }
            });
        } else {
            if (confirm(`Are you sure you want to delete ${jobCode}? This action cannot be undone.`)) {
                deleteJob();
            }
        }
    });

    /**
     * Update statistics cards
     */
    function updateStatistics() {
        window.jQuery.ajax({
            url: ajaxUrl,
            data: {
                get_stats: true
            },
            success: function(data) {
                if (data.stats) {
                    window.jQuery('#pendingCount').text(data.stats.pending || 0);
                    window.jQuery('#assignedCount').text(data.stats.assigned || 0);
                    window.jQuery('#inProgressCount').text(data.stats.in_progress || 0);
                    window.jQuery('#completedCount').text(data.stats.completed || 0);
                }
            },
            error: function(xhr) {
                console.error('Failed to fetch statistics:', xhr);
            }
        });
    }

    /**
     * Show notification
     */
    function showNotification(type, message) {
        // Try SweetAlert2 first
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: type,
                title: type === 'success' ? 'Success' : 'Error',
                text: message,
                timer: 3000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
            return;
        }

        // Fallback to Bootstrap alert
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const icon = type === 'success' ? 'bi-check-circle' : 'bi-exclamation-circle';
        
        const notification = window.jQuery(`
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed top-0 end-0 m-3" 
                 role="alert" style="z-index: 9999; min-width: 300px;">
                <i class="bi ${icon} me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `);
        
        window.jQuery('body').append(notification);
        
        // Auto-dismiss after 3 seconds
        setTimeout(function() {
            notification.fadeOut(function() {
                window.jQuery(this).remove();
            });
        }, 3000);
    }

    // Load initial statistics
    updateStatistics();
});

