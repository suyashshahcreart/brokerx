<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use Hash;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:user_view')->only(['index']);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Filter only users with 'customer' role and load bookings count
            $query = User::role('customer')
                ->withCount('bookings');
            $canEdit = $request->user()->can('user_edit');
            $canDelete = $request->user()->can('user_delete');
            $canShow = true;

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
                ->addColumn('bookings_count', function (User $user) {
                    $count = $user->bookings_count ?? 0;
                    return '<span class="badge bg-primary">' . $count . '</span>';
                })
                ->addColumn('actions', function (User $user) use ($canEdit, $canDelete) {
                    return view('admin.customers.partials.actions', compact('user', 'canEdit', 'canDelete'))->render();
                })
                ->editColumn('email', fn(User $user) => e($user->email))
                ->rawColumns(['bookings_count', 'actions'])
                ->toJson();
        }

        $canEdit = $request->user()->can('user_edit');
        $canDelete = $request->user()->can('user_delete');

        return view('admin.customers.index', compact('canEdit', 'canDelete'));
    }
    /* 
    show function of a customer show all the booking and tour details of the custoner
    @paramer User $customer
    */
    public function show(Request $request, User $customer)
    {
        if ($request->ajax()) {
            // DataTable AJAX for bookings
            $query = $customer->bookings()->latest();
            return DataTables::of($query)
                ->addColumn('user', function (Booking $booking) {
                    return $booking->user ? $booking->user->firstname . ' ' . $booking->user->lastname : '-';
                })
                ->addColumn('type_subtype', function (Booking $booking) {
                    return $booking->propertyType?->name . '<div class="text-muted small">' . ($booking->propertySubType?->name ?? '-') . '</div>';
                })
                ->addColumn('bhk', fn(Booking $booking) => $booking->bhk?->name ?? '-')
                ->addColumn('city_state', function (Booking $booking) {
                    return ($booking->city?->name ?? '-') . '<div class="text-muted small">' . ($booking->state?->name ?? '-') . '</div>';
                })
                ->editColumn('area', fn(Booking $booking) => number_format($booking->area))
                ->editColumn('price', fn(Booking $booking) => '₹ ' . number_format($booking->price))
                ->editColumn('booking_date', fn(Booking $booking) => optional($booking->booking_date)->format('Y-m-d') ?? '-')
                ->editColumn('status', fn(Booking $booking) => '<span class="badge bg-secondary text-uppercase">' . $booking->status . '</span>')
                ->editColumn('payment_status', fn(Booking $booking) => '<span class="badge bg-info text-uppercase">' . $booking->payment_status . '</span>')
                ->addColumn('schedule', function (Booking $booking) {
                    if (auth()->user()->can('booking_delete')) {
                        return '<a href="#" class="btn btn-soft-warning btn-sm" title="Schedule"><i class="ri-calendar-line"></i></a>';
                    }
                    return '';
                })
                ->addColumn('actions', function (Booking $booking) {
                    $view = route('admin.bookings.show', $booking);
                    $schedule = '';
                    return $schedule .
                        '<a href="' . $view . '" class="btn btn-light btn-sm border" title="View"><i class="ri-eye-line"></i></a>';

                })
                ->rawColumns(['type_subtype', 'city_state', 'status', 'payment_status', 'actions', 'schedule'])
                ->toJson();
        }

        $totalBookings = $customer->bookings()->count();
        $totalTours = $totalBookings;
        return view('admin.customers.show', compact('customer', 'totalBookings', 'totalTours'));
    }

    /* 
    create customer form
    */
    public function create()
    {
        return view('admin.customers.create');
    }

    /* 
    stoere customer function
    save the customer in DB
    @params Request $request
    */
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

        activity('Customers')
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
            ->log('Customer created');

        return redirect()->route('admin.customer.index')->with('success', 'Customers created');
    }

    /* 
    Edit the customer details form
    @paramer User $customer
    */
    public function edit(User $customer)
    {
        return view('admin.customers.edit', compact('customer'));
    }

    /* 
    Update the customer details in DB
    @paramer Request $request, User $customer
     */
    public function update(Request $request, User $customer)
    {
        $rules = [
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'numeric', 'digits:10', 'unique:users,mobile,' . $customer->id],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $customer->id],
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
        $customer->load('roles');
        $before = [
            'name' => $customer->name,
            'firstname' => $customer->firstname,
            'lastname' => $customer->lastname,
            'mobile' => $customer->mobile,
            'email' => $customer->email,
            'roles' => $customer->roles->pluck('name')->sort()->values()->toArray(),
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
        $customer->update($data);

        // Capture after state
        $after = [
            'name' => $customer->name,
            'firstname' => $customer->firstname,
            'lastname' => $customer->lastname,
            'mobile' => $customer->mobile,
            'email' => $customer->email,
            'roles' => $customer->roles->pluck('name')->sort()->values()->toArray(),
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

        activity('Customers')
            ->performedOn($customer)
            ->causedBy($request->user())
            ->withProperties([
                'event' => 'updated',
                'before' => $before,
                'after' => $after,
                'changes' => $changes
            ])
            ->log('Customer updated');

        return redirect()->route('admin.customer.index')->with('success', 'Customer updated');
    }
}
