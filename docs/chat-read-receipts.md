# Chat Read Receipts — Implementation Guide

## Overview

WhatsApp-style message delivery and read indicators for the Laravel + Firebase support chat system.

- **✓** (grey) = Message sent/delivered
- **✓✓** (blue) = Message read by the other party

---

## How It Works — The Big Picture

```
┌─────────────────────────────────────────────────────────────────┐
│                        MESSAGE LIFECYCLE                         │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  1. Customer sends message                                      │
│     → Firestore doc created: { delivered: true, read: false }   │
│     → Customer sees: ✓ (grey)                                   │
│                                                                 │
│  2. Admin opens that chat                                       │
│     → markMessagesAsRead() runs                                 │
│     → Updates all customer messages: { read: true }             │
│     → onSnapshot fires on customer side                         │
│     → Customer sees: ✓✓ (blue) — realtime, no refresh needed   │
│                                                                 │
│  Same flow in reverse for admin → customer direction.           │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## Firestore Document Structure

**Path:** `support_chats/{chatId}/messages/{messageId}`

| Field       | Type      | Description                                    |
|-------------|-----------|------------------------------------------------|
| sender      | string    | `"customer"` or `"admin"`                      |
| message     | string    | Message text                                   |
| media_url   | string    | File URL (or null)                             |
| media_type  | string    | MIME type (or null)                             |
| delivered   | boolean   | Always `true` on creation                      |
| read        | boolean   | `false` on creation, `true` when other reads   |
| created_at  | Timestamp | Firestore Timestamp (via `new Date()` in JS)   |

---

## Files Involved

| File | Role |
|------|------|
| `public/js/firebase.js` | Central Firebase module — exports `where`, `getDocs`, `updateDoc` |
| `resources/views/layouts/customer.blade.php` | Customer chat widget — sends with fields, marks admin msgs read, renders ticks |
| `resources/views/admin/chat/index.blade.php` | Admin chat panel — sends with fields, marks customer msgs read, renders ticks |
| `app/Http/Controllers/Api/ChatController.php` | Laravel API — customer send (includes `delivered` + `read`) |
| `app/Http/Controllers/Api/AdminChatController.php` | Laravel API — admin send (includes `delivered` + `read`) |

---

## Implementation Details

### 1. firebase.js — Shared Module

Added three new Firestore functions to the import and export:

```javascript
import { ..., where, getDocs, updateDoc } from "firebase-firestore.js";

export { ..., where, getDocs, updateDoc };
```

These are needed for:
- `where()` — filter messages by sender and read status
- `getDocs()` — one-time query (not realtime) to find unread messages
- `updateDoc()` — update a single document's `read` field to `true`

---

### 2. Sending Messages — Adding the Fields

Every message creation (both frontend JS and Laravel API) now includes:

```javascript
// Frontend (Blade JS)
{
    sender: "customer",  // or "admin"
    message: message,
    delivered: true,     // ← NEW
    read: false,         // ← NEW
    created_at: new Date()
}
```

```php
// Laravel API (ChatController / AdminChatController)
[
    'sender'    => 'customer',  // or 'admin'
    'message'   => $request->message,
    'delivered' => true,        // ← NEW
    'read'      => false,       // ← NEW
    'created_at' => new \DateTime('now', new \DateTimeZone('UTC')),
]
```

**Why `delivered` is always `true`:** If the document exists in Firestore, it was delivered. No separate delivery confirmation system is needed.

---

### 3. Marking Messages as Read

#### Customer Side (customer.blade.php)

When the customer opens the chat (page loads), all **admin** messages get marked as read:

```javascript
async function markMessagesAsRead() {
    const q = query(
        collection(db, "support_chats", chatId, "messages"),
        where("sender", "==", "admin")
    );
    const snapshot = await getDocs(q);
    snapshot.forEach(async (docSnap) => {
        const data = docSnap.data();
        if (data.read !== true) {
            await updateDoc(docSnap.ref, { read: true });
        }
    });
}
```

**Called in two places:**
1. On page load (initial mark)
2. Inside `onSnapshot` callback (marks new admin messages as they arrive in realtime)

#### Admin Side (admin/chat/index.blade.php)

When admin clicks a customer chat, all **customer** messages get marked as read:

```javascript
async function markMessagesAsRead(chatId) {
    const q = query(
        collection(db, "support_chats", chatId, "messages"),
        where("sender", "==", "customer"),
        where("read", "==", false)
    );
    const snapshot = await getDocs(q);
    snapshot.forEach(async (docSnap) => {
        await updateDoc(docSnap.ref, { read: true });
    });
}
```

**Called in:** `loadMessages(chatId)` — runs every time admin selects a customer.

---

### 4. Rendering Ticks

Ticks are shown **only on the sender's own messages** (right-aligned bubbles).

#### Customer Side — ticks on customer's messages:

```javascript
${
    data.sender === 'customer'
    ? (data.read === true
        ? '<span style="color:#90caf9;font-size:13px;">✓✓</span>'   // blue double tick
        : '<span style="color:rgba(255,255,255,0.55);font-size:13px;">✓</span>')  // grey single tick
    : ''  // no tick on admin messages
}
```

#### Admin Side — ticks on admin's messages:

```javascript
if (data.sender === 'admin') {
    if (data.read === true) {
        tickHtml = '<span style="color:#90caf9;">✓✓</span>';       // blue
    } else {
        tickHtml = '<span style="color:rgba(255,255,255,0.55);">✓</span>';  // grey
    }
}
```

---

### 5. Realtime Updates — How Ticks Change Automatically

No extra listener is needed. Here's the flow:

```
Customer sends message
    → Firestore creates doc: { read: false }
    → Customer's onSnapshot fires → renders ✓ (grey)

Admin opens chat
    → markMessagesAsRead() → updateDoc({ read: true })
    → Firestore doc changes
    → Customer's onSnapshot fires AGAIN (realtime)
    → Re-renders → now shows ✓✓ (blue)
```

The existing `onSnapshot` listener already re-renders all messages on any document change. When `updateDoc` sets `read: true`, Firestore triggers the snapshot → the render loop runs → tick updates from grey to blue automatically.

---

## Firestore Composite Index

The admin's `markMessagesAsRead` uses two `where()` clauses:

```javascript
where("sender", "==", "customer")
where("read", "==", false)
```

Firestore requires a **composite index** for queries with multiple `where` conditions on different fields.

**How to create it:**
1. Open the browser console after deploying
2. If the index is missing, Firestore logs an error with a **direct URL** to create it
3. Click that URL → Firebase Console opens → click "Create Index"
4. Wait ~1 minute for it to build

**Index definition:**
- Collection: `messages` (subcollection of `support_chats/{chatId}`)
- Fields: `sender` (Ascending) + `read` (Ascending)

> Note: The customer side uses a simpler query (`where("sender", "==", "admin")` only) which doesn't require a composite index.

---

## Key Design Decisions

| Decision | Reasoning |
|----------|-----------|
| `delivered` is always `true` | If the doc exists in Firestore, it was delivered. No ACK system needed. |
| `read` updates happen client-side only | Using `updateDoc()` from Firebase JS SDK, not via Laravel API. Faster, fewer round-trips. |
| Customer marks on `onSnapshot` | Ensures new admin messages are marked read immediately if chat is open. |
| Admin marks on `loadMessages()` | Runs once when admin selects a chat — sufficient since admin actively switches. |
| No external icon library | Plain Unicode checkmarks (✓ and ✓✓) — zero dependencies. |
| Tick colors | Blue `#90caf9` for read (matches WhatsApp blue), semi-transparent white for unread (visible on blue bubble). |

---

## Testing Checklist

| # | Scenario | Expected |
|---|----------|----------|
| 1 | Customer sends message | Grey ✓ appears on customer side |
| 2 | Admin opens that chat | Customer's ✓ changes to blue ✓✓ in realtime |
| 3 | Admin sends message | Grey ✓ appears on admin side |
| 4 | Customer opens chat / is already on chat | Admin's ✓ changes to blue ✓✓ in realtime |
| 5 | Send via Postman (Laravel API) | Message has `delivered: true, read: false` in Firestore |
| 6 | Switch between customers (admin) | No duplicate messages, ticks correct per chat |
| 7 | No console errors | Especially no "missing index" after index is created |

---

## Troubleshooting

| Problem | Solution |
|---------|----------|
| Ticks not showing | Check if `delivered` and `read` fields exist on the message doc in Firestore Console |
| Grey tick never turns blue | Check browser console for index errors. Create the composite index. |
| `updateDoc is not a function` | Ensure `firebase.js` exports `updateDoc` and the blade imports it |
| Old messages don't have ticks | They were created before this feature. They lack `delivered`/`read` fields. Ticks will show as grey ✓ (since `data.read` is `undefined`, which is not `=== true`). |
