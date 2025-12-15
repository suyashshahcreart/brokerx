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
    public function __construct()
    {
        $this->middleware('permission:role_view')->only(['index', 'show']);
        $this->middleware('permission:role_create')->only(['create', 'store']);
        $this->middleware('permission:role_edit')->only(['edit', 'update']);
        $this->middleware('permission:role_delete')->only(['destroy']);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Role::query()->withCount('users')->with('permissions');
            $canEdit = $request->user()->can('role_edit');
            $canDelete = $request->user()->can('role_delete');

            return DataTables::of($query)
                ->addColumn('permissions', function (Role $role) {
                    $role->loadMissing('permissions');
                    $permissions = $role->permissions->pluck('name')->sort()->values();
                    return view('admin.roles.partials.permissions', compact('permissions'))->render();
                })
                ->addColumn('actions', function (Role $role) use ($canEdit, $canDelete) {
                    return view('admin.roles.partials.actions', compact('role', 'canEdit', 'canDelete'))->render();
                })
                ->editColumn('name', fn(Role $role) => e($role->name))
                ->rawColumns(['permissions', 'actions'])
                ->toJson();
        }

        $canCreate = $request->user()->can('role_create');
        $canEdit = $request->user()->can('role_edit');
        $canDelete = $request->user()->can('role_delete');

        return view('admin.roles.index', compact('canCreate', 'canEdit', 'canDelete'));
    }

    public function create()
    {
        $canAssignPermissions = auth()->user()->can('role_assign_permissions');
        $permissions = $canAssignPermissions ? Permission::orderBy('name')->get() : collect();
        $groupedPermissions = $canAssignPermissions
            ? $permissions->groupBy(function (Permission $permission) {
                $parts = explode('_', $permission->name, 2);
                return Str::title(str_replace('_', ' ', $parts[0] ?? 'General'));
            })
            : collect();

        return view('admin.roles.create', [
            'groupedPermissions' => $groupedPermissions,
            'canAssignPermissions' => $canAssignPermissions,
        ]);
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
        ];

        if ($request->user()->can('role_assign_permissions')) {
            $rules['permissions'] = ['array'];
            $rules['permissions.*'] = ['string', 'exists:permissions,name'];
        } else {
            $rules['permissions'] = ['prohibited'];
        }

        $validated = $request->validate($rules);
        
        $role = Role::create(['name' => $validated['name']]);
        $selectedPermissions = [];
        if ($request->user()->can('role_assign_permissions')) {
            $selectedPermissions = array_values(array_filter($request->input('permissions', [])));
        }
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
        $canAssignPermissions = auth()->user()->can('role_assign_permissions');
        $permissions = $canAssignPermissions ? Permission::orderBy('name')->get() : collect();
        $groupedPermissions = $canAssignPermissions
            ? $permissions->groupBy(function (Permission $permission) {
                $parts = explode('_', $permission->name, 2);
                return Str::title(str_replace('_', ' ', $parts[0] ?? 'General'));
            })
            : collect();

        $role->load('permissions');
        return view('admin.roles.edit', [
            'role' => $role,
            'groupedPermissions' => $groupedPermissions,
            'canAssignPermissions' => $canAssignPermissions,
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $rules = [
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,' . $role->id],
        ];

        if ($request->user()->can('role_assign_permissions')) {
            $rules['permissions'] = ['array'];
            $rules['permissions.*'] = ['string', 'exists:permissions,name'];
        } else {
            $rules['permissions'] = ['prohibited'];
        }

        $validated = $request->validate($rules);
        
        // Capture before state
        $role->load('permissions');
        $before = [
            'name' => $role->name,
            'permissions' => $role->permissions->pluck('name')->sort()->values()->toArray(),
        ];
        
        // Update role
        $role->update(['name' => $validated['name']]);
        
        // Sync permissions
        $selectedPermissions = [];
        if ($request->user()->can('role_assign_permissions')) {
            $selectedPermissions = array_values(array_filter($request->input('permissions', [])));
        } else {
            $selectedPermissions = $role->permissions->pluck('name')->toArray();
        }
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


