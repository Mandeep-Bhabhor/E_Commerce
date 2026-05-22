<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Twilio\Rest\Client;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone_number' => ['nullable', 'string', 'max:10','min:10','unique:'.User::class], 
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number, 
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        // ==========================================
        // THE FORK IN THE ROAD
        // ==========================================
        
        // IF NO PHONE NUMBER: Log them in normally and skip OTP!
        if (empty($request->phone_number)) {
          //  Auth::login($user);
            
            // Change 'dashboard' to 'home' if your main page is named differently
            return redirect()->route('login'); 
        }


        // IF THEY PROVIDED A PHONE NUMBER: Do the OTP Twilio Flow
        $otp = rand(100000, 999999);
        $user->otp = $otp;
        $user->otp_expires_at = now()->addMinutes(5);
        $user->save();

        // Store ID in session temporarily (Do NOT log them in yet)
        session(['pending_otp_user_id' => $user->id]);
        session()->save();

        // ==========================================
        // TWILIO SMS LOGIC STARTS HERE
        // ==========================================
        try {
            $sid = env('TWILIO_SID');
            $token = env('TWILIO_AUTH_TOKEN');
            $twilioNumber = env('TWILIO_PHONE_NUMBER');

            $twilio = new Client($sid, $token);

            $twilio->messages->create(
                $request->phone_number, 
                [
                    'from' => $twilioNumber,
                    'body' => "Your verification code is: {$otp}. It will expire in 5 minutes.",
                ]
            );

        } catch (\Exception $e) {
            Log::error('Twilio SMS Failed: '.$e->getMessage());

            $user->delete();
            session()->forget('pending_otp_user_id');

            return back()->withInput()->withErrors(['phone_number' => 'We could not send an SMS to this number. Please check the format (e.g., +919876543210).']);
        }

        // If successful, redirect to the OTP input page
        return redirect()->route('otp.verify.page');
    }
}
