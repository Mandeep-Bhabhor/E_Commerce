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

            'media'   => 'nullable|file|max:5120'

        ]);

        $chatId = $request->chat_id;

        $mediaUrl = null;
        $mediaType = null;

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
                'sender'     => 'customer',
                'user_id'    => auth()->id(),
                'message'    => $request->message,
                'media_url'  => $mediaUrl,
                'media_type' => $mediaType,
                'delivered'  => true,
                'read'       => false,
                'created_at' => new \DateTime('now', new \DateTimeZone('UTC')),
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
