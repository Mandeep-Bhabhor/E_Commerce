<?php

namespace App\Services;

use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected Client $twilio;

    public function __construct()
    {
        $this->twilio = new Client(
            env('TWILIO_SID'),
            env('TWILIO_AUTH_TOKEN')
        );
    }

    public function sendMessage(
        string $phone,
        string $message,
        ?string $mediaUrl = null
    ): bool {

        try {

            // Keep only numbers
            $phone = preg_replace('/\D/', '', $phone);

            // Remove India country code if already exists
            if (str_starts_with($phone, '91')) {
                $phone = substr($phone, 2);
            }

            // Validate Indian mobile number
            if (strlen($phone) !== 10) {

                Log::warning("Invalid WhatsApp number: {$phone}");

                return false;
            }

            // Base message data
            $data = [
                'from' => env('TWILIO_WHATSAPP_NUMBER'),
                'body' => $message,
            ];

            // Add media if available
            if (!empty($mediaUrl)) {
                // dd($mediaUrl);
                $data['mediaUrl'] = [$mediaUrl];
            }
            //dd($data);
            // Send message
            $this->twilio->messages->create(
                'whatsapp:+91' . $phone,
                $data
            );

            return true;
        } catch (\Exception $e) {

            dd($e->getMessage());

            return false;
        }
    }
}
