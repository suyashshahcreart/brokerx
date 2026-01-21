/**
 * QR Analytics Index - DataTable Implementation
 */

import GMaps from 'gmaps/gmaps';

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const tableElement = document.getElementById('qr-analytics-table');
    if (!tableElement) {
        console.error('QR Analytics table element not found');
        return;
    }

    // Initialize DataTable
    const table = $('#qr-analytics-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: window.qrAnalyticsIndexUrl || '',
            type: 'GET',
            data: function (d) {
                // Add filter parameters
                d.tour_code = $('#filterTourCode').val() || '';
                d.booking_id = $('#filterBookingId').val() || '';
                d.country = $('#filterCountry').val() || '';
                d.city = $('#filterCity').val() || '';
                d.device_type = $('#filterDeviceType').val() || '';
                d.location_source = $('#filterLocationSource').val() || '';
                d.tracking_status = $('#filterTrackingStatus').val() || '';
                d.page_type = $('#filterPageType').val() || '';
                d.date_from = $('#filterDateFrom').val() || '';
                d.date_to = $('#filterDateTo').val() || '';
            },
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
                data: 'tour_code', 
                name: 'tour_code',
                orderable: true
            },
            { 
                data: 'booking_id', 
                name: 'booking_id',
                orderable: true
            },
            { 
                data: 'location', 
                name: 'city',
                orderable: false
            },
            { 
                data: 'device_info', 
                name: 'device_type',
                orderable: false
            },
            { 
                data: 'location_source', 
                name: 'location_source',
                orderable: true
            },
            { 
                data: 'tracking_status', 
                name: 'tracking_status',
                orderable: true
            },
            { 
                data: 'scan_date', 
                name: 'scan_date',
                orderable: true
            },
            { 
                data: 'actions', 
                name: 'actions', 
                orderable: false, 
                searchable: false, 
                className: 'text-end'
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
            searchPlaceholder: "Search analytics...",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ analytics",
            infoEmpty: "No analytics found",
            infoFiltered: "(filtered from _MAX_ total analytics)",
            loadingRecords: "Loading...",
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
            emptyTable: "No analytics available",
            zeroRecords: "No matching analytics found"
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

    // Apply filters button
    $('#applyFilters').on('click', function() {
        table.ajax.reload();
    });

    // Clear filters button
    $('#clearFilters').on('click', function() {
        $('#filterTourCode').val('');
        $('#filterBookingId').val('');
        $('#filterCountry').val('');
        $('#filterCity').val('');
        $('#filterDeviceType').val('');
        $('#filterLocationSource').val('');
        $('#filterTrackingStatus').val('');
        $('#filterPageType').val('');
        $('#filterDateFrom').val('');
        $('#filterDateTo').val('');
        table.ajax.reload();
    });

    // Store map instance for cleanup
    let currentMapInstance = null;
    
    // View analytics fullscreen modal
    $(document).on('click', '.view-analytics', function() {
        const analyticsId = $(this).data('id');
        const modal = new bootstrap.Modal(document.getElementById('viewAnalyticsModal'));
        const detailsContainer = document.getElementById('analyticsDetails');
        
        // Clean up previous map instance if exists
        if (currentMapInstance) {
            try {
                // Clear map instance
                currentMapInstance = null;
            } catch (e) {
                console.warn('Error cleaning up map:', e);
            }
        }
        
        // Show loading
        detailsContainer.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
        modal.show();
        
        // Fetch analytics details
        const url = window.qrAnalyticsShowUrl.replace(':id', analyticsId);
        fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.analytics) {
                const analytics = data.analytics;
                const booking = analytics.booking || null;
                const user = analytics.user || null;
                const metadata = analytics.metadata || {};
                
                let html = `
                    <div class="container-fluid">
                        <div class="row g-3">
                            <!-- Basic Information -->
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0"><i class="ri-information-line me-2"></i>Basic Information</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-2">
                                            <div class="col-md-2">
                                                <label class="form-label fw-semibold small">ID</label>
                                                <p class="mb-2 small">#${analytics.id}</p>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label fw-semibold small">Tour Code</label>
                                                <p class="mb-2">${analytics.tour_code ? '<span class="badge bg-primary">' + analytics.tour_code + '</span>' : '<span class="text-muted small">-</span>'}</p>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label fw-semibold small">Page Type</label>
                                                <p class="mb-2"><span class="badge bg-info">${analytics.page_type || '-'}</span></p>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label fw-semibold small">Status</label>
                                                <p class="mb-2">
                `;
                
                const statusBadges = {
                    'success': 'bg-success',
                    'error': 'bg-danger',
                    'invalid_tour_code': 'bg-warning'
                };
                const statusClass = statusBadges[analytics.tracking_status] || 'bg-secondary';
                html += `<span class="badge ${statusClass} text-uppercase">${analytics.tracking_status || '-'}</span></p></div>`;
                
                if (booking && booking.id) {
                    html += `
                                            <div class="col-md-2">
                                                <label class="form-label fw-semibold small">Booking</label>
                                                <p class="mb-2 small">
                                                    <a href="${window.appBaseUrl}/${window.adminBasePath}/bookings/${booking.id}" class="text-primary" target="_blank">
                                                        #${booking.id}
                                                    </a>
                                                </p>
                                            </div>
                    `;
                } else if (analytics.booking_id) {
                    html += `
                                            <div class="col-md-2">
                                                <label class="form-label fw-semibold small">Booking</label>
                                                <p class="mb-2 small">
                                                    <a href="${window.appBaseUrl}/${window.adminBasePath}/bookings/${analytics.booking_id}" class="text-primary" target="_blank">
                                                        #${analytics.booking_id}
                                                    </a>
                                                </p>
                                            </div>
                    `;
                }
                
                html += `
                                            <div class="col-md-2">
                                                <label class="form-label fw-semibold small">Scan Date</label>
                                                <p class="mb-2 small">${analytics.formatted_scan_date || (analytics.scan_date || '-')}</p>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold small">Page URL</label>
                                                <p class="mb-0 small text-break">${analytics.page_url || '<span class="text-muted">-</span>'}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Location Information and Map Row -->
                            <div class="col-12">
                                <div class="row g-3">
                                    <!-- Location Information -->
                                    <div class="col-md-6">
                                        <div class="card h-100">
                                            <div class="card-header bg-success text-white">
                                                <h5 class="mb-0"><i class="ri-map-pin-line me-2"></i>Location Information</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row g-2">
                                                    <div class="col-12">
                                                        <label class="form-label fw-semibold small">Location Source</label>
                                                        <p class="mb-2">
                `;
                
                const locationBadges = {
                    'GPS': 'bg-success',
                    'IP': 'bg-info',
                    'UNAVAILABLE': 'bg-secondary'
                };
                const locClass = locationBadges[analytics.location_source] || 'bg-secondary';
                html += `<span class="badge ${locClass}">${analytics.location_source || '-'}</span></p></div>`;
                
                html += `
                                                    <div class="col-6">
                                                        <label class="form-label fw-semibold small">Country</label>
                                                        <p class="mb-2 small">${analytics.country || '<span class="text-muted">-</span>'}</p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label fw-semibold small">Region/State</label>
                                                        <p class="mb-2 small">${analytics.region || '<span class="text-muted">-</span>'}</p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label fw-semibold small">City</label>
                                                        <p class="mb-2 small">${analytics.city || '<span class="text-muted">-</span>'}</p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label fw-semibold small">PIN Code</label>
                                                        <p class="mb-2 small">${analytics.pincode || '<span class="text-muted">-</span>'}</p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label fw-semibold small">Latitude</label>
                                                        <p class="mb-2 small">${analytics.latitude || '<span class="text-muted">-</span>'}</p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label fw-semibold small">Longitude</label>
                                                        <p class="mb-2 small">${analytics.longitude || '<span class="text-muted">-</span>'}</p>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label fw-semibold small">Full Address</label>
                                                        <p class="mb-2 small">${analytics.full_address || '<span class="text-muted">-</span>'}</p>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label fw-semibold small">Timezone</label>
                                                        <p class="mb-0 small">${analytics.timezone || '<span class="text-muted">-</span>'}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Google Map -->
                                    <div class="col-md-6">
                `;
                
                // Add Google Map section only if lat/long are available
                if (analytics.latitude && analytics.longitude) {
                    html += `
                                        <div class="card h-100">
                                            <div class="card-header bg-danger text-white">
                                                <h5 class="mb-0"><i class="ri-map-2-line me-2"></i>Location Map</h5>
                                            </div>
                                            <div class="card-body p-0" style="min-height: 400px;">
                                                <div id="qr-analytics-map" style="width: 100%; height: 400px; min-height: 400px;"></div>
                                            </div>
                                        </div>
                    `;
                } else {
                    html += `
                                        <div class="card h-100">
                                            <div class="card-header bg-secondary text-white">
                                                <h5 class="mb-0"><i class="ri-map-2-line me-2"></i>Location Map</h5>
                                            </div>
                                            <div class="card-body d-flex align-items-center justify-content-center" style="min-height: 400px;">
                                                <p class="text-muted mb-0"><i class="ri-information-line me-2"></i>No location coordinates available</p>
                                            </div>
                                        </div>
                    `;
                }
                
                html += `
                                    </div>
                                </div>
                            </div>
                `;
                
                html += `
                            <!-- Device & Browser Information -->
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="mb-0"><i class="ri-computer-line me-2"></i>Device & Browser</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <label class="form-label fw-semibold small">Device Type</label>
                                                <p class="mb-2">${analytics.device_type ? '<span class="badge bg-secondary">' + analytics.device_type + '</span>' : '<span class="text-muted small">-</span>'}</p>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label fw-semibold small">Screen Resolution</label>
                                                <p class="mb-2 small">${analytics.screen_resolution || '<span class="text-muted">-</span>'}</p>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label fw-semibold small">Browser</label>
                                                <p class="mb-2 small">${analytics.browser_name || '<span class="text-muted">-</span>'} ${analytics.browser_version ? 'v' + analytics.browser_version : ''}</p>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label fw-semibold small">Operating System</label>
                                                <p class="mb-2 small">${analytics.os_name || '<span class="text-muted">-</span>'} ${analytics.os_version ? 'v' + analytics.os_version : ''}</p>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label fw-semibold small">User Agent</label>
                                                <p class="mb-2 small text-break text-muted">${analytics.user_agent || '<span class="text-muted">-</span>'}</p>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label fw-semibold small">Language</label>
                                                <p class="mb-0 small">${analytics.language || '<span class="text-muted">-</span>'}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Network & Session Information -->
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-header bg-warning text-dark">
                                        <h5 class="mb-0"><i class="ri-global-line me-2"></i>Network & Session</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-2">
                                            <div class="col-12">
                                                <label class="form-label fw-semibold small">IP Address</label>
                                                <p class="mb-2 small">${analytics.user_ip || '<span class="text-muted">-</span>'}</p>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label fw-semibold small">Session ID</label>
                                                <p class="mb-2 small text-break">${analytics.session_id || '<span class="text-muted">-</span>'}</p>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label fw-semibold small">Referrer</label>
                                                <p class="mb-2 small text-break">${analytics.referrer || '<span class="text-muted">-</span>'}</p>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label fw-semibold small">Load Time</label>
                                                <p class="mb-0 small">${analytics.load_time ? analytics.load_time + 's' : '<span class="text-muted">-</span>'}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- UTM Parameters and Metadata Row -->
                            <div class="col-12">
                                <div class="row g-3">
                                    <!-- UTM Parameters -->
                                    <div class="col-md-6">
                                        <div class="card h-100">
                                            <div class="card-header bg-secondary text-white">
                                                <h5 class="mb-0"><i class="ri-links-line me-2"></i>UTM Parameters</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row g-2">
                                                    <div class="col-6">
                                                        <label class="form-label fw-semibold small">UTM Source</label>
                                                        <p class="mb-2 small">${analytics.utm_source || '<span class="text-muted">-</span>'}</p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label fw-semibold small">UTM Medium</label>
                                                        <p class="mb-2 small">${analytics.utm_medium || '<span class="text-muted">-</span>'}</p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label fw-semibold small">UTM Campaign</label>
                                                        <p class="mb-2 small">${analytics.utm_campaign || '<span class="text-muted">-</span>'}</p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label fw-semibold small">UTM Term</label>
                                                        <p class="mb-2 small">${analytics.utm_term || '<span class="text-muted">-</span>'}</p>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label fw-semibold small">UTM Content</label>
                                                        <p class="mb-0 small">${analytics.utm_content || '<span class="text-muted">-</span>'}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Metadata -->
                                    <div class="col-md-6">
                `;
                
                if (Object.keys(metadata).length > 0) {
                    html += `
                                        <div class="card h-100">
                                            <div class="card-header bg-dark text-white">
                                                <h5 class="mb-0"><i class="ri-database-2-line me-2"></i>Metadata</h5>
                                            </div>
                                            <div class="card-body">
                                                <pre class="bg-light p-2 rounded small mb-0" style="max-height: 300px; overflow-y: auto; font-size: 11px;">${JSON.stringify(metadata, null, 2)}</pre>
                                            </div>
                                        </div>
                    `;
                } else {
                    html += `
                                        <div class="card h-100">
                                            <div class="card-header bg-dark text-white">
                                                <h5 class="mb-0"><i class="ri-database-2-line me-2"></i>Metadata</h5>
                                            </div>
                                            <div class="card-body d-flex align-items-center justify-content-center" style="min-height: 200px;">
                                                <p class="text-muted mb-0"><i class="ri-information-line me-2"></i>No metadata available</p>
                                            </div>
                                        </div>
                    `;
                }
                
                html += `
                                    </div>
                                </div>
                            </div>
                `;
                
                if (analytics.error_message) {
                    html += `
                            <!-- Error Information -->
                            <div class="col-12">
                                <div class="card border-danger">
                                    <div class="card-header bg-danger text-white">
                                        <h5 class="mb-0"><i class="ri-error-warning-line me-2"></i>Error Information</h5>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-0 text-danger">${analytics.error_message}</p>
                                    </div>
                                </div>
                            </div>
                    `;
                }
                
                html += `</div></div>`;
                detailsContainer.innerHTML = html;
                
                // Initialize Google Map if lat/long are available
                if (analytics.latitude && analytics.longitude) {
                    const lat = parseFloat(analytics.latitude);
                    const lng = parseFloat(analytics.longitude);
                    
                    // Validate coordinates
                    if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
                        // Function to initialize map
                        const initMap = () => {
                            const mapElement = document.getElementById('qr-analytics-map');
                            if (!mapElement) return;
                            
                            try {
                                // Clear any existing map
                                mapElement.innerHTML = '';
                                
                                // Check if Google Maps API is available
                                if (typeof google === 'undefined' || !google.maps) {
                                    mapElement.innerHTML = '<div class="alert alert-warning m-3">Google Maps API is not loaded. Please check your configuration and ensure GOOGLE_MAPS_API_KEY is set in your .env file.</div>';
                                    return;
                                }
                                
                                // Initialize Google Map
                                const map = new GMaps({
                                    div: '#qr-analytics-map',
                                    lat: lat,
                                    lng: lng,
                                    zoom: 15
                                });
                                
                                // Store map instance
                                currentMapInstance = map;
                                
                                // Add marker
                                map.addMarker({
                                    lat: lat,
                                    lng: lng,
                                    title: analytics.full_address || `${analytics.city || ''}, ${analytics.country || ''}`.trim() || 'Location',
                                    infoWindow: {
                                        content: `
                                            <div style="padding: 10px; min-width: 200px;">
                                                <strong>Location Details</strong><br>
                                                ${analytics.full_address ? `<p class="mb-1" style="font-size: 12px;">${analytics.full_address}</p>` : ''}
                                                ${analytics.city ? `<p class="mb-1" style="font-size: 12px;"><strong>City:</strong> ${analytics.city}</p>` : ''}
                                                ${analytics.region ? `<p class="mb-1" style="font-size: 12px;"><strong>Region:</strong> ${analytics.region}</p>` : ''}
                                                ${analytics.country ? `<p class="mb-1" style="font-size: 12px;"><strong>Country:</strong> ${analytics.country}</p>` : ''}
                                                <p class="mb-0" style="font-size: 12px;"><strong>Coordinates:</strong> ${analytics.latitude}, ${analytics.longitude}</p>
                                            </div>
                                        `
                                    }
                                });
                                
                                // Trigger resize to ensure map displays correctly
                                setTimeout(() => {
                                    if (google && google.maps && map && map.map) {
                                        google.maps.event.trigger(map.map, 'resize');
                                    }
                                }, 100);
                            } catch (error) {
                                console.error('Error initializing Google Map:', error);
                                const mapElement = document.getElementById('qr-analytics-map');
                                if (mapElement) {
                                    mapElement.innerHTML = '<div class="alert alert-warning m-3">Unable to load map. Error: ' + error.message + '</div>';
                                }
                            }
                        };
                        
                        // Wait for Google Maps API to load
                        const waitForGoogleMaps = (attempts = 0) => {
                            if (typeof google !== 'undefined' && google.maps) {
                                // Google Maps is loaded, initialize map
                                setTimeout(() => initMap(), 300);
                            } else if (attempts < 20) {
                                // Wait a bit more (max 10 seconds)
                                setTimeout(() => waitForGoogleMaps(attempts + 1), 500);
                            } else {
                                // Timeout - show error
                                const mapElement = document.getElementById('qr-analytics-map');
                                if (mapElement) {
                                    mapElement.innerHTML = '<div class="alert alert-warning m-3">Google Maps API failed to load. Please check your API key configuration in .env file (GOOGLE_MAPS_API_KEY).</div>';
                                }
                            }
                        };
                        
                        // Start waiting for Google Maps
                        waitForGoogleMaps();
                    } else {
                        // Invalid coordinates
                        const mapElement = document.getElementById('qr-analytics-map');
                        if (mapElement) {
                            mapElement.innerHTML = '<div class="alert alert-warning m-3">Invalid coordinates provided.</div>';
                        }
                    }
                }
            } else {
                detailsContainer.innerHTML = '<div class="alert alert-danger">Failed to load analytics details.</div>';
            }
        })
        .catch(error => {
            console.error('Error loading analytics:', error);
            detailsContainer.innerHTML = '<div class="alert alert-danger">Error loading analytics details. Please try again.</div>';
        });
    });
    
    // Clean up map when modal is closed
    $('#viewAnalyticsModal').on('hidden.bs.modal', function () {
        if (currentMapInstance) {
            try {
                // Clear map instance
                currentMapInstance = null;
                // Clear map container
                const mapElement = document.getElementById('qr-analytics-map');
                if (mapElement) {
                    mapElement.innerHTML = '';
                }
            } catch (e) {
                console.warn('Error cleaning up map on modal close:', e);
            }
        }
    });
});

