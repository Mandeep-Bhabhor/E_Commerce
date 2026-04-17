<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AuthController extends Controller
{
    //
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone_number' => ['nullable', 'string', 'max:15', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        return response()->json(
            [
                $user, 'Successfully registereed the user',
            ]
        );
    }

    public function login(Request $request)
    {

        $credentials = $request->validate(
            [
                'email' => 'required|email',
                'password' => 'required',
            ]
        );

        if (! Auth::attempt($credentials)) {
            return response([
                'message' => 'wrong credentions ',
            ], 401);
        }

        $user = Auth::user();

        $token = $user->createToken(
            'tokkken',
            ['*'],
            Carbon::now()->addMinutes(10)
        )->plainTextToken;

        return response()->json([
            'token' => $token,
            $user,
            'message' => 'successfully logged in',
        ], 200);

    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    public function refreshtoken(Request $request)
    {

        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }

        // delete current token only
        $request->user()->currentAccessToken()?->delete();

        // create new token valid for 7 days
        $newToken = $user->createToken(
            'token',
            ['*'],
            Carbon::now()->addMinutes(10)
        )->plainTextToken;

        return response()->json([
            'message' => 'New token generated successfully',
            'token' => $newToken,
            'expires_in' => '30 min',
        ]);
    }
}
