<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MainOrderStatus extends Mailable
{
     use SerializesModels;

    public $order;
    public $oldStatus;
    public $newStatus;

    public function __construct($order,$newStatus,$oldStatus)
    {
        $this->order = $order;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    public function build()
    {
        return $this->subject('Main Order Status Updated')
            ->view('main-order-status-updated');
    }
}
