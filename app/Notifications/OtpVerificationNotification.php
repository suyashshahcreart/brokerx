<?php

namespace App\Notifications;

use App\Models\VisitorsOtpRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OtpVerificationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The OTP request instance.
     *
     * @var VisitorsOtpRequest
     */
    public $otpRequest;

    /**
     * The OTP code.
     *
     * @var string
     */
    public $otp;

    /**
     * Create a new notification instance.
     */
    public function __construct(VisitorsOtpRequest $otpRequest, string $otp)
    {
        $this->otpRequest = $otpRequest;
        $this->otp = $otp;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $htmlContent = $this->buildHtmlEmail();

        return (new MailMessage)
            ->subject('Your OTP for Tour Booking Verification')
            ->html($htmlContent)
            ->from(config('mail.from.address'), config('mail.from.name'));
    }

    /**
     * Build HTML email content
     */
    private function buildHtmlEmail(): string
    {
        $visitorName = $this->otpRequest->visitors_name;
        $otp = $this->otp;

        return <<<HTML
        <html>
            <body style='font-family: Arial, sans-serif;'>
                <h2>Hello {$visitorName},</h2>
                <p>Your OTP code is:</p>
                <h3 style='background: #f0f0f0; padding: 10px; display: inline-block;'>{$otp}</h3>
                <p>This OTP is valid for 5 minutes only.</p>
                <p><strong>Please do not share this OTP with anyone.</strong></p>
                <hr>
                <p>Regards,<br>Proppik Team</p>
            </body>
        </html>
        HTML;
    }
}
