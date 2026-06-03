<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\FirebaseService;

class AdminChatController extends Controller
{
    public function send(
        Request $request,
        FirebaseService $firebase
    ) {

        /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

        $request->validate([

            'chat_id' => 'required|string',

            'message' => 'nullable|string',

            'media' => 'nullable|file|max:5120',

            'reply_to_id'   => 'nullable|string',
            'reply_to_text' => 'nullable|string|max:200',

            // Location fields
            'lat'   => 'nullable|numeric|between:-90,90',
            'lng'   => 'nullable|numeric|between:-180,180',
            'label' => 'nullable|string|max:100',

        ]);

        /*
    |--------------------------------------------------------------------------
    | Variables
    |--------------------------------------------------------------------------
    */

        $chatId = $request->chat_id;

        $mediaUrl  = null;

        $mediaType = null;

        /*
        |--------------------------------------------------------------------------
        | LOCATION MESSAGE
        |--------------------------------------------------------------------------
        */

        $messageText = $request->message ?? '';

        if ($request->filled('lat') && $request->filled('lng')) {
            $messageText = json_encode([
                'lat'   => number_format((float) $request->lat, 6, '.', ''),
                'lng'   => number_format((float) $request->lng, 6, '.', ''),
                'label' => $request->label ?? ('Admin Location'),
            ]);
            $mediaType = 'location';
        }

        /*
    |--------------------------------------------------------------------------
    | CREATE PARENT CHAT DOCUMENT
    |--------------------------------------------------------------------------
    */

        $firebase->setDocument(
            'support_chats/' . $chatId,
            [
                'chat_id'    => $chatId,
                'admin_id'   => auth()->id(),
                'updated_at' => new \DateTime('now', new \DateTimeZone('UTC')),
            ]
        );

        /*
    |--------------------------------------------------------------------------
    | Upload File To Firebase Storage
    |--------------------------------------------------------------------------
    */

        if ($request->hasFile('media')) {

            $file =
                $request->file('media');

            $fileName =

                'admin_' .
                auth()->id() .
                '_' .
                time() .
                '_' .
                $file->getClientOriginalName();

            $bucket =
                $firebase
                ->storage
                ->getBucket();

            $bucket->upload(

                fopen(
                    $file->getRealPath(),
                    'r'
                ),

                [
                    'name' =>
                    'chat_media/' . $fileName
                ]

            );

            $encodedPath = urlencode(
                'chat_media/' . $fileName
            );

            $mediaUrl =

                'https://firebasestorage.googleapis.com/v0/b/' .

                $bucket->name() .

                '/o/' .

                $encodedPath .

                '?alt=media';

            $mediaType =
                $file->getMimeType();
        }

        /*
    |--------------------------------------------------------------------------
    | Save Message To Firestore
    |--------------------------------------------------------------------------
    */

        $firebase->addDocument(
            'support_chats/' . $chatId . '/messages',
            [
                'sender'        => 'admin',
                'message'       => $messageText,
                'media_url'     => $mediaUrl,
                'media_type'    => $mediaType,
                'delivered'     => true,
                'read'          => false,
                'reply_to_id'   => $request->reply_to_id,
                'reply_to_text' => $request->reply_to_text,
                'created_at'    => new \DateTime('now', new \DateTimeZone('UTC')),
            ]
        );

        /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

        return response()->json([

            'success' => true,

            'message' =>
            'Admin message sent successfully'

        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | LIST ALL CHATS (for admin)
    |--------------------------------------------------------------------------
    |
    | GET /api/admin/chat/users
    |
    | Returns all support_chats documents — one per customer.
    | Each entry includes customer_id, customer_name, updated_at, etc.
    |
    */

    public function chatUsers(FirebaseService $firebase)
    {
        $chats = $firebase->listDocuments('support_chats');

        return response()->json([
            'success' => true,
            'chats'   => $chats,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | GET MESSAGES FOR A SPECIFIC USER/CHAT
    |--------------------------------------------------------------------------
    |
    | GET /api/admin/chat/messages/{chatId}
    |
    | Returns all messages in support_chats/{chatId}/messages
    | sorted by created_at (Firestore default document order).
    |
    */

    public function messages($chatId, FirebaseService $firebase)
    {
        $messages = $firebase->listDocuments(
            'support_chats/' . $chatId . '/messages'
        );

        return response()->json([
            'success'  => true,
            'chat_id'  => $chatId,
            'messages' => $messages,
        ]);
    }
}
