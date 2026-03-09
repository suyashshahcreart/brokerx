<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class visitorVerifyOtp extends Mailable
{
    use Queueable, SerializesModels;
    public $otpRequest;
    public $link;
    public $tour;
    /**
     * Create a new message instance.
     */
    public function __construct($otpRequest, $link, $tour)
    {
        $this->otpRequest = $otpRequest;
        $this->link = $link;
        $this->tour = $tour;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'OTP Verified - Download Link - Proppik',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mailTemplate.visitor_verify_otp',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
