<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FirebaseService;
use Illuminate\Http\Request;

class ReadReceiptController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | MARK MESSAGES AS READ
    |--------------------------------------------------------------------------
    |
    | POST /api/chat/mark-read
    |
    | Body:
    |   {
    |       "chat_id": "user_5",
    |       "reader_role": "admin"   ← who is reading (admin or customer)
    |   }
    |
    | Logic:
    |   - If reader_role is "admin"    → marks all "customer" messages as read
    |   - If reader_role is "customer" → marks all "admin" messages as read
    |
    | This is the Laravel API equivalent of the frontend markMessagesAsRead()
    | function. Useful for mobile apps or Postman testing.
    |
    */

    public function markRead(
        Request $request,
        FirebaseService $firebase
    ) {
        $request->validate([
            'chat_id'     => 'required|string',
            'reader_role' => 'required|string|in:admin,customer',
        ]);

        $chatId     = $request->chat_id;
        $readerRole = $request->reader_role;

        // The sender whose messages we need to mark as read
        $senderToMark = $readerRole === 'admin' ? 'customer' : 'admin';

        /*
        |----------------------------------------------------------------------
        | QUERY: find all unread messages from the other party
        |----------------------------------------------------------------------
        */

        $unreadMessages = $firebase->runQuery(
            'support_chats/' . $chatId,
            'messages',
            [
                ['field' => 'sender', 'op' => 'EQUAL', 'value' => $senderToMark],
                ['field' => 'read',   'op' => 'EQUAL', 'value' => false],
            ]
        );

        /*
        |----------------------------------------------------------------------
        | UPDATE: set read = true on each unread message
        |----------------------------------------------------------------------
        */

        $updatedCount = 0;

        foreach ($unreadMessages as $msg) {

            if (!isset($msg['__path'])) {
                continue;
            }

            $firebase->updateDocument(
                $msg['__path'],
                ['read' => true]
            );

            $updatedCount++;
        }

        return response()->json([
            'success'       => true,
            'messages_read' => $updatedCount,
            'chat_id'       => $chatId,
            'marked_sender' => $senderToMark,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | GET UNREAD COUNT
    |--------------------------------------------------------------------------
    |
    | GET /api/chat/unread-count?chat_id=user_5&reader_role=customer
    |
    | Returns the number of unread messages for the reader.
    | Useful for showing badge counts in mobile apps.
    |
    */

    public function unreadCount(
        Request $request,
        FirebaseService $firebase
    ) {
        $request->validate([
            'chat_id'     => 'required|string',
            'reader_role' => 'required|string|in:admin,customer',
        ]);

        $chatId     = $request->chat_id;
        $readerRole = $request->reader_role;

        $senderToCount = $readerRole === 'admin' ? 'customer' : 'admin';

        $unreadMessages = $firebase->runQuery(
            'support_chats/' . $chatId,
            'messages',
            [
                ['field' => 'sender', 'op' => 'EQUAL', 'value' => $senderToCount],
                ['field' => 'read',   'op' => 'EQUAL', 'value' => false],
            ]
        );

        return response()->json([
            'success'      => true,
            'unread_count' => count($unreadMessages),
            'chat_id'      => $chatId,
        ]);
    }
}
