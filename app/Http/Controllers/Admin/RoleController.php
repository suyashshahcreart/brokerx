<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Role::query()->withCount('users')->with('permissions');

            return DataTables::of($query)
                ->addColumn('permissions', function (Role $role) {
                    $role->loadMissing('permissions');
                    $permissions = $role->permissions->pluck('name')->sort()->values();
                    return view('admin.roles.partials.permissions', compact('permissions'))->render();
                })
                ->addColumn('actions', function (Role $role) {
                    return view('admin.roles.partials.actions', compact('role'))->render();
                })
                ->editColumn('name', fn(Role $role) => e($role->name))
                ->rawColumns(['permissions', 'actions'])
                ->toJson();
        }

        return view('admin.roles.index');
    }

    public function create()
    {
        $permissions = Permission::orderBy('name')->get();
        $groupedPermissions = $permissions->groupBy(function (Permission $permission) {
            $parts = explode('_', $permission->name, 2);
            return Str::title(str_replace('_', ' ', $parts[0] ?? 'General'));
        });

        return view('admin.roles.create', [
            'groupedPermissions' => $groupedPermissions,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['array']
        ]);
        
        $role = Role::create(['name' => $validated['name']]);
        $selectedPermissions = $request->input('permissions', []);
        $role->syncPermissions($selectedPermissions);
        $role->load('permissions');
        
        activity('roles')
            ->performedOn($role)
            ->causedBy($request->user())
            ->withProperties([
                'event' => 'created',
                'after' => [
                    'name' => $role->name,
                    'permissions' => $role->permissions->pluck('name')->sort()->values()->toArray()
                ]
            ])
            ->log('Role created');
            
        return redirect()->route('admin.roles.index')->with('success', 'Role created');
    }

    public function edit(Role $role)
    {
        $permissions = Permission::orderBy('name')->get();
        $groupedPermissions = $permissions->groupBy(function (Permission $permission) {
            $parts = explode('_', $permission->name, 2);
            return Str::title(str_replace('_', ' ', $parts[0] ?? 'General'));
        });

        $role->load('permissions');
        return view('admin.roles.edit', [
            'role' => $role,
            'groupedPermissions' => $groupedPermissions,
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,' . $role->id],
            'permissions' => ['array']
        ]);
        
        // Capture before state
        $role->load('permissions');
        $before = [
            'name' => $role->name,
            'permissions' => $role->permissions->pluck('name')->sort()->values()->toArray(),
        ];
        
        // Update role
        $role->update(['name' => $validated['name']]);
        
        // Sync permissions
        $selectedPermissions = $request->input('permissions', []);
        $role->syncPermissions($selectedPermissions);
        $role->load('permissions');
        
        // Capture after state
        $after = [
            'name' => $role->name,
            'permissions' => $role->permissions->pluck('name')->sort()->values()->toArray(),
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
        
        activity('roles')
            ->performedOn($role)
            ->causedBy($request->user())
            ->withProperties([
                'event' => 'updated',
                'before' => $before,
                'after' => $after,
                'changes' => $changes
            ])
            ->log('Role updated');
            
        return redirect()->route('admin.roles.index')->with('success', 'Role updated');
    }

    public function destroy(Request $request, Role $role)
    {
        if (in_array(strtolower($role->name), ['admin', 'broker'])) {
            return redirect()->route('admin.roles.index')->with('error', 'The admin and broker roles cannot be deleted.');
        }

        $assignedUsers = User::role($role->name)->get();
        $assignedCount = $assignedUsers->count();

        if ($assignedCount > 0 && ! $request->boolean('force')) {
            return redirect()->route('admin.roles.index')->with('role_delete_warning', [
                'role_id' => $role->id,
                'role_name' => $role->name,
                'user_count' => $assignedCount,
            ]);
        }

        $role->load('permissions');
        $before = [
            'name' => $role->name,
            'permissions' => $role->permissions->pluck('name')->sort()->values()->toArray(),
        ];

        if ($assignedCount > 0) {
            $fallbackRole = Role::firstOrCreate(['name' => 'broker', 'guard_name' => 'web']);

            foreach ($assignedUsers as $user) {
                $user->removeRole($role->name);
                if ($user->roles()->count() === 0) {
                    $user->assignRole($fallbackRole);
                }
            }
        }

        $roleId = $role->id;
        $roleType = get_class($role);
        $role->delete();

        Activity::create([
            'log_name' => 'roles',
            'description' => 'Role deleted',
            'subject_type' => $roleType,
            'subject_id' => $roleId,
            'causer_type' => get_class(auth()->user()),
            'causer_id' => auth()->id(),
            'properties' => [
                'event' => 'deleted',
                'before' => $before,
                'deleted_id' => $roleId,
                'reassigned_users' => $assignedCount,
            ]
        ]);

        $message = $assignedCount > 0
            ? "Role deleted. {$assignedCount} user(s) were reassigned to the broker role."
            : 'Role deleted.';

        return redirect()->route('admin.roles.index')->with('success', $message);
    }
}


