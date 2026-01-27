<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class CityController extends Controller
{
    /**
     * Display a listing of cities for DataTables.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $cities = City::with('state:id,name');
            
            return DataTables::of($cities)
                ->addColumn('state_name', function ($city) {
                    return $city->state ? $city->state->name : '-';
                })
                ->editColumn('updated_at', function ($city) {
                    return $city->updated_at ? $city->updated_at->format('d M Y, h:i A') : '-';
                })
                ->make(true);
        }

        return response()->json(['error' => 'Invalid request'], 400);
    }

    /**
     * Get all cities for dropdown options.
     */
    public function options(Request $request)
    {
        $query = City::select('id', 'name', 'state_id')->with('state:id,name');
        
        // Filter by state if provided
        if ($request->has('state_id') && $request->state_id) {
            $query->where('state_id', $request->state_id);
        }
        
        $cities = $query->orderBy('name')->get();
        return response()->json($cities);
    }

    /**
     * Store a newly created city.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'state_id' => 'required|exists:states,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('cities')->where(function ($query) use ($request) {
                    return $query->where('state_id', $request->state_id);
                })
            ],
        ], [
            'state_id.required' => 'State is required.',
            'state_id.exists' => 'Selected state does not exist.',
            'name.required' => 'City name is required.',
            'name.unique' => 'This city already exists in the selected state.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $city = City::create([
                'state_id' => $request->state_id,
                'name' => $request->name,
            ]);

            $city->load('state:id,name');

            return response()->json([
                'success' => true,
                'message' => 'City created successfully.',
                'data' => $city
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create city: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified city.
     */
    public function show($id)
    {
        $city = City::with('state:id,name')->find($id);

        if (!$city) {
            return response()->json([
                'success' => false,
                'message' => 'City not found.'
            ], 404);
        }

        return response()->json($city);
    }

    /**
     * Update the specified city.
     */
    public function update(Request $request, $id)
    {
        $city = City::find($id);

        if (!$city) {
            return response()->json([
                'success' => false,
                'message' => 'City not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'state_id' => 'required|exists:states,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('cities')->where(function ($query) use ($request) {
                    return $query->where('state_id', $request->state_id);
                })->ignore($id)
            ],
        ], [
            'state_id.required' => 'State is required.',
            'state_id.exists' => 'Selected state does not exist.',
            'name.required' => 'City name is required.',
            'name.unique' => 'This city already exists in the selected state.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $city->update([
                'state_id' => $request->state_id,
                'name' => $request->name,
            ]);

            $city->load('state:id,name');

            return response()->json([
                'success' => true,
                'message' => 'City updated successfully.',
                'data' => $city
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update city: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified city.
     */
    public function destroy($id)
    {
        $city = City::find($id);

        if (!$city) {
            return response()->json([
                'success' => false,
                'message' => 'City not found.'
            ], 404);
        }

        try {
            $city->delete();

            return response()->json([
                'success' => true,
                'message' => 'City deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete city: ' . $e->getMessage()
            ], 500);
        }
    }
}
