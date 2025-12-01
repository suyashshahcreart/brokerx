/**
 * Photographer Visits Index Page
 * DataTables initialization and filter handling
 */

document.addEventListener('DOMContentLoaded', function() {
    'use strict';
    
    console.log('Initializing DataTables for photographer visits...');
    
    // Check if jQuery and DataTables are loaded
    if (typeof jQuery === 'undefined') {
        console.error('jQuery is not loaded!');
        return;
    }
    
    if (typeof jQuery.fn.DataTable === 'undefined') {
        console.error('DataTables is not loaded!');
        return;
    }
    
    const $ = jQuery;
    
    // Initialize DataTable
    const table = $('#visits-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: window.photographerVisitsConfig.indexRoute,
            type: 'GET',
            data: function(d) {
                d.status = $('#filter-status').val();
                d.photographer_id = $('#filter-photographer').val();
                d.date_from = $('#filter-date-from').val();
                d.date_to = $('#filter-date-to').val();
                console.log('DataTables request data:', d);
            },
            dataSrc: function(json) {
                console.log('DataTables raw response:', json);
                return json.data;
            },
            error: function(xhr, error, thrown) {
                console.error('DataTables Error:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    error: error,
                    thrown: thrown,
                    response: xhr.responseText,
                    responseJSON: xhr.responseJSON
                });
                
                let errorMsg = 'Error loading data.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg += ' ' + xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg += ' ' + xhr.responseJSON.error;
                }
                
                alert(errorMsg + ' Please check console for details.');
            }
        },
        columns: [
            { 
                data: 'id', 
                name: 'id', 
                width: '80px',
                render: function(data, type, row) {
                    return data || '-';
                }
            },
            { 
                data: 'photographer_name', 
                name: 'photographer.firstname',
                render: function(data, type, row) {
                    return data || '-';
                }
            },
            { 
                data: 'booking_info', 
                name: 'booking_id',
                render: function(data, type, row) {
                    return data || '-';
                }
            },
            { 
                data: 'visit_date', 
                name: 'visit_date',
                render: function(data, type, row) {
                    return data || '-';
                }
            },
            { 
                data: 'status', 
                name: 'status', 
                width: '120px',
                render: function(data, type, row) {
                    return data || '-';
                }
            },
            { 
                data: 'actions', 
                name: 'actions', 
                orderable: false, 
                searchable: false, 
                className: 'text-end', 
                width: '100px',
                render: function(data, type, row) {
                    return data || '';
                }
            }
        ],
        order: [[0, 'desc']],
        language: {
            processing: '<i class="ri-loader-4-line spin"></i> Loading...',
            emptyTable: 'No photographer visits found',
            zeroRecords: 'No matching visits found'
        },
        drawCallback: function(settings) {
            console.log('DataTables draw completed', settings);
            
            // Reinitialize tooltips if they exist
            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }
        }
    });

    // Filter change events
    $('#filter-status, #filter-photographer, #filter-date-from, #filter-date-to').on('change', function() {
        console.log('Filter changed, redrawing table...');
        table.draw();
    });

    // Panel card refresh
    $('[data-panel-action="refresh"]').on('click', function() {
        console.log('Refreshing table...');
        table.ajax.reload();
    });
    
    console.log('Photographer Visits DataTable initialized successfully');
});
