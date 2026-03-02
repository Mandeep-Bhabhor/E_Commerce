<?php

namespace App\Listeners;

use App\Events\MainOrderStatus;
use App\Mail\MainOrderStatus as MainOrderStatusMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendMainOrderStatus implements ShouldQueue
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
    public function handle(MainOrderStatus $event): void
    {
        //
        try{
            Mail::to($event->order->user->email)
            ->send(new MainOrderStatusMail($event->order,$event->newStatus,$event->oldStatus));
        } catch(\Exception $e) {
            \Log::error($e->getMessage());
        }
    }
}
