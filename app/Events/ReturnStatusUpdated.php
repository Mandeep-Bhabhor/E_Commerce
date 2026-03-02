<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReturnStatusUpdated
{
    use Dispatchable, SerializesModels;

    public $item;

    public function __construct($item)
    {

        $this->item = $item;

    }
}
