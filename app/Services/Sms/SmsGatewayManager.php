<?php

namespace App\Services\Sms;

use App\Services\Sms\Contracts\SmsGatewayInterface;
use App\Services\Sms\Gateways\Msg91Gateway;
use RuntimeException;

class SmsGatewayManager
{
    protected array $gateways = [];
    protected ?string $activeGateway = null;

    public function __construct()
    {
        // Register available gateways
        $this->registerGateway('msg91', Msg91Gateway::class);
        
        // Get active gateway from settings
        $this->activeGateway = $this->getActiveGatewayFromSettings();
    }

    /**
     * Register a new SMS gateway
     */
    public function registerGateway(string $key, string $gatewayClass): void
    {
        if (!is_subclass_of($gatewayClass, SmsGatewayInterface::class)) {
            throw new RuntimeException("Gateway class must implement SmsGatewayInterface");
        }

        $this->gateways[$key] = $gatewayClass;
    }

    /**
     * Get the active gateway instance
     */
    public function getActiveGateway(): SmsGatewayInterface
    {
        if (!$this->activeGateway) {
            throw new RuntimeException('No active SMS gateway configured. Please configure an SMS gateway in the admin panel.');
        }

        if (!isset($this->gateways[$this->activeGateway])) {
            throw new RuntimeException("SMS Gateway '{$this->activeGateway}' is not registered.");
        }

        $gatewayClass = $this->gateways[$this->activeGateway];
        $gateway = new $gatewayClass();

        if (!$gateway->isConfigured()) {
            throw new RuntimeException("SMS Gateway '{$this->activeGateway}' is not properly configured. Please check your SMS configuration settings.");
        }

        return $gateway;
    }

    /**
     * Set the active gateway
     */
    public function setActiveGateway(string $gatewayKey): void
    {
        if (!isset($this->gateways[$gatewayKey])) {
            throw new RuntimeException("SMS Gateway '{$gatewayKey}' is not registered.");
        }

        $this->activeGateway = $gatewayKey;
    }

    /**
     * Get all registered gateways
     */
    public function getRegisteredGateways(): array
    {
        return $this->gateways;
    }

    /**
     * Get gateway instance by key
     */
    public function getGateway(string $key): ?SmsGatewayInterface
    {
        if (!isset($this->gateways[$key])) {
            return null;
        }

        $gatewayClass = $this->gateways[$key];
        return new $gatewayClass();
    }

    /**
     * Get active gateway name from settings
     */
    public function getActiveGatewayFromSettings(): ?string
    {
        try {
            $setting = \App\Models\Setting::where('name', 'active_sms_gateway')->first();
            return $setting ? $setting->value : 'msg91'; // Default to msg91
        } catch (\Exception $e) {
            return 'msg91'; // Default fallback
        }
    }

    /**
     * Check if a gateway is active
     */
    public function isGatewayActive(string $gatewayKey): bool
    {
        return $this->activeGateway === $gatewayKey;
    }

    /**
     * Get status of a gateway (enabled/disabled)
     */
    public function getGatewayStatus(string $gatewayKey): bool
    {
        try {
            $setting = \App\Models\Setting::where('name', "sms_gateway_{$gatewayKey}_status")->first();
            return $setting ? (bool) $setting->value : false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if the active gateway is enabled
     */
    public function isActiveGatewayEnabled(): bool
    {
        if (!$this->activeGateway) {
            return false;
        }
        
        return $this->getGatewayStatus($this->activeGateway);
    }

    /**
     * Check if any SMS gateway is configured and enabled
     */
    public function hasActiveEnabledGateway(): bool
    {
        if (!$this->activeGateway) {
            return false;
        }
        
        // Check if gateway is enabled
        if (!$this->getGatewayStatus($this->activeGateway)) {
            return false;
        }
        
        // Check if gateway is properly configured
        try {
            $gateway = $this->getGateway($this->activeGateway);
            return $gateway && $gateway->isConfigured();
        } catch (\Exception $e) {
            return false;
        }
    }
}

