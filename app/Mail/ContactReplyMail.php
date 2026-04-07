<?php

namespace App\Mail;

use App\Models\Contact;
use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public $contact;

    public $replyText;

    public function __construct(Contact $contact, $replyText)
    {
        $this->contact = $contact;
        $this->replyText = $replyText;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'RE: '.$this->contact->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contactReply', // We will create this view next
        );
    }
}
