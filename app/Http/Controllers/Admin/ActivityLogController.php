<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\Facades\DataTables;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Activity::query()->with(['causer', 'subject']);

            if ($request->filled('log_name')) {
                $query->where('log_name', $request->string('log_name'));
            }

            if ($request->filled('event')) {
                $query->where('description', 'like', '%' . $request->string('event') . '%');
            }

            if ($request->filled('causer_id')) {
                $query->where('causer_id', $request->integer('causer_id'));
            }

            if ($request->filled('date_range')) {
                [$start, $end] = array_pad(explode(' - ', $request->date_range), 2, null);
                try {
                    $startDate = Carbon::createFromFormat('m/d/Y', $start)->startOfDay();
                    $endDate = Carbon::createFromFormat('m/d/Y', $end)->endOfDay();
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                } catch (\InvalidArgumentException $e) {
                    // Ignore invalid date formats
                }
            }

            return DataTables::of($query)
                ->editColumn('created_at', function (Activity $activity) {
                    return $activity->created_at?->format('M d, Y H:i:s');
                })
                ->addColumn('event_badge', function (Activity $activity) {
                    $event = strtolower($activity->properties['event'] ?? $activity->description);
                    return view('admin.activity.partials.event-badge', compact('event'))->render();
                })
                ->addColumn('subject_info', function (Activity $activity) {
                    return view('admin.activity.partials.subject', compact('activity'))->render();
                })
                ->addColumn('causer_info', function (Activity $activity) {
                    return view('admin.activity.partials.causer', compact('activity'))->render();
                })
                ->addColumn('changes', function (Activity $activity) {
                    return view('admin.activity.partials.changes', compact('activity'))->render();
                })
                ->rawColumns(['event_badge', 'subject_info', 'causer_info', 'changes'])
                ->toJson();
        }

        $logNames = Activity::select('log_name')->distinct()->pluck('log_name')->sort()->values();
        $events = Activity::select('description')->distinct()->pluck('description')->sort()->values();
        $users = User::select('id', 'firstname', 'lastname')
            ->orderBy('firstname')
            ->orderBy('lastname')
            ->get();

        return view('admin.activity.index', compact('logNames', 'events', 'users'));
    }
}


