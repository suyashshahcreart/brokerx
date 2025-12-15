<?php

namespace App\Services;

use App\Services\SmsService;
use RuntimeException;

/**
 * Msg91Service - Legacy service for backward compatibility
 * 
 * @deprecated Use SmsService instead for dynamic gateway support
 * This service now uses the dynamic SMS gateway system internally
 */
class Msg91Service
{
    protected SmsService $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Send SMS using the active SMS gateway
     * 
     * Note: This now uses the dynamic SMS gateway system.
     * The active gateway is determined by the admin panel configuration.
     *
     * @param string $templateKey Template key from config (e.g., 'login_otp', 'registration_otp')
     * @param string $mobile Mobile number (with country code, e.g., '919876543210')
     * @param array $params Template parameters (e.g., ['OTP' => '123456', 'NAME' => 'John'])
     * @param array $options Additional options for logging (type, reference_type, reference_id, notes)
     * @return array Response from SMS gateway API
     * @throws RuntimeException
     * 
     * @deprecated Use SmsService::send() instead
     */
    public function send(string $templateKey, string $mobile, array $params = [], array $options = []): array
    {
        // Use the new dynamic SMS service (note: parameter order is different)
        return $this->smsService->send($mobile, $templateKey, $params, $options);
    }

    /**
     * Get available template keys
     *
     * @return array
     */
    public function getAvailableTemplates(): array
    {
        return array_keys(config('msg91.templates', []));
    }
}

