<?php

namespace App\Services\Sms\Contracts;

interface SmsGatewayInterface
{
    /**
     * Send SMS using the gateway
     *
     * @param string $mobile Mobile number (with country code)
     * @param string $templateKey Template key identifier
     * @param array $params Template parameters
     * @return array Response from the gateway
     */
    public function send(string $mobile, string $templateKey, array $params = []): array;

    /**
     * Get the gateway name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Check if the gateway is configured and ready to use
     *
     * @return bool
     */
    public function isConfigured(): bool;

    /**
     * Get configuration fields required for this gateway
     *
     * @return array Array of field definitions
     */
    public function getConfigFields(): array;
}

