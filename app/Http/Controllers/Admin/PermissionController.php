<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\Facades\DataTables;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Permission::query()->withCount('roles')->select(['permissions.*']);

            return DataTables::of($query)
                ->addColumn('roles_count_badge', function (Permission $permission) {
                    $count = (int) $permission->roles_count;
                    return '<span class="badge bg-soft-secondary text-secondary">' . $count . '</span>';
                })
                ->addColumn('actions', function (Permission $permission) {
                    return view('admin.permissions.partials.actions', compact('permission'))->render();
                })
                ->editColumn('created_at', function (Permission $permission) {
                    return optional($permission->created_at)->format('M d, Y');
                })
                ->rawColumns(['actions', 'roles_count_badge'])
                ->toJson();
        }

        return view('admin.permissions.index');
    }

    public function create()
    {
        return view('admin.permissions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:permissions,name'],
        ]);
        
        $permission = Permission::create(['name' => $validated['name']]);
        
        activity('permissions')
            ->performedOn($permission)
            ->causedBy($request->user())
            ->withProperties([
                'event' => 'created',
                'after' => [
                    'name' => $permission->name,
                    'guard_name' => $permission->guard_name
                ]
            ])
            ->log('Permission created');
            
        if ($admin = Role::where('name', 'admin')->first()) {
            $admin->givePermissionTo($permission);
        }
        
        return redirect()->route('admin.permissions.index')->with('success', 'Permission created and attached to admin');
    }

    public function edit(Permission $permission)
    {
        return view('admin.permissions.edit', compact('permission'));
    }

    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:permissions,name,' . $permission->id],
        ]);
        
        // Capture before state
        $before = [
            'name' => $permission->name,
            'guard_name' => $permission->guard_name,
        ];
        
        // Update permission
        $permission->update(['name' => $validated['name']]);
        $permission->refresh();
        
        // Capture after state
        $after = [
            'name' => $permission->name,
            'guard_name' => $permission->guard_name,
        ];
        
        // Calculate changes
        $changes = [];
        foreach ($after as $key => $value) {
            if (!isset($before[$key]) || $before[$key] !== $value) {
                $changes[$key] = [
                    'old' => $before[$key] ?? null,
                    'new' => $value
                ];
            }
        }
        
        activity('permissions')
            ->performedOn($permission)
            ->causedBy($request->user())
            ->withProperties([
                'event' => 'updated',
                'before' => $before,
                'after' => $after,
                'changes' => $changes
            ])
            ->log('Permission updated');
            
        return redirect()->route('admin.permissions.index')->with('success', 'Permission updated');
    }

    public function destroy(Request $request, Permission $permission)
    {
        $protectedPermissions = [
            'permission_view',
            'permission_create',
            'permission_edit',
            'permission_delete',
            'permission_assign',
            'role_view',
            'role_create',
            'role_edit',
            'role_delete',
            'role_assign_permissions',
            'user_view',
            'user_create',
            'user_edit',
            'user_delete',
            'user_manage_roles',
        ];

        if (in_array($permission->name, $protectedPermissions, true)) {
            return redirect()->route('admin.permissions.index')->with('error', 'This permission is protected and cannot be deleted.');
        }

        $permission->load('roles');
        $assignedRoles = $permission->roles;
        $assignedCount = $assignedRoles->count();

        if ($assignedCount > 0 && ! $request->boolean('force')) {
            return redirect()->route('admin.permissions.index')->with('permission_delete_warning', [
                'permission_id' => $permission->id,
                'permission_name' => $permission->name,
                'role_count' => $assignedCount,
            ]);
        }

        // Capture before deletion
        $before = [
            'name' => $permission->name,
            'guard_name' => $permission->guard_name,
        ];

        if ($assignedCount > 0) {
            foreach ($assignedRoles as $role) {
                $role->revokePermissionTo($permission);
            }
        }

        $permissionId = $permission->id;
        $permissionType = get_class($permission);
        $permission->delete();

        // Manually create activity log for deleted model
        Activity::create([
            'log_name' => 'permissions',
            'description' => 'Permission deleted',
            'subject_type' => $permissionType,
            'subject_id' => $permissionId,
            'causer_type' => get_class(auth()->user()),
            'causer_id' => auth()->id(),
            'properties' => [
                'event' => 'deleted',
                'before' => $before,
                'deleted_id' => $permissionId,
                'roles_detached' => $assignedCount,
            ]
        ]);

        $message = $assignedCount > 0
            ? "Permission deleted. It was removed from {$assignedCount} role(s)."
            : 'Permission deleted.';

        return redirect()->route('admin.permissions.index')->with('success', $message);
    }
}


