<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FirebaseService;
use Illuminate\Http\Request;

class FavouriteController extends Controller
{
    /*
    |==========================================================================
    | FAVOURITE USERS (CHATS)
    |==========================================================================
    |
    | Firestore path: admin_favourites/{adminId}/users/{chatId}
    |
    */

    /*
    |--------------------------------------------------------------------------
    | ADD USER TO FAVOURITES
    |--------------------------------------------------------------------------
    |
    | POST /api/admin/favourites/users
    | Body: { "chat_id": "user_5", "customer_name": "Ravi" }
    |
    */

    public function addUser(Request $request, FirebaseService $firebase)
    {
        $request->validate([
            'chat_id'       => 'required|string',
            'customer_name' => 'nullable|string',
        ]);

        $adminId = (string) auth()->id();
        $chatId  = $request->chat_id;

        /*
        |----------------------------------------------------------------------
        | Resolve customer name: use provided name, or look up from MySQL
        |----------------------------------------------------------------------
        */
        $customerName = $request->customer_name;

        if (! $customerName) {
            // Extract userId from chatId: "user_5" → "5"
            $userId = str_replace('user_', '', $chatId);
            $user = \App\Models\User::find($userId);
            $customerName = $user ? $user->name : $chatId;
        }

        $firebase->setDocument(
            'admin_favourites/' . $adminId . '/users/' . $chatId,
            [
                'chat_id'       => $chatId,
                'customer_name' => $customerName,
                'added_at'      => new \DateTime('now', new \DateTimeZone('UTC')),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'User added to favourites',
            'customer_name' => $customerName,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | REMOVE USER FROM FAVOURITES
    |--------------------------------------------------------------------------
    |
    | DELETE /api/admin/favourites/users/{chatId}
    |
    */

    public function removeUser(Request $request, FirebaseService $firebase)
    {
        $request->validate([
            'chat_id' => 'required|string',
        ]);

        $adminId = (string) auth()->id();
        $chatId  = $request->chat_id;

        $firebase->deleteDocument(
            'admin_favourites/' . $adminId . '/users/' . $chatId
        );

        return response()->json([
            'success' => true,
            'message' => 'User removed from favourites',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | LIST FAVOURITE USERS
    |--------------------------------------------------------------------------
    |
    | GET /api/admin/favourites/users
    |
    */

    public function listUsers(FirebaseService $firebase)
    {
        $adminId = (string) auth()->id();

        $users = $firebase->listDocuments(
            'admin_favourites/' . $adminId . '/users'
        );

        return response()->json([
            'success' => true,
            'favourites' => $users,
        ]);
    }

    /*
    |==========================================================================
    | FAVOURITE MESSAGES (STARRED)
    |==========================================================================
    |
    | Firestore path: admin_favourites/{adminId}/messages/{messageId}
    |
    */

    /*
    |--------------------------------------------------------------------------
    | STAR A MESSAGE
    |--------------------------------------------------------------------------
    |
    | POST /api/admin/favourites/messages
    | Body: {
    |     "chat_id": "user_5",
    |     "message_id": "abc123",
    |     "sender": "customer",
    |     "message": "Hello, I need help",
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

        $adminId   = (string) auth()->id();
        $messageId = $request->message_id;

        $firebase->setDocument(
            'admin_favourites/' . $adminId . '/messages/' . $messageId,
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
    | DELETE /api/admin/favourites/messages/{messageId}
    |
    */

    public function removeMessage(Request $request, FirebaseService $firebase)
    {
        $request->validate([
            'message_id' => 'required|string',
        ]);

        $adminId   = (string) auth()->id();
        $messageId = $request->message_id;

        $firebase->deleteDocument(
            'admin_favourites/' . $adminId . '/messages/' . $messageId
        );

        return response()->json([
            'success' => true,
            'message' => 'Message unstarred',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | LIST STARRED MESSAGES
    |--------------------------------------------------------------------------
    |
    | GET /api/admin/favourites/messages
    | Optional query param: ?chat_id=user_5 (filter by chat)
    |
    */

    public function listMessages(Request $request, FirebaseService $firebase)
    {
        $adminId = (string) auth()->id();

        $messages = $firebase->listDocuments(
            'admin_favourites/' . $adminId . '/messages'
        );

        // Optional filter by chat_id
        if ($request->has('chat_id')) {
            $chatId = $request->chat_id;
            $messages = array_filter($messages, function ($msg) use ($chatId) {
                return ($msg['chat_id'] ?? '') === $chatId;
            });
            $messages = array_values($messages);
        }

        return response()->json([
            'success'  => true,
            'messages' => $messages,
        ]);
    }
}
