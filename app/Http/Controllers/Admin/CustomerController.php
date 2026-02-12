<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Country;
use App\Models\Customer;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:customer_view')->only(['index', 'show']);
        $this->middleware('permission:customer_create')->only(['create', 'store']);
        $this->middleware('permission:customer_edit')->only(['edit', 'update']);
        $this->middleware('permission:customer_delete')->only(['destroy']);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Filter only users with 'customer' role and load bookings count
            $query = Customer::query()
                ->with(['country:id,name,country_code,dial_code'])
                ->withCount('bookings');
            $canEdit = $request->user()->can('customer_edit');
            $canDelete = $request->user()->can('customer_delete');
            $canShow = $request->user()->can('customer_view');

            return DataTables::of($query)
                ->addColumn('name', function (Customer $customer) {
                    return e($customer->name);
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
                ->editColumn('mobile', fn(Customer $customer) => $customer->country?->dial_code . ' ' . e($customer->base_mobile))
                ->addColumn('country', function (Customer $customer) {
                    $name = $customer->country?->name;
                    $code = $customer->country_code ?? $customer->country?->country_code;
                    if ($name && $code) {
                        return e($name . ' (' . $code . ')');
                    }
                    return e($name ?: ($code ?: '-'));
                })
                ->addColumn('bookings_count', function (Customer $customer) {
                    $count = $customer->bookings_count ?? 0;
                    return '<span class="badge bg-primary">' . $count . '</span>';
                })
                ->addColumn('actions', function (Customer $customer) use ($canEdit, $canDelete, $canShow) {
                    return view('admin.customers.partials.actions', compact('customer', 'canEdit', 'canDelete', 'canShow'))->render();
                })
                ->editColumn('email', fn(Customer $customer) => e($customer->email))
                ->rawColumns(['bookings_count', 'actions'])
                ->toJson();
        }

        $canEdit = $request->user()->can('customer_edit');
        $canDelete = $request->user()->can('customer_delete');
        $canCreate = $request->user()->can('customer_create');
        $canShow = $request->user()->can('customer_view');

        return view('admin.customers.index', compact('canEdit', 'canDelete', 'canCreate', 'canShow'));
    }
    /* 
    show function of a customer show all the booking and tour details of the custoner
    @paramer Customer $customer
    */
    public function show(Request $request, Customer $customer)
    {
        if ($request->ajax()) {
            // DataTable AJAX for bookings
            $query = $customer->bookings()->latest();
            return DataTables::of($query)
                ->addColumn('user', function (Booking $booking) {
                    return $booking->customer ? $booking->customer->name : '-';
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
        // Permission check is handled by middleware
        $countries = Country::where('is_active', true)->orderBy('name')->get();
        $defaultCountryId = old('country_id');
        if (!$defaultCountryId) {
            $defaultCountryId = optional($countries->first(function ($country) {
                return strcasecmp($country->name, 'India') === 0 || strtoupper($country->country_code) === 'IN';
            }))->id;
        }

        return view('admin.customers.create', compact('countries', 'defaultCountryId'));
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
            'base_mobile' => ['required', 'numeric', 'digits_between:6,15'],
            'country_id' => ['required', 'exists:countries,id'],
            'email' => ['required', 'email', 'max:255', 'unique:customers,email'],
            'password' => ['required', 'string', 'min:6'],
        ];

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
                if (Customer::where('mobile', $fullMobile)->exists()) {
                    $validator->errors()->add('base_mobile', 'This mobile number already exists.');
                }
            } else {
                $validator->errors()->add('country_id', 'Selected country does not exist.');
            }
        }

        $validated = $validator->validate();
        $dialCode = ltrim($country->dial_code, '+');
        $fullMobile = $dialCode . $validated['base_mobile'];

        $customer = Customer::create([
            'firstname' => $validated['firstname'],
            'lastname' => $validated['lastname'],
            'mobile' => $fullMobile,
            'base_mobile' => $validated['base_mobile'],
            'country_code' => strtoupper($country->country_code),
            'dial_code' => $country->dial_code,
            'country_id' => $country->id,
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        activity('Customers')
            ->performedOn($customer)
            ->causedBy($request->user())
            ->withProperties([
                'event' => 'created',
                'after' => [
                    'name' => $customer->name,
                    'firstname' => $customer->firstname,
                    'lastname' => $customer->lastname,
                    'mobile' => $customer->mobile,
                    'email' => $customer->email,
                ]
            ])
            ->log('Customer created');

        return redirect()->route('admin.customer.index')->with('success', 'Customers created');
    }

    /* 
    Edit the customer details form
    @paramer Customer $customer
    */
    public function edit(Customer $customer)
    {
        // Permission check is handled by middleware
        $countries = Country::where('is_active', true)->orderBy('name')->get();
        $defaultCountryId = old('country_id', $customer->country_id);
        if (!$defaultCountryId) {
            $defaultCountryId = optional($countries->first(function ($country) {
                return strcasecmp($country->name, 'India') === 0 || strtoupper($country->country_code) === 'IN';
            }))->id;
        }

        return view('admin.customers.edit', compact('customer', 'countries', 'defaultCountryId'));
    }

    /* 
    Update the customer details in DB
    @paramer Request $request, Customer $customer
     */
    public function update(Request $request, Customer $customer)
    {
        $rules = [
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'base_mobile' => ['required', 'numeric', 'digits_between:6,15'],
            'country_id' => ['required', 'exists:countries,id'],
            'email' => ['required', 'email', 'max:255', 'unique:customers,email,' . $customer->id],
            'password' => ['nullable', 'string', 'min:6'],
        ];

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
                $exists = Customer::where('mobile', $fullMobile)
                    ->where('id', '!=', $customer->id)
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
        $before = [
            'name' => $customer->name,
            'firstname' => $customer->firstname,
            'lastname' => $customer->lastname,
            'mobile' => $customer->mobile,
            'email' => $customer->email,
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
            'email' => $validated['email'],
            'updated_by' => $request->user()->id,
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

    /* 
    Delete the customer from DB
    @paramer Request $request, Customer $customer
    */
    public function destroy(Request $request, Customer $customer)
    {
        // Capture before deletion
        $before = [
            'name' => $customer->name,
            'firstname' => $customer->firstname,
            'lastname' => $customer->lastname,
            'mobile' => $customer->mobile,
            'email' => $customer->email,
        ];

        $customerId = $customer->id;
        $customer->delete();

        // Log activity for deleted customer
        activity('customers')
            ->causedBy($request->user())
            ->withProperties([
                'event' => 'deleted',
                'before' => $before,
                'deleted_id' => $customerId,
            ])
            ->log('Customer deleted');

        return redirect()->route('admin.customer.index')->with('success', 'Customer deleted successfully.');
    }
}
