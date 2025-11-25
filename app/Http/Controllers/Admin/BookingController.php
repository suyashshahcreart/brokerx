<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BHK;
use App\Models\Booking;
use App\Models\City;
use App\Models\PropertySubType;
use App\Models\PropertyType;
use App\Models\State;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class BookingController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:booking_view')->only(['index', 'show']);
        $this->middleware('permission:booking_create')->only(['create', 'store']);
        $this->middleware('permission:booking_edit')->only(['edit', 'update']);
        $this->middleware('permission:booking_delete')->only(['destroy']);
    }
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Booking::with(['user', 'propertyType', 'propertySubType', 'bhk', 'city', 'state']);
            return \Yajra\DataTables\Facades\DataTables::of($query)
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
                    $edit = route('admin.bookings.edit', $booking);
                    $delete = route('admin.bookings.destroy', $booking);
                    $csrf = csrf_field();
                    $method = method_field('DELETE');
                    $schedule = '';
                    if (auth()->user()->can('booking_delete')) {
                        $schedule = '<a href="#" class="btn btn-soft-warning btn-sm me-1" title="Schedule"><i class="ri-calendar-line"></i></a>';
                    }
                    return $schedule .
                        '<a href="' . $view . '" class="btn btn-light btn-sm border" title="View"><i class="ri-eye-line"></i></a>' .
                        ' <a href="' . $edit . '" class="btn btn-soft-primary btn-sm" title="Edit"><i class="ri-edit-line"></i></a>' .
                        ' <form action="' . $delete . '" method="POST" class="d-inline">' . $csrf . $method .
                        '<button type="submit" class="btn btn-soft-danger btn-sm" onclick="return confirm(\'Delete this booking?\')"><i class="ri-delete-bin-line"></i></button></form>';
                })
                ->rawColumns(['type_subtype', 'city_state', 'status', 'payment_status', 'actions', 'schedule'])
                ->toJson();
        }
        $canCreate = $request->user()->can('booking_create');
        $canEdit = $request->user()->can('booking_edit');
        $canDelete = $request->user()->can('booking_delete');
        return view('admin.bookings.index', compact('canCreate', 'canEdit', 'canDelete'));
    }

    public function create()
    {
        $users = User::orderBy('firstname')->get();
        $propertyTypes = PropertyType::orderBy('name')->get();
        $propertySubTypes = PropertySubType::orderBy('name')->get();
        $bhks = BHK::orderBy('name')->get();
        $cities = City::orderBy('name')->get();
        $states = State::orderBy('name')->get();

        $paymentStatuses = ['pending', 'paid', 'failed', 'refunded'];
        $statuses = ['pending', 'confirmed', 'cancelled', 'completed'];

        return view('admin.bookings.create', compact(
            'users',
            'propertyTypes',
            'propertySubTypes',
            'bhks',
            'cities',
            'states',
            'paymentStatuses',
            'statuses'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'property_type_id' => ['required', 'exists:property_types,id'],
            'property_sub_type_id' => ['required', 'exists:property_sub_types,id'],
            'bhk_id' => ['nullable', 'exists:bhks,id'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'state_id' => ['nullable', 'exists:states,id'],
            'furniture_type' => ['nullable', 'string', 'max:255'],
            'area' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'integer', 'min:0'],
            'house_no' => ['nullable', 'string', 'max:255'],
            'building' => ['nullable', 'string', 'max:255'],
            'society_name' => ['nullable', 'string', 'max:255'],
            'address_area' => ['nullable', 'string', 'max:255'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'full_address' => ['nullable', 'string'],
            'pin_code' => ['nullable', 'string', 'max:20'],
            'booking_date' => ['nullable', 'date'],
            'payment_status' => ['required', 'in:pending,paid,failed,refunded'],
            'status' => ['required', 'in:pending,confirmed,cancelled,completed'],
        ]);

        $validated['created_by'] = $request->user()->id ?? null;

        $booking = Booking::create($validated);

        activity('bookings')
            ->performedOn($booking)
            ->causedBy($request->user())
            ->withProperties([
                'event' => 'created',
                'after' => $booking->toArray()
            ])
            ->log('Booking created');

        return redirect()->route('admin.bookings.index')->with('success', 'Booking created successfully.');
    }

    public function show(Booking $booking)
    {
        $booking->load(['user', 'propertyType', 'propertySubType', 'bhk', 'city', 'state', 'creator']);
        return view('admin.bookings.show', compact('booking'));
    }

    public function edit(Booking $booking)
    {
        $users = User::orderBy('firstname')->get();
        $propertyTypes = PropertyType::orderBy('name')->get();
        $propertySubTypes = PropertySubType::orderBy('name')->get();
        $bhks = BHK::orderBy('name')->get();
        $cities = City::orderBy('name')->get();
        $states = State::orderBy('name')->get();

        $paymentStatuses = ['pending', 'paid', 'failed', 'refunded'];
        $statuses = ['pending', 'confirmed', 'cancelled', 'completed'];

        // Load tour if linked
        $tour = Tour::where('booking_id', $booking->id)->first();

        return view('admin.bookings.edit', compact(
            'booking',
            'tour',
            'users',
            'propertyTypes',
            'propertySubTypes',
            'bhks',
            'cities',
            'states',
            'paymentStatuses',
            'statuses'
        ));
    }

    public function update(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'property_type_id' => ['required', 'exists:property_types,id'],
            'property_sub_type_id' => ['required', 'exists:property_sub_types,id'],
            'bhk_id' => ['nullable', 'exists:bhks,id'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'state_id' => ['nullable', 'exists:states,id'],
            'furniture_type' => ['nullable', 'string', 'max:255'],
            'area' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'integer', 'min:0'],
            'house_no' => ['nullable', 'string', 'max:255'],
            'building' => ['nullable', 'string', 'max:255'],
            'society_name' => ['nullable', 'string', 'max:255'],
            'address_area' => ['nullable', 'string', 'max:255'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'full_address' => ['nullable', 'string'],
            'pin_code' => ['nullable', 'string', 'max:20'],
            'booking_date' => ['nullable', 'date'],
            'payment_status' => ['required', 'in:pending,paid,failed,refunded'],
            'status' => ['required', 'in:pending,confirmed,cancelled,completed'],
        ]);

        $validated['updated_by'] = $request->user()->id ?? null;

        $before = $booking->getOriginal();
        $booking->update($validated);
        $after = $booking->toArray();
        $changes = [];
        foreach ($after as $key => $value) {
            if (!isset($before[$key]) || $before[$key] !== $value) {
                $changes[$key] = [
                    'old' => $before[$key] ?? null,
                    'new' => $value
                ];
            }
        }
        activity('bookings')
            ->performedOn($booking)
            ->causedBy($request->user())
            ->withProperties([
                'event' => 'updated',
                'before' => $before,
                'after' => $after,
                'changes' => $changes
            ])
            ->log('Booking updated');

        return redirect()->route('admin.bookings.index')->with('success', 'Booking updated successfully.');
    }

    public function destroy(Booking $booking)
    {
        $before = $booking->toArray();
        $bookingId = $booking->id;
        $bookingType = get_class($booking);
        $booking->delete();

        Activity::create([
            'log_name' => 'bookings',
            'description' => 'Booking deleted',
            'subject_type' => $bookingType,
            'subject_id' => $bookingId,
            'causer_type' => get_class(auth()->user()),
            'causer_id' => auth()->id(),
            'properties' => [
                'event' => 'deleted',
                'before' => $before,
                'deleted_id' => $bookingId,
            ]
        ]);
        return redirect()->route('admin.bookings.index')->with('success', 'Booking deleted successfully.');
    }

    public function reschedule(Request $request, Booking $booking)
    {
        $request->validate([
            'schedule_date' => ['required', 'date'],
        ]);
        $oldDate = $booking->booking_date;
        $booking->booking_date = $request->input('schedule_date');
        $booking->save();

        activity('bookings')
            ->performedOn($booking)
            ->causedBy($request->user())
            ->withProperties([
                'event' => 'rescheduled',
                'old_date' => $oldDate,
                'new_date' => $booking->booking_date,
            ])
            ->log('Booking rescheduled');

        return response()->json(['success' => true, 'new_date' => $booking->booking_date->format('Y-m-d')]);
    }

    /**
     * Update booking via AJAX
     */
    public function updateAjax(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'property_type_id' => ['required', 'exists:property_types,id'],
            'property_sub_type_id' => ['required', 'exists:property_sub_types,id'],
            'area' => ['required', 'numeric', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'payment_status' => ['required', 'in:pending,paid,failed,refunded'],
            'status' => ['required', 'in:pending,confirmed,cancelled,completed'],
            'bhk_id' => ['nullable', 'exists:bhks,id'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'state_id' => ['nullable', 'exists:states,id'],
            'furniture_type' => ['nullable', 'string'],
            'booking_date' => ['nullable', 'date'],
            'house_no' => ['nullable', 'string', 'max:255'],
            'building' => ['nullable', 'string', 'max:255'],
            'society_name' => ['nullable', 'string', 'max:255'],
            'address_area' => ['nullable', 'string', 'max:255'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'pin_code' => ['nullable', 'string', 'max:20'],
            'full_address' => ['nullable', 'string'],
        ]);

        $oldData = $booking->toArray();
        $booking->update($validated);

        activity('bookings')
            ->performedOn($booking)
            ->causedBy($request->user())
            ->withProperties([
                'old' => $oldData,
                'attributes' => $booking->toArray(),
            ])
            ->log('Booking updated via AJAX');

        return response()->json([
            'success' => true,
            'message' => 'Booking updated successfully',
            'booking' => $booking->fresh(),
        ]);
    }
}