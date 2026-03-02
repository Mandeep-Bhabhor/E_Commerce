<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserReturnProduct extends Mailable
{
    use Queueable, SerializesModels;

    public $item;

    public function __construct($item)
    {
        $this->item = $item;
    }

    public function build()
    {
        return $this->subject('Return Request Received')
            ->view('return-requested');
    }
}
