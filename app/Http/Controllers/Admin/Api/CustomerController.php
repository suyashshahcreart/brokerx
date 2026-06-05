<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Customer options for Select2 filters/dropdowns.
     */
    public function options(Request $request)
    {
        $query = Customer::query()
            ->select('id', 'firstname', 'lastname', 'base_mobile', 'mobile')
            ->orderBy('firstname')
            ->orderBy('lastname');

        if ($request->filled('q')) {
            $term = $request->q;
            $query->where(function ($subQuery) use ($term) {
                $subQuery
                    ->where('firstname', 'like', "%{$term}%")
                    ->orWhere('lastname', 'like', "%{$term}%")
                    ->orWhere('base_mobile', 'like', "%{$term}%")
                    ->orWhere('mobile', 'like', "%{$term}%");
            });
        }

        if ($request->filled('id')) {
            $ids = is_array($request->id) ? $request->id : [$request->id];
            $query->whereIn('id', $ids);
        }

        $customers = $query
            ->limit(25)
            ->get()
            ->map(function (Customer $customer) {
                $name = trim($customer->firstname . ' ' . $customer->lastname);
                $mobile = $customer->base_mobile ?: $customer->mobile;
                $text = $mobile ? "{$name} | {$mobile}" : $name;

                return [
                    'id' => $customer->id,
                    'text' => $text,
                ];
            });

        return response()->json(['results' => $customers]);
    }
}
