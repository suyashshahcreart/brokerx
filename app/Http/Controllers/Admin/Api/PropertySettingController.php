<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\PropertySubType;
use App\Models\PropertyType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class PropertySettingController extends Controller
{
    /**
     * List property types for DataTables.
     */
    public function propertyTypes(Request $request)
    {
        $query = PropertyType::query()->withCount('subTypes');

        return DataTables::of($query)
            ->editColumn('icon', fn (PropertyType $type) => $type->icon ?? '')
            ->editColumn('created_at', fn (PropertyType $type) => optional($type->created_at)->format('Y-m-d H:i'))
            ->editColumn('updated_at', fn (PropertyType $type) => optional($type->updated_at)->format('Y-m-d H:i'))
            ->toJson();
    }

    /**
     * Return a lightweight list of property types for selects.
     */
    public function propertyTypeOptions()
    {
        return response()->json([
            'success' => true,
            'data' => PropertyType::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Create a new property type.
     */
    public function storePropertyType(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:property_types,name'],
            'icon' => ['nullable', 'string', 'max:255'],
        ]);

        $type = PropertyType::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Property type created successfully.',
            'data' => $type,
        ], 201);
    }

    /**
     * Update an existing property type.
     */
    public function updatePropertyType(Request $request, PropertyType $propertyType)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('property_types', 'name')->ignore($propertyType->id)],
            'icon' => ['nullable', 'string', 'max:255'],
        ]);

        $propertyType->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Property type updated successfully.',
            'data' => $propertyType->fresh('subTypes'),
        ]);
    }

    /**
     * Delete a property type if not in use by bookings.
     */
    public function deletePropertyType(PropertyType $propertyType)
    {
        $hasBookings = Booking::where('property_type_id', $propertyType->id)->exists();
        if ($hasBookings) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete property type because it is used by existing bookings.',
            ], 422);
        }

        $propertyType->delete();

        return response()->json([
            'success' => true,
            'message' => 'Property type deleted successfully.',
        ]);
    }

    /**
     * List property sub types for DataTables.
     */
    public function propertySubTypes(Request $request)
    {
        $query = PropertySubType::with('propertyType:id,name');

        return DataTables::of($query)
            ->addColumn('property_type_name', fn (PropertySubType $subType) => $subType->propertyType?->name ?? '-')
            ->filterColumn('property_type_name', function ($query, $keyword) {
                $query->whereHas('propertyType', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->editColumn('icon', fn (PropertySubType $subType) => $subType->icon ?? '')
            ->editColumn('created_at', fn (PropertySubType $subType) => optional($subType->created_at)->format('Y-m-d H:i'))
            ->editColumn('updated_at', fn (PropertySubType $subType) => optional($subType->updated_at)->format('Y-m-d H:i'))
            ->toJson();
    }

    /**
     * Create a new property sub type.
     */
    public function storePropertySubType(Request $request)
    {
        $data = $request->validate([
            'property_type_id' => ['required', 'exists:property_types,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('property_sub_types')->where(fn ($q) => $q->where('property_type_id', $request->property_type_id)),
            ],
            'icon' => ['nullable', 'string', 'max:255'],
        ]);

        $subType = PropertySubType::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Property sub type created successfully.',
            'data' => $subType->load('propertyType:id,name'),
        ], 201);
    }

    /**
     * Update an existing property sub type.
     */
    public function updatePropertySubType(Request $request, PropertySubType $propertySubType)
    {
        $data = $request->validate([
            'property_type_id' => ['required', 'exists:property_types,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('property_sub_types')
                    ->where(fn ($q) => $q->where('property_type_id', $request->property_type_id))
                    ->ignore($propertySubType->id),
            ],
            'icon' => ['nullable', 'string', 'max:255'],
        ]);

        $propertySubType->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Property sub type updated successfully.',
            'data' => $propertySubType->load('propertyType:id,name'),
        ]);
    }

    /**
     * Delete a property sub type if not in use by bookings.
     */
    public function deletePropertySubType(PropertySubType $propertySubType)
    {
        $hasBookings = Booking::where('property_sub_type_id', $propertySubType->id)->exists();
        if ($hasBookings) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete property sub type because it is used by existing bookings.',
            ], 422);
        }

        $propertySubType->delete();

        return response()->json([
            'success' => true,
            'message' => 'Property sub type deleted successfully.',
        ]);
    }
}
