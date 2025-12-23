/**
 * QR Location Tracker
 * Handles GPS location capture with user permission modal
 */
(function() {
    'use strict';
    
    let locationRequested = false;
    let locationDenied = false;
    
    // Create location permission modal
    function createLocationModal() {
        const modal = document.createElement('div');
        modal.id = 'locationModal';
        modal.className = 'location-modal';
        modal.innerHTML = `
            <div class="location-modal-content">
                <h3>📍 Location Access Required</h3>
                <p>To provide accurate location tracking, we need access to your device's location.</p>
                <p><strong>Why we need this:</strong></p>
                <ul style="text-align: left; color: #666; margin: 16px 0;">
                    <li>Accurate GPS coordinates for precise location tracking</li>
                    <li>Better analytics and reporting</li>
                    <li>Improved user experience</li>
                </ul>
                <div class="location-modal-buttons">
                    <button class="location-modal-btn primary" onclick="requestLocationAgain()">Allow Location Access</button>
                </div>
            </div>
        `;
        
        // Add modal styles if not already present
        if (!document.getElementById('locationModalStyles')) {
            const style = document.createElement('style');
            style.id = 'locationModalStyles';
            style.textContent = `
                .location-modal {
                    display: none;
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.7);
                    z-index: 10000;
                    align-items: center;
                    justify-content: center;
                }
                .location-modal.active {
                    display: flex;
                }
                .location-modal-content {
                    background: #fff;
                    color: #333;
                    padding: 32px;
                    border-radius: 16px;
                    max-width: 500px;
                    width: 90%;
                    box-shadow: 0 20px 60px rgba(0,0,0,0.5);
                }
                .location-modal-content h3 {
                    margin: 0 0 16px;
                    color: #667eea;
                }
                .location-modal-content p {
                    margin: 12px 0;
                    color: #666;
                }
                .location-modal-buttons {
                    display: flex;
                    justify-content: center;
                    gap: 12px;
                    margin-top: 24px;
                }
                .location-modal-btn {
                    flex: 1;
                    padding: 12px 24px;
                    border: none;
                    border-radius: 8px;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all 0.3s;
                }
                .location-modal-btn.primary {
                    background: #667eea;
                    color: #fff;
                }
                .location-modal-btn.primary:hover {
                    background: #5568d3;
                }
                .location-modal-btn.secondary {
                    background: #e0e0e0;
                    color: #333;
                }
                .location-modal-btn.secondary:hover {
                    background: #d0d0d0;
                }
            `;
            document.head.appendChild(style);
        }
        
        document.body.appendChild(modal);
        return modal;
    }
    
    function showLocationModal() {
        let modal = document.getElementById('locationModal');
        if (!modal) {
            modal = createLocationModal();
        }
        if (locationDenied) {
            modal.classList.add('active');
        }
    }
    
    window.closeLocationModal = function() {
        const modal = document.getElementById('locationModal');
        if (modal) {
            modal.classList.remove('active');
        }
    };
    
    window.requestLocationAgain = function() {
        // Don't close modal yet - wait for permission to be granted
        console.log('🔄 Requesting location permission again...');
        
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    // Permission granted! Now close modal and proceed
                    console.log('✅ Location permission granted!');
                    closeLocationModal();
                    locationDenied = false; // Reset flag
                    
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    console.log('📍 GPS Coordinates captured:', lat, lng);
                    
                    // Send GPS coordinates to session
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || 
                                     document.querySelector('input[name="_token"]')?.value ||
                                     window.csrfToken;
                    
                    const currentOrigin = window.location.origin;
                    const trackScreenUrl = currentOrigin + '/track-screen';
                    
                    fetch(trackScreenUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken || '',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            gps_latitude: lat,
                            gps_longitude: lng
                        })
                    }).then(() => {
                        console.log('✅ GPS coordinates stored in session');
                        // Now trigger full tracking with GPS coordinates
                        const screenResolution = window.screen.width + 'x' + window.screen.height;
                        const tourCode = getTourCodeFromUrl();
                        const pageType = getPageType();
                        
                        const trackingData = {
                            screen_resolution: screenResolution,
                            gps_latitude: lat,
                            gps_longitude: lng
                        };
                        
                        // Send tracking data with GPS
                        sendTrackingData(trackingData);
                    }).catch(error => {
                        console.error('❌ Failed to store GPS coordinates:', error);
                        // Still try to send tracking data
                        const screenResolution = window.screen.width + 'x' + window.screen.height;
                        const trackingData = {
                            screen_resolution: screenResolution,
                            gps_latitude: lat,
                            gps_longitude: lng
                        };
                        sendTrackingData(trackingData);
                    });
                },
                function(error) {
                    // Permission still denied - keep modal open and try again
                    console.warn('⚠️ Location permission still denied:', error.message);
                    console.warn('⚠️ Modal will remain open - please allow location access');
                    
                    // Keep modal open - don't close it
                    // Show a message in the modal
                    const modal = document.getElementById('locationModal');
                    if (modal) {
                        const content = modal.querySelector('.location-modal-content');
                        if (content) {
                            const existingMsg = content.querySelector('.error-message');
                            if (existingMsg) {
                                existingMsg.textContent = 'Location access is required. Please click "Allow" in the browser popup.';
                            } else {
                                const errorMsg = document.createElement('p');
                                errorMsg.className = 'error-message';
                                errorMsg.style.cssText = 'color: #dc2626; font-weight: 600; margin-top: 12px;';
                                errorMsg.textContent = 'Location access is required. Please click "Allow" in the browser popup.';
                                content.appendChild(errorMsg);
                            }
                        }
                    }
                    
                    // Try again after a short delay
                    setTimeout(() => {
                        requestLocationAgain();
                    }, 2000);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        } else {
            alert('Geolocation is not supported by your browser.');
        }
    };
    
    // Main tracking function
    function trackLocation() {
        console.log('📍 QR Location Tracker: Starting location tracking...');
        const screenResolution = window.screen.width + 'x' + window.screen.height;
        console.log('Screen resolution:', screenResolution);
        const trackingData = {
            screen_resolution: screenResolution
        };
        
        // Try to get GPS coordinates from browser (more accurate than IP-based)
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    // GPS coordinates available - use these for accurate location
                    const lat = parseFloat(position.coords.latitude);
                    const lng = parseFloat(position.coords.longitude);
                    
                    trackingData.gps_latitude = lat;
                    trackingData.gps_longitude = lng;
                    
                    console.log('GPS Coordinates captured:', lat, lng);
                    console.log('GPS Accuracy:', position.coords.accuracy, 'meters');
                    sendTrackingData(trackingData);
                    locationRequested = true;
                },
                function(error) {
                    // GPS not available or denied
                    console.warn('⚠️ Location error:', error.code, error.message);
                    locationRequested = true;
                    
                    if (error.code === error.PERMISSION_DENIED) {
                        locationDenied = true;
                        console.warn('⚠️ Location permission denied - showing modal');
                        // Show modal immediately - DO NOT send tracking data with wrong location
                        setTimeout(() => {
                            showLocationModal();
                        }, 500);
                        // DO NOT send tracking data when permission is denied - wait for user to allow
                        return; // Exit early, don't send tracking data
                    }
                    
                    // For other errors (timeout, position unavailable, etc.)
                    // DO NOT send tracking data with wrong IP-based location
                    // Mark as permission denied equivalent - save null location data
                    console.warn('⚠️ Location error (timeout/unavailable) - NOT sending tracking to avoid wrong IP-based data');
                    locationDenied = true; // Treat timeout as denied to save null data
                    // Don't send tracking data - wait for user to allow GPS or save null
                    return; // Exit early, don't send tracking data
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        } else {
            // Geolocation not supported
            console.warn('⚠️ Geolocation not supported by browser, using IP-based tracking');
            sendTrackingData(trackingData);
        }
        
        function sendTrackingData(data) {
            // If permission was denied and no GPS, don't send tracking data
            if (locationDenied && !data.gps_latitude && !data.gps_longitude) {
                console.warn('⚠️ Skipping tracking - permission denied and no GPS data');
                return; // Don't send tracking data with wrong IP-based location
            }
            
            // Get CSRF token from multiple sources
            let csrfToken = window.csrfToken || 
                           document.querySelector('meta[name="csrf-token"]')?.content ||
                           document.querySelector('meta[name="csrf-token-value"]')?.content ||
                           document.querySelector('input[name="_token"]')?.value;
            
            if (!csrfToken) {
                console.warn('CSRF token not found in meta tags, trying cookies...');
                // Try to get it from cookies (Laravel stores as XSRF-TOKEN)
                const cookies = document.cookie.split(';');
                for (let cookie of cookies) {
                    const [name, value] = cookie.trim().split('=');
                    if (name === 'XSRF-TOKEN' || name === '_token') {
                        csrfToken = decodeURIComponent(value);
                        break;
                    }
                }
            }
            
            if (!csrfToken) {
                console.error('CSRF token not found! Tracking may fail.');
            } else {
                console.log('CSRF token found, proceeding with tracking...');
            }
            
            // First, store screen resolution and GPS in session
            // ALWAYS use the current page's origin to avoid cross-domain issues
            const currentOrigin = window.location.origin; // https://qr.proppik.com
            let trackScreenUrl = document.querySelector('[data-track-url]')?.dataset.trackUrl;
            
            // If data-track-url is provided, check if it's relative or absolute
            if (!trackScreenUrl) {
                // No data-track-url, use relative URL with current origin
                trackScreenUrl = currentOrigin + '/track-screen';
            } else if (trackScreenUrl.startsWith('http://') || trackScreenUrl.startsWith('https://')) {
                // Absolute URL - check if it's from a different domain
                try {
                    const urlObj = new URL(trackScreenUrl);
                    if (urlObj.origin !== currentOrigin) {
                        // Different domain - use current origin instead
                        console.warn('⚠️ Replacing different domain URL:', trackScreenUrl);
                        trackScreenUrl = currentOrigin + urlObj.pathname;
                    } else if (trackScreenUrl.startsWith('http://')) {
                        // Same domain but HTTP - convert to HTTPS
                        trackScreenUrl = trackScreenUrl.replace('http://', 'https://');
                        console.warn('⚠️ Converted HTTP to HTTPS:', trackScreenUrl);
                    }
                } catch (e) {
                    // Invalid URL, use relative with current origin
                    trackScreenUrl = currentOrigin + '/track-screen';
                }
            } else if (trackScreenUrl.startsWith('/')) {
                // Relative URL - prepend current origin
                trackScreenUrl = currentOrigin + trackScreenUrl;
            } else {
                // Invalid format, use default
                trackScreenUrl = currentOrigin + '/track-screen';
            }
            
            console.log('📤 Using track-screen URL:', trackScreenUrl);
            
            fetch(trackScreenUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify(data)
            }).then(response => {
                if (response.ok) {
                    console.log('Screen/GPS data stored in session');
                    
                    // Now trigger the actual tracking with GPS coordinates
                    const tourCode = getTourCodeFromUrl();
                    const pageType = getPageType();
                    
                    // Use current origin to ensure same domain
                    const trackVisitUrl = currentOrigin + '/track-visit';
                    console.log('📤 Using track-visit URL:', trackVisitUrl);
                    return fetch(trackVisitUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken || '',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            tour_code: tourCode,
                            page_type: pageType,
                            gps_latitude: data.gps_latitude ? parseFloat(data.gps_latitude) : null,
                            gps_longitude: data.gps_longitude ? parseFloat(data.gps_longitude) : null,
                            screen_resolution: data.screen_resolution,
                            permission_denied: locationDenied && !data.gps_latitude && !data.gps_longitude ? true : false,
                            gps_unavailable: !data.gps_latitude && !data.gps_longitude ? true : false
                        })
                    });
                } else {
                    console.error('Failed to store screen/GPS data:', response.status, response.statusText);
                    // Still try to track even if session storage fails
                    const tourCode = getTourCodeFromUrl();
                    const pageType = getPageType();
                    
                    // Use relative URL to avoid mixed content errors
                    const trackVisitUrl = currentOrigin + '/track-visit';
                    console.log('📤 Using track-visit URL (fallback):', trackVisitUrl);
                    return fetch(trackVisitUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken || '',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            tour_code: tourCode,
                            page_type: pageType,
                            gps_latitude: data.gps_latitude ? parseFloat(data.gps_latitude) : null,
                            gps_longitude: data.gps_longitude ? parseFloat(data.gps_longitude) : null,
                            screen_resolution: data.screen_resolution,
                            permission_denied: locationDenied && !data.gps_latitude && !data.gps_longitude ? true : false,
                            gps_unavailable: !data.gps_latitude && !data.gps_longitude ? true : false
                        })
                    });
                }
            }).then(response => {
                console.log('📥 track-visit response status:', response?.status, response?.statusText);
                if (response && response.ok) {
                    return response.json().then(data => {
                        console.log('📊 Tracking result JSON:', data);
                        return data;
                    }).catch(err => {
                        console.error('❌ Failed to parse JSON:', err);
                        return null;
                    });
                } else {
                    console.error('❌ Tracking response not OK:', response?.status, response?.statusText);
                    // Try to read error response
                    if (response) {
                        return response.text().then(text => {
                            console.error('❌ Error response body:', text);
                            return null;
                        }).catch(err => {
                            console.error('❌ Failed to read error response:', err);
                            return null;
                        });
                    }
                    return null;
                }
            }).then(result => {
                console.log('📊 Final tracking result:', result);
                if (result && result.success) {
                    console.log('✅ Tracking completed successfully!');
                    console.log('📍 Location data:', {
                        source: result.location_source,
                        city: result.city,
                        region: result.region,
                        country: result.country,
                        lat: result.latitude,
                        lng: result.longitude
                    });
                    // Display geolocation data - ALWAYS show if we have any location data
                    if (result.latitude || result.longitude || result.city || result.country) {
                        console.log('🎨 Displaying geolocation data...');
                        displayGeolocationData(result);
                    } else {
                        console.warn('⚠️ No location data in tracking result to display');
                    }
                } else {
                    console.error('❌ Tracking failed - result:', result);
                    // Even if tracking fails, try to display what we have
                    if (result && (result.latitude || result.city || result.country)) {
                        console.log('🎨 Displaying partial location data...');
                        displayGeolocationData(result);
                    }
                }
            }).catch(function(error) {
                console.error('❌ Tracking error:', error);
                console.error('Full error details:', error.message);
                if (error.stack) {
                    console.error('Stack trace:', error.stack);
                }
            });
        }
        
        function getTourCodeFromUrl() {
            const path = window.location.pathname;
            // Extract tour_code from URL (e.g., /e4GzGJC1z)
            const match = path.match(/\/([A-Za-z0-9]+)$/);
            return match ? match[1] : null;
        }
        
        function getPageType() {
            const path = window.location.pathname;
            if (path === '/' || path === '') return 'welcome';
            if (path.includes('/analytics')) return 'analytics';
            if (getTourCodeFromUrl()) return 'tour_code';
            return 'welcome';
        }
        
        function displayGeolocationData(data) {
            // Create or update geolocation display section
            let geoDisplay = document.getElementById('qr-geolocation-display');
            if (!geoDisplay) {
                geoDisplay = document.createElement('div');
                geoDisplay.id = 'qr-geolocation-display';
                geoDisplay.className = 'location-info';
                geoDisplay.style.cssText = 'margin-top: 24px; padding: 20px; background: linear-gradient(135deg, rgba(30, 64, 175, 0.15) 0%, rgba(6, 182, 212, 0.12) 50%, rgba(30, 64, 175, 0.1) 100%); border-radius: 16px; border: 1px solid rgba(255,255,255,0.2); backdrop-filter: blur(10px);';
                
                // Find a good place to insert it (after booking info or at end of card)
                const card = document.querySelector('.card');
                const bookingInfo = document.querySelector('.booking-info');
                if (card) {
                    if (bookingInfo) {
                        // Insert after booking info
                        bookingInfo.parentNode.insertBefore(geoDisplay, bookingInfo.nextSibling);
                    } else {
                        // Insert at end of card
                        card.appendChild(geoDisplay);
                    }
                }
            }
            
            const lat = data.latitude ? parseFloat(data.latitude).toFixed(8) : 'N/A';
            const lng = data.longitude ? parseFloat(data.longitude).toFixed(8) : 'N/A';
            const sourceBadge = data.location_source === 'GPS' ? 
                '<span style="background: rgba(22, 163, 74, 0.3); color: #a8ffd0; padding: 4px 8px; border-radius: 6px; font-size: 0.85rem; font-weight: 600;">GPS</span>' :
                '<span style="background: rgba(251, 191, 36, 0.3); color: #fde047; padding: 4px 8px; border-radius: 6px; font-size: 0.85rem; font-weight: 600;">IP</span>';
            
            geoDisplay.innerHTML = `
                <h4 style="margin: 0 0 16px; font-size: 1.3rem; color: #fff; text-shadow: 0 0 10px rgba(255,255,255,0.3); display: flex; align-items: center; gap: 8px;">
                    <span>📍</span> Your Location ${sourceBadge}
                </h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; text-align: left;">
                    <div>
                        <strong style="color: #e0f2fe; display: block; margin-bottom: 4px; font-size: 0.9rem;">Country:</strong>
                        <div style="color: #f0f9ff; font-size: 1rem; font-weight: 500;">${data.country || 'N/A'}</div>
                    </div>
                    <div>
                        <strong style="color: #e0f2fe; display: block; margin-bottom: 4px; font-size: 0.9rem;">State:</strong>
                        <div style="color: #f0f9ff; font-size: 1rem; font-weight: 500;">${data.region || 'N/A'}</div>
                    </div>
                    <div>
                        <strong style="color: #e0f2fe; display: block; margin-bottom: 4px; font-size: 0.9rem;">City:</strong>
                        <div style="color: #f0f9ff; font-size: 1rem; font-weight: 500;">${data.city || 'N/A'}</div>
                    </div>
                    ${data.pincode ? `
                    <div>
                        <strong style="color: #e0f2fe; display: block; margin-bottom: 4px; font-size: 0.9rem;">PIN Code:</strong>
                        <div style="color: #f0f9ff; font-size: 1rem; font-weight: 500;">${data.pincode}</div>
                    </div>
                    ` : ''}
                    <div style="grid-column: 1 / -1;">
                        <strong style="color: #e0f2fe; display: block; margin-bottom: 4px; font-size: 0.9rem;">Coordinates:</strong>
                        <div style="color: #f0f9ff; font-size: 0.95rem; font-family: monospace; background: rgba(0,0,0,0.2); padding: 8px; border-radius: 6px;">
                            Lat: ${lat}<br>Lng: ${lng}
                        </div>
                    </div>
                    ${data.full_address ? `
                    <div style="grid-column: 1 / -1;">
                        <strong style="color: #e0f2fe; display: block; margin-bottom: 4px; font-size: 0.9rem;">Full Address:</strong>
                        <div style="color: #f0f9ff; font-size: 0.9rem; line-height: 1.5;">${data.full_address}</div>
                    </div>
                    ` : ''}
                </div>
            `;
        }
    }
    
    // Initialize when DOM is ready
    function initTracking() {
        console.log('🚀 QR Location Tracker: Initializing...');
        console.log('Page URL:', window.location.href);
        console.log('CSRF Token available:', !!window.csrfToken);
        trackLocation();
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTracking);
    } else {
        // DOM already loaded, initialize immediately
        initTracking();
    }
})();

