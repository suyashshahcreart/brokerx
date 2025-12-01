<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\Sms\SmsGatewayManager;
use Illuminate\Http\Request;

class SmsConfigurationController extends Controller
{
    protected SmsGatewayManager $gatewayManager;

    public function __construct(SmsGatewayManager $gatewayManager)
    {
        $this->middleware('permission:setting_view')->only(['index']);
        $this->middleware('permission:setting_edit')->only(['update']);
        $this->gatewayManager = $gatewayManager;
    }

    /**
     * Display SMS configuration page
     */
    public function index(Request $request)
    {
        $canEdit = $request->user()->can('setting_edit');
        
        // Get all settings
        $settings = Setting::pluck('value', 'name')->toArray();
        
        // Get registered gateways
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
        
        // Get active gateway
        $activeGateway = $this->gatewayManager->getActiveGatewayFromSettings();
        
        return view('admin.sms-configuration.index', compact(
            'settings',
            'gatewayInstances',
            'activeGateway',
            'canEdit'
        ));
    }

    /**
     * Update SMS gateway configuration via API
     */
    public function update(Request $request)
    {
        $settingsData = $request->except(['_token', '_method', 'csrf_token']);
        
        if (empty($settingsData)) {
            return response()->json([
                'success' => true,
                'message' => 'No settings to update'
            ]);
        }

        $updatedSettings = [];

        // Handle active gateway setting
        if (isset($settingsData['active_sms_gateway'])) {
            $gatewayKey = $settingsData['active_sms_gateway'];
            
            // Validate gateway exists
            $gateways = $this->gatewayManager->getRegisteredGateways();
            if (!isset($gateways[$gatewayKey])) {
                return response()->json([
                    'success' => false,
                    'message' => "SMS Gateway '{$gatewayKey}' is not registered."
                ], 400);
            }
            
            // Update active gateway setting
            $setting = Setting::firstOrNew(['name' => 'active_sms_gateway']);
            $oldValue = $setting->value ?? null;
            
            if ($setting->value != $gatewayKey) {
                $setting->value = $gatewayKey;
                $setting->updated_by = $request->user()->id;
                if (!$setting->exists) {
                    $setting->created_by = $request->user()->id;
                }
                $setting->save();
                
                $updatedSettings[] = [
                    'name' => 'active_sms_gateway',
                    'old_value' => $oldValue,
                    'new_value' => $gatewayKey,
                ];
            }
            
            unset($settingsData['active_sms_gateway']);
        }

        // Handle gateway status toggles (e.g., sms_gateway_msg91_status)
        foreach ($settingsData as $key => $value) {
            if (strpos($key, 'sms_gateway_') === 0 && strpos($key, '_status') !== false) {
                // This is a gateway status toggle
                $setting = Setting::firstOrNew(['name' => $key]);
                $oldValue = $setting->value ?? '0';
                $newValue = $value ? '1' : '0';
                
                if ($setting->value != $newValue) {
                    $setting->value = $newValue;
                    $setting->updated_by = $request->user()->id;
                    if (!$setting->exists) {
                        $setting->created_by = $request->user()->id;
                    }
                    $setting->save();
                    
                    $updatedSettings[] = [
                        'name' => $key,
                        'old_value' => $oldValue,
                        'new_value' => $newValue,
                    ];
                }
                
                unset($settingsData[$key]);
            }
        }

        // Handle other SMS gateway configuration fields
        foreach ($settingsData as $key => $value) {
            if (empty($key)) {
                continue;
            }

            $setting = Setting::firstOrNew(['name' => $key]);
            $isNew = !$setting->exists;
            $oldValue = $setting->value ?? null;

            $settingValue = is_array($value) ? json_encode($value) : $value;
            
            if ($settingValue != $oldValue) {
                $setting->value = $settingValue;
                
                if ($isNew) {
                    $setting->created_by = $request->user()->id;
                }
                $setting->updated_by = $request->user()->id;
                $setting->save();

                activity('sms_configuration')
                    ->performedOn($setting)
                    ->causedBy($request->user())
                    ->withProperties([
                        'event' => $isNew ? 'created' : 'updated',
                        'before' => ['name' => $setting->name, 'value' => $oldValue],
                        'after' => ['name' => $setting->name, 'value' => $setting->value],
                    ])
                    ->log($isNew ? 'SMS configuration created' : 'SMS configuration updated');

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

        if (empty($updatedSettings)) {
            return response()->json([
                'success' => true,
                'message' => 'No changes detected',
                'data' => []
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => count($updatedSettings) > 1 
                ? count($updatedSettings) . ' settings updated successfully' 
                : 'Setting updated successfully',
            'data' => $updatedSettings,
            'count' => count($updatedSettings)
        ]);
    }
}

