<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $avaliable_day = Setting::where('name', 'avaliable_days')->first();
        $start = \Carbon\Carbon::today()->toDateString();
        $end = \Carbon\Carbon::today()->addDays((int) $avaliable_day->value)->toDateString();
        $holidays = Holiday::whereBetween('date', [$start, $end])
            ->orderBy('date')
            ->get(['id', 'name', 'date']);

        return response()->json(['holidays' => $holidays, 'day_limit' => $avaliable_day]);
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
