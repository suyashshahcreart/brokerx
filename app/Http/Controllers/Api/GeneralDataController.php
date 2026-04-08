<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\State;
use App\Models\City;


class GeneralDataController extends Controller
{
    public function getCountries()
    {
        try {

            $countries = Country::where('is_active', true)
                ->orderBy('name', 'asc')
                ->get(['id', 'name', 'country_code', 'dial_code']);

            if ($countries->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No countries found',
                    'data' => []
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Countries fetched successfully',
                'data' => $countries
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong while fetching countries',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getStates(Request $request)
    {
        try {
            $countryId = $request->query('country_id');

            if (!$countryId) {
                return response()->json([
                    'status' => false,
                    'message' => 'Country ID is required',
                    'data' => []
                ], 400);
            }

            $states = State::where('country_id', $countryId)
                ->orderBy('name', 'asc')
                ->get(['id', 'name', 'code']);

            return response()->json([
                'status' => true,
                'message' => 'States fetched successfully',
                'data' => $states
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong while fetching states',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getCities(Request $request)
    {
        try {
            $stateId = $request->query('state_id');

            if (!$stateId) {
                return response()->json([
                    'status' => false,
                    'message' => 'State ID is required',
                    'data' => []
                ], 400);
            }

            $cities = City::where('state_id', $stateId)
                ->orderBy('name', 'asc')
                ->get(['id', 'name']);

            return response()->json([
                'status' => true,
                'message' => 'Cities fetched successfully',
                'data' => $cities
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong while fetching cities',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
