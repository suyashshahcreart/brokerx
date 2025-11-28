<?php

namespace App\Services;

use App\Models\SmsLog;
use App\Services\Sms\SmsGatewayManager;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

/**
 * Main SMS Service - Use this service to send SMS
 * It automatically uses the active SMS gateway configured in admin panel
 */
class SmsService
{
    protected SmsGatewayManager $gatewayManager;

    public function __construct(SmsGatewayManager $gatewayManager)
    {
        $this->gatewayManager = $gatewayManager;
    }

    /**
     * Send SMS using the active gateway
     *
     * @param string $mobile Mobile number (with country code, e.g., '919876543210')
     * @param string $templateKey Template key (e.g., 'login_otp', 'registration_otp')
     * @param array $params Template parameters (e.g., ['OTP' => '123456', 'NAME' => 'John'])
     * @param array $options Additional options:
     *   - 'type' => 'manual'|'cron'|'scheduled' (default: 'manual')
     *   - 'reference_type' => Model class name (e.g., 'App\Models\Booking')
     *   - 'reference_id' => Related model ID
     *   - 'notes' => Additional notes
     * @return array Response from the gateway
     * @throws RuntimeException
     */
    public function send(string $mobile, string $templateKey, array $params = [], array $options = []): array
    {
        // Check if any SMS gateway is active and enabled
        if (!$this->gatewayManager->hasActiveEnabledGateway()) {
            throw new RuntimeException('SMS gateway is not enabled. Please enable an SMS gateway in the admin panel settings.');
        }
        
        $gateway = $this->gatewayManager->getActiveGateway();
        $gatewayName = $gateway->getName();
        
        // Create log entry before sending (don't fail SMS if logging fails)
        $smsLog = null;
        try {
            $logData = [
                'gateway' => $gatewayName,
                'type' => $options['type'] ?? (app()->runningInConsole() ? 'cron' : 'manual'),
                'template_key' => $templateKey,
                'mobile' => $mobile,
                'params' => $params,
                'status' => 'pending',
                'success' => false,
                'user_id' => Auth::id(),
                'reference_type' => $options['reference_type'] ?? null,
                'reference_id' => $options['reference_id'] ?? null,
                'notes' => $options['notes'] ?? null,
            ];
            
            $smsLog = SmsLog::create($logData);
        } catch (\Exception $e) {
            // Log the logging error but don't fail SMS sending
            \Log::warning('Failed to create SMS log entry: ' . $e->getMessage());
        }
        
        try {
            // Send SMS
            $response = $gateway->send($mobile, $templateKey, $params);
            
            // Update log with response (if log was created)
            if ($smsLog) {
                try {
                    $updateData = [
                        'status' => $response['success'] ? 'sent' : 'failed',
                        'status_code' => $response['status_code'] ?? null,
                        'success' => $response['success'] ?? false,
                        'response_body' => $response['body'] ?? null,
                        'response_json' => $response['json'] ?? null,
                        'sent_at' => now(),
                    ];
                    
                    // Extract additional info from response
                    if (isset($response['json'])) {
                        $json = $response['json'];
                        if (isset($json['message_id'])) {
                            $updateData['gateway_message_id'] = $json['message_id'];
                        }
                        if (isset($json['cost'])) {
                            $updateData['cost'] = $json['cost'];
                        }
                        if (isset($json['error'])) {
                            $updateData['error_message'] = is_string($json['error']) ? $json['error'] : json_encode($json['error']);
                        }
                    }
                    
                    // Get template ID if available
                    if (method_exists($gateway, 'getTemplateId')) {
                        $updateData['template_id'] = $gateway->getTemplateId($templateKey);
                    }
                    
                    // Get sender ID if available
                    if (method_exists($gateway, 'getSenderId')) {
                        $updateData['sender_id'] = $gateway->getSenderId();
                    }
                    
                    $smsLog->update($updateData);
                } catch (\Exception $e) {
                    // Log the update error but don't fail SMS response
                    \Log::warning('Failed to update SMS log entry: ' . $e->getMessage());
                }
            }
            
            return $response;
            
        } catch (\Exception $e) {
            // Log error (if log was created)
            if ($smsLog) {
                try {
                    $smsLog->update([
                        'status' => 'failed',
                        'success' => false,
                        'error_message' => $e->getMessage(),
                        'response_body' => $e->getMessage(),
                    ]);
                } catch (\Exception $updateError) {
                    \Log::warning('Failed to update SMS log with error: ' . $updateError->getMessage());
                }
            }
            
            throw $e;
        }
    }

    /**
     * Get the active gateway name
     */
    public function getActiveGatewayName(): string
    {
        try {
            $gateway = $this->gatewayManager->getActiveGateway();
            return $gateway->getName();
        } catch (\Exception $e) {
            return 'Not Configured';
        }
    }

    /**
     * Get gateway manager instance
     */
    public function getGatewayManager(): SmsGatewayManager
    {
        return $this->gatewayManager;
    }
}

