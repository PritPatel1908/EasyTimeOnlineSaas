<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class TenantEmailVerificationMail extends Mailable
{
    public function __construct(
        public readonly string $verificationUrl,
        public readonly string $userName,
        public readonly string $fromAddress,
        public readonly string $fromName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address($this->fromAddress, $this->fromName),
            subject: 'Verify your new email address',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tenant-email-verification',
        );
    }
}
