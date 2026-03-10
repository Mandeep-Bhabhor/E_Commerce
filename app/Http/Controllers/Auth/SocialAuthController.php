<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Auth0\SDK\Auth0;
use Auth0\SDK\Configuration\SdkConfiguration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SocialAuthController extends Controller
{
    private function auth0(): Auth0
    {
        $config = new SdkConfiguration([
            'strategy'     => SdkConfiguration::STRATEGY_REGULAR,
            'domain'       => env('AUTH0_DOMAIN'),
            'clientId'     => env('AUTH0_CLIENT_ID'),
            'clientSecret' => env('AUTH0_CLIENT_SECRET'),
            'redirectUri'  => env('AUTH0_REDIRECT_URI'),
            'cookieSecret' => env('APP_KEY'),
        ]);

        return new Auth0($config);
    }

    // ── Detect provider from Auth0 subject claim ──────────────────────────────
    private function detectProvider(string $sub): string
    {
        if (str_starts_with($sub, 'google-oauth2|')) return 'google';
        if (str_starts_with($sub, 'facebook|'))      return 'facebook';
        return 'social';
    }

    // ── Google Redirect ───────────────────────────────────────────────────────
    public function redirectToGoogle()
    {
        $url = $this->auth0()->login(null, [
            'connection' => 'google-oauth2',
            'prompt'     => 'select_account',
        ]);

        return redirect($url);
    }

    // ── Facebook Redirect ─────────────────────────────────────────────────────
    public function redirectToFacebook()
    {
        $url = $this->auth0()->login(null, [
            'connection' => 'facebook',
        ]);

        return redirect($url);
    }

    // ── Shared Callback (handles both Google & Facebook) ──────────────────────
    public function handleCallback(Request $request)
    {
        try {
            $auth0 = $this->auth0();
            $auth0->exchange();
            $profile = $auth0->getUser();

            // Facebook may withhold email if user denies permission
            if (!$profile || empty($profile['email'])) {
                return redirect()->route('login')
                    ->withErrors(['msg' => 'Could not retrieve your email. Please allow email access and try again.']);
            }

            // Detect which provider was used from the sub claim
            $provider = $this->detectProvider($profile['sub'] ?? '');

            // Default name fallback differs per provider
            $defaultName = $provider === 'facebook' ? 'Facebook User' : 'Google User';

            $user = User::firstOrCreate(
                ['email' => $profile['email']],
                [
                    'name'              => $profile['name'] ?? $defaultName,
                    'password'          => bcrypt(Str::random(32)),
                    'email_verified_at' => now(),
                    'role'              => 'customer',
                    'social_provider'   => $provider,
                    'social_id'         => $profile['sub'] ?? null,
                    'avatar'            => $profile['picture'] ?? null,
                ]
            );

            // If user already exists, update their social info & avatar
            if (!$user->wasRecentlyCreated) {
                $user->update([
                    'social_provider' => $provider,
                    'social_id'       => $profile['sub'] ?? $user->social_id,
                    'avatar'          => $profile['picture'] ?? $user->avatar,
                ]);
            }

            // Block admins from using social login
            if ($user->role === 'admin') {
                return redirect()->route('login')
                    ->withErrors(['msg' => 'Admin accounts must use email and password.']);
            }

            Auth::guard('web')->login($user, remember: true);
            $request->session()->regenerate();

            return redirect()->route('customer.dashboard');

        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('login')
                ->withErrors(['msg' => 'Social login failed: ' . $e->getMessage()]);
        }
    }
}