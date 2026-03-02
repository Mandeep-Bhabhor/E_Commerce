<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Mail\OrderMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendOrderEmail implements ShouldQueue
{ 
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //

    }
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(OrderPlaced $event): void
    {
        \Log::info('Listener running');

        try {
            Mail::to('mandeepmandeep06786@gmail.com')
                ->send(new OrderMail($event->order));

            \Log::info('Mail sent');
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
        }
    }
}
