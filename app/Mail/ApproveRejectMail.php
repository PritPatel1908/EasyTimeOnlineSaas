<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApproveRejectMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $record;

    public $user;

    public $updated_by;

    public $status;

    public $hasNextLeavelFlow;

    public $levelNumber;

    public $nextLevel;

    /**
     * Create a new message instance.
     */
    public function __construct($record, $user, $authUserName, $status, $hasNextLeavelFlow, $levelNumber, $nextLevel = null)
    {
        $this->record = $record;
        $this->user = $user;
        $this->updated_by = $authUserName;
        $this->status = $status;
        $this->hasNextLeavelFlow = $hasNextLeavelFlow;
        $this->levelNumber = $levelNumber;
        $this->nextLevel = $nextLevel;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pending Request Approve/Reject',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mails.notify-mail',
            with: ['record' => $this->record, 'user' => $this->user, 'status' => $this->status, 'updated_by' => $this->updated_by, 'has_next_leavel_flow' => $this->hasNextLeavelFlow, 'level_number' => $this->levelNumber, 'next_level' => $this->nextLevel],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
