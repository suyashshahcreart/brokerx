<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BHK;
use App\Models\Booking;
use App\Models\City;
use App\Models\PropertySubType;
use App\Models\PropertyType;
use App\Models\State;

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
    public function index()
    {
        $bookings = Booking::with(['user', 'propertyType', 'propertySubType', 'bhk', 'city', 'state'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.bookings.index', compact('bookings'));
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
            'users', 'propertyTypes', 'propertySubTypes', 'bhks', 'cities', 'states', 'paymentStatuses', 'statuses'
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

        return view('admin.bookings.edit', compact(
            'booking', 'users', 'propertyTypes', 'propertySubTypes', 'bhks', 'cities', 'states', 'paymentStatuses', 'statuses'
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
}
