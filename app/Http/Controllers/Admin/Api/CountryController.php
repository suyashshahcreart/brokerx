<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class CountryController extends Controller
{
    /**
     * Display a listing of countries for DataTables.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $countries = Country::query();
            $status = $request->input('status');
            if ($status === 'active') {
                $countries->where('is_active', true);
            } elseif ($status === 'inactive') {
                $countries->where('is_active', false);
            }

            return DataTables::of($countries)
                ->editColumn('is_active', function ($country) {
                    return $country->is_active ? 1 : 0;
                })
                ->editColumn('updated_at', function ($country) {
                    return $country->updated_at ? $country->updated_at->format('d M Y, h:i A') : '-';
                })
                ->make(true);
        }

        return response()->json(['error' => 'Invalid request'], 400);
    }

    /**
     * Get all countries for dropdown options.
     */
    public function options()
    {
        $countries = Country::select('id', 'name')->orderBy('name')->get();
        return response()->json($countries);
    }

    /**
     * Store a newly created country.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:countries,name',
            'country_code' => 'required|string|size:2|unique:countries,country_code',
            'dial_code' => 'required|string|max:8',
            'is_active' => 'nullable|boolean',
        ], [
            'name.required' => 'Country name is required.',
            'name.unique' => 'This country already exists.',
            'country_code.required' => 'Country code is required.',
            'country_code.size' => 'Country code must be 2 characters.',
            'country_code.unique' => 'This country code already exists.',
            'dial_code.required' => 'Dial code is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $country = Country::create([
                'name' => $request->name,
                'country_code' => strtoupper($request->country_code),
                'dial_code' => $request->dial_code,
                'is_active' => (bool) $request->input('is_active', true),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Country created successfully.',
                'data' => $country
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create country: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified country.
     */
    public function show($id)
    {
        $country = Country::find($id);

        if (!$country) {
            return response()->json([
                'success' => false,
                'message' => 'Country not found.'
            ], 404);
        }

        return response()->json($country);
    }

    /**
     * Update the specified country.
     */
    public function update(Request $request, $id)
    {
        $country = Country::find($id);

        if (!$country) {
            return response()->json([
                'success' => false,
                'message' => 'Country not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:countries,name,' . $id,
            'country_code' => 'required|string|size:2|unique:countries,country_code,' . $id,
            'dial_code' => 'required|string|max:8',
            'is_active' => 'nullable|boolean',
        ], [
            'name.required' => 'Country name is required.',
            'name.unique' => 'This country already exists.',
            'country_code.required' => 'Country code is required.',
            'country_code.size' => 'Country code must be 2 characters.',
            'country_code.unique' => 'This country code already exists.',
            'dial_code.required' => 'Dial code is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $country->update([
                'name' => $request->name,
                'country_code' => strtoupper($request->country_code),
                'dial_code' => $request->dial_code,
                'is_active' => (bool) $request->input('is_active', false),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Country updated successfully.',
                'data' => $country
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update country: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified country.
     */
    public function destroy($id)
    {
        $country = Country::find($id);

        if (!$country) {
            return response()->json([
                'success' => false,
                'message' => 'Country not found.'
            ], 404);
        }

        try {
            if (State::where('country_id', $country->id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete country with existing states. Please delete all states first.'
                ], 422);
            }

            $country->delete();

            return response()->json([
                'success' => true,
                'message' => 'Country deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete country: ' . $e->getMessage()
            ], 500);
        }
    }
}
