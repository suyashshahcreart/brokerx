<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class StateController extends Controller
{
    /**
     * Display a listing of states for DataTables.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $states = State::query()->withCount('cities');
            
            return DataTables::of($states)
                ->addColumn('cities_count', function ($state) {
                    return $state->cities_count ?? 0;
                })
                ->editColumn('updated_at', function ($state) {
                    return $state->updated_at ? $state->updated_at->format('d M Y, h:i A') : '-';
                })
                ->make(true);
        }

        return response()->json(['error' => 'Invalid request'], 400);
    }

    /**
     * Get all states for dropdown options.
     */
    public function options()
    {
        $states = State::select('id', 'name')->orderBy('name')->get();
        return response()->json($states);
    }

    /**
     * Store a newly created state.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:states,name',
        ], [
            'name.required' => 'State name is required.',
            'name.unique' => 'This state already exists.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $state = State::create([
                'name' => $request->name,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'State created successfully.',
                'data' => $state
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create state: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified state.
     */
    public function show($id)
    {
        $state = State::find($id);

        if (!$state) {
            return response()->json([
                'success' => false,
                'message' => 'State not found.'
            ], 404);
        }

        return response()->json($state);
    }

    /**
     * Update the specified state.
     */
    public function update(Request $request, $id)
    {
        $state = State::find($id);

        if (!$state) {
            return response()->json([
                'success' => false,
                'message' => 'State not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:states,name,' . $id,
        ], [
            'name.required' => 'State name is required.',
            'name.unique' => 'This state already exists.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $state->update([
                'name' => $request->name,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'State updated successfully.',
                'data' => $state
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update state: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified state.
     */
    public function destroy($id)
    {
        $state = State::find($id);

        if (!$state) {
            return response()->json([
                'success' => false,
                'message' => 'State not found.'
            ], 404);
        }

        try {
            // Check if state has cities
            if ($state->cities()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete state with existing cities. Please delete all cities first.'
                ], 422);
            }

            $state->delete();

            return response()->json([
                'success' => true,
                'message' => 'State deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete state: ' . $e->getMessage()
            ], 500);
        }
    }
}
