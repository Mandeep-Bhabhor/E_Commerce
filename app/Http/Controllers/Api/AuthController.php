<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Order;
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
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'phone_number' => ['nullable', 'string', 'max:15', 'unique:' . User::class],
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
                $user,
                'Successfully registereed the user',
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

         $user = User::where('email', $request['email'])->first();

        if ($user && $user->status !== 'active') {
            return response()->json([
                'message' => 'Your account is not approved yet',
            ]);
            // 'email' => 'Your account is not approved yet.',
        }

        if (! Auth::attempt($credentials)) {
            return response([
                'message' => 'wrong credentions ',
            ], 401);
        }

        $user = Auth::user();

        $token = $user->createToken(
            'tokkken',
            ['*'],
            Carbon::now()->addDays(1)
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
            Carbon::now()->addDays(1)
        )->plainTextToken;

        return response()->json([
            'message' => 'New token generated successfully',
            'token' => $newToken,
            'expires_in' => '1 day',
        ]);
    }
    public function userProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'filter' => 'nullable|in:pending,placed,delivered,cancelled'
        ]);

        $filter = $validated['filter'] ?? null;

        $addresses = Address::where('user_id', $user->id)->get();

        $orders = Order::where('user_id', $user->id)
            ->when($filter, function ($query) use ($filter) {
                $query->where('order_status', $filter);
            })
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'role' => $user->role,
                'avatar' => $user->avatar,
                'pfp' => $user->pfp,
                'social_provider' => $user->social_provider,
                'social_id' => $user->social_id,
                'created_at' => $user->created_at,
                'addresses' => $addresses,
                'orders' => $orders
            ]
        ], 200);
    }


    public function saveFcmToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string'
        ]);

        \Log::info('API FCM TOKEN SAVE');
        \Log::info($request->all());

        $user = $request->user();

        $user->update([
            'fcm_token' => $request->token
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'FCM token saved successfully'
        ]);
    }
}
