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
    {
        if ($request->ajax()) {
            $query = Setting::query()
                ->with(['creator:id,firstname,lastname', 'updater:id,firstname,lastname'])
                ->latest();
            
            $canEdit = $request->user()->can('setting_edit');
            $canDelete = $request->user()->can('setting_delete');

            return DataTables::of($query)
                ->addColumn('created_by_name', function (Setting $setting) {
                    return $setting->creator 
                        ? e($setting->creator->firstname . ' ' . $setting->creator->lastname)
                        : '-';
                })
                ->addColumn('updated_by_name', function (Setting $setting) {
                    return $setting->updater 
                        ? e($setting->updater->firstname . ' ' . $setting->updater->lastname)
                        : '-';
                })
                ->addColumn('actions', function (Setting $setting) use ($canEdit, $canDelete) {
                    return view('admin.settings.partials.actions', compact('setting', 'canEdit', 'canDelete'))->render();
                })
                ->editColumn('name', fn(Setting $setting) => e($setting->name))
                ->editColumn('value', function (Setting $setting) {
                    $value = e($setting->value);
                    return strlen($value) > 50 ? substr($value, 0, 50) . '...' : $value;
                })
                ->editColumn('created_at', fn(Setting $setting) => $setting->created_at ? $setting->created_at->format('M d, Y h:i A') : '-')
                ->editColumn('updated_at', fn(Setting $setting) => $setting->updated_at ? $setting->updated_at->format('M d, Y h:i A') : '-')
                ->rawColumns(['actions'])
                ->toJson();
        }

        $canCreate = $request->user()->can('setting_create');
        $canEdit = $request->user()->can('setting_edit');
        $canDelete = $request->user()->can('setting_delete');

        return view('admin.settings.index', compact('canCreate', 'canEdit', 'canDelete'));
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
            'name' => ['required', 'string', 'max:255', 'unique:settings,name'],
            'value' => ['nullable', 'string'],
        ]);

        $setting = Setting::create([
            'name' => $validated['name'],
            'value' => $validated['value'] ?? null,
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        activity('settings')
            ->performedOn($setting)
            ->causedBy($request->user())
            ->withProperties([
                'event' => 'created',
                'after' => [
                    'name' => $setting->name,
                    'value' => $setting->value,
                ]
            ])
            ->log('Setting created');

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Setting created successfully.');
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
}
