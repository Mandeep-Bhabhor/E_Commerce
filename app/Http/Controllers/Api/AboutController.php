<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FirebaseService;
use Illuminate\Http\Request;

class AboutController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | GET ABOUT
    |--------------------------------------------------------------------------
    |
    | GET /api/about
    | Returns the authenticated user's "about" text from Firestore.
    | Path: users_profile/{userId}
    |
    */

    public function show(FirebaseService $firebase)
    {
        $userId = (string) auth()->id();

        try {
            $doc = $firebase->getDocument('users_profile/' . $userId);

            return response()->json([
                'success' => true,
                'about'   => $doc['about'] ?? null,
            ]);
        } catch (\Exception $e) {
            // Document may not exist yet — that's fine
            if (str_contains($e->getMessage(), '404') || str_contains($e->getMessage(), 'NOT_FOUND')) {
                return response()->json([
                    'success' => true,
                    'about'   => null,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch about: ' . $e->getMessage(),
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE ABOUT
    |--------------------------------------------------------------------------
    |
    | POST /api/about
    | Body: { "about": "Some text here" }
    | Saves/updates the "about" field in Firestore users_profile/{userId}.
    |
    */

    public function update(Request $request, FirebaseService $firebase)
    {
        $request->validate([
            'about' => 'nullable|string|max:500',
        ]);

        $userId = (string) auth()->id();
        $about  = $request->input('about', '');

        try {
            $firebase->setDocument(
                'users_profile/' . $userId,
                [
                    'about' => $about,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'About updated successfully',
                'about'   => $about,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update about: ' . $e->getMessage(),
            ], 500);
        }
    }
}
