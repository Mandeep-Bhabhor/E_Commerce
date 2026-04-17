<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;
use PragmaRX\Google2FAQRCode\Google2FA as Google2FAQRCode;

class TwoFactorController extends Controller
{
    // ── Web: Show QR setup page ───────────────────────────────────────────────
    public function setup()
    {
        $user = Auth::user();

        // If 2FA is already fully enabled, skip setup and go straight to verify
        if ($user->google2fa_secret && $user->is_2fa_enabled) {
            return redirect()->route('2fa.verify');
        }

        $google2fa      = new Google2FA;
        $google2faQRCode = new Google2FAQRCode;

        // Always generate a fresh secret for the setup flow and keep it in session.
        // We do NOT write it to the DB yet — only after the user proves they scanned
        // it correctly in verifySetup().
        $secret = $google2fa->generateSecretKey();
        session(['2fa_secret' => $secret]);

        $qr = $google2faQRCode->getQRCodeInline(
            config('app.name', 'YourApp'),
            $user->email,
            $secret
        );

        return view('2fa_setup', compact('qr', 'secret'));
    }

    // ── Web: Confirm the scanned code and persist the secret ─────────────────
    public function verifySetup(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $user   = Auth::user();
        $secret = session('2fa_secret');

        if (! $secret) {
            // Session expired — send them back to start setup again
            return redirect()->route('2fa.setup')
                ->withErrors(['otp' => 'Session expired. Please scan the QR code again.']);
        }

        $google2fa = new Google2FA;
        $isValid   = $google2fa->verifyKey($secret, $request->otp);

        if ($isValid) {
            $user->google2fa_secret = $secret;
            $user->is_2fa_enabled   = true;
            $user->save();

            session()->forget('2fa_secret');
            session(['2fa_verified' => true]); // Mark as verified for this session too

            return redirect()->route('customer.dashboard');
        }

        return back()->withErrors(['otp' => 'Invalid code. Please try again.']);
    }

    // ── Web: Show OTP verify page ─────────────────────────────────────────────
    public function verifyPage()
    {
        return view('2fa_verify');
    }

    // ── Web: Verify OTP on login ──────────────────────────────────────────────
    // FIX: was returning JsonResponse — browser form POST needs a redirect, not JSON.
    public function verify(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $user      = Auth::user();
        $google2fa = new Google2FA;

        $isValid = $google2fa->verifyKey(
            $user->google2fa_secret,
            $request->otp
        );

        if ($isValid) {
            session(['2fa_verified' => true]);

            // If the request expects JSON (e.g. axios from a blade page), return JSON.
            // Otherwise do a proper redirect.
            if ($request->expectsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => '2FA verified',
                ]);
            }

            return redirect()->route('customer.dashboard');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid code',
            ], 422);
        }

        return back()->withErrors(['otp' => 'Invalid code. Please try again.']);
    }

    // ── API: Generate QR for setup ────────────────────────────────────────────
    public function apiSetup()
    {
        $user      = Auth::user();
        $google2fa = new Google2FA;

        // Guard: do not overwrite an already-active secret
        if ($user->google2fa_secret && $user->is_2fa_enabled) {
            return response()->json([
                'error' => '2FA is already enabled for this account.',
            ], 409);
        }

        $google2faQRCode = new Google2FAQRCode;
        $secret          = $google2fa->generateSecretKey();

        // Store in temp column until the user confirms with a valid OTP
        $user->temp_2fa_secret = $secret;
        $user->save();

        $qr = $google2faQRCode->getQRCodeInline(
            config('app.name', 'YourApp'),
            $user->email,
            $secret
        );

        return response()->json([
            'qr'     => $qr,
            'secret' => $secret,
        ]);
    }

    // ── API: Confirm OTP and activate 2FA ─────────────────────────────────────
    public function apiVerifySetup(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $user      = Auth::user();
        $google2fa = new Google2FA;

        if (! $user->temp_2fa_secret) {
            return response()->json([
                'error' => 'No pending 2FA setup found. Please call /2fa/setup first.',
            ], 400);
        }

        $isValid = $google2fa->verifyKey($user->temp_2fa_secret, $request->otp);

        if ($isValid) {
            $user->google2fa_secret = $user->temp_2fa_secret;
            $user->temp_2fa_secret  = null;
            $user->is_2fa_enabled   = true;
            $user->save();

            return response()->json([
                'message' => '2FA enabled successfully.',
            ]);
        }

        return response()->json([
            'error' => 'Invalid OTP.',
        ], 400);
    }

    // ── API: Verify OTP on login, exchange temp token for real token ──────────
    public function apiVerify(Request $request): JsonResponse
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $user      = $request->user(); // Sanctum reads Bearer token automatically
        $google2fa = new Google2FA;

        $isValid = $google2fa->verifyKey(
            $user->google2fa_secret,
            $request->otp
        );

        if ($isValid) {
            // Revoke the single-use temp token
            $request->user()->currentAccessToken()->delete();

            // Issue a real long-lived token
            $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'status'  => 'success',
                'message' => 'Logged in successfully.',
                'token'   => $token,
                'user'    => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'role'  => $user->role,
                ],
            ]);
        }

        return response()->json(['error' => 'Invalid OTP.'], 401);
    }
}