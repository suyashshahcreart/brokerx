<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Customer;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class CustomerAuthController extends Controller
{
    protected SmsService $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'base_mobile' => ['required', 'numeric', 'digits_between:6,15'],
            'country_id' => ['required', 'exists:countries,id'],
            'email' => ['required', 'email', 'max:255', 'unique:customers,email'],
        ], [
            'base_mobile.required' => 'Mobile number is required.',
            'base_mobile.digits_between' => 'Mobile number must be between 6 and 15 digits.',
            'country_id.required' => 'Country is required.',
        ]);

        $country = null;
        if ($validator->passes()) {
            $country = Country::find($request->country_id);
            if ($country) {
                $dialCode = ltrim($country->dial_code, '+');
                $fullMobile = $dialCode . $request->base_mobile;
                if (Customer::where('mobile', $fullMobile)->exists()) {
                    $validator->errors()->add('base_mobile', 'This mobile number already exists.');
                }
            } else {
                $validator->errors()->add('country_id', 'Selected country does not exist.');
            }
        }

        $validated = $validator->validate();

        $firstname = $validated['firstname'];
        $lastname = $validated['lastname'];

        $dialCode = ltrim($country->dial_code, '+');
        $fullMobile = $dialCode . $validated['base_mobile'];

        $customer = Customer::create([
            'firstname' => $firstname,
            'lastname' => $lastname,
            'mobile' => $fullMobile,
            'base_mobile' => $validated['base_mobile'],
            'country_code' => strtoupper($country->country_code),
            'dial_code' => $country->dial_code,
            'country_id' => $country->id,
            'email' => $validated['email'],
            'password' => Hash::make(Str::random(32)),
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Customer registered successfully. Please login with OTP.',
            'data' => [
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'firstname' => $customer->firstname,
                    'lastname' => $customer->lastname,
                    'base_mobile' => $customer->base_mobile,
                    'mobile' => $customer->mobile,
                    'email' => $customer->email,
                    'country_id' => $customer->country_id,
                    'dial_code' => $customer->dial_code,
                    'country_code' => $customer->country_code,
                    'mobile_verified' => !is_null($customer->mobile_verified_at),
                ],
            ],
        ], 201);
    }

    public function sendOtp(Request $request)
    {
        $validated = $request->validate([
            'base_mobile' => ['required', 'numeric', 'digits_between:6,15'],
            'country_id' => ['required', 'exists:countries,id'],
        ]);

        $customer = Customer::where('base_mobile', $validated['base_mobile'])
            ->where('country_id', $validated['country_id'])
            ->first();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found. Please register first.',
            ], 404);
        }

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $customer->update([
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(5),
            'otp_verified_at' => null,
        ]);

        $smsSent = false;
        try {
            $mobileForSms = ltrim($customer->dial_code ?? '', '+') . $customer->base_mobile;

            $this->smsService->send(
                $mobileForSms,
                'login_otp',
                ['OTP' => $otp],
                [
                    'type' => 'manual',
                    'reference_type' => Customer::class,
                    'reference_id' => $customer->id,
                    'notes' => 'Customer login OTP',
                ]
            );

            $smsSent = true;
        } catch (\RuntimeException $e) {
            Log::warning('Customer OTP SMS not sent (gateway issue)', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
            ]);
        } catch (\Exception $e) {
            Log::warning('Customer OTP SMS failed', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => $smsSent
                ? 'OTP sent to your mobile number.'
                : 'OTP generated. SMS sending failed. Please contact support if needed.',
            'sms_sent' => $smsSent,
            'data' => [
                'customer_id' => $customer->id,
            ],
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $validated = $request->validate([
            'base_mobile' => ['required', 'numeric', 'digits_between:6,15'],
            'country_id' => ['required', 'exists:countries,id'],
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $customer = Customer::where('base_mobile', $validated['base_mobile'])
            ->where('country_id', $validated['country_id'])
            ->first();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found. Please register first.',
            ], 404);
        }

        if (!$customer->otp) {
            return response()->json([
                'success' => false,
                'message' => 'No OTP found. Please request a new one.',
            ], 410);
        }

        if ($customer->otp_expires_at && now()->isAfter($customer->otp_expires_at)) {
            return response()->json([
                'success' => false,
                'message' => 'OTP has expired. Please request a new one.',
            ], 410);
        }

        if ($customer->otp !== $validated['otp']) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP. Please try again.',
            ], 422);
        }

        $customer->update([
            'otp' => null,
            'otp_expires_at' => null,
            'otp_verified_at' => now(),
            'mobile_verified_at' => $customer->mobile_verified_at ?? now(),
        ]);

        $token = $customer->createToken('customer-api')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'OTP verified successfully.',
            'token_type' => 'Bearer',
            'token' => $token,
            'data' => [
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'firstname' => $customer->firstname,
                    'lastname' => $customer->lastname,
                    'base_mobile' => $customer->base_mobile,
                    'mobile' => $customer->mobile,
                    'email' => $customer->email,
                    'country_id' => $customer->country_id,
                    'dial_code' => $customer->dial_code,
                    'country_code' => $customer->country_code,
                    'mobile_verified' => !is_null($customer->mobile_verified_at),
                ],
            ],
        ]);
    }
}
