/**
 * QR Location Tracker
 * Handles GPS location capture with user permission modal
 */
(function() {
    'use strict';
    
    let locationRequested = false;
    let locationDenied = false;
    let pendingLocationAlert = null; // Store location data for alert after backend response
    let locationRequestStartTime = null; // Track when location request started
    
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
        console.log('📍 Location permission popup triggered - waiting for user action...');
        locationRequestStartTime = Date.now(); // Track when request started
        
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    // Permission granted! Now close modal and proceed
                    console.log('✅ Location permission granted!');
                    closeLocationModal();
                    locationDenied = false; // Reset flag
                    
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    const accuracy = position.coords.accuracy || 'N/A';
                    
                    console.log('📍 GPS Coordinates captured:', lat, lng);
                    
                    // Store location data for alert - will show after backend response with full address details
                    pendingLocationAlert = {
                        latitude: lat,
                        longitude: lng,
                        accuracy: accuracy,
                        timestamp: new Date(position.timestamp).toLocaleString()
                    };
                    
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
                        
                        const currentOrigin = window.location.origin;
                        const trackVisitUrl = currentOrigin + '/track-visit';
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || 
                                         document.querySelector('input[name="_token"]')?.value ||
                                         window.csrfToken;
                        
                        // Helper functions for getting tour code and page type
                        const getTourCodeFromUrl = function() {
                            const path = window.location.pathname;
                            const match = path.match(/\/([A-Za-z0-9]+)$/);
                            return match ? match[1] : null;
                        };
                        
                        const getPageType = function() {
                            const path = window.location.pathname;
                            if (path === '/' || path === '') return 'welcome';
                            if (path.includes('/analytics')) return 'analytics';
                            if (getTourCodeFromUrl()) return 'tour_code';
                            return 'welcome';
                        };
                        
                        // Send tracking data with GPS and handle response for alert
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
                                tour_code: getTourCodeFromUrl(),
                                page_type: getPageType(),
                                gps_latitude: lat,
                                gps_longitude: lng,
                                screen_resolution: screenResolution
                            })
                        });
                    }).then(response => {
                        if (response && response.ok) {
                            return response.json().then(result => {
                                // Clear pending alert (no popup shown, but tracking continues)
                                if (pendingLocationAlert) {
                                    pendingLocationAlert = null;
                                }
                                return result;
                            });
                        }
                        return null;
                    }).catch(error => {
                        console.error('❌ Failed to store GPS coordinates:', error);
                        // Clear pending alert (no popup shown, but tracking continues)
                        if (pendingLocationAlert) {
                            pendingLocationAlert = null;
                        }
                    });
                },
                function(error) {
                    // Permission still denied - keep modal open and try again
                    console.warn('⚠️ Location permission still denied:', error.message);
                    console.warn('⚠️ Modal will remain open - please allow location access');
                    
                    // Calculate how long the request took
                    const requestDuration = locationRequestStartTime ? Date.now() - locationRequestStartTime : 0;
                    locationRequestStartTime = null; // Reset
                    
                    let actionType = 'close';
                    let actionMessage = 'You closed the location permission popup - No location access granted.';
                    
                    if (error.code === error.PERMISSION_DENIED) {
                        // Check if it was a quick close (X button) or actual Block click
                        if (requestDuration < 2000) {
                            // Quick response = user closed popup (X button)
                            actionType = 'close';
                            actionMessage = 'You closed the location permission popup - No location access granted.';
                            console.warn('⚠️ Location permission denied quickly - user closed popup (X button)');
                            // No alert popup shown
                        } else {
                            // Slower response = user clicked Block button
                            actionType = 'block';
                            actionMessage = 'You clicked "Block" - Location access was denied.';
                            console.warn('⚠️ Location permission denied - user clicked Block button');
                            // No alert popup shown
                        }
                    } else if (error.code === error.TIMEOUT) {
                        // User closed the popup or it timed out
                        actionType = 'close';
                        actionMessage = 'You closed the location permission popup - No location access granted.';
                        // No alert popup shown
                    } else if (error.code === error.POSITION_UNAVAILABLE) {
                        // User closed the popup or location unavailable
                        actionType = 'close';
                        actionMessage = 'You closed the location permission popup - No location access granted.';
                        // No alert popup shown
                    } else {
                        // Other errors - likely user closed popup
                        actionType = 'close';
                        actionMessage = 'You closed the location permission popup - No location access granted.';
                        // No alert popup shown
                    }
                    
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
            console.log('📍 Location permission popup triggered - waiting for user action...');
            locationRequestStartTime = Date.now(); // Track when request started
            
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    // GPS coordinates available - user clicked "Allow"
                    const lat = parseFloat(position.coords.latitude);
                    const lng = parseFloat(position.coords.longitude);
                    const accuracy = position.coords.accuracy || 'N/A';
                    
                    trackingData.gps_latitude = lat;
                    trackingData.gps_longitude = lng;
                    
                    console.log('✅ GPS Coordinates captured:', lat, lng);
                    console.log('GPS Accuracy:', accuracy, 'meters');
                    
                    // Store location data for alert - will show after backend response with full address details
                    pendingLocationAlert = {
                        latitude: lat,
                        longitude: lng,
                        accuracy: accuracy,
                        timestamp: new Date(position.timestamp).toLocaleString()
                    };
                    
                    sendTrackingData(trackingData);
                    locationRequested = true;
                },
                function(error) {
                    // GPS not available or denied
                    console.warn('⚠️ Location error:', error.code, error.message);
                    locationRequested = true;
                    
                    // Calculate how long the request took
                    const requestDuration = locationRequestStartTime ? Date.now() - locationRequestStartTime : 0;
                    locationRequestStartTime = null; // Reset
                    
                    let actionType = 'close';
                    let actionMessage = 'You closed the location permission popup - No location access granted.';
                    
                    if (error.code === error.PERMISSION_DENIED) {
                        // Check if it was a quick close (X button) or actual Block click
                        // If error happened very quickly (< 2 seconds), user likely closed the popup
                        // If it took longer, user likely clicked Block button
                        if (requestDuration < 2000) {
                            // Quick response = user closed popup (X button)
                            actionType = 'close';
                            actionMessage = 'You closed the location permission popup - No location access granted.';
                            console.warn('⚠️ Location permission denied quickly - user closed popup (X button)');
                        } else {
                            // Slower response = user clicked Block button
                            locationDenied = true;
                            actionType = 'block';
                            actionMessage = 'You clicked "Block" - Location access was denied.';
                            console.warn('⚠️ Location permission denied - user clicked Block button');
                            // Show modal immediately (no alert popup)
                            setTimeout(() => {
                                showLocationModal();
                            }, 500);
                        }
                    } else if (error.code === error.TIMEOUT) {
                        // User closed the popup or it timed out
                        actionType = 'close';
                        actionMessage = 'You closed the location permission popup - No location access granted.';
                        console.warn('⚠️ Location timeout - user closed popup');
                    } else if (error.code === error.POSITION_UNAVAILABLE) {
                        // User closed the popup or location unavailable
                        actionType = 'close';
                        actionMessage = 'You closed the location permission popup - No location access granted.';
                        console.warn('⚠️ Location unavailable - user closed popup');
                    } else {
                        // Other errors - likely user closed popup
                        actionType = 'close';
                        actionMessage = 'You closed the location permission popup - No location access granted.';
                        console.warn('⚠️ Location error - user likely closed popup');
                    }
                    
                    // No alert popup shown - tracking continues silently
                    
                    // Always send tracking data even when permission denied/closed
                    // Mark as permission denied/closed - save null location data but track the action
                    locationDenied = true;
                    console.log('📊 Sending tracking data for action:', actionType);
                    
                    // Add action type to tracking data
                    trackingData.location_action = actionType; // 'block' or 'close'
                    trackingData.permission_denied = true;
                    trackingData.gps_unavailable = true;
                    
                    // Send tracking data with action information
                    sendTrackingData(trackingData);
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
            // Always send tracking data - track all actions (allow, block, close)
            // Even if permission denied/closed, we still want to track the visit
            
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
                            gps_unavailable: !data.gps_latitude && !data.gps_longitude ? true : false,
                            location_action: data.location_action || null // 'allow', 'block', or 'close'
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
                            gps_unavailable: !data.gps_latitude && !data.gps_longitude ? true : false,
                            location_action: data.location_action || null // 'allow', 'block', or 'close'
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
                
                // Clear pending alert (no popup shown, but tracking continues)
                if (pendingLocationAlert) {
                    pendingLocationAlert = null;
                }
                
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
                    // Location data tracking completed (no UI display - for debugging only)
                    if (result.latitude || result.longitude || result.city || result.country) {
                        console.log('✅ Location data tracked successfully (UI display removed)');
                    } else {
                        console.warn('⚠️ No location data in tracking result');
                    }
                } else {
                    console.error('❌ Tracking failed - result:', result);
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
        
        // displayGeolocationData function removed - UI display disabled for production
        // Tracking data is still saved to database, but visual display is removed
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

