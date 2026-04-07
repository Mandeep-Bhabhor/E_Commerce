<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;
use PragmaRX\Google2FAQRCode\Google2FA as Google2FAQRCode;

class TwoFactorController extends Controller
{
    //

    public function setup()
    {
        $user = Auth::user();

        $google2fa = new Google2FA;
        $google2faQRCode = new Google2FAQRCode;
        // Generate secret if not exists
        if (! $user->google2fa_secret) {
            $secret = $google2fa->generateSecretKey();

            session(['2fa_secret' => $secret]); // temporary
        } else {
            $secret = $user->google2fa_secret;
        }

        $qr = $google2faQRCode->getQRCodeInline(
            'YourApp',
            $user->email,
            $secret
        );

        return view('2fa_setup', compact('qr', 'secret'));
    }

    public function verifySetup(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $user = Auth::user();

        $google2fa = new Google2FA;

        $secret = session('2fa_secret');

        if (! $secret) {
            return redirect()->route('login')->withErrors('Session expired');
        }

        $isValid = $google2fa->verifyKey($secret, $request->otp);

        if ($isValid) {
            $user->google2fa_secret = $secret;
            $user->is_2fa_enabled = true;
            $user->save();

            session()->forget('2fa_secret');

            return redirect()->route('customer.dashboard');
        }

        return back()->withErrors(['otp' => 'Invalid code']);
    }

    public function verifyPage()
    {
        return view('2fa_verify');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $user = Auth::user();

        $google2fa = new Google2FA;

        $isValid = $google2fa->verifyKey(
            $user->google2fa_secret,
            $request->otp
        );

        if ($isValid) {
            session(['2fa_verified' => true]);

            return redirect()->route('customer.dashboard');
        }

        return back()->withErrors(['otp' => 'Invalid code']);
    }
}
