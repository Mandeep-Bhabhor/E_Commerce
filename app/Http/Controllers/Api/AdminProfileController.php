<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminProfileController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | GET CUSTOMER PROFILE
    |--------------------------------------------------------------------------
    |
    | Called by the admin chat panel to fetch a customer's profile picture
    | and basic info from MySQL. The "about" field is fetched client-side
    | directly from Firestore (users_profile/{userId}).
    |
    */

    public function profile(Request $request, $userId)
    {
        $user = User::find($userId);

        if (! $user) {
            return response()->json(
                ['message' => 'User not found'],
                404
            );
        }

        /*
        |--------------------------------------------------------------------------
        | BUILD PROFILE PICTURE URL
        |--------------------------------------------------------------------------
        |
        | The pfp column stores a relative path like "profiles/abc.jpg".
        | We serve it through Laravel's public storage disk.
        |
        */

        $pictureUrl = null;

        if ($user->pfp) {
            $pictureUrl = url('storage/' . $user->pfp);
        }

        return response()->json([
            'id'              => $user->id,
            'name'            => $user->name,
            'email'           => $user->email,
            'profile_picture' => $pictureUrl,
        ]);
    }
}
