<?php

namespace App\Listeners;

use App\Events\ReturnStatusUpdated;
use App\Mail\ReturnStatusUpdated as MailReturnStatusUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendReturnStatusUpdated implements ShouldQueue
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
    public function handle(ReturnStatusUpdated $event): void
    {
        try {
            Mail::to($event->item->order->user->email)
                ->send(new MailReturnStatusUpdated($event->item));

            \Log::info('Return Status Update mail is sent');
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
        }
    }
}
