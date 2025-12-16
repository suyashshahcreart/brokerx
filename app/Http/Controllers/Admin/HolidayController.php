<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HolidayController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:holiday_view')->only(['index', 'show']);
        $this->middleware('permission:holiday_create')->only(['create', 'store']);
        $this->middleware('permission:holiday_edit')->only(['edit', 'update']);
        $this->middleware('permission:holiday_delete')->only(['destroy']);
    }
    public function index()
    {
        $holidays = Holiday::orderBy('date', 'desc')->paginate(15);
        return view('admin.holidays.index', compact('holidays'));
    }
    public function indexAPI()
    {
        // Try 'available_days' first, fallback to 'avaliable_days' for backward compatibility
        $available_day = Setting::where('name', 'available_days')->first();
        if (!$available_day) {
            $available_day = Setting::where('name', 'avaliable_days')->first();
        }
        
        // Get per day booking limit
        $perDayBooking = Setting::where('name', 'per_day_booking')->first();
        $perDayLimit = $perDayBooking && $perDayBooking->value ? (int) $perDayBooking->value : 20;
        
        $dayLimit = $available_day && $available_day->value ? (int) $available_day->value : 30;
        $start = \Carbon\Carbon::today()->toDateString();
        $end = \Carbon\Carbon::today()->addDays($dayLimit)->toDateString();
        $holidays = Holiday::whereBetween('date', [$start, $end])
            ->orderBy('date')
            ->get(['id', 'name', 'date']);

        // Get booking counts for each date (excluding schedul_decline status)
        // Count all statuses except schedul_decline
        $bookingCounts = \App\Models\Booking::whereNotNull('booking_date')
            ->whereBetween('booking_date', [$start, $end])
            ->where('status', '!=', 'schedul_decline')
            ->selectRaw('DATE(booking_date) as date, COUNT(*) as count')
            ->groupBy(DB::raw('DATE(booking_date)'))
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        // Get dates that have reached the limit
        $disabledDates = [];
        foreach ($bookingCounts as $date => $count) {
            if ($count >= $perDayLimit) {
                $disabledDates[] = $date;
            }
        }

        return response()->json([
            'holidays' => $holidays,
            'day_limit' => $available_day,
            'per_day_booking' => $perDayBooking,
            'booking_counts' => $bookingCounts,
            'disabled_dates' => $disabledDates
        ]);
    }

    public function create()
    {
        return view('admin.holidays.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date|unique:holidays,date',
        ]);
        $holiday = Holiday::create([
            'name' => $request->name,
            'date' => $request->date,
            'created_by' => Auth::id(),
        ]);
        activity('holidays')
            ->performedOn($holiday)
            ->causedBy($request->user())
            ->withProperties([
                'event' => 'created',
                'data' => $holiday->toArray()
            ])
            ->log('Holiday created');
        return redirect()->route('admin.holidays.index')->with('success', 'Holiday created successfully.');
    }

    public function show(Holiday $holiday)
    {
        return view('admin.holidays.show', compact('holiday'));
    }

    public function edit(Holiday $holiday)
    {
        return view('admin.holidays.edit', compact('holiday'));
    }

    public function update(Request $request, Holiday $holiday)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date|unique:holidays,date,' . $holiday->id,
        ]);
        $oldData = $holiday->toArray();
        $holiday->update([
            'name' => $request->name,
            'date' => $request->date,
            'updated_by' => Auth::id(),
        ]);
        activity('holidays')
            ->performedOn($holiday)
            ->causedBy($request->user())
            ->withProperties([
                'event' => 'updated',
                'before' => $oldData,
                'after' => $holiday->toArray()
            ])
            ->log('Holiday updated');
        return redirect()->route('admin.holidays.index')->with('success', 'Holiday updated successfully.');
    }

    public function destroy(Holiday $holiday)
    {
        $oldData = $holiday->toArray();
        $holiday->delete();
        activity('holidays')
            ->performedOn($holiday)
            ->causedBy(Auth::user())
            ->withProperties([
                'event' => 'deleted',
                'data' => $oldData
            ])
            ->log('Holiday deleted');
        return redirect()->route('admin.holidays.index')->with('success', 'Holiday deleted successfully.');
    }
}
