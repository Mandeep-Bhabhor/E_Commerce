<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated extends Mailable
{
    use SerializesModels;

    public $item;
    public $oldStatus;

    public function __construct($item,$oldStatus)
    {
        $this->item = $item;
        $this->oldStatus = $oldStatus;
    }

    public function build()
    {
        return $this->subject('Order Status Updated')
            ->view('order-status-updated');
    }
}
