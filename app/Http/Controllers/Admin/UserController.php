<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:user_view')->only(['index', 'show']);
        $this->middleware('permission:user_create')->only(['create', 'store']);
        $this->middleware('permission:user_edit')->only(['edit', 'update']);
        $this->middleware('permission:user_delete')->only(['destroy']);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = User::query()->with('roles');
            // Filter: load only non-customer users if requested
            $query->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'customer');
            });
            $canEdit = $request->user()->can('user_edit');
            $canDelete = $request->user()->can('user_delete');

            return DataTables::of($query)
                ->addColumn('name', function (User $user) {
                    return e($user->name);
                })
                ->filterColumn('name', function ($query, $keyword) {
                    $query->where(function ($subQuery) use ($keyword) {
                        $subQuery
                            ->where('firstname', 'like', "%{$keyword}%")
                            ->orWhere('lastname', 'like', "%{$keyword}%");
                    });
                })
                ->orderColumn('name', function ($query, $direction) {
                    $query
                        ->orderBy('firstname', $direction)
                        ->orderBy('lastname', $direction);
                })
                ->editColumn('mobile', fn(User $user) => e($user->mobile))
                ->addColumn('roles_badges', function (User $user) {
                    $user->loadMissing('roles');
                    return view('admin.users.partials.roles', ['roles' => $user->roles])->render();
                })
                ->addColumn('actions', function (User $user) use ($canEdit, $canDelete) {
                    return view('admin.users.partials.actions', compact('user', 'canEdit', 'canDelete'))->render();
                })
                ->editColumn('email', fn(User $user) => e($user->email))
                ->rawColumns(['roles_badges', 'actions'])
                ->toJson();
        }

        $canEdit = $request->user()->can('user_edit');
        $canDelete = $request->user()->can('user_delete');

        return view('admin.users.index', compact('canEdit', 'canDelete'));
    }

    public function create()
    {
        $canManageRoles = auth()->user()->can('user_manage_roles');
        $roles = $canManageRoles ? Role::orderBy('name')->get() : collect();

        return view('admin.users.create', compact('roles', 'canManageRoles'));
    }

    public function store(Request $request)
    {
        $rules = [
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'numeric', 'digits:10', 'unique:users,mobile'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
        ];

        if ($request->user()->can('user_manage_roles')) {
            $rules['roles'] = ['array'];
            $rules['roles.*'] = ['string', 'exists:roles,name'];
        } else {
            $rules['roles'] = ['prohibited'];
        }

        $validated = $request->validate($rules);

        $user = User::create([
            'firstname' => $validated['firstname'],
            'lastname' => $validated['lastname'],
            'mobile' => $validated['mobile'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password'])
        ]);

        $selectedRoles = [];
        if ($request->user()->can('user_manage_roles')) {
            $selectedRoles = array_values(array_filter($request->input('roles', [])));
        }

        $user->syncRoles($selectedRoles);
        $user->load('roles');

        activity('users')
            ->performedOn($user)
            ->causedBy($request->user())
            ->withProperties([
                'event' => 'created',
                'after' => [
                    'name' => $user->name,
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'mobile' => $user->mobile,
                    'email' => $user->email,
                    'roles' => $user->roles->pluck('name')->toArray()
                ]
            ])
            ->log('User created');

        return redirect()->route('admin.users.index')->with('success', 'User created');
    }

    public function edit(User $user)
    {
        $canManageRoles = auth()->user()->can('user_manage_roles');
        $roles = $canManageRoles ? Role::orderBy('name')->get() : collect();
        $user->load('roles');
        return view('admin.users.edit', compact('user', 'roles', 'canManageRoles'));
    }

    public function update(Request $request, User $user)
    {
        $rules = [
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'numeric', 'digits:10', 'unique:users,mobile,' . $user->id],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:6'],
        ];

        if ($request->user()->can('user_manage_roles')) {
            $rules['roles'] = ['array'];
            $rules['roles.*'] = ['string', 'exists:roles,name'];
        } else {
            $rules['roles'] = ['prohibited'];
        }

        $validated = $request->validate($rules);

        // Capture before state
        $user->load('roles');
        $before = [
            'name' => $user->name,
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'mobile' => $user->mobile,
            'email' => $user->email,
            'roles' => $user->roles->pluck('name')->sort()->values()->toArray(),
        ];

        // Prepare update data
        $data = [
            'firstname' => $validated['firstname'],
            'lastname' => $validated['lastname'],
            'mobile' => $validated['mobile'],
            'email' => $validated['email']
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
            $before['password'] = '***';
        }

        // Update user
        $user->update($data);

        // Sync roles
        if ($request->user()->can('user_manage_roles')) {
            $selectedRoles = array_values(array_filter($request->input('roles', [])));

            $isUserAdmin = $user->hasRole('admin');
            $adminRoleRetained = in_array('admin', $selectedRoles, true);

            if ($isUserAdmin && !$adminRoleRetained) {
                $otherAdmins = User::role('admin')
                    ->where('users.id', '!=', $user->id)
                    ->count();

                if ($otherAdmins === 0) {
                    return redirect()
                        ->back()
                        ->withInput()
                        ->with('error', 'Cannot remove the admin role from the last admin user.');
                }
            }
        } else {
            $selectedRoles = $user->roles->pluck('name')->toArray();
        }

        $user->syncRoles($selectedRoles);
        $user->load('roles');

        // Capture after state
        $after = [
            'name' => $user->name,
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'mobile' => $user->mobile,
            'email' => $user->email,
            'roles' => $user->roles->pluck('name')->sort()->values()->toArray(),
        ];

        if (!empty($validated['password'])) {
            $after['password'] = '***';
        }

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

        activity('users')
            ->performedOn($user)
            ->causedBy($request->user())
            ->withProperties([
                'event' => 'updated',
                'before' => $before,
                'after' => $after,
                'changes' => $changes
            ])
            ->log('User updated');

        return redirect()->route('admin.users.index')->with('success', 'User updated');
    }

    public function destroy(User $user)
    {
        // Prevent deleting the last admin user
        $isAdmin = $user->hasRole('admin');
        if ($isAdmin) {
            $adminCount = User::role('admin')->count();
            if ($adminCount <= 1) {
                return redirect()->route('admin.users.index')->with('error', 'Cannot delete the last admin user.');
            }
        }

        // Capture before deletion
        $user->load('roles');
        $before = [
            'name' => $user->name,
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'mobile' => $user->mobile,
            'email' => $user->email,
            'roles' => $user->roles->pluck('name')->sort()->values()->toArray(),
        ];

        $userId = $user->id;
        $userType = get_class($user);
        $user->delete();

        // Manually create activity log for deleted model
        Activity::create([
            'log_name' => 'users',
            'description' => 'User deleted',
            'subject_type' => $userType,
            'subject_id' => $userId,
            'causer_type' => get_class(auth()->user()),
            'causer_id' => auth()->id(),
            'properties' => [
                'event' => 'deleted',
                'before' => $before,
                'deleted_id' => $userId
            ]
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User deleted');
    }
}


