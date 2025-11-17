<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class WhatsAppController extends Controller
{
    protected WhatsAppService $whatsapp;

    public function __construct(WhatsAppService $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    public function index()
    {
        return view('whatsapp.demo');
    }

    public function sendText(Request $request)
    {
        $request->validate([
            'phone'   => 'required',
            'message' => 'required',
        ]);

        $res = $this->whatsapp->sendText(
            $request->phone,
            $request->message
        );

        return back()->with('response', $res);
    }

    public function sendTemplate(Request $request)
    {
        $request->validate([
            'phone'        => 'required',
            'template_name' => 'required',
        ]);

        // Example body variables
        $components = [];
        if ($request->filled('body_var_1') || $request->filled('body_var_2')) {
            $params = [];
            if ($request->filled('body_var_1')) {
                $params[] = [
                    'type' => 'text',
                    'text' => $request->body_var_1,
                ];
            }
            if ($request->filled('body_var_2')) {
                $params[] = [
                    'type' => 'text',
                    'text' => $request->body_var_2,
                ];
            }
            $components[] = [
                'type'       => 'body',
                'parameters' => $params,
            ];
        }

        $res = $this->whatsapp->sendTemplate(
            $request->phone,
            $request->template_name,
            $request->language ?? 'en_US',
            $components
        );

        return back()->with('response', $res);
    }

    public function sendButtons(Request $request)
    {
        $request->validate([
            'phone' => 'required',
        ]);

        $buttons = [
            ['id' => 'btn_yes', 'title' => 'Yes'],
            ['id' => 'btn_no', 'title' => 'No'],
        ];

        $res = $this->whatsapp->sendInteractiveButtons(
            $request->phone,
            'Do you confirm your booking?',
            'Tap a button below',
            $buttons
        );

        return back()->with('response', $res);
    }

    public function sendList(Request $request)
    {
        $request->validate([
            'phone' => 'required',
        ]);

        $sections = [
            [
                'title' => 'Choose a service',
                'rows'  => [
                    ['id' => 'svc_1', 'title' => 'Basic Plan', 'description' => 'Basic package'],
                    ['id' => 'svc_2', 'title' => 'Premium Plan', 'description' => 'Premium package'],
                ],
            ],
        ];

        $res = $this->whatsapp->sendInteractiveList(
            $request->phone,
            'Please choose one of our plans:',
            'View Plans',
            $sections
        );

        return back()->with('response', $res);
    }

    public function listTemplates()
    {
        $res = $this->whatsapp->listTemplates(50);

        return back()->with('templates', $res)->with('response', $res);
    }

    public function createTemplate(Request $request)
    {
        $request->validate([
            'name'      => 'required',
            'language'  => 'required',
            'category'  => 'required',
            'body_text' => 'required',
        ]);

        $templateData = [
            'name'       => $request->name,
            'language'  => $request->language,
            'category'   => $request->category, // e.g. "TRANSACTIONAL"
            'components' => [
                [
                    'type' => 'BODY',
                    'text' => $request->body_text,
                ],
            ],
        ];

        $res = $this->whatsapp->createTemplate($templateData);

        return back()->with('response', $res);
    }

    public function deleteTemplate(Request $request)
    {
        $request->validate([
            'name'     => 'required',
            'language' => 'required',
        ]);

        $res = $this->whatsapp->deleteTemplate(
            $request->name,
            $request->language
        );

        return back()->with('response', $res);
    }
}

