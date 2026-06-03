<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FirebaseService;
use Illuminate\Http\Request;

class CustomerFavouriteController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LIST STARRED MESSAGES
    |--------------------------------------------------------------------------
    |
    | GET /api/customer/favourites/messages
    |
    */

    public function listMessages(FirebaseService $firebase)
    {
        $userId = (string) auth()->id();

        $messages = $firebase->listDocuments(
            'customer_favourites/' . $userId . '/messages'
        );

        return response()->json([
            'success'  => true,
            'messages' => $messages,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | STAR A MESSAGE
    |--------------------------------------------------------------------------
    |
    | POST /api/customer/favourites/messages
    | Body: {
    |     "chat_id": "user_5",
    |     "message_id": "abc123",
    |     "sender": "customer" or "admin",
    |     "message": "Hello",
    |     "media_url": null
    | }
    |
    */

    public function addMessage(Request $request, FirebaseService $firebase)
    {
        $request->validate([
            'chat_id'    => 'required|string',
            'message_id' => 'required|string',
            'sender'     => 'required|string|in:admin,customer',
            'message'    => 'nullable|string',
            'media_url'  => 'nullable|string',
        ]);

        $userId    = (string) auth()->id();
        $messageId = $request->message_id;

        $firebase->setDocument(
            'customer_favourites/' . $userId . '/messages/' . $messageId,
            [
                'chat_id'    => $request->chat_id,
                'message_id' => $messageId,
                'sender'     => $request->sender,
                'message'    => $request->message ?? '',
                'media_url'  => $request->media_url,
                'starred_at' => new \DateTime('now', new \DateTimeZone('UTC')),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Message starred',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | UNSTAR A MESSAGE
    |--------------------------------------------------------------------------
    |
    | POST /api/customer/favourites/messages/remove
    | Body: { "message_id": "abc123" }
    |
    */

    public function removeMessage(Request $request, FirebaseService $firebase)
    {
        $request->validate([
            'message_id' => 'required|string',
        ]);

        $userId    = (string) auth()->id();
        $messageId = $request->message_id;

        $firebase->deleteDocument(
            'customer_favourites/' . $userId . '/messages/' . $messageId
        );

        return response()->json([
            'success' => true,
            'message' => 'Message unstarred',
        ]);
    }
}
