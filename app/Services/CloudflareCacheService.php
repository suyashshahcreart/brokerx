<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CloudflareCacheService
{
    protected string $baseUrl = 'https://api.cloudflare.com/client/v4';
    
    /**
     * Get Cloudflare Zone ID from settings
     */
    protected function getZoneId(): ?string
    {
        return getCloudflareZoneId();
    }
    
    /**
     * Get Cloudflare API Token from settings
     */
    protected function getApiToken(): ?string
    {
        return getCloudflareApiToken();
    }
    
    /**
     * Purge cache by prefixes
     * 
     * @param array $prefixes Array of URL prefixes to purge
     * @return array ['success' => bool, 'message' => string, 'data' => array|null]
     */
    public function purgeByPrefixes(array $prefixes): array
    {
        $zoneId = $this->getZoneId();
        $apiToken = $this->getApiToken();
        
        if (!$zoneId || !$apiToken) {
            return [
                'success' => false,
                'message' => 'Cloudflare Zone ID or API Token is not configured. Please configure them in Settings.',
                'data' => null
            ];
        }
        
        if (empty($prefixes)) {
            return [
                'success' => false,
                'message' => 'No prefixes provided for purge.',
                'data' => null
            ];
        }
        
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiToken,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/zones/{$zoneId}/purge_cache", [
                'prefixes' => $prefixes
            ]);
            
            $responseData = $response->json();
            
            // Check if Cloudflare API returned success: false in response body
            if ($response->successful() && isset($responseData['success']) && $responseData['success'] === true) {
                Log::info('Cloudflare cache purged successfully', [
                    'zone_id' => $zoneId,
                    'prefixes' => $prefixes,
                    'response' => $responseData
                ]);
                
                return [
                    'success' => true,
                    'message' => 'Cache purged successfully.',
                    'data' => $responseData
                ];
            } else {
                // Handle both HTTP errors and Cloudflare API errors (success: false)
                $errorData = $responseData ?? $response->json();
                $errorMessage = 'Unknown error occurred';
                
                if (isset($errorData['errors']) && is_array($errorData['errors']) && !empty($errorData['errors'])) {
                    $errorMessages = array_map(function($error) {
                        return $error['message'] ?? 'Unknown error';
                    }, $errorData['errors']);
                    $errorMessage = implode('; ', $errorMessages);
                } elseif (isset($errorData['message'])) {
                    $errorMessage = $errorData['message'];
                }
                
                Log::error('Cloudflare cache purge failed', [
                    'zone_id' => $zoneId,
                    'prefixes' => $prefixes,
                    'http_status' => $response->status(),
                    'cloudflare_success' => $responseData['success'] ?? null,
                    'error' => $errorData
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Failed to purge cache: ' . $errorMessage,
                    'data' => $errorData
                ];
            }
        } catch (\Exception $e) {
            Log::error('Cloudflare cache purge exception', [
                'zone_id' => $zoneId,
                'prefixes' => $prefixes,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'Error purging cache: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }
    
    /**
     * Purge everything from cache
     * 
     * @return array ['success' => bool, 'message' => string, 'data' => array|null]
     */
    public function purgeEverything(): array
    {
        $zoneId = $this->getZoneId();
        $apiToken = $this->getApiToken();
        
        if (!$zoneId || !$apiToken) {
            return [
                'success' => false,
                'message' => 'Cloudflare Zone ID or API Token is not configured. Please configure them in Settings.',
                'data' => null
            ];
        }
        
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiToken,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/zones/{$zoneId}/purge_cache", [
                'purge_everything' => true
            ]);
            
            $responseData = $response->json();
            
            // Check if Cloudflare API returned success: false in response body
            if ($response->successful() && isset($responseData['success']) && $responseData['success'] === true) {
                Log::info('Cloudflare cache purged everything successfully', [
                    'zone_id' => $zoneId,
                    'response' => $responseData
                ]);
                
                return [
                    'success' => true,
                    'message' => 'All cache purged successfully.',
                    'data' => $responseData
                ];
            } else {
                // Handle both HTTP errors and Cloudflare API errors (success: false)
                $errorData = $responseData ?? $response->json();
                $errorMessage = 'Unknown error occurred';
                
                if (isset($errorData['errors']) && is_array($errorData['errors']) && !empty($errorData['errors'])) {
                    $errorMessages = array_map(function($error) {
                        return $error['message'] ?? 'Unknown error';
                    }, $errorData['errors']);
                    $errorMessage = implode('; ', $errorMessages);
                } elseif (isset($errorData['message'])) {
                    $errorMessage = $errorData['message'];
                }
                
                Log::error('Cloudflare cache purge everything failed', [
                    'zone_id' => $zoneId,
                    'http_status' => $response->status(),
                    'cloudflare_success' => $responseData['success'] ?? null,
                    'error' => $errorData
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Failed to purge cache: ' . $errorMessage,
                    'data' => $errorData
                ];
            }
        } catch (\Exception $e) {
            Log::error('Cloudflare cache purge everything exception', [
                'zone_id' => $zoneId,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'Error purging cache: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }
    
    /**
     * Build prefix URL from s3_link_base and tour_code
     * Cloudflare API requires prefixes without URI scheme (no https://)
     * 
     * @param string $tourCode Tour code from booking
     * @return string
     */
    public function buildTourPrefix(string $tourCode): string
    {
        $s3LinkBase = getS3LinkBase();
        
        // Remove URI scheme (https:// or http://) if present
        $s3LinkBase = preg_replace('#^https?://#', '', $s3LinkBase);
        
        // Remove trailing slash if present
        $s3LinkBase = rtrim($s3LinkBase, '/');
        
        return $s3LinkBase . '/tours/' . $tourCode;
    }
    
    /**
     * Build default prefixes for purge everything (tours and settings)
     * Cloudflare API requires prefixes without URI scheme (no https://)
     * 
     * @return array
     */
    public function buildDefaultPrefixes(): array
    {
        $s3LinkBase = getS3LinkBase();
        
        // Remove URI scheme (https:// or http://) if present
        $s3LinkBase = preg_replace('#^https?://#', '', $s3LinkBase);
        
        // Remove trailing slash if present
        $s3LinkBase = rtrim($s3LinkBase, '/');
        
        return [
            $s3LinkBase . '/tours/',
            $s3LinkBase . '/settings/'
        ];
    }
}
