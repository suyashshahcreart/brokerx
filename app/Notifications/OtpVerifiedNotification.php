<?php

namespace App\Notifications;

use App\Models\VisitorsOtpRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OtpVerifiedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The OTP request instance.
     *
     * @var VisitorsOtpRequest
     */
    public $otpRequest;

    /**
     * Create a new notification instance.
     */
    public function __construct(VisitorsOtpRequest $otpRequest)
    {
        $this->otpRequest = $otpRequest;
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
     *
     * Handles both agent notifications and customer download link notifications.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Determine if this is for agent or customer
        $isAgent = $notifiable->is_admin ?? false;

        if ($isAgent) {
            return $this->buildAgentNotification($notifiable);
        } else {
            return $this->buildCustomerDownloadNotification($notifiable);
        }
    }

    /**
     * Build notification for agent/admin
     */
    private function buildAgentNotification(object $agent): MailMessage
    {
        $htmlContent = $this->buildAgentHtmlEmail();

        return (new MailMessage)
            ->subject('Visitor OTP Verification Completed')
            ->html($htmlContent)
            ->from(config('mail.from.address'), config('mail.from.name'));
    }

    /**
     * Build HTML email content for agent notification
     */
    private function buildAgentHtmlEmail(): string
    {
        $agentName = 'Agent'; // Default fallback
        $customerName = $this->otpRequest->visitors_name;
        $customerEmail = $this->otpRequest->visitors_email;
        $customerMobile = $this->otpRequest->visitors_mobile;
        $propertyName = $this->otpRequest->booking?->propertyType?->name ?? 'Property';

        return <<<HTML
        <html>
            <body style='font-family: Arial, sans-serif;'>
                <h2>Hello {$agentName},</h2>
                <p>You've received a new inquiry for your {$propertyName} property.</p>
                <p><strong>Customer Details:</strong></p>
                <ul>
                    <li>Name: {$customerName}</li>
                    <li>Email: {$customerEmail}</li>
                    <li>Mobile: {$customerMobile}</li>
                </ul>
                <p>Download link has been shared with the customer.</p>
                <hr>
                <p>Regards,<br>Proppik Team</p>
            </body>
        </html>
        HTML;
    }

    /**
     * Build notification for customer with download link
     */
    private function buildCustomerDownloadNotification(object $customer): MailMessage
    {
        $htmlContent = $this->buildCustomerHtmlEmail();

        return (new MailMessage)
            ->subject('Your Tour Materials Are Ready to Download')
            ->html($htmlContent)
            ->from(config('mail.from.address'), config('mail.from.name'));
    }

    /**
     * Build HTML email content for customer download link
     */
    private function buildCustomerHtmlEmail(): string
    {
        $visitorName = $this->otpRequest->visitors_name;
        $propertyName = $this->otpRequest->booking?->property_type_id 
            ? ($this->otpRequest->booking->propertyType?->name ?? 'Property') 
            : 'Property';
        $downloadLink = $this->otpRequest->download_link ?? '#';

        return <<<HTML
        <html>
            <body style='font-family: Arial, sans-serif;'>
                <h2>Hello {$visitorName},</h2>                    
                <p>Thank you for your inquiry about the property <strong>{$propertyName}</strong>.</p>
                <p>You can now download your document using the link below:</p>
                <p><a href='{$downloadLink}' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Download Document</a></p>
                <hr style='margin:20px 0;'>
                <p>Regards,<br>Proppik Team</p>
            </body>
        </html>
        HTML;
    }
}
