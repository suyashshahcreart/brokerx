<?php

namespace App\Services\Sms\Gateways;

use App\Services\Sms\Contracts\SmsGatewayInterface;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class Msg91Gateway implements SmsGatewayInterface
{
    protected string $authKey;
    protected string $sender;
    protected int $timeout;
    protected array $templates;

    public function __construct()
    {
        // Get configuration from database settings (fallback to config file)
        $this->authKey = (string) $this->getSetting('msg91_auth_key', config('msg91.auth_key', ''));
        $this->sender = (string) $this->getSetting('msg91_sender_id', config('msg91.sender', 'PROPPK'));
        $this->timeout = (int) $this->getSetting('msg91_timeout', 30);
        
        // Get templates from config file (directly from config/msg91.php)
        $this->templates = config('msg91.templates', []);
    }

    /**
     * Send SMS using MSG91 Flow API
     */
    public function send(string $mobile, string $templateKey, array $params = []): array
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('MSG91 is not properly configured. Please check your SMS configuration settings.');
        }

        $templateId = $this->templates[$templateKey] ?? null;
        
        if (!$templateId) {
            throw new RuntimeException("MSG91 Template '$templateKey' not found. Available templates: " . implode(', ', array_keys($this->templates)));
        }

        $payload = array_merge([
            'template_id' => $templateId,
            'sender'      => $this->sender,
            'mobiles'     => $mobile,
            'short_url'   => '0'
        ], $params);

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'authkey' => $this->authKey,
                    'Content-Type' => 'application/json'
                ])
                ->post('https://api.msg91.com/api/v5/flow/', $payload);

            return [
                'success' => $response->successful(),
                'status_code' => $response->status(),
                'body' => $response->body(),
                'json' => $response->json(),
                'gateway' => $this->getName(),
            ];
        } catch (\Exception $e) {
            throw new RuntimeException("MSG91 API Error: " . $e->getMessage());
        }
    }

    /**
     * Get the gateway name
     */
    public function getName(): string
    {
        return 'MSG91';
    }

    /**
     * Check if the gateway is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->authKey) && !empty($this->sender);
    }

    /**
     * Get configuration fields required for MSG91
     */
    public function getConfigFields(): array
    {
        return [
            [
                'key' => 'msg91_auth_key',
                'label' => 'MSG91 Auth Key',
                'type' => 'text',
                'required' => true,
                'placeholder' => 'Enter your MSG91 Auth Key',
                'help' => 'Your MSG91 authentication key from the dashboard',
            ],
            [
                'key' => 'msg91_sender_id',
                'label' => 'Sender ID',
                'type' => 'text',
                'required' => true,
                'placeholder' => 'PROPPK',
                'help' => 'Your approved sender ID (e.g., PROPPK)',
            ],
            [
                'key' => 'msg91_timeout',
                'label' => 'Request Timeout (seconds)',
                'type' => 'number',
                'required' => false,
                'placeholder' => '30',
                'help' => 'API request timeout in seconds (default: 30)',
                'default' => 30,
            ],
        ];
    }

    /**
     * Get available templates
     */
    public function getAvailableTemplates(): array
    {
        return array_keys($this->templates);
    }

    /**
     * Get template ID by key (for logging)
     */
    public function getTemplateId(string $templateKey): ?string
    {
        return $this->templates[$templateKey] ?? null;
    }

    /**
     * Get sender ID (for logging)
     */
    public function getSenderId(): string
    {
        return $this->sender;
    }

    /**
     * Get setting from database or return default
     */
    protected function getSetting(string $key, $default = null)
    {
        try {
            $setting = \App\Models\Setting::where('name', $key)->first();
            return $setting ? $setting->value : $default;
        } catch (\Exception $e) {
            return $default;
        }
    }
}

