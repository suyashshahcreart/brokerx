<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Scheduler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get logged-in scheduler from session (for scheduler view)
        $schedulerId = Session::get('scheduler_id');
        $loggedInScheduler = null;

        if ($schedulerId) {
            $loggedInScheduler = Scheduler::find($schedulerId);
        }

        // If accessed by authenticated user (not scheduler), show all appointments
        if (Auth::check() && !$loggedInScheduler) {
            $appointments = Appointment::with('scheduler')->latest()->paginate(10);
            return view('appointments.index', compact('appointments', 'loggedInScheduler'));
        }

        // If accessed by scheduler, show their appointments with calendar
        if ($loggedInScheduler) {
            $appointments = Appointment::where('scheduler_id', $schedulerId)
                ->with('scheduler')
                ->latest()
                ->paginate(10);
            return view('appointments.index', compact('appointments', 'loggedInScheduler'));
        }

        // If no auth, redirect to login
        return redirect()->route('login')->with('error', 'Please login first.');
    }

    /**
     * Get appointments as JSON for calendar
     */
    public function getAppointmentsJson()
    {   
        // If scheduler is logged in, show only their appointments
        $appointments = Appointment::all();
        
        // Format appointments for FullCalendar
        $events = $appointments->map(function ($appointment) {
            // Determine background color based on status
            $bgClass = match($appointment->status) {
                'pending' => 'bg-primary',
                'confirmed' => 'bg-success',
                'completed' => 'bg-info',
                'cancelled' => 'bg-danger',
                default => 'bg-secondary'
            };
            
            return [
                'id' => $appointment->id,
                'title' => $appointment->address . ', ' . $appointment->city,
                'start' => $appointment->start_time,
                'end' => $appointment->end_time,
                'date'=>$appointment->date,
                'classNames' => [$bgClass], // FullCalendar expects an array
                'backgroundColor' => $this->getColorFromClass($bgClass),
                'borderColor' => $this->getColorFromClass($bgClass),
                'extendedProps' => [
                    'status' => $appointment->status,
                    'address' => $appointment->address,
                    'city' => $appointment->city,
                    'state' => $appointment->state,
                    'country' => $appointment->country,
                    'pin_code' => $appointment->pin_code,
                ]
            ];
        });
        
        return response()->json($events);
    }
    
    /**
     * Get hex color from Bootstrap class
     */
    private function getColorFromClass($class)
    {
        return match($class) {
            'bg-primary' => '#3b76e1',
            'bg-success' => '#22c55e',
            'bg-info' => '#0dcaf0',
            'bg-danger' => '#ef4444',
            default => '#6c757d'
        };
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $schedulers = Scheduler::all();
        return view('appointments.create', compact('schedulers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'scheduler_id' => 'required|exists:schedulers,id',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'pin_code' => 'required|string|max:20',
            'status' => 'nullable|in:pending,confirmed,cancelled,completed',
            'assigne_by' => 'nullable|exists:users,id',
            'assigne_to' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $appointment = Appointment::create([
                'scheduler_id' => $request->scheduler_id,
                'date' => $request->date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'country' => $request->country,
                'pin_code' => $request->pin_code,
                'status' => $request->status ?? 'pending',
                'assigne_by' => $request->assigne_by,
                'assigne_to' => $request->assigne_to,
                'create_by' => $request->scheduler_id, // Using scheduler_id as creator
            ]);

            // Redirect back to scheduler index if created by scheduler
            return redirect()->route('schedulers.index')
                ->with('success', 'Appointment created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to create appointment: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Appointment $appointment)
    {
        $appointment->load('scheduler');
        return view('appointments.show', compact('appointment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Appointment $appointment)
    {
        $schedulers = Scheduler::all();
        return view('appointments.edit', compact('appointment', 'schedulers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Appointment $appointment)
    {
        $validator = Validator::make($request->all(), [
            'scheduler_id' => 'required|exists:schedulers,id',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'pin_code' => 'required|string|max:20',
            'status' => 'nullable|in:pending,confirmed,cancelled,completed',
            'assigne_by' => 'nullable|exists:users,id',
            'assigne_to' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $appointment->update([
                'scheduler_id' => $request->scheduler_id,
                'date' => $request->date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'country' => $request->country,
                'pin_code' => $request->pin_code,
                'status' => $request->status ?? 'pending',
                'assigne_by' => $request->assigne_by,
                'assigne_to' => $request->assigne_to,
                'updated_by' => Auth::id(),
            ]);

            return redirect()->route('appointments.index')
                ->with('success', 'Appointment updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update appointment: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Appointment $appointment)
    {
        try {
            $appointment->delete();
            return redirect()->route('appointments.index')
                ->with('success', 'Appointment deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete appointment: ' . $e->getMessage());
        }
    }
}
