<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Country;
use App\Models\Customer;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
    Show function for a customer; displays all the booking and tour details of the customer.
    @param Customer $customer
    */
    public function show(Request $request, Customer $customer)
    {
        if ($request->ajax()) {
            // DataTable AJAX for bookings
            $query = $customer->bookings()->with(['customer'])->latest();
            return DataTables::of($query)
                ->addColumn('user', function (Booking $booking) {
                    return $booking->customer ? trim($booking->customer->firstname . ' ' . $booking->customer->lastname) : 'N/A';
                })
                ->addColumn('customer', function (Booking $booking) {
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
    Display form to create a new customer.
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
    Store customer record in the database.
    @param  Request  $request
    */
    public function store(Request $request)
    {    
        $rules = [
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'base_mobile' => ['required', 'numeric', 'digits_between:6,15,','unique:customers,base_mobile'],
            'country_id' => ['required', 'exists:countries,id'],
            'email' => ['required', 'email', 'max:255', 'unique:customers,email'],
            'password' => ['nullable', 'string', 'min:6'],
            // profile/cover photos
            'profile_photo' => ['nullable', 'image', 'max:2048'],
            'cover_photo' => ['nullable', 'image', 'max:2048'],
            // additional profile details
            'company_name' => ['nullable', 'string', 'max:255'],
            'designation' => ['nullable', 'string', 'max:255'],
            'company_website' => ['nullable', 'url', 'max:255'],
            'tag_line' => ['nullable', 'string', 'max:255'],
            // social links as associative array key=>value
            'social_link' => ['nullable', 'array'],
            // allow any string value (not limited to URL)
            'social_link.*' => ['nullable', 'array'],
            // SEO fields are optional but add basic rules here in case they're submitted
            'slug' => ['nullable', 'string', 'alpha_dash', 'unique:customers,slug'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'canonical_url' => ['nullable', 'url', 'max:2048'],
            'meta_robots' => ['nullable', 'string', 'max:255'],
            'twitter_title' => ['nullable', 'string', 'max:255'],
            'twitter_description' => ['nullable', 'string'],
            'twitter_image' => ['nullable', 'string', 'max:255'],
            'og_title' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string'],
            'header_code' => ['nullable', 'string'],
            'footer_code' => ['nullable', 'string'],
            'gtm_tag' => ['nullable', 'string'],
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


        // ensure social_link is always an array (empty when absent)
        $validated['social_link'] = $validated['social_link'] ?? [];

        $dialCode = ltrim($country->dial_code, '+');
        $fullMobile = $dialCode . $validated['base_mobile'];

        // Prepare the data for creation including SEO fields
        $data = [
            'firstname' => $validated['firstname'],
            'lastname' => $validated['lastname'],
            'mobile' => $fullMobile,
            'base_mobile' => $validated['base_mobile'],
            'country_code' => strtoupper($country->country_code),
            'dial_code' => $country->dial_code,
            'country_id' => $country->id,
            'email' => $validated['email'],
            // 'password' => Hash::make($validated['password']),
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
            'social_link' => $request->input('social_link', []),
        ];

        // add optional profile info
        // foreach (['company_name', 'designation', 'company_website', 'tag_line', 'social_link'] as $fld) {
        //     if (array_key_exists($fld, $validated)) {
        //         $data[$fld] = $validated[$fld];
        //     }
        // }

        // include any supplied seo fields
        // foreach (['slug', 'meta_title', 'meta_description', 'meta_keywords', 'meta_image', 'canonical_url', 'meta_robots', 'og_title', 'og_description', 'og_image', 'og_type', 'og_url', 'twitter_title', 'twitter_description', 'twitter_image', 'twitter_card', 'header_code', 'footer_code', 'gtm_tag'] as $seoField) {
        //     if (array_key_exists($seoField, $validated)) {
        //         $data[$seoField] = $validated[$seoField];
        //     }
        // }

        $customer = Customer::create($data);

        /** @var \Illuminate\Filesystem\FilesystemAdapter $s3Disk */
        $s3Disk = Storage::disk('s3');

        // handle file uploads after customer is created (needs id)
        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')
                ->storeAs('settings/customer/' . $customer->id . '/Files', 'profile_' . time() . '.' . $request->file('profile_photo')->extension(), 's3');
            $customer->update(['profile_photo' => $s3Disk->url($path)]);
        }
        if ($request->hasFile('cover_photo')) {
            $path = $request->file('cover_photo')
                ->storeAs('settings/customer/' . $customer->id . '/Files', 'cover_' . time() . '.' . $request->file('cover_photo')->extension(), 's3');
            $customer->update(['cover_photo' => $s3Disk->url($path)]);
        }

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
    Show form for editing customer details.
    @param Customer $customer
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
    Update customer details in the database.
    @param Request $request
    @param Customer $customer
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
            'profile_photo' => ['nullable', 'image', 'max:2048'],
            'cover_photo' => ['nullable', 'image', 'max:2048'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'designation' => ['nullable', 'string', 'max:255'],
            'company_website' => ['nullable', 'url', 'max:255'],
            'tag_line' => ['nullable', 'string', 'max:255'],
            'social_link' => ['nullable', 'array'],
            // any string accepted
            'social_link.*' => ['nullable', 'array'],
            // slug should remain unique except for this customer
            'slug' => ['nullable', 'string', 'alpha_dash', 'unique:customers,slug,' . $customer->id],
        ];

        // support old json format for social_link (stringified associative or array)
        if ($request->has('social_link') && is_string($request->input('social_link'))) {
            $decoded = json_decode($request->input('social_link'), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $request->merge(['social_link' => $decoded]);
            }
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

        // ensure social_link always present as array
        $validated['social_link'] = $validated['social_link'] ?? [];

        $dialCode = ltrim($country->dial_code, '+');
        $fullMobile = $dialCode . $validated['base_mobile'];

        // Capture before state
        $before = [
            'name' => $customer->name,
            'firstname' => $customer->firstname,
            'lastname' => $customer->lastname,
            'mobile' => $customer->mobile,
            'email' => $customer->email,
            'slug' => $customer->slug,
            'company_name' => $customer->company_name,
            'designation' => $customer->designation,
            'company_website' => $customer->company_website,
            'tag_line' => $customer->tag_line,
            'social_link' => $customer->social_link,
        ];


        // Prepare update data
        $data = [
            'firstname' => $validated['firstname'],
            'lastname' => $validated['lastname'],
            'mobile' => $fullMobile,
            'base_mobile' => $validated['base_mobile'],
            'slug' => $validated['slug'] ?? $customer->slug,
            'country_code' => strtoupper($country->country_code),
            'dial_code' => $country->dial_code,
            'country_id' => $country->id,
            'email' => $validated['email'],
            'updated_by' => $request->user()->id,
            'socail_link' => $request->input('social_link', []),
        ];

        // include profile / contact fields
        foreach (['company_name', 'designation', 'company_website', 'tag_line', 'social_link'] as $fld) {
            if (array_key_exists($fld, $validated)) {
                $data[$fld] = $validated[$fld];
            }
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $s3Disk */
        $s3Disk = Storage::disk('s3');

        $extractS3Path = static function (?string $value): ?string {
            if (empty($value)) {
                return null;
            }

            $path = filter_var($value, FILTER_VALIDATE_URL)
                ? (parse_url($value, PHP_URL_PATH) ?: '')
                : $value;

            $path = ltrim($path, '/');
            $bucket = trim((string) config('filesystems.disks.s3.bucket'), '/');
            if ($bucket !== '' && str_starts_with($path, $bucket . '/')) {
                $path = substr($path, strlen($bucket) + 1);
            }

            return $path !== '' ? $path : null;
        };

        // handle file uploads
        if ($request->hasFile('profile_photo')) {
            $oldProfilePath = $extractS3Path($customer->profile_photo);
            if ($oldProfilePath && $s3Disk->exists($oldProfilePath)) {
                $s3Disk->delete($oldProfilePath);
            }

            $path = $request->file('profile_photo')
                ->storeAs('settings/customer/' . $customer->id . '/Files', 'profile_' . time() . '.' . $request->file('profile_photo')->extension(), 's3');
            $data['profile_photo'] = $s3Disk->url($path);
        }
        if ($request->hasFile('cover_photo')) {
            $oldCoverPath = $extractS3Path($customer->cover_photo);
            if ($oldCoverPath && $s3Disk->exists($oldCoverPath)) {
                $s3Disk->delete($oldCoverPath);
            }

            $path = $request->file('cover_photo')
                ->storeAs('settings/customer/' . $customer->id . '/Files', 'cover_' . time() . '.' . $request->file('cover_photo')->extension(), 's3');
            $data['cover_photo'] = $s3Disk->url($path);
        }

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
            'slug' => $customer->slug,
            'company_name' => $customer->company_name,
            'designation' => $customer->designation,
            'company_website' => $customer->company_website,
            'tag_line' => $customer->tag_line,
            'social_link' => $customer->social_link,
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
    Delete the customer record from the database.
    @param Request $request
    @param Customer $customer
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

    /**
     * Endpoint to update only the SEO related fields for a customer.
     * This method can be called from a dedicated form or ajax request.
     *
     * @param Request $request
     * @param Customer $customer
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateSeo(Request $request, Customer $customer)
    {
        $rules = [
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'meta_image' => ['nullable', 'image', 'max:2048'],
            'canonical_url' => ['nullable', 'url', 'max:2048'],
            'meta_robots' => ['nullable', 'string', 'max:255'],
            'og_title' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string'],
            'og_image' => ['nullable', 'image', 'max:2048'],
            'og_type' => ['nullable', 'string', 'max:64'],
            'og_url' => ['nullable', 'url', 'max:2048'],
            'twitter_title' => ['nullable', 'string', 'max:255'],
            'twitter_description' => ['nullable', 'string'],
            'twitter_image' => ['nullable', 'image', 'max:2048'],
            'twitter_card' => ['nullable', 'string', 'max:64'],
            'header_code' => ['nullable', 'string'],
            'footer_code' => ['nullable', 'string'],
            'gtm_tag' => ['nullable', 'string', 'max:64'],
            'slug' => ['nullable', 'string', 'alpha_dash', 'unique:customers,slug,' . $customer->id],
        ];

        $validated = $request->validate($rules);

        /** @var \Illuminate\Filesystem\FilesystemAdapter $s3Disk */
        $s3Disk = Storage::disk('s3');

        $extractS3Path = static function (?string $value): ?string {
            if (empty($value)) {
                return null;
            }

            $path = filter_var($value, FILTER_VALIDATE_URL)
                ? (parse_url($value, PHP_URL_PATH) ?: '')
                : $value;

            $path = ltrim($path, '/');
            $bucket = trim((string) config('filesystems.disks.s3.bucket'), '/');
            if ($bucket !== '' && str_starts_with($path, $bucket . '/')) {
                $path = substr($path, strlen($bucket) + 1);
            }

            return $path !== '' ? $path : null;
        };

        foreach (['meta_image', 'og_image', 'twitter_image'] as $field) {
            if (!$request->hasFile($field)) {
                unset($validated[$field]);
                continue;
            }

            $oldPath = $extractS3Path($customer->{$field});
            if ($oldPath && $s3Disk->exists($oldPath)) {
                $s3Disk->delete($oldPath);
            }

            $path = $request->file($field)->storeAs(
                'settings/customer/' . $customer->id . '/Seo',
                $field . '_' . time() . '.' . $request->file($field)->extension(),
                's3'
            );

            $validated[$field] = $s3Disk->url($path);
        }

        $customer->updateSeo($validated);

        activity('Customers')
            ->performedOn($customer)
            ->causedBy($request->user())
            ->withProperties([
                'event' => 'seo_updated',
                'changed' => $validated,
            ])
            ->log('Customer SEO updated');

        return redirect()->back()->with('success', 'SEO fields updated');
    }
}
