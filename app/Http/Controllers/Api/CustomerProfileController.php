<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CustomerProfileController extends Controller
{
    public function show(Request $request)
    {
        $customer = $request->user();
        if (!$customer instanceof Customer) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $profilePhotoUrl = $customer->profile_photo
            ? Storage::disk('public')->url($customer->profile_photo)
            : null;
        $coverPhotoUrl = $customer->cover_photo
            ? Storage::disk('public')->url($customer->cover_photo)
            : null;

        return response()->json([
            'success' => true,
            'data' => [
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'firstname' => $customer->firstname,
                    'lastname' => $customer->lastname,
                    'email' => $customer->email,
                    'base_mobile' => $customer->base_mobile,
                    'mobile' => $customer->mobile,
                    'country_id' => $customer->country_id,
                    'dial_code' => $customer->dial_code,
                    'country_code' => $customer->country_code,
                    'company_name' => $customer->company_name,
                    'company_website' => $customer->company_website,
                    'designation' => $customer->designation,
                    'tag_line' => $customer->tag_line,
                    'social_link' => $customer->social_link,
                    'profile_photo' => $customer->profile_photo,
                    'cover_photo' => $customer->cover_photo,
                    'profile_photo_url' => $profilePhotoUrl,
                    'cover_photo_url' => $coverPhotoUrl,
                ],
            ],
        ]);
    }

    public function update(Request $request)
    {
        $customer = $request->user();
        if (!$customer instanceof Customer) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        if ($request->has('social_link') && is_string($request->input('social_link'))) {
            $decoded = json_decode($request->input('social_link'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid social_link JSON. Please send valid JSON or an array.',
                ], 422);
            }
            $request->merge(['social_link' => $decoded]);
        }

        if ($request->has('mobile') && !$request->has('base_mobile')) {
            $request->merge(['base_mobile' => $request->input('mobile')]);
        }

        $validated = $request->validate([
            'firstname' => ['nullable', 'string', 'max:255'],
            'lastname' => ['nullable', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:customers,email,' . $customer->id],
            'base_mobile' => ['nullable', 'numeric', 'digits_between:6,15'],
            'mobile' => ['nullable', 'numeric', 'digits_between:6,15'],
            'country_id' => ['nullable', 'exists:countries,id'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'company_website' => ['nullable', 'url', 'max:255'],
            'designation' => ['nullable', 'string', 'max:255'],
            'tag_line' => ['nullable', 'string', 'max:255'],
            'social_link' => ['nullable', 'array'],
            'social_link.*' => ['nullable', 'url'],
        ]);

        if (array_key_exists('name', $validated)) {
            $nameParts = preg_split('/\s+/', trim($validated['name']), 2);
            if (!array_key_exists('firstname', $validated)) {
                $validated['firstname'] = $nameParts[0] ?? null;
            }
            if (!array_key_exists('lastname', $validated)) {
                $validated['lastname'] = $nameParts[1] ?? null;
            }
            unset($validated['name']);
        }

        $baseMobile = $validated['base_mobile'] ?? null;
        $countryId = $validated['country_id'] ?? null;
        if ($baseMobile !== null || $countryId !== null) {
            $resolvedCountryId = $countryId ?? $customer->country_id;
            if (!$resolvedCountryId) {
                return response()->json([
                    'success' => false,
                    'message' => 'country_id is required when updating mobile.',
                ], 422);
            }

            $country = Country::find($resolvedCountryId);
            if (!$country) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected country does not exist.',
                ], 422);
            }

            $resolvedBaseMobile = $baseMobile ?? $customer->base_mobile;
            if (!$resolvedBaseMobile) {
                return response()->json([
                    'success' => false,
                    'message' => 'mobile is required when updating country.',
                ], 422);
            }

            $dialCode = ltrim($country->dial_code, '+');
            $fullMobile = $dialCode . $resolvedBaseMobile;

            $mobileExists = Customer::where('mobile', $fullMobile)
                ->where('id', '!=', $customer->id)
                ->exists();
            if ($mobileExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'This mobile number already exists.',
                ], 422);
            }

            $validated['base_mobile'] = $resolvedBaseMobile;
            $validated['mobile'] = $fullMobile;
            $validated['country_id'] = $country->id;
            $validated['dial_code'] = $country->dial_code;
            $validated['country_code'] = strtoupper($country->country_code);
        }

        $customer->update($validated);

        $profilePhotoUrl = $customer->profile_photo
            ? Storage::disk('public')->url($customer->profile_photo)
            : null;
        $coverPhotoUrl = $customer->cover_photo
            ? Storage::disk('public')->url($customer->cover_photo)
            : null;

        return response()->json([
            'success' => true,
            'message' => 'Customer profile updated successfully.',
            'data' => [
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'firstname' => $customer->firstname,
                    'lastname' => $customer->lastname,
                    'email' => $customer->email,
                    'base_mobile' => $customer->base_mobile,
                    'mobile' => $customer->mobile,
                    'country_id' => $customer->country_id,
                    'dial_code' => $customer->dial_code,
                    'country_code' => $customer->country_code,
                    'company_name' => $customer->company_name,
                    'company_website' => $customer->company_website,
                    'designation' => $customer->designation,
                    'tag_line' => $customer->tag_line,
                    'social_link' => $customer->social_link,
                    'profile_photo' => $customer->profile_photo,
                    'cover_photo' => $customer->cover_photo,
                    'profile_photo_url' => $profilePhotoUrl,
                    'cover_photo_url' => $coverPhotoUrl,
                ],
            ],
        ]);
    }

    public function updateImages(Request $request)
    {
        $customer = $request->user();
        if (!$customer instanceof Customer) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $validated = $request->validate([
            'profile_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'cover_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:4096'],
        ]);

        if (!$request->hasFile('profile_photo') && !$request->hasFile('cover_photo')) {
            return response()->json([
                'success' => false,
                'message' => 'No images provided for update.',
            ], 422);
        }

        if ($request->hasFile('profile_photo')) {
            if ($customer->profile_photo && Storage::disk('public')->exists($customer->profile_photo)) {
                Storage::disk('public')->delete($customer->profile_photo);
            }
            $validated['profile_photo'] = $request->file('profile_photo')
                ->store('customers/profiles', 'public');
        }

        if ($request->hasFile('cover_photo')) {
            if ($customer->cover_photo && Storage::disk('public')->exists($customer->cover_photo)) {
                Storage::disk('public')->delete($customer->cover_photo);
            }
            $validated['cover_photo'] = $request->file('cover_photo')
                ->store('customers/covers', 'public');
        }

        $customer->update($validated);

        $profilePhotoUrl = $customer->profile_photo
            ? Storage::disk('public')->url($customer->profile_photo)
            : null;
        $coverPhotoUrl = $customer->cover_photo
            ? Storage::disk('public')->url($customer->cover_photo)
            : null;

        return response()->json([
            'success' => true,
            'message' => 'Customer images updated successfully.',
            'data' => [
                'customer' => [
                    'id' => $customer->id,
                    'profile_photo' => $customer->profile_photo,
                    'cover_photo' => $customer->cover_photo,
                    'profile_photo_url' => $profilePhotoUrl,
                    'cover_photo_url' => $coverPhotoUrl,
                ],
            ],
        ]);
    }
}
