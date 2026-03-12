<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;


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
}
