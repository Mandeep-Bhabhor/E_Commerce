<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReturnStatusUpdated extends Mailable
{
   use SerializesModels;

    public $item;

    public function __construct($item)
    {
        $this->item = $item;
    }

    public function build()
    {
        return $this->subject('Return Status Updated')
            ->view('return-status-updated');
    }
}
