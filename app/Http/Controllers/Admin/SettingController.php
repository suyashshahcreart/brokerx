<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\FtpConfiguration;
use App\Services\Sms\SmsGatewayManager;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class SettingController extends Controller
{
    protected SmsGatewayManager $gatewayManager;

    public function __construct(SmsGatewayManager $gatewayManager)
    {
        // Allow access to index if user has setting_view OR any specific settings tab permission
        $this->middleware(function ($request, $next) {
            $user = $request->user();
            if (!$user->can('setting_view') && 
                !$user->can('setting_booking_schedule') && 
                !$user->can('setting_photographer') && 
                !$user->can('setting_base_price') && 
                !$user->can('setting_payment_gateway') && 
                !$user->can('setting_sms_configuration') && 
                !$user->can('setting_ftp_configuration')) {
                abort(403, 'Unauthorized access to settings.');
            }
            return $next($request);
        })->only(['index', 'show']);
        
        $this->middleware('permission:setting_create')->only(['create', 'store']);
        $this->middleware('permission:setting_edit')->only(['edit', 'update']);
        $this->middleware('permission:setting_delete')->only(['destroy']);
        $this->gatewayManager = $gatewayManager;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {   $canCreate = $request->user()->can('setting_create');
        $canEdit = $request->user()->can('setting_edit');
        $canDelete = $request->user()->can('setting_delete');

        $settings = Setting::pluck('value', 'name')->toArray();
        
        // Create default values for base price settings if they don't exist
        $defaults = [
            'base_price' => '599',
            'base_area' => '1500',
            'extra_area' => '500',
            'extra_area_price' => '200',
            // Payment Gateway defaults
            'active_payment_gateway' => '', // Comma-separated list of active gateways
            'cashfree_status' => '0',
            // Note: Cashfree credentials are NOT stored in database - they come from .env file
            'payu_status' => '0',
            'payu_merchant_key' => '',
            'payu_merchant_salt' => '',
            'payu_mode' => 'test',
            'razorpay_status' => '0',
            'razorpay_key' => '',
            'razorpay_secret' => '',
            'razorpay_mode' => 'test',
        ];
        
        foreach ($defaults as $key => $defaultValue) {
            if (!isset($settings[$key])) {
                // Create the setting with default value
                Setting::create([
                    'name' => $key,
                    'value' => $defaultValue,
                    'created_by' => $request->user()->id,
                    'updated_by' => $request->user()->id,
                ]);
                $settings[$key] = $defaultValue;
            }
        }
        
        // Get SMS gateway data for SMS Configuration tab
        $gateways = $this->gatewayManager->getRegisteredGateways();
        $gatewayInstances = [];
        
        foreach ($gateways as $key => $gatewayClass) {
            $gateway = $this->gatewayManager->getGateway($key);
            if ($gateway) {
                $gatewayInstances[$key] = [
                    'name' => $gateway->getName(),
                    'configFields' => $gateway->getConfigFields(),
                    'isConfigured' => $gateway->isConfigured(),
                    'isActive' => $this->gatewayManager->isGatewayActive($key),
                    'status' => $this->gatewayManager->getGatewayStatus($key),
                ];
            }
        }
        
        $activeSmsGateway = $this->gatewayManager->getActiveGatewayFromSettings();
        
        // Get MSG91 templates from config file (always from config now)
        $msg91Templates = config('msg91.templates', []);
        $templatesSource = 'config';
        
        // Check permissions for each settings tab
        $canBookingSchedule = $request->user()->can('setting_booking_schedule');
        $canPhotographer = $request->user()->can('setting_photographer');
        $canBasePrice = $request->user()->can('setting_base_price');
        $canPaymentGateway = $request->user()->can('setting_payment_gateway');
        $canSmsConfiguration = $request->user()->can('setting_sms_configuration');
        $canFtpConfiguration = $request->user()->can('setting_ftp_configuration');
        
        return view('admin.settings.index', compact(
            'settings', 
            'canCreate', 
            'canEdit', 
            'canDelete', 
            'gatewayInstances', 
            'activeSmsGateway', 
            'msg91Templates', 
            'templatesSource',
            'canBookingSchedule',
            'canPhotographer',
            'canBasePrice',
            'canPaymentGateway',
            'canSmsConfiguration',
            'canFtpConfiguration'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.settings.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'avaliable_days' => ['nullable', 'string'],
            'holidays' => ['nullable', 'string'],
        ]);

        // Update or create avaliable_days setting
        if ($request->has('avaliable_days')) {
            Setting::updateOrCreate(
                ['name' => 'avaliable_days'],
                [
                    'value' => $validated['avaliable_days'],
                    'created_by' => $request->user()->id,
                    'updated_by' => $request->user()->id,
                ]
            );
        }

        // Update or create holidays setting
        if ($request->has('holidays')) {
            Setting::updateOrCreate(
                ['name' => 'holidays'],
                [
                    'value' => $validated['holidays'], // Already JSON string from frontend
                    'created_by' => $request->user()->id,
                    'updated_by' => $request->user()->id,
                ]
            );
        }

        activity('settings')
            ->causedBy($request->user())
            ->withProperties([
                'event' => 'updated',
                'data' => $validated
            ])
            ->log('Settings updated');

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Settings updated successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Setting $setting)
    {
        $setting->load(['creator', 'updater']);
        return view('admin.settings.show', compact('setting'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Setting $setting)
    {
        return view('admin.settings.edit', compact('setting'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Setting $setting)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:settings,name,' . $setting->id],
            'value' => ['nullable', 'string'],
        ]);

        $oldData = [
            'name' => $setting->name,
            'value' => $setting->value,
        ];

        $setting->update([
            'name' => $validated['name'],
            'value' => $validated['value'] ?? null,
            'updated_by' => $request->user()->id,
        ]);

        activity('settings')
            ->performedOn($setting)
            ->causedBy($request->user())
            ->withProperties([
                'event' => 'updated',
                'before' => $oldData,
                'after' => [
                    'name' => $setting->name,
                    'value' => $setting->value,
                ]
            ])
            ->log('Setting updated');

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Setting updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Setting $setting)
    {
        activity('settings')
            ->performedOn($setting)
            ->causedBy($request->user())
            ->withProperties([
                'event' => 'deleted',
                'before' => [
                    'name' => $setting->name,
                    'value' => $setting->value,
                ]
            ])
            ->log('Setting deleted');

        $setting->delete();

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Setting deleted successfully.');
    }

    /**
     * Update .env file with Cashfree credentials
     */
    protected function updateEnvFile(array $cashfreeData)
    {
        $envPath = base_path('.env');
        
        if (!file_exists($envPath)) {
            return false;
        }
        
        // Read .env file
        $envContent = file_get_contents($envPath);
        
        // Map form fields to .env keys
        $envMappings = [
            'cashfree_app_id' => 'CASHFREE_APP_ID',
            'cashfree_secret_key' => 'CASHFREE_SECRET_KEY',
            'cashfree_env' => 'CASHFREE_ENV',
            'cashfree_base_url' => 'CASHFREE_BASE_URL',
            'cashfree_return_url' => 'CASHFREE_RETURN_URL',
        ];
        
        // Update each Cashfree environment variable
        foreach ($envMappings as $formKey => $envKey) {
            if (isset($cashfreeData[$formKey])) {
                $value = trim($cashfreeData[$formKey]);
                
                if (empty($value)) {
                    continue; // Skip empty values
                }
                
                // Escape special characters for .env file
                // If value contains spaces or special chars, wrap in quotes
                $needsQuotes = preg_match('/[\s\$"\'\\\]/', $value);
                if ($needsQuotes) {
                    $escapedValue = '"' . str_replace(['\\', '$', '"'], ['\\\\', '\\$', '\\"'], $value) . '"';
                } else {
                    $escapedValue = $value;
                }
                
                // Pattern to match the env variable (handles with/without quotes and spaces)
                $pattern = '/^' . preg_quote($envKey, '/') . '\s*=\s*.*$/m';
                
                if (preg_match($pattern, $envContent)) {
                    // Replace existing value
                    $envContent = preg_replace(
                        $pattern,
                        $envKey . '=' . $escapedValue,
                        $envContent
                    );
                } else {
                    // Add new entry if it doesn't exist
                    $envContent .= "\n" . $envKey . '=' . $escapedValue . "\n";
                }
            }
        }
        
        // Write back to .env file
        $result = file_put_contents($envPath, $envContent);
        
        // Clear config cache so changes take effect immediately
        if ($result !== false) {
            try {
                \Illuminate\Support\Facades\Artisan::call('config:clear');
            } catch (\Exception $e) {
                // If config:clear fails, continue anyway
                \Log::warning('Failed to clear config cache: ' . $e->getMessage());
            }
        }
        
        return $result !== false;
    }

    /**
     * Update msg91.php config file with templates
     */
    protected function updateMsg91ConfigFile(array $templates)
    {
        $configPath = config_path('msg91.php');
        
        if (!file_exists($configPath)) {
            return false;
        }
        
        // Read config file
        $configContent = file_get_contents($configPath);
        
        // Build templates array string with proper formatting
        $templatesLines = [];
        foreach ($templates as $key => $id) {
            // Escape single quotes in values if needed
            $escapedKey = addcslashes($key, "'\\");
            $escapedId = addcslashes($id, "'\\");
            $templatesLines[] = "        '{$escapedKey}' => '{$escapedId}',";
        }
        $templatesString = implode("\n", $templatesLines);
        
        // Pattern to match the entire templates array section (including comment and all content between brackets)
        // Matches: // All Flow Template IDs\n    'templates' => [\n        ...\n    ],
        // Using non-greedy match with .*? to match everything between [ and ],
        $pattern = "/(\s*\/\/\s*All Flow Template IDs\s*\n\s*'templates'\s*=>\s*\[)(.*?)(\s*\],)/s";
        
        if (preg_match($pattern, $configContent)) {
            // Replace existing templates array content
            $replacement = '$1' . "\n" . $templatesString . "\n" . '    $3';
            $configContent = preg_replace($pattern, $replacement, $configContent);
        } else {
            // Fallback: try without comment (just the templates array)
            $pattern2 = "/(\s*'templates'\s*=>\s*\[)(.*?)(\s*\],)/s";
            if (preg_match($pattern2, $configContent)) {
                $replacement = '$1' . "\n" . $templatesString . "\n" . '    $3';
                $configContent = preg_replace($pattern2, $replacement, $configContent);
            } else {
                // Last resort: find return array and add templates after sender
                $pattern3 = "/(\s*'sender'\s*=>\s*[^,]+,\s*)(\s*)(\s*\];)/s";
                if (preg_match($pattern3, $configContent)) {
                    $replacement = '$1' . "\n\n    // All Flow Template IDs\n    'templates' => [\n" . $templatesString . "\n    ]," . '$3';
                    $configContent = preg_replace($pattern3, $replacement, $configContent);
                } else {
                    \Log::error('Could not find templates section in msg91.php config file');
                    return false;
                }
            }
        }
        
        // Write back to config file
        $result = file_put_contents($configPath, $configContent);
        
        // Clear config cache so changes take effect immediately
        if ($result !== false) {
            try {
                \Illuminate\Support\Facades\Artisan::call('config:clear');
            } catch (\Exception $e) {
                \Log::warning('Failed to clear config cache: ' . $e->getMessage());
            }
        }
        
        return $result !== false;
    }

    /**
     * Update setting via API (for AJAX requests)
     */

    public function apiUpdate(Request $request)
    {
        // Get all request data except system fields
        $settingsData = $request->except(['_token', '_method', 'csrf_token']);
        // Define settings fields for each tab
        $bookingScheduleFields = ['avaliable_days', 'per_day_booking', 'customer_attempt', 'customer_attempt_note'];
        $photographerFields = ['photographer_available_from', 'photographer_available_to', 'photographer_working_duration'];
        $basePriceFields = ['base_price', 'base_area', 'extra_area', 'extra_area_price'];
        $tourDefayltsFields = ['tour_meta_title', 'tour_meta_description', 'tour_bottommark_logo', 'tour_bottommark_contact_text', 'tour_bottommark_contact_mobile'];
        $paymentGatewayFields = ['cashfree_status', 'cashfree_app_id', 'cashfree_secret_key', 'cashfree_env', 'cashfree_base_url', 'cashfree_return_url', 
                                  'payu_status', 'payu_merchant_key', 'payu_merchant_salt', 'payu_mode', 
                                  'razorpay_status', 'razorpay_key', 'razorpay_secret', 'razorpay_mode', 'active_payment_gateway'];
        $smsFields = ['active_sms_gateway', 'msg91_templates'];
        $ftpFields = ['ftp_configuration']; // FTP is handled separately via API routes
        
        // Check which sections are being updated and validate permissions
        $fieldsToCheck = array_keys($settingsData);
        
        // Check Booking Schedule permissions
        if (array_intersect($fieldsToCheck, $bookingScheduleFields)) {
            if (!$request->user()->can('setting_booking_schedule')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to update Booking Schedule settings.'
                ], 403);
            }
        }

        // tour Defaults permissions
        if (array_intersect($fieldsToCheck, $tourDefayltsFields)) {
            if (!$request->user()->can('setting_booking_schedule')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to update Tour Defaults settings.'
                ], 403);
            }
        }
        
        // Check Photographer permissions
        if (array_intersect($fieldsToCheck, $photographerFields)) {
            if (!$request->user()->can('setting_photographer')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to update Photographer settings.'
                ], 403);
            }
        }
        
        // Check Base Price permissions
        if (array_intersect($fieldsToCheck, $basePriceFields)) {
            if (!$request->user()->can('setting_base_price')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to update Base Price settings.'
                ], 403);
            }
        }
        
        // Check Payment Gateway permissions
        if (array_intersect($fieldsToCheck, $paymentGatewayFields)) {
            if (!$request->user()->can('setting_payment_gateway')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to update Payment Gateway settings.'
                ], 403);
            }
        }
        
        // Check SMS Configuration permissions
        $smsRelatedFields = array_filter($fieldsToCheck, function($field) use ($smsFields) {
            return in_array($field, $smsFields) || strpos($field, 'sms_gateway_') === 0;
        });
        if (!empty($smsRelatedFields)) {
            if (!$request->user()->can('setting_sms_configuration')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to update SMS Configuration settings.'
                ], 403);
            }
        }
        
        // Separate Cashfree credentials for .env update
        $cashfreeEnvFields = ['cashfree_app_id', 'cashfree_secret_key', 'cashfree_env', 'cashfree_base_url', 'cashfree_return_url'];
        $cashfreeEnvData = [];
        foreach ($cashfreeEnvFields as $field) {
            if (isset($settingsData[$field])) {
                $cashfreeEnvData[$field] = $settingsData[$field];
                // Remove from settingsData so it's not saved to database
                unset($settingsData[$field]);
            }
        }
        
        // Auto-update base URL based on environment if environment is being updated
        if (isset($cashfreeEnvData['cashfree_env'])) {
            if ($cashfreeEnvData['cashfree_env'] === 'production') {
                $cashfreeEnvData['cashfree_base_url'] = 'https://api.cashfree.com/pg';
            } else {
                $cashfreeEnvData['cashfree_base_url'] = 'https://sandbox.cashfree.com/pg';
            }
        } elseif (isset($settingsData['cashfree_env'])) {
            // If env is in settingsData (shouldn't happen, but handle it)
            if ($settingsData['cashfree_env'] === 'production') {
                $cashfreeEnvData['cashfree_base_url'] = 'https://api.cashfree.com/pg';
            } else {
                $cashfreeEnvData['cashfree_base_url'] = 'https://sandbox.cashfree.com/pg';
            }
            unset($settingsData['cashfree_env']);
        }
        
        // Update .env file if Cashfree data is present
        if (!empty($cashfreeEnvData)) {
            $envUpdated = $this->updateEnvFile($cashfreeEnvData);
            if (!$envUpdated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update .env file. Please check file permissions.'
                ], 500);
            }
        }
        
        // Handle SMS gateway settings
        $smsGatewayManager = app(\App\Services\Sms\SmsGatewayManager::class);
        
        // Handle active SMS gateway setting
        if (isset($settingsData['active_sms_gateway'])) {
            $gatewayKey = $settingsData['active_sms_gateway'];
            
            // Validate gateway exists
            $gateways = $smsGatewayManager->getRegisteredGateways();
            if (!isset($gateways[$gatewayKey])) {
                return response()->json([
                    'success' => false,
                    'message' => "SMS Gateway '{$gatewayKey}' is not registered."
                ], 400);
            }
            
            // Will be saved in the main loop below
        }

        // Handle SMS gateway status toggles (e.g., sms_gateway_msg91_status)
        $smsStatusFields = [];
        foreach ($settingsData as $key => $value) {
            if (strpos($key, 'sms_gateway_') === 0 && strpos($key, '_status') !== false) {
                // This is a gateway status toggle - ensure it's 0 or 1
                $normalizedValue = ($value === true || $value === '1' || $value === 1) ? '1' : '0';
                $settingsData[$key] = $normalizedValue;
                $smsStatusFields[] = $key;
            }
        }

        // Handle MSG91 templates - save directly to config file
        if (isset($settingsData['msg91_templates'])) {
            $templatesJson = $settingsData['msg91_templates'];
            $decoded = json_decode($templatesJson, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid JSON format for templates: ' . json_last_error_msg()
                ], 400);
            }
            
            if (!is_array($decoded)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Templates must be a valid array/object'
                ], 400);
            }
            
            // Validate template keys and IDs
            foreach ($decoded as $key => $id) {
                if (empty($key) || empty($id)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Template key and ID cannot be empty'
                    ], 400);
                }
                
                // Validate key format (lowercase with underscores)
                if (!preg_match('/^[a-z0-9_]+$/', $key)) {
                    return response()->json([
                        'success' => false,
                        'message' => "Invalid template key format: '{$key}'. Use only lowercase letters, numbers, and underscores."
                    ], 400);
                }
            }
            
            // Update config file directly
            $configUpdated = $this->updateMsg91ConfigFile($decoded);
            if (!$configUpdated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update config file. Please check file permissions.'
                ], 500);
            }
            
            // Remove from settingsData so it's not saved to database
            unset($settingsData['msg91_templates']);
        }

        if (empty($settingsData)) {
            return response()->json([
                'success' => true,
                'message' => !empty($cashfreeEnvData) ? 'Cashfree settings updated in .env file successfully' : 'No settings to update'
            ]);
        }

        $updatedSettings = [];
        // Handle tour bottommark logo upload to S3
        if ($request->hasFile('tour_bottommark_logo')) {
            $file = $request->file('tour_bottommark_logo');
            
            // Validate file
            if (!$file->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid file upload.'
                ], 400);
            }
            
            // Validate file type and size
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $ext = strtolower($file->getClientOriginalExtension());
            if (!in_array($ext, $allowed)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid file type. Allowed: ' . implode(', ', $allowed)
                ], 400);
            }
            if ($file->getSize() > 5 * 1024 * 1024) {
                return response()->json([
                    'success' => false,
                    'message' => 'File size exceeds 5MB limit.'
                ], 400);
            }

            $filename = 'bottommark_' . time() . '.' . $ext;

            try {
                // Store in local public disk
                $brandFilename = 'tour_bottommark_logo_' . time() . '_' . \Illuminate\Support\Str::random(8) . '.' . $ext;
                $brandPath = 'settings/tour-bottommark/' . $brandFilename;
                $brandContent = file_get_contents($file->getRealPath());
                $brandMime = $file->getMimeType();
                $uploaded = Storage::disk('s3')->put($brandPath, $brandContent, ['ContentType' => $brandMime]);
                if (!$uploaded) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to upload logo to S3 storage.'
                    ], 500);
                }
                $settingsData['tour_bottommark_logo'] = $brandPath;

            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Upload error: ' . $e->getMessage()
                ], 500);
            }
        }

        // Loop through each setting and update/create
        foreach ($settingsData as $key => $value) {
            // Skip empty keys (but allow '0' for status fields)
            if (empty($key)) {
                continue;
            }
            
            // Allow '0' value for SMS gateway status fields
            if (in_array($key, $smsStatusFields) && $value === '0') {
                // This is fine, continue processing
            } elseif (empty($value) && !in_array($key, $smsStatusFields) && $key !== 'msg91_templates') {
                // Skip empty values for non-status fields (but allow msg91_templates which is JSON)
                continue;
            }

            // Find or create the setting
            $setting = Setting::firstOrNew(['name' => $key]);
            
            $isNew = !$setting->exists;
            $oldValue = $setting->value ?? null;

            // Prepare the value (encode arrays as JSON, but msg91_templates is already JSON)
            if ($key === 'msg91_templates') {
                $settingValue = $value; // Already JSON string
            } else {
                $settingValue = is_array($value) ? json_encode($value) : (string) $value;
            }
            
            // Only update if value has changed
            if ($settingValue != $oldValue) {
                $setting->value = $settingValue;
                
                if ($isNew) {
                    $setting->created_by = $request->user()->id;
                }
                $setting->updated_by = $request->user()->id;
                $setting->save();

                // Log activity for each setting change with old and new values
                activity('settings')
                    ->performedOn($setting)
                    ->causedBy($request->user())
                    ->withProperties([
                        'event' => $isNew ? 'created' : 'updated',
                        'before' => [
                            'name' => $setting->name,
                            'value' => $oldValue
                        ],
                        'after' => [
                            'name' => $setting->name,
                            'value' => $setting->value
                        ],
                        'changes' => [
                            'old_value' => $oldValue,
                            'new_value' => $setting->value
                        ]
                    ])
                    ->log($isNew ? 'Setting created via API' : 'Setting updated via API');

                $updatedSettings[] = [
                    'id' => $setting->id,
                    'name' => $setting->name,
                    'value' => $setting->value,
                    'is_new' => $isNew,
                    'old_value' => $oldValue,
                    'new_value' => $setting->value,
                ];
            }
        }

        // Build success message
        $messages = [];
        if (!empty($cashfreeEnvData)) {
            $messages[] = 'Cashfree credentials updated in .env file';
        }
        if (!empty($updatedSettings)) {
            $count = count($updatedSettings);
            $messages[] = $count > 1 
                ? $count . ' settings updated in database' 
                : 'Setting updated in database';
        }
        
        if (empty($updatedSettings) && empty($cashfreeEnvData)) {
            return response()->json([
                'success' => true,
                'message' => 'No changes detected',
                'data' => []
            ]);
        }
        
        $finalMessage = !empty($messages) 
            ? implode(' and ', $messages) . ' successfully'
            : 'Settings updated successfully';

        return response()->json([
            'success' => true,
            'message' => $finalMessage,
            'data' => $updatedSettings,
            'count' => count($updatedSettings),
            'env_updated' => !empty($cashfreeEnvData)
        ]);
    }

    /**
     * Get setting via API
     */
    public function apiGet(Request $request, $name)
    {
        $setting = Setting::where('name', $name)->first();

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Setting not found'
            ], 404);
        }

        // Try to decode JSON if it's a JSON string
        $value = $setting->value;
        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $value = $decoded;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $setting->id,
                'name' => $setting->name,
                'value' => $value,
            ]
        ]);
    }

    /**
     * Get FTP configurations for API
     */
    public function apiGetFtpConfigurations(Request $request)
    {
        // Check permission
        if (!$request->user()->can('setting_ftp_configuration')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view FTP configurations.'
            ], 403);
        }
        
        // Get all configurations (active and inactive) for settings page
        // This allows admins to manage all configurations
        $configs = FtpConfiguration::ordered()
            ->get(['id', 'category_name', 'display_name', 'main_url', 'driver', 'host', 'port', 'is_active']);

        return response()->json([
            'success' => true,
            'data' => $configs
        ]);
    }

    /**
     * Get single FTP configuration by ID
     */
    public function apiGetFtpConfiguration(Request $request, $id)
    {
        // Check permission
        if (!$request->user()->can('setting_ftp_configuration')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view FTP configurations.'
            ], 403);
        }
        
        $config = FtpConfiguration::find($id);

        if (!$config) {
            return response()->json([
                'success' => false,
                'message' => 'FTP configuration not found'
            ], 404);
        }

        // Return config without password
        $configData = $config->toArray();
        unset($configData['password']);

        return response()->json([
            'success' => true,
            'data' => $configData
        ]);
    }

    /**
     * Create/Update FTP Configuration via API
     */
    public function apiStoreFtpConfiguration(Request $request)
    {
        // Check permission
        if (!$request->user()->can('setting_ftp_configuration')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to manage FTP configurations.'
            ], 403);
        }
        
        $validated = $request->validate([
            'id' => 'nullable|exists:ftp_configurations,id',
            'category_name' => 'required|string|max:255|unique:ftp_configurations,category_name,' . ($request->id ?? 'NULL'),
            'display_name' => 'required|string|max:255',
            'main_url' => 'required|string|max:255',
            'driver' => 'required|in:ftp,sftp',
            'host' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'password' => 'nullable|string', // Make password optional for updates
            'port' => 'required|integer|min:1|max:65535',
            'root' => 'nullable|string|max:500',
            'passive' => 'boolean',
            'ssl' => 'boolean',
            'timeout' => 'integer|min:1|max:300',
            'remote_path_pattern' => 'nullable|string|max:500',
            'url_pattern' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
            'notes' => 'nullable|string',
        ]);

        // For updates, password is optional (only update if provided)
        if ($request->filled('id')) {
            $ftpConfig = FtpConfiguration::findOrFail($request->id);
            
            // If password is not provided or empty, remove it from validated data
            if (empty($validated['password'])) {
                unset($validated['password']);
            }
            
            $ftpConfig->update(array_merge($validated, [
                'updated_by' => $request->user()->id
            ]));
            $message = 'FTP configuration updated successfully';
        } else {
            // For new records, password is required
            if (empty($validated['password'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password is required for new FTP configurations.'
                ], 422);
            }
            
            $ftpConfig = FtpConfiguration::create(array_merge($validated, [
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id
            ]));
            $message = 'FTP configuration created successfully';
        }

        // Prepare data for activity log (without password)
        $logData = $validated;
        if (isset($logData['password'])) {
            $logData['password'] = '***hidden***';
        }

        activity('ftp_configuration')
            ->performedOn($ftpConfig)
            ->causedBy($request->user())
            ->withProperties([
                'event' => $request->filled('id') ? 'updated' : 'created',
                'data' => $logData
            ])
            ->log('FTP configuration ' . ($request->filled('id') ? 'updated' : 'created'));

        // Return config without password
        $configData = $ftpConfig->toArray();
        unset($configData['password']);

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $configData
        ]);
    }

    /**
     * Delete FTP Configuration via API
     */
    public function apiDeleteFtpConfiguration(Request $request, $id)
    {
        // Check permission
        if (!$request->user()->can('setting_ftp_configuration')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete FTP configurations.'
            ], 403);
        }
        
        $ftpConfig = FtpConfiguration::findOrFail($id);
        $ftpConfig->delete();

        activity('ftp_configuration')
            ->performedOn($ftpConfig)
            ->causedBy($request->user())
            ->log('FTP configuration deleted');

        return response()->json([
            'success' => true,
            'message' => 'FTP configuration deleted successfully'
        ]);
    }
}
