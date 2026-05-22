<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;

class PhoneLoginController extends Controller
{
    // 1. Show the Phone Login Form
    public function create()
    {
        return view('auth.login-phone');
    }

    // 2. Process the Phone Number and Send OTP
    public function store(Request $request)
    {
        $request->validate([
            'phone_number' => ['required', 'string'],
        ]);

        $phone = preg_replace('/\D/', '', $request->phone_number);

        // Remove country code if present
        if (str_starts_with($phone, '91')) {
            $phone = substr($phone, 2);
        }

        $user = User::where('phone_number', $phone)->first();        //dd($user);
        if (!$user) {
            return back()->withErrors(['phone_number' => 'This phone number is not registered. Please sign up first.']);
        }

        // Generate OTP
        $otp = rand(100000, 999999);
        $user->otp = $otp;
        $user->otp_expires_at = now()->addMinutes(5);
        $user->save();
        $twilioPhone = '+91' . $phone;
        // Send Twilio SMS
        try {
            $twilio = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));
            $twilio->messages->create(
                $twilioPhone,
                [
                    "from" => env('TWILIO_PHONE_NUMBER'),
                    "body" => "Welcome back! Your login code is: {$otp}. It will expire in 5 minutes."
                ]
            );
        } catch (\Exception $e) {
            Log::error('Twilio Login SMS Failed: ' . $e->getMessage());
            return back()->withErrors(['phone_number' => 'Failed to send SMS. Please check the number.']);
        }

        // Put user in waiting room and redirect to the universal OTP page
        session(['pending_otp_user_id' => $user->id]);
        session()->save();

        return redirect()->route('otp.verify.page');
    }
}
