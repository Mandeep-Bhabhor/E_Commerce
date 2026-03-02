<?php

namespace App\Listeners;

use App\Events\OrderStatusUpdated;
use App\Mail\OrderStatusUpdated as MailOrderStatusUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendOrderStatusUpdated implements ShouldQueue
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
    public function handle(OrderStatusUpdated $event): void
    {
        //
        \Log::info(' status Listener running');

        try {
            Mail::to($event->item->order->user->email)
                ->send(new MailOrderStatusUpdated($event->item,$event->oldStatus));

            \Log::info('Status Update mail is sent');
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
        }
    }
}
