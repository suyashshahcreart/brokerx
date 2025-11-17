<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected string $baseUrl;
    protected string $version;
    protected string $phoneNumberId;
    protected string $wabaId;
    protected string $token;

    public function __construct()
    {
        $this->baseUrl       = config('services.whatsapp.base_url');
        $this->version       = config('services.whatsapp.version');
        $this->phoneNumberId = config('services.whatsapp.phone_number_id');
        $this->wabaId        = config('services.whatsapp.waba_id');
        $this->token         = config('services.whatsapp.token');
    }

    protected function messagesEndpoint(string $path = '/messages'): string
    {
        return "{$this->baseUrl}/{$this->version}/{$this->phoneNumberId}{$path}";
    }

    protected function wabaEndpoint(string $path = ''): string
    {
        return "{$this->baseUrl}/{$this->version}/{$this->wabaId}{$path}";
    }

    protected function request()
    {
        return Http::withToken($this->token)
            ->acceptJson()
            ->asJson();
    }

    // ========== BASIC TEXT MESSAGE ==========
    public function sendText(string $to, string $message): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to'                => $to, // e.g. 91XXXXXXXXXX
            'type'              => 'text',
            'text'              => [
                'body' => $message,
            ],
        ];

        $response = $this->request()->post(
            $this->messagesEndpoint(),
            $payload
        );

        if (!$response->successful()) {
            Log::error('WhatsApp sendText failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        }

        return $response->json();
    }

    // ========== TEMPLATE MESSAGE ==========
    public function sendTemplate(string $to, string $templateName, string $languageCode = 'en_US', array $components = []): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to'                => $to,
            'type'              => 'template',
            'template'          => [
                'name'     => $templateName,
                'language' => [
                    'code' => $languageCode,
                ],
            ],
        ];

        if (!empty($components)) {
            $payload['template']['components'] = $components;
        }

        $response = $this->request()->post(
            $this->messagesEndpoint(),
            $payload
        );

        if (!$response->successful()) {
            Log::error('WhatsApp sendTemplate failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        }

        return $response->json();
    }

    // ========== INTERACTIVE MESSAGE (BUTTONS) ==========
    public function sendInteractiveButtons(string $to, string $bodyText, string $footerText, array $buttons): array
    {
        // $buttons = [
        //     ['id' => 'btn_yes', 'title' => 'Yes'],
        //     ['id' => 'btn_no', 'title' => 'No'],
        // ];

        $btns = array_map(function ($btn) {
            return [
                'type'  => 'reply',
                'reply' => [
                    'id'    => $btn['id'],
                    'title' => $btn['title'],
                ],
            ];
        }, $buttons);

        $payload = [
            'messaging_product' => 'whatsapp',
            'to'                => $to,
            'type'              => 'interactive',
            'interactive'       => [
                'type'   => 'button',
                'body'  => [
                    'text' => $bodyText,
                ],
                'footer' => [
                    'text' => $footerText,
                ],
                'action' => [
                    'buttons' => $btns,
                ],
            ],
        ];

        $response = $this->request()->post(
            $this->messagesEndpoint(),
            $payload
        );

        if (!$response->successful()) {
            Log::error('WhatsApp sendInteractiveButtons failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        }

        return $response->json();
    }

    // ========== INTERACTIVE MESSAGE (LIST) ==========
    public function sendInteractiveList(string $to, string $bodyText, string $buttonText, array $sections): array
    {
        // $sections = [
        //     [
        //         'title' => 'Section 1',
        //         'rows' => [
        //             ['id' => 'opt_1', 'title' => 'Option 1', 'description' => 'Desc 1'],
        //             ['id' => 'opt_2', 'title' => 'Option 2', 'description' => 'Desc 2'],
        //         ]
        //     ],
        // ];

        $payload = [
            'messaging_product' => 'whatsapp',
            'to'                => $to,
            'type'              => 'interactive',
            'interactive'       => [
                'type'   => 'list',
                'body'  => [
                    'text' => $bodyText,
                ],
                'action' => [
                    'button'   => $buttonText,
                    'sections' => $sections,
                ],
            ],
        ];

        $response = $this->request()->post(
            $this->messagesEndpoint(),
            $payload
        );

        if (!$response->successful()) {
            Log::error('WhatsApp sendInteractiveList failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        }

        return $response->json();
    }

    // ========== TEMPLATE LIST ==========
    public function listTemplates(int $limit = 50, ?string $after = null): array
    {
        $url = $this->wabaEndpoint('/message_templates');

        $query = [
            'limit' => $limit,
        ];

        if ($after) {
            $query['after'] = $after;
        }

        $response = $this->request()->get($url, $query);

        if (!$response->successful()) {
            Log::error('WhatsApp listTemplates failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        }

        return $response->json();
    }

    // ========== TEMPLATE CREATE ==========
    public function createTemplate(array $templateData): array
    {
        // $templateData must follow WhatsApp structure:
        // [
        //     'name' => 'my_template',
        //     'category' => 'TRANSACTIONAL',
        //     'language' => 'en_US',
        //     'components' => [ ... ]
        // ]

        $url = $this->wabaEndpoint('/message_templates');

        $response = $this->request()->post($url, $templateData);

        if (!$response->successful()) {
            Log::error('WhatsApp createTemplate failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        }

        return $response->json();
    }

    // ========== TEMPLATE DELETE ==========
    public function deleteTemplate(string $templateName, string $language = 'en_US'): array
    {
        $url = $this->wabaEndpoint('/message_templates');

        $response = $this->request()->delete($url, [
            'name'     => $templateName,
            'language' => $language,
        ]);

        if (!$response->successful()) {
            Log::error('WhatsApp deleteTemplate failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        }

        return $response->json();
    }

    // ========== TEMPLATE UPDATE (STATUS OR CONTENT) ==========
    // Note: WhatsApp mainly allows PATCH for some fields; real use will depend on your approved template.
    public function updateTemplate(string $templateName, array $data): array
    {
        $url = $this->wabaEndpoint('/message_templates');

        $payload = array_merge([
            'name' => $templateName,
        ], $data);

        $response = $this->request()->post($url, $payload);

        // some operations are POST /message_templates
        if (!$response->successful()) {
            Log::error('WhatsApp updateTemplate failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        }

        return $response->json();
    }
}

