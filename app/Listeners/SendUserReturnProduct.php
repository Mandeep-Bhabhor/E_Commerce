<?php

namespace App\Listeners;

use App\Events\UserReturnProduct;
use App\Mail\UserReturnProduct as MailUserReturnProduct;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendUserReturnProduct implements ShouldQueue
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
    public function handle(UserReturnProduct $event): void
    {
        try {
            Mail::to('mandeepmandeep06786@gmail.com')
                ->send(new MailUserReturnProduct($event->item));

            \Log::info('User Requested For Product Return');
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
        }

    }
}
