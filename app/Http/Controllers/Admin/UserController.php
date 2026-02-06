<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
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
            // Filter: exclude users who have ONLY customer role
            // Show users who: don't have customer role OR have customer role + other roles
            $query->where(function ($q) {
                $q->whereDoesntHave('roles', function ($q2) {
                    $q2->where('name', 'customer');
                })
                    ->orWhere(function ($q2) {
                        $q2->whereHas('roles', function ($q3) {
                            $q3->where('name', 'customer');
                        })->has('roles', '>', 1); // Has more than 1 role total
                    });
            })->with(['country:id,name,country_code,dial_code']);
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
                ->editColumn('mobile', fn(User $user) => $user->country?->dial_code . ' ' . e($user->base_mobile))
                ->addColumn('country', function (User $user) {
                    $name = $user->country?->name;
                    $code = $user->country_code ?? $user->country?->country_code;
                    if ($name && $code) {
                        return e($name . ' (' . $code . ')');
                    }
                    return e($name ?: ($code ?: '-'));
                })
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

        $countries = Country::where('is_active', true)->orderBy('name')->get();
        $defaultCountryId = old('country_id');
        if (!$defaultCountryId) {
            $defaultCountryId = optional($countries->first(function ($country) {
                return strcasecmp($country->name, 'India') === 0 || strtoupper($country->country_code) === 'IN';
            }))->id;
        }

        return view('admin.users.create', compact('roles', 'canManageRoles', 'countries', 'defaultCountryId'));
    }

    public function store(Request $request)
    {
        $rules = [
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'base_mobile' => ['required', 'numeric', 'digits_between:6,15'],
            'country_id' => ['required', 'exists:countries,id'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
        ];

        if ($request->user()->can('user_manage_roles')) {
            $rules['roles'] = ['array'];
            $rules['roles.*'] = ['string', 'exists:roles,name'];
        } else {
            $rules['roles'] = ['prohibited'];
        }

        $validator = Validator::make($request->all(), $rules, [
            'base_mobile.required' => 'Mobile number is required.',
            'base_mobile.digits_between' => 'Mobile number must be between 6 and 15 digits.',
            'country_id.required' => 'Country is required.',
        ]);

        $country = null;
        if ($validator->passes()) {
            $country = Country::find($request->country_id);
            if ($country) {
                $dialCode = ltrim($country->dial_code, '+');
                $fullMobile = $dialCode . $request->base_mobile;
                if (User::where('mobile', $fullMobile)->exists()) {
                    $validator->errors()->add('base_mobile', 'This mobile number already exists.');
                }
            } else {
                $validator->errors()->add('country_id', 'Selected country does not exist.');
            }
        }

        $validated = $validator->validate();
        $dialCode = ltrim($country->dial_code, '+');
        $fullMobile = $dialCode . $validated['base_mobile'];

        $user = User::create([
            'firstname' => $validated['firstname'],
            'lastname' => $validated['lastname'],
            'mobile' => $fullMobile,
            'base_mobile' => $validated['base_mobile'],
            'country_code' => strtoupper($country->country_code),
            'dial_code' => $country->dial_code,
            'country_id' => $country->id,
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
        $countries = Country::where('is_active', true)->orderBy('name')->get();
        $defaultCountryId = old('country_id', $user->country_id);
        if (!$defaultCountryId) {
            $defaultCountryId = optional($countries->first(function ($country) {
                return strcasecmp($country->name, 'India') === 0 || strtoupper($country->country_code) === 'IN';
            }))->id;
        }

        return view('admin.users.edit', compact('user', 'roles', 'canManageRoles', 'countries', 'defaultCountryId'));
    }

    public function update(Request $request, User $user)
    {
        $rules = [
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'base_mobile' => ['required', 'numeric', 'digits_between:6,15'],
            'country_id' => ['required', 'exists:countries,id'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:6'],
        ];

        if ($request->user()->can('user_manage_roles')) {
            $rules['roles'] = ['array'];
            $rules['roles.*'] = ['string', 'exists:roles,name'];
        } else {
            $rules['roles'] = ['prohibited'];
        }

        $validator = Validator::make($request->all(), $rules, [
            'base_mobile.required' => 'Mobile number is required.',
            'base_mobile.digits_between' => 'Mobile number must be between 6 and 15 digits.',
            'country_id.required' => 'Country is required.',
        ]);

        $country = null;
        if ($validator->passes()) {
            $country = Country::find($request->country_id);
            if ($country) {
                $dialCode = ltrim($country->dial_code, '+');
                $fullMobile = $dialCode . $request->base_mobile;
                $exists = User::where('mobile', $fullMobile)
                    ->where('id', '!=', $user->id)
                    ->exists();
                if ($exists) {
                    $validator->errors()->add('base_mobile', 'This mobile number already exists.');
                }
            } else {
                $validator->errors()->add('country_id', 'Selected country does not exist.');
            }
        }

        $validated = $validator->validate();
        $dialCode = ltrim($country->dial_code, '+');
        $fullMobile = $dialCode . $validated['base_mobile'];

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
            'mobile' => $fullMobile,
            'base_mobile' => $validated['base_mobile'],
            'country_code' => strtoupper($country->country_code),
            'dial_code' => $country->dial_code,
            'country_id' => $country->id,
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


