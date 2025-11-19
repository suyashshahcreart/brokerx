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
        // dd($settings);
        return view('admin.settings.index', compact('settings'));
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
     * Update setting via API (for AJAX requests)
     */
    public function apiUpdate(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'value' => ['required'],
        ]);

        // Find or create the setting
        $setting = Setting::firstOrNew(['name' => $validated['name']]);
        
        $isNew = !$setting->exists;
        $oldValue = $setting->value;

        $setting->value = is_array($validated['value']) 
            ? json_encode($validated['value']) 
            : $validated['value'];
        
        if ($isNew) {
            $setting->created_by = $request->user()->id;
        }
        $setting->updated_by = $request->user()->id;
        $setting->save();

        activity('settings')
            ->performedOn($setting)
            ->causedBy($request->user())
            ->withProperties([
                'event' => $isNew ? 'created' : 'updated',
                'before' => ['name' => $setting->name, 'value' => $oldValue],
                'after' => ['name' => $setting->name, 'value' => $setting->value],
            ])
            ->log($isNew ? 'Setting created via API' : 'Setting updated via API');

        return response()->json([
            'success' => true,
            'message' => 'Setting updated successfully',
            'data' => [
                'id' => $setting->id,
                'name' => $setting->name,
                'value' => $setting->value,
            ]
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
