<?php

namespace App\Listeners;

use App\Events\AdminApprove;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendAdminApproveMail
{
    public function handle(AdminApprove $event): void
    {
        $user = $event->user;

        Mail::send('admin_approve_mail', [
            'user' => $user
        ], function ($message) use ($user) {

            $message->to($user->email)
                ->subject('Your Account Has Been Approved');
        });
    }
}
