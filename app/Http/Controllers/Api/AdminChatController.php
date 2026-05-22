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

            'media' => 'nullable|file|max:5120'

        ]);

        /*
    |--------------------------------------------------------------------------
    | Variables
    |--------------------------------------------------------------------------
    */

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
                'sender'     => 'admin',
                'message'    => $request->message ?? '',
                'media_url'  => $mediaUrl,
                'media_type' => $mediaType,
                'delivered'  => true,
                'read'       => false,
                'created_at' => new \DateTime('now', new \DateTimeZone('UTC')),
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
}
