<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OtpController extends Controller
{
    // 1. Show the OTP input form
    public function showVerifyPage()
    {
        // If they don't have a pending session, kick them back to login
        if (!session()->has('pending_otp_user_id')) {
            return redirect()->route('login')->withErrors(['msg' => 'Please log in first.']);
        }

        return view('auth.verify-otp');
    }

    // 2. Process the submitted OTP
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|numeric|digits:6',
        ]);

        $userId = session('pending_otp_user_id');

        if (!$userId) {
            return redirect()->route('login')->withErrors(['msg' => 'Session expired. Please try again.']);
        }

        $user = User::find($userId);

        // 3. Check if OTP matches and hasn't expired
        if ($user && $user->otp === $request->otp && now()->lessThanOrEqualTo($user->otp_expires_at)) {
            
            // Success! Clear the OTP from the database
            $user->otp = null;
            $user->otp_expires_at = null;
            $user->save();
    
            // Clear the waiting room session
            session()->forget('pending_otp_user_id');

            // Officially log them in
            Auth::guard('web')->login($user, true);
            $request->session()->regenerate();
   
            
            // Send them to their dashboard
            return redirect()->route('customer.dashboard');
        }

        // 4. If OTP is wrong or expired
        return back()->withErrors(['otp' => 'Invalid or expired OTP. Please try again.']);
    }
}