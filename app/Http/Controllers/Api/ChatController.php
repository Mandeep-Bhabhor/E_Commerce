<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FirebaseService;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function send(
        Request $request,
        FirebaseService $firebase
    ) {

        $request->validate([

            'chat_id' => 'required',

            'message' => 'nullable|string',

            'media'   => 'nullable|file|max:5120',

            'reply_to_id'   => 'nullable|string',
            'reply_to_text' => 'nullable|string|max:200',

            // Location fields
            'lat'   => 'nullable|numeric|between:-90,90',
            'lng'   => 'nullable|numeric|between:-180,180',
            'label' => 'nullable|string|max:100',

        ]);

        $chatId = $request->chat_id;

        $mediaUrl  = null;
        $mediaType = null;

        /*
        |--------------------------------------------------------------------------
        | LOCATION MESSAGE
        |--------------------------------------------------------------------------
        */

        $messageText = $request->message;

        if ($request->filled('lat') && $request->filled('lng')) {
            $messageText = json_encode([
                'lat'   => number_format((float) $request->lat, 6, '.', ''),
                'lng'   => number_format((float) $request->lng, 6, '.', ''),
                'label' => $request->label ?? (auth()->user()->name . "'s Location"),
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
                'chat_id'       => $chatId,
                'customer_id'   => auth()->id(),
                'customer_name' => auth()->user()->name,
                'updated_at'    => new \DateTime('now', new \DateTimeZone('UTC')),
            ]
        );
        /*
    |--------------------------------------------------------------------------
    | UPLOAD FILE
    |--------------------------------------------------------------------------
    */

        if ($request->hasFile('media')) {

            $file = $request->file('media');

            $fileName =
                auth()->id() .
                '_' .
                time() .
                '_' .
                $file->getClientOriginalName();

            $bucket = $firebase
                ->storage
                ->getBucket();

            $bucket->upload(

                fopen($file->getPathname(), 'r'),

                [
                    'name' => 'chat_media/' . $fileName
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

            $mediaType = $file->getMimeType();
        }

        /*
    |--------------------------------------------------------------------------
    | SAVE TO FIRESTORE
    |--------------------------------------------------------------------------
    */
        $firebase->addDocument(
            'support_chats/' . $chatId . '/messages',
            [
                'sender'        => 'customer',
                'user_id'       => auth()->id(),
                'message'       => $messageText ?? $request->message,
                'media_url'     => $mediaUrl,
                'media_type'    => $mediaType ?? ($mediaUrl ? $request->file('media')?->getMimeType() : null),
                'delivered'     => true,
                'read'          => false,
                'reply_to_id'   => $request->reply_to_id,
                'reply_to_text' => $request->reply_to_text,
                'created_at'    => new \DateTime('now', new \DateTimeZone('UTC')),
            ]
        );

        return response()->json([

            'success' => true

        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | GET MESSAGES
    |--------------------------------------------------------------------------
    */

    public function messages(
        $chatId,
        FirebaseService $firebase
    ) {

        $messages = $firebase->listDocuments(

            'support_chats/' .
                $chatId .
                '/messages'

        );

        return response()->json($messages);
    }
}
