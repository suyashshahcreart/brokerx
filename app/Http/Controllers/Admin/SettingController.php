<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:setting_view')->only(['index', 'show']);
        $this->middleware('permission:setting_create')->only(['create', 'store']);
        $this->middleware('permission:setting_edit')->only(['edit', 'update']);
        $this->middleware('permission:setting_delete')->only(['destroy']);
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
        
        return view('admin.settings.index', compact('settings', 'canCreate', 'canEdit', 'canDelete'));
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
     * Update setting via API (for AJAX requests)
     */

    public function apiUpdate(Request $request)
    {
        // Get all request data except system fields
        $settingsData = $request->except(['_token', '_method', 'csrf_token']);
        
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
        
        if (empty($settingsData)) {
            return response()->json([
                'success' => true,
                'message' => !empty($cashfreeEnvData) ? 'Cashfree settings updated in .env file successfully' : 'No settings to update'
            ]);
        }

        $updatedSettings = [];

        // Loop through each setting and update/create
        foreach ($settingsData as $key => $value) {
            // Skip empty keys
            if (empty($key)) {
                continue;
            }

            // Find or create the setting
            $setting = Setting::firstOrNew(['name' => $key]);
            
            $isNew = !$setting->exists;
            $oldValue = $setting->value ?? null;

            // Prepare the value (encode arrays as JSON)
            $settingValue = is_array($value) ? json_encode($value) : $value;
            
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
}
