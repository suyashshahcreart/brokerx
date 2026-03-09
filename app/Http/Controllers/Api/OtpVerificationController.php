<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\agentNewInquery;
use App\Mail\visitorOtpMaile;
use App\Models\Tour;
use App\Models\VisitorsOtpRequest;
use App\Models\Customer;
use App\Models\Booking;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OtpVerificationController extends Controller
{
    protected SmsService $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Send OTP to customer via SMS and Email
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendOtp(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'customer_id' => ['required', 'exists:customers,id'],
                'booking_id' => ['required', 'exists:bookings,id'],
                'tour_id' => ['nullable', 'exists:tours,id'],
                'visitors_name' => ['required', 'string', 'max:255'],
                'visitors_mobile' => ['required', 'string', 'max:20'],
                'visitors_email' => ['required', 'email', 'max:255'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Check if customer exists
            $customer = Customer::find($request->customer_id);
            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer not found',
                ], 404);
            }

            // Check if booking exists
            $booking = Booking::find($request->booking_id);
            if (!$booking || $booking->customer_id != $request->customer_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found or does not belong to this customer',
                ], 404);
            }


            // Generate 6-digit OTP
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expiresAt = now()->addMinutes(5); // OTP valid for 5 minutes

            // Create OTP request record
            $otpRequest = VisitorsOtpRequest::create([
                'customer_id' => $request->customer_id,
                'booking_id' => $request->booking_id,
                'tour_id' => $request->tour_id,
                'visitors_name' => $request->visitors_name,
                'visitors_mobile' => $request->visitors_mobile,
                'visitors_email' => $request->visitors_email,
                'otp' => $otp,
                'status' => 'pending',
                'otp_expires_at' => $expiresAt,
            ]);

            // Send OTP via SMS
            // $this->sendOtpViaSms($otpRequest, $otp);

            // Send OTP via Email
            $this->sendOtpViaEmail($otpRequest, $otp);

            return response()->json([
                'success' => true,
                'message' => 'OTP sent successfully to your email and mobile',
                'data' => [
                    'otp_request_id' => $otpRequest->id,
                    'expires_in' => 300, // 5 minutes in seconds
                    'visitors_mobile' => $this->maskMobileNumber($request->visitors_mobile),
                    'visitors_email' => $this->maskEmail($request->visitors_email),
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error sending OTP', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP. Please try again later.',
            ], 500);
        }
    }

    /**
     * Verify OTP and send notifications
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyOtp(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'customer_id' => ['required', 'exists:customers,id'],
                'booking_id' => ['required', 'exists:bookings,id'],
                'tour_id' => ['nullable', 'exists:tours,id'],
                'visitors_name' => ['required', 'string', 'max:255'],
                'visitors_mobile' => ['required', 'string', 'max:20'],
                'visitors_email' => ['required', 'email', 'max:255'],
                'otp' => ['required', 'string', 'size:6'],
                'download_link' => ['nullable', 'url'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Get latest pending OTP request by mobile or email
            $otpRequest = VisitorsOtpRequest::where('customer_id', $request->customer_id)
                ->where('booking_id', $request->booking_id)
                ->where('status', 'pending')
                ->where(function ($query) use ($request) {
                    $query->where('visitors_mobile', $request->visitors_mobile)
                        ->orWhere('visitors_email', $request->visitors_email);
                })->with(['customer', 'booking', 'tour'])
                ->latest()
                ->first();

            if (!$otpRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'No pending OTP request found, send Again.',
                ], 404);
            }

            // Check if OTP is expired
            if ($otpRequest->isExpired()) {
                $otpRequest->markAsExpired();
                return response()->json([
                    'success' => false,
                    'message' => 'OTP has expired. Please request a new OTP.',
                ], 410);
            }

            // Check attempts limit
            if ($otpRequest->isMaxAttemptsExceeded()) {
                $otpRequest->markAsFailed();
                return response()->json([
                    'success' => false,
                    'message' => 'Maximum verification attempts exceeded. Please request a new OTP.',
                ], 429);
            }

            // Increment attempt count
            $otpRequest->incrementAttempt();

            // Verify OTP
            if ($otpRequest->otp !== $request->otp) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid OTP',
                    'attempts_remaining' => 5 - $otpRequest->attempt_count,
                ], 401);
            }

            // Update OTP request as verified
            if ($request->download_link) {
                $otpRequest->update(['download_link' => $request->download_link]);
            }
            $otpRequest->markAsVerified();

            // Get customer and booking details
            $customer = $otpRequest->customer;
            $tour = Tour::select(['name'])->find($otpRequest->tour_id);

            // Send notification to agent (admin)
            $this->notifyAgent($otpRequest,$customer,$tour);

            // Send download link to customer
            // $this->sendDownloadLinkToCustomer($otpRequest);

            Log::info('OTP verified successfully', context: [
                'customer_id' => $request->customer_id,
                'booking_id' => $request->booking_id,
                'otp_request_id' => $otpRequest->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'OTP verified successfully',
                'data' => [
                    'otp_request_id' => $otpRequest->id,
                    'customer_name' => $customer->name,
                    'download_link' => $otpRequest->download_link,
                    'verified_at' => $otpRequest->verified_at,
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error verifying OTP', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to verify OTP. Please try again later.',
            ], 500);
        }
    }

    /**
     * Send OTP via SMS
     *
     * @param VisitorsOtpRequest $otpRequest
     * @param string $otp
     */
    private function sendOtpViaSms(VisitorsOtpRequest $otpRequest, string $otp): void
    {
        try {
            $this->smsService->send(
                $otpRequest->visitors_mobile,
                'login_otp',
                [
                    'visitors_name' => $otpRequest->visitors_name,
                    'otp' => $otp,
                ],
                [
                    'reference_type' => 'VisitorsOtpRequest',
                    'reference_id' => $otpRequest->id,
                ]
            );

            $otpRequest->update(['otp_sent_via_sms' => true]);
            Log::info('OTP sent via SMS', [
                'mobile' => $otpRequest->visitors_mobile,
                'otp_request_id' => $otpRequest->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send OTP via SMS', [
                'error' => $e->getMessage(),
                'mobile' => $otpRequest->visitors_mobile,
            ]);
        }
    }

    /**
     * Send OTP via Email
     *
     * @param VisitorsOtpRequest $otpRequest
     * @param string $otp
     */
    private function sendOtpViaEmail(VisitorsOtpRequest $otpRequest, string $otp): void
    {
        try {
            // Get customer and verify they exist
            $customer = $otpRequest->customer;

            if (!$customer) {
                Log::warning('Customer not found for OTP notification', [
                    'customer_id' => $otpRequest->customer_id,
                    'otp_request_id' => $otpRequest->id,
                ]);
                return;
            }

            // Send email using Laravel notification (Notifiable trait)
            // This follows Laravel standard for sending emails via notifications
            Mail::to($otpRequest->visitors_email)
                ->send(new visitorOtpMaile($otp, $otpRequest));

            // Update the flag indicating email was sent
            $otpRequest->update(['otp_sent_via_email' => true]);

        } catch (\Exception $e) {
            Log::error('Failed to send OTP via Email', [
                'error' => $e->getMessage(),
                'email' => $otpRequest->visitors_email,
                'otp_request_id' => $otpRequest->id,
                'customer_id' => $otpRequest->customer_id,
            ]);
        }
    }

    /**
     * Notify agent about successful verification
     *
     * @param VisitorsOtpRequest $otpRequest
     */
    private function notifyAgent(VisitorsOtpRequest $otpRequest,Customer $customer,Tour $tour): void
    {
        try {
            if (!$customer) {
                Log::warning('No admin agents found for notification', [
                    'otp_request_id' => $otpRequest->id,
                ]);
                return;
            }
            // send the email to agent inquiry
            Mail::to($customer->email)
                ->send(new agentNewInquery($otpRequest,$tour));
            // Mark notification as sent
            $otpRequest->markNotificationAsSent();

        } catch (\Exception $e) {
            Log::error('Failed to notify agents', [
                'error' => $e->getMessage(),
                'otp_request_id' => $otpRequest->id,
            ]);
        }
    }

    /**
     * Send download link to customer
     *
     * @param VisitorsOtpRequest $otpRequest
     */
    private function sendDownloadLinkToCustomer(VisitorsOtpRequest $otpRequest): void
    {
        try {
            if (!$otpRequest->download_link) {
                Log::warning('No download link provided for customer', [
                    'otp_request_id' => $otpRequest->id,
                ]);
                return;
            }

            // Get customer and verify they exist
            $customer = $otpRequest->customer;

            if (!$customer) {
                Log::warning('Customer not found for download link notification', [
                    'customer_id' => $otpRequest->customer_id,
                    'otp_request_id' => $otpRequest->id,
                ]);
                return;
            }

            // Send email using Laravel notification with download link
            // This follows Laravel standard for sending emails via notifications
            $customer->notify(new OtpVerifiedNotification($otpRequest));

            Log::info('Download link sent to customer successfully', [
                'email' => $otpRequest->visitors_email,
                'otp_request_id' => $otpRequest->id,
                'customer_id' => $otpRequest->customer_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send download link to customer', [
                'error' => $e->getMessage(),
                'email' => $otpRequest->visitors_email,
                'otp_request_id' => $otpRequest->id,
                'customer_id' => $otpRequest->customer_id,
            ]);
        }
    }

    /**
     * Mask mobile number for security
     *
     * @param string $mobile
     * @return string
     */
    private function maskMobileNumber(string $mobile): string
    {
        if (strlen($mobile) < 4) {
            return str_repeat('*', strlen($mobile));
        }
        return str_repeat('*', strlen($mobile) - 4) . substr($mobile, -4);
    }

    /**
     * Mask email for security
     *
     * @param string $email
     * @return string
     */
    private function maskEmail(string $email): string
    {
        list($name, $domain) = explode('@', $email);
        if (strlen($name) <= 2) {
            return str_repeat('*', strlen($name)) . '@' . $domain;
        }
        return substr($name, 0, 2) . str_repeat('*', strlen($name) - 2) . '@' . $domain;
    }
}
