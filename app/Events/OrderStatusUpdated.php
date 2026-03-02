<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated
{
    use Dispatchable, SerializesModels;

    public $item;

    public $oldStatus;

    public function __construct($item, $oldStatus)
    {
        $this->item = $item;
        $this->oldStatus = $oldStatus;

    }
}
