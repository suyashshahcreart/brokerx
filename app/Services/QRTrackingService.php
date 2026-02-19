<?php

namespace App\Services;

use App\Models\QRAnalytics;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QRTrackingService
{
    /**
     * Track QR code visit/scan
     * 
     * @param Request $request
     * @param string|null $tour_code
     * @param string $page_type
     * @return QRAnalytics|null
     */
    public function trackVisit(Request $request, ?string $tour_code = null, string $page_type = 'welcome'): ?QRAnalytics
    {
        $startTime = microtime(true);
        
        try {
            // Find booking by tour_code if provided
            $booking = null;
            if ($tour_code) {
                $booking = Booking::where('tour_code', $tour_code)->first();
            }
            
            // Determine if we have GPS coordinates (prioritize request input, then session)
            $gpsLat = $request->input('gps_latitude');
            $gpsLng = $request->input('gps_longitude');
            
            // If not in request, check session
            if (empty($gpsLat) || empty($gpsLng)) {
                $gpsLat = session('qr_gps_latitude');
                $gpsLng = session('qr_gps_longitude');
            }
            
            // Convert to float and validate
            $hasGPS = !empty($gpsLat) && !empty($gpsLng) && is_numeric($gpsLat) && is_numeric($gpsLng);
            $latitude = $hasGPS ? (float)$gpsLat : null;
            $longitude = $hasGPS ? (float)$gpsLng : null;
            
            Log::info("QR Tracking GPS Check", [
                'hasGPS' => $hasGPS,
                'gpsLat' => $gpsLat,
                'gpsLng' => $gpsLng,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'request_input' => $request->input('gps_latitude'),
                'session' => session('qr_gps_latitude')
            ]);
            
            // Collect user data (pass GPS coordinates)
            $userData = $this->collectUserData($request, $gpsLat, $gpsLng);
            
            $geoData = [];
            $apiResponse = null;
            $apiService = null;
            
            // PRIORITY 1: GPS-based location - perfect accuracy, full address details
            // When GPS is available from browser, always use it first (skip IP lookup)
            if ($hasGPS && $latitude && $longitude) {
                Log::info("QR Tracking: Using GPS coordinates (first priority) - reverse geocoding for full accuracy", [
                    'lat' => $latitude,
                    'lng' => $longitude
                ]);
                $reverseGeoData = $this->reverseGeocode($latitude, $longitude);
                if (!empty($reverseGeoData)) {
                    $geoData['country'] = $reverseGeoData['country'] ?? null;
                    $geoData['city'] = $reverseGeoData['city'] ?? null;
                    $geoData['region'] = $reverseGeoData['region'] ?? null;
                    $geoData['full_address'] = $reverseGeoData['full_address'] ?? null;
                    $geoData['pincode'] = $reverseGeoData['pincode'] ?? null;
                    $geoData['timezone'] = $reverseGeoData['timezone'] ?? null;
                    Log::info("QR Tracking: GPS reverse geocoding successful - full accuracy", $reverseGeoData);
                } else {
                    Log::warning("QR Tracking: GPS reverse geocoding returned empty, keeping coordinates", [
                        'lat' => $latitude, 'lng' => $longitude
                    ]);
                }
            }
            
            // PRIORITY 2: IP-based fallback - only when GPS is NOT available
            if (!$hasGPS) {
                $permissionDenied = $request->input('permission_denied', false);
                $gpsUnavailable = $request->input('gps_unavailable', false);
                $locationAction = $request->input('location_action');
                
                $geoDataResult = $this->getGeolocationData($userData['user_ip'], $request);
                $geoData = $geoDataResult['data'] ?? [];
                $apiResponse = $geoDataResult['api_response'] ?? null;
                $apiService = $geoDataResult['service'] ?? null;
                
                if (!empty($geoData) && isset($geoData['latitude']) && isset($geoData['longitude'])) {
                    $latitude = $geoData['latitude'];
                    $longitude = $geoData['longitude'];
                    Log::info("QR Tracking: Using IP-based geolocation (GPS not available)", [
                        'api_service' => $apiService, 'latitude' => $latitude, 'longitude' => $longitude
                    ]);
                } else {
                    $latitude = null;
                    $longitude = null;
                    $geoData['country'] = $geoData['country'] ?? null;
                    $geoData['city'] = $geoData['city'] ?? null;
                    $geoData['region'] = $geoData['region'] ?? null;
                    $geoData['full_address'] = $geoData['full_address'] ?? null;
                    $geoData['pincode'] = $geoData['pincode'] ?? null;
                    Log::info("QR Tracking: No GPS and no IP-based data available", [
                        'permission_denied' => $permissionDenied, 'gps_unavailable' => $gpsUnavailable
                    ]);
                }
            }
            
            // Calculate load time
            $loadTime = round(microtime(true) - $startTime, 4);
            
            // Ensure user_ip is not empty (required field)
            $userIp = $userData['user_ip'] ?: $request->ip() ?: '0.0.0.0';
            
            // Create tracking record
            $tracking = QRAnalytics::create([
                'tour_code' => $tour_code,
                'booking_id' => $booking?->id,
                'page_url' => $request->fullUrl(),
                'page_type' => $page_type,
                'user_ip' => $userIp,
                'user_agent' => $userData['user_agent'],
                'browser_name' => $userData['browser_name'],
                'browser_version' => $userData['browser_version'],
                'os_name' => $userData['os_name'],
                'os_version' => $userData['os_version'],
                'device_type' => $userData['device_type'],
                'screen_resolution' => $userData['screen_resolution'] ?: null,
                'language' => $userData['language'],
                'country' => $geoData['country'] ?? null,
                'city' => $geoData['city'] ?? null,
                'region' => $geoData['region'] ?? null,
                'full_address' => $geoData['full_address'] ?? null,
                'pincode' => $geoData['pincode'] ?? null,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'timezone' => $geoData['timezone'] ?? null,
                'location_source' => $hasGPS ? 'GPS' : ($apiService ? 'IP-' . strtoupper($apiService) : 'UNAVAILABLE'),
                'referrer' => $userData['referrer'],
                'utm_source' => $userData['utm_source'],
                'utm_medium' => $userData['utm_medium'],
                'utm_campaign' => $userData['utm_campaign'],
                'utm_term' => $userData['utm_term'],
                'utm_content' => $userData['utm_content'],
                'session_id' => $userData['session_id'],
                'user_id' => auth()->id(),
                'scan_date' => now(),
                'tracking_status' => $booking ? 'success' : ($tour_code ? 'invalid_tour_code' : 'success'),
                'error_message' => $tour_code && !$booking ? 'Tour code not found' : null,
                'load_time' => $loadTime,
                'metadata' => $this->collectMetadata($request, $apiResponse, $apiService),
            ]);
            
            return $tracking;
            
        } catch (\Exception $e) {
            // Log error with full details
            Log::error('QR Tracking Error: ' . $e->getMessage(), [
                'tour_code' => $tour_code,
                'page_type' => $page_type,
                'exception' => $e,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return null;
        } catch (\Throwable $e) {
            // Catch any other throwable errors
            Log::error('QR Tracking Throwable Error: ' . $e->getMessage(), [
                'tour_code' => $tour_code,
                'page_type' => $page_type,
                'exception' => $e,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return null;
        }
    }
    
    /**
     * Collect user data from request
     */
    private function collectUserData(Request $request, $gpsLat = null, $gpsLng = null): array
    {
        $userAgent = $request->userAgent() ?? '';
        $browserInfo = $this->parseUserAgent($userAgent);
        
        return [
            'user_ip' => $this->getUserIP($request),
            'user_agent' => $userAgent,
            'browser_name' => $browserInfo['browser_name'],
            'browser_version' => $browserInfo['browser_version'],
            'os_name' => $browserInfo['os_name'],
            'os_version' => $browserInfo['os_version'],
            'device_type' => $browserInfo['device_type'],
            'screen_resolution' => $request->input('screen_resolution') ?: session('qr_screen_resolution') ?: null,
            'gps_latitude' => $gpsLat,
            'gps_longitude' => $gpsLng,
            'language' => $request->getPreferredLanguage() ?? substr($request->header('Accept-Language', ''), 0, 10),
            'referrer' => $request->header('Referer'),
            'utm_source' => $request->input('utm_source'),
            'utm_medium' => $request->input('utm_medium'),
            'utm_campaign' => $request->input('utm_campaign'),
            'utm_term' => $request->input('utm_term'),
            'utm_content' => $request->input('utm_content'),
            'session_id' => $request->session()->getId(),
        ];
    }
    
    /**
     * Get user IP address
     */
    private function getUserIP(Request $request): string
    {
        $ipKeys = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        
        foreach ($ipKeys as $key) {
            if ($request->server($key)) {
                $ips = explode(',', $request->server($key));
                foreach ($ips as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                        return $ip;
                    }
                }
            }
        }
        
        return $request->ip() ?? '0.0.0.0';
    }
    
    /**
     * Parse user agent string
     */
    private function parseUserAgent(string $userAgent): array
    {
        $browserName = 'Unknown';
        $browserVersion = '';
        $osName = 'Unknown';
        $osVersion = '';
        $deviceType = 'desktop';
        
        // Detect browser
        if (preg_match('/Chrome/i', $userAgent) && !preg_match('/Edg|OPR/i', $userAgent)) {
            $browserName = 'Chrome';
            if (preg_match('/Chrome\/([0-9.]+)/', $userAgent, $matches)) {
                $browserVersion = $matches[1];
            }
        } elseif (preg_match('/Firefox/i', $userAgent)) {
            $browserName = 'Firefox';
            if (preg_match('/Firefox\/([0-9.]+)/', $userAgent, $matches)) {
                $browserVersion = $matches[1];
            }
        } elseif (preg_match('/Safari/i', $userAgent) && !preg_match('/Chrome/i', $userAgent)) {
            $browserName = 'Safari';
            if (preg_match('/Version\/([0-9.]+)/', $userAgent, $matches)) {
                $browserVersion = $matches[1];
            }
        } elseif (preg_match('/Edg/i', $userAgent)) {
            $browserName = 'Edge';
            if (preg_match('/Edg\/([0-9.]+)/', $userAgent, $matches)) {
                $browserVersion = $matches[1];
            }
        } elseif (preg_match('/MSIE|Trident/i', $userAgent)) {
            $browserName = 'Internet Explorer';
            if (preg_match('/MSIE ([0-9.]+)/', $userAgent, $matches)) {
                $browserVersion = $matches[1];
            }
        }
        
        // Detect operating system
        if (preg_match('/Windows NT ([0-9.]+)/', $userAgent, $matches)) {
            $osName = 'Windows';
            $osVersion = $matches[1];
        } elseif (preg_match('/Mac OS X ([0-9._]+)/', $userAgent, $matches)) {
            $osName = 'macOS';
            $osVersion = str_replace('_', '.', $matches[1]);
        } elseif (preg_match('/Linux/i', $userAgent)) {
            $osName = 'Linux';
        } elseif (preg_match('/Android ([0-9.]+)/', $userAgent, $matches)) {
            $osName = 'Android';
            $osVersion = $matches[1];
        } elseif (preg_match('/iPhone|iPad|iPod/i', $userAgent)) {
            $osName = 'iOS';
            if (preg_match('/OS ([0-9._]+)/', $userAgent, $matches)) {
                $osVersion = str_replace('_', '.', $matches[1]);
            }
        }
        
        // Detect device type
        if (preg_match('/Mobile|Android|iPhone/i', $userAgent) && !preg_match('/iPad/i', $userAgent)) {
            $deviceType = 'mobile';
        } elseif (preg_match('/Tablet|iPad/i', $userAgent)) {
            $deviceType = 'tablet';
        }
        
        return [
            'browser_name' => $browserName,
            'browser_version' => $browserVersion,
            'os_name' => $osName,
            'os_version' => $osVersion,
            'device_type' => $deviceType,
        ];
    }
    
    /**
     * Get geolocation data from IP using multiple services for better accuracy
     * GPS coordinates from browser are preferred if available
     */
    private function getGeolocationData(string $ip, Request $request): array
    {
        $data = [];
        $apiResponse = null;
        $apiService = null;
        
        // Skip for localhost and private IPs
        if (in_array($ip, ['127.0.0.1', '::1', '0.0.0.0']) || 
            filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return ['data' => $data, 'api_response' => null, 'service' => null];
        }
        
        // Try multiple services in order of preference for better accuracy
        $services = [
            'ipapi' => function($ip) {
                // ipapi.co - Generally more accurate (2s timeout for fast redirect)
                $url = "https://ipapi.co/{$ip}/json/";
                $context = stream_context_create([
                    'http' => [
                        'timeout' => 2,
                        'user_agent' => 'QR-Tracker/1.0',
                        'method' => 'GET'
                    ],
                    'https' => [
                        'timeout' => 2,
                        'user_agent' => 'QR-Tracker/1.0',
                        'method' => 'GET'
                    ]
                ]);
                
                $response = @file_get_contents($url, false, $context);
                if ($response !== false) {
                    $geoData = json_decode($response, true);
                    if ($geoData && !isset($geoData['error'])) {
                        return [
                            'data' => [
                                'country' => $geoData['country_name'] ?? $geoData['country'] ?? '',
                                'city' => $geoData['city'] ?? '',
                                'region' => $geoData['region'] ?? $geoData['region_name'] ?? '',
                                'latitude' => isset($geoData['latitude']) ? (float)$geoData['latitude'] : null,
                                'longitude' => isset($geoData['longitude']) ? (float)$geoData['longitude'] : null,
                                'timezone' => $geoData['timezone'] ?? '',
                                'accuracy' => 'ipapi'
                            ],
                            'raw_response' => $geoData
                        ];
                    }
                }
                return null;
            },
            'ipinfo' => function($ip) {
                // ipinfo.io - Good accuracy (2s timeout for fast redirect)
                $url = "https://ipinfo.io/{$ip}/json";
                $context = stream_context_create([
                    'http' => [
                        'timeout' => 2,
                        'user_agent' => 'QR-Tracker/1.0',
                        'method' => 'GET'
                    ],
                    'https' => [
                        'timeout' => 2,
                        'user_agent' => 'QR-Tracker/1.0',
                        'method' => 'GET'
                    ]
                ]);
                
                $response = @file_get_contents($url, false, $context);
                if ($response !== false) {
                    $geoData = json_decode($response, true);
                    if ($geoData && !isset($geoData['error'])) {
                        $lat = null;
                        $lng = null;
                        if (isset($geoData['loc'])) {
                            $locParts = explode(',', $geoData['loc']);
                            if (count($locParts) === 2) {
                                $lat = (float)trim($locParts[0]);
                                $lng = (float)trim($locParts[1]);
                            }
                        }
                        
                        return [
                            'data' => [
                                'country' => $geoData['country'] ?? '',
                                'city' => $geoData['city'] ?? '',
                                'region' => $geoData['region'] ?? '',
                                'latitude' => $lat,
                                'longitude' => $lng,
                                'timezone' => $geoData['timezone'] ?? '',
                                'accuracy' => 'ipinfo'
                            ],
                            'raw_response' => $geoData
                        ];
                    }
                }
                return null;
            },
            'ipapi_com' => function($ip) {
                // ip-api.com - Fallback service (2s timeout for fast redirect)
                $url = "http://ip-api.com/json/{$ip}?fields=status,message,country,countryCode,region,regionName,city,lat,lon,timezone";
                $context = stream_context_create([
                    'http' => [
                        'timeout' => 2,
                        'user_agent' => 'QR-Tracker/1.0',
                        'method' => 'GET'
                    ]
                ]);
                
                $response = @file_get_contents($url, false, $context);
                if ($response !== false) {
                    $geoData = json_decode($response, true);
                    if ($geoData && isset($geoData['status']) && $geoData['status'] === 'success') {
                        return [
                            'data' => [
                                'country' => $geoData['country'] ?? '',
                                'city' => $geoData['city'] ?? '',
                                'region' => $geoData['regionName'] ?? '',
                                'latitude' => isset($geoData['lat']) ? (float)$geoData['lat'] : null,
                                'longitude' => isset($geoData['lon']) ? (float)$geoData['lon'] : null,
                                'timezone' => $geoData['timezone'] ?? '',
                                'accuracy' => 'ip-api'
                            ],
                            'raw_response' => $geoData
                        ];
                    }
                }
                return null;
            }
        ];
        
        // Try each service until we get valid data
        foreach ($services as $serviceName => $serviceFunction) {
            try {
                $result = $serviceFunction($ip);
                if ($result && isset($result['data']['latitude']) && isset($result['data']['longitude']) && 
                    $result['data']['latitude'] !== null && $result['data']['longitude'] !== null) {
                    $data = $result['data'];
                    $apiResponse = $result['raw_response'];
                    $apiService = $serviceName;
                    Log::info("QR Geolocation: Used {$serviceName} for IP {$ip}", [
                        'latitude' => $result['data']['latitude'],
                        'longitude' => $result['data']['longitude']
                    ]);
                    break; // Use first successful result
                }
            } catch (\Exception $e) {
                Log::debug("QR Geolocation service {$serviceName} failed: " . $e->getMessage());
                continue; // Try next service
            }
        }
        
        // If no service returned data, log warning
        if (empty($data)) {
            Log::warning("QR Geolocation: All services failed for IP {$ip}");
        }
        
        return ['data' => $data, 'api_response' => $apiResponse, 'service' => $apiService];
    }
    
    /**
     * Reverse geocode GPS coordinates to get address details
     * Uses OpenStreetMap Nominatim API (free, no API key required)
     */
    private function reverseGeocode(float $latitude, float $longitude): array
    {
        $data = [];
        
        try {
            // Use OpenStreetMap Nominatim API for reverse geocoding
            $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat={$latitude}&lon={$longitude}&zoom=18&addressdetails=1";
            
            $context = stream_context_create([
                'http' => [
                    'timeout' => 5,
                    'user_agent' => 'QR-Tracker/1.0',
                    'method' => 'GET'
                ],
                'https' => [
                    'timeout' => 5,
                    'user_agent' => 'QR-Tracker/1.0',
                    'method' => 'GET'
                ]
            ]);
            
            $response = @file_get_contents($url, false, $context);
            
            if ($response !== false) {
                $geoData = json_decode($response, true);
                
                if ($geoData && isset($geoData['address'])) {
                    $address = $geoData['address'];
                    
                    // Extract address components
                    $data['full_address'] = $geoData['display_name'] ?? '';
                    $data['country'] = $address['country'] ?? '';
                    $data['region'] = $address['state'] ?? $address['region'] ?? '';
                    $data['city'] = $address['city'] ?? $address['town'] ?? $address['village'] ?? $address['county'] ?? '';
                    $data['pincode'] = $address['postcode'] ?? null;
                    
                    Log::info("QR Reverse Geocoding: Success for coordinates {$latitude}, {$longitude}", $data);
                }
            }
        } catch (\Exception $e) {
            Log::warning("QR Reverse Geocoding failed: " . $e->getMessage());
        }
        
        return $data;
    }
    
    /**
     * Collect additional metadata
     */
    private function collectMetadata(Request $request, $apiResponse = null, $apiService = null): array
    {
        $metadata = [
            'http_method' => $request->method(),
            'request_uri' => $request->getRequestUri(),
            'query_string' => $request->getQueryString(),
            'accept_language' => $request->header('Accept-Language'),
            'accept_encoding' => $request->header('Accept-Encoding'),
            'accept' => $request->header('Accept'),
        ];
        
        // Add location action if provided (allow, block, close)
        if ($request->has('location_action')) {
            $metadata['location_action'] = $request->input('location_action');
        }
        
        // Add full API response if available
        if ($apiResponse !== null) {
            $metadata['ip_geolocation_api_response'] = $apiResponse;
            $metadata['ip_geolocation_api_service'] = $apiService;
        }
        
        return $metadata;
    }
}

