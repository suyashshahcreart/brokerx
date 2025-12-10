<?php

namespace App\Http\Controllers\Admin;

use App\Models\BookingAssignee;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class BookingAssigneeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = BookingAssignee::query()->with(['booking', 'user', 'createdBy']);

            return DataTables::of($query)
                ->addColumn('id', function (BookingAssignee $assignee) {
                    return '<span class="badge bg-primary">' . $assignee->id . '</span>';
                })
                ->addColumn('booking', function (BookingAssignee $assignee) {
                    if ($assignee->booking) {
                        return '<a href="' . route('admin.bookings.show', $assignee->booking->id) . '" class="text-decoration-none">#' . $assignee->booking->id . ' - ' . ($assignee->booking->property_name ?? 'N/A') . '</a>';
                    }
                    return '<span class="text-muted">Booking Deleted</span>';
                })
                ->addColumn('user', function (BookingAssignee $assignee) {
                    if ($assignee->user) {
                        $avatar = $assignee->user->profile_photo_path 
                            ? '<img src="' . \Storage::url($assignee->user->profile_photo_path) . '" class="avatar-sm rounded-circle" alt="' . $assignee->user->name . '">'
                            : '<div class="avatar-sm rounded-circle bg-secondary d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;"><span class="text-white">' . substr($assignee->user->name, 0, 1) . '</span></div>';
                        return '<div class="d-flex align-items-center gap-2">' . $avatar . '<span>' . $assignee->user->name . '</span></div>';
                    }
                    return '<span class="text-muted">User Deleted</span>';
                })
                ->editColumn('date', function (BookingAssignee $assignee) {
                    return $assignee->date ? '<i class="ri-calendar-line"></i> ' . $assignee->date->format('d M Y') : '<span class="text-muted">-</span>';
                })
                ->editColumn('time', function (BookingAssignee $assignee) {
                    return $assignee->time ? '<i class="ri-time-line"></i> ' . $assignee->time->format('H:i') : '<span class="text-muted">-</span>';
                })
                ->addColumn('created_by', function (BookingAssignee $assignee) {
                    return $assignee->createdBy ? $assignee->createdBy->name : '<span class="text-muted">-</span>';
                })
                ->editColumn('created_at', function (BookingAssignee $assignee) {
                    return '<small class="text-muted">' . $assignee->created_at->format('d M Y H:i') . '</small>';
                })
                ->addColumn('actions', function (BookingAssignee $assignee) {
                    return view('admin.booking-assignees.partials.actions', compact('assignee'))->render();
                })
                ->rawColumns(['id', 'booking', 'user', 'date', 'time', 'actions'])
                ->toJson();
        }

        return view('admin.booking-assignees.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $bookings = Booking::all();
        $users = User::all();
        return view('admin.booking-assignees.create', compact('bookings', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'user_id' => 'required|exists:users,id',
            'date' => 'nullable|date',
            'time' => 'nullable|date_format:H:i',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();

        $assignee = BookingAssignee::create($validated);

        activity('booking_assignees')
            ->performedOn($assignee)
            ->causedBy($request->user())
            ->withProperties([
                'event' => 'created',
                'after' => [
                    'booking_id' => $assignee->booking_id,
                    'user_id' => $assignee->user_id,
                    'date' => $assignee->date,
                    'time' => $assignee->time,
                ]
            ])
            ->log('Booking assignment created');

        return redirect()->route('admin.booking-assignees.index')
            ->with('success', 'Booking assigned successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(BookingAssignee $bookingAssignee)
    {
        $bookingAssignee->load(['booking', 'user', 'createdBy', 'updatedBy', 'deletedBy']);
        return view('admin.booking-assignees.show', compact('bookingAssignee'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BookingAssignee $bookingAssignee)
    {
        $bookings = Booking::all();
        $users = User::all();
        return view('admin.booking-assignees.edit', compact('bookingAssignee', 'bookings', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BookingAssignee $bookingAssignee)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'user_id' => 'required|exists:users,id',
            'date' => 'nullable|date',
            'time' => 'nullable|date_format:H:i',
        ]);

        $old = [
            'booking_id' => $bookingAssignee->booking_id,
            'user_id' => $bookingAssignee->user_id,
            'date' => $bookingAssignee->date,
            'time' => $bookingAssignee->time,
        ];

        $validated['updated_by'] = auth()->id();

        $bookingAssignee->update($validated);

        activity('booking_assignees')
            ->performedOn($bookingAssignee)
            ->causedBy($request->user())
            ->withProperties([
                'event' => 'updated',
                'before' => $old,
                'after' => [
                    'booking_id' => $bookingAssignee->booking_id,
                    'user_id' => $bookingAssignee->user_id,
                    'date' => $bookingAssignee->date,
                    'time' => $bookingAssignee->time,
                ]
            ])
            ->log('Booking assignment updated');

        return redirect()->route('admin.booking-assignees.index')
            ->with('success', 'Booking assignment updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BookingAssignee $bookingAssignee)
    {
        $bookingAssignee->update(['deleted_by' => auth()->id()]);
        $bookingAssignee->delete();

        activity('booking_assignees')
            ->performedOn($bookingAssignee)
            ->causedBy(auth()->user())
            ->withProperties([
                'event' => 'deleted',
                'before' => [
                    'booking_id' => $bookingAssignee->booking_id,
                    'user_id' => $bookingAssignee->user_id,
                    'date' => $bookingAssignee->date,
                    'time' => $bookingAssignee->time,
                ]
            ])
            ->log('Booking assignment deleted');

        return redirect()->route('admin.booking-assignees.index')
            ->with('success', 'Booking assignment deleted successfully.');
    }
}

