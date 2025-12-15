/**
 * Tours Index - DataTable Implementation
 */

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const tableElement = document.getElementById('tours-table');
    if (!tableElement) {
        console.error('Tours table element not found');
        return;
    }

    // Initialize DataTable
    const table = $('#tours-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: tourIndexUrl,
            type: 'GET',
            error: function (xhr, error, code) {
                console.error('DataTable Ajax Error:', error);
            }
        },
        columns: [
            { 
                data: 'id', 
                name: 'id',
                width: '60px'
            },
            { 
                data: 'title', 
                name: 'title',
                render: function(data, type, row) {
                    return data || '<span class="text-muted">-</span>';
                }
            },
            { 
                data: 'location', 
                name: 'location',
                render: function(data, type, row) {
                    return data || '<span class="text-muted">-</span>';
                }
            },
            { 
                data: 'price', 
                name: 'price',
                orderable: true
            },
            { 
                data: 'duration', 
                name: 'duration_days',
                orderable: true
            },
            { 
                data: 'dates', 
                name: 'start_date',
                orderable: true
            },
            { 
                data: 'participants', 
                name: 'max_participants',
                orderable: true
            },
            { 
                data: 'status', 
                name: 'status',
                orderable: true
            },
            { 
                data: 'actions', 
                name: 'actions', 
                orderable: false, 
                searchable: false, 
                className: 'text-end',
                width: '120px'
            }
        ],
        order: [[0, 'desc']],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        responsive: true,
        language: {
            paginate: {
                previous: "<i class='ri-arrow-left-s-line'></i>",
                next: "<i class='ri-arrow-right-s-line'></i>"
            },
            search: "_INPUT_",
            searchPlaceholder: "Search tours...",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ tours",
            infoEmpty: "No tours found",
            infoFiltered: "(filtered from _MAX_ total tours)",
            loadingRecords: "Loading...",
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
            emptyTable: "No tours available",
            zeroRecords: "No matching tours found"
        },
        drawCallback: function() {
            $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
        },
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
    });

    // Panel card refresh button
    const refreshBtn = document.querySelector('[data-panel-action="refresh"]');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            table.ajax.reload(null, false); // false to keep current page
        });
    }

    // Handle delete confirmation
    $(document).on('submit', 'form[action*="tours"]', function(e) {
        const form = this;
        if (form.querySelector('button[type="submit"]').classList.contains('btn-soft-danger')) {
            e.preventDefault();
            if (confirm('Are you sure you want to delete this tour?')) {
                form.submit();
            }
        }
    });
});
