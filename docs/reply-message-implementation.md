# Reply Message Feature — Implementation Steps

## Concept

When a user swipes/clicks "reply" on a message, the new message stores a reference to the original message. The UI shows the quoted original above the reply text (like WhatsApp).

---

## Step 1 — Add Fields to Message Document

Add two new optional fields when creating a message:

```
support_chats/{chatId}/messages/{messageId}
    ...existing fields...
    reply_to_id:      null | "originalMessageId"
    reply_to_text:    null | "Preview of original message"
```

- `reply_to_id` — Firestore document ID of the message being replied to
- `reply_to_text` — Short preview of the original message text (stored at send time so we don't need to fetch it later)

---

## Step 2 — API Changes

### ChatController.php (customer send)

1. Add validation: `'reply_to_id' => 'nullable|string'`, `'reply_to_text' => 'nullable|string'`
2. Add to `addDocument()` call:
   ```php
   'reply_to_id'   => $request->reply_to_id,
   'reply_to_text' => $request->reply_to_text,
   ```

### AdminChatController.php (admin send)

Same — add `reply_to_id` and `reply_to_text` to validation and `addDocument()`.

### Postman test body:
```json
{
    "chat_id": "user_5",
    "message": "Yes I can help with that",
    "reply_to_id": "abc123firestore_doc_id",
    "reply_to_text": "I need help with my order"
}
```

---

## Step 3 — Admin Blade (Web)

### 3a. Add "Reply" button on each message bubble

In the message render loop (`renderMessages`), add a reply icon that appears on hover:

```html
<span class="msg-reply" data-msg-id="${msgId}" data-msg-text="${data.message}" data-msg-sender="${data.sender}">
    ↩
</span>
```

### 3b. Track reply state

```javascript
let replyingTo = null; // { id, text, sender }
```

### 3c. Reply click handler

When reply icon is clicked:
1. Set `replyingTo = { id: msgId, text: msgText, sender: sender }`
2. Show a "Replying to: ..." preview bar above the input area
3. Focus the input

### 3d. Cancel reply

Add an × button on the reply preview bar. Click → set `replyingTo = null`, hide the bar.

### 3e. Send with reply

In the `adminSend` click handler, when building the `addDoc` payload:
```javascript
reply_to_id: replyingTo ? replyingTo.id : null,
reply_to_text: replyingTo ? replyingTo.text : null,
```
After send → reset `replyingTo = null` and hide the preview bar.

### 3f. Render reply quote in message bubble

In the render loop, if `data.reply_to_text` exists:
```javascript
let replyHtml = '';
if (data.reply_to_text) {
    replyHtml = `
        <div style="
            background: rgba(0,0,0,0.05);
            border-left: 3px solid #0d6efd;
            padding: 4px 8px;
            margin-bottom: 6px;
            border-radius: 4px;
            font-size: 12px;
            color: #555;
        ">
            ${data.reply_to_text}
        </div>
    `;
}
```
Insert `${replyHtml}` at the top of the bubble content (before `${mediaHtml}`).

---

## Step 4 — Customer Blade (Web)

Exact same pattern as admin:

### 4a. Add reply icon on each message (appears on hover)
### 4b. Track `replyingTo` state variable
### 4c. Show reply preview bar above input when replying
### 4d. Include `reply_to_id` and `reply_to_text` in `addDoc` payload
### 4e. Render reply quote in message bubble (same HTML as admin)

---

## Step 5 — CSS (both blades)

```css
/* Reply icon on messages */
.msg-reply {
    cursor: pointer;
    font-size: 12px;
    color: #999;
    opacity: 0;
    transition: opacity 0.2s;
    margin-left: 6px;
}
.msg-bubble-wrap:hover .msg-reply,
.cust-bubble-wrap:hover .msg-reply {
    opacity: 1;
}
.msg-reply:hover {
    color: #0d6efd;
}

/* Reply preview bar above input */
.reply-preview {
    background: #e3f2fd;
    border-left: 3px solid #0d6efd;
    padding: 6px 12px;
    font-size: 13px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 4px;
    margin-bottom: 8px;
}
.reply-preview .reply-cancel {
    cursor: pointer;
    font-size: 16px;
    color: #999;
}
```

---

## Step 6 — Reply Preview Bar HTML

### Admin (inside card-footer, above the file input):
```html
<div id="reply-bar" class="reply-preview d-none">
    <span id="reply-bar-text">Replying to: ...</span>
    <span class="reply-cancel" id="reply-cancel">&times;</span>
</div>
```

### Customer (inside chat footer, above file input):
```html
<div id="cust-reply-bar" class="reply-preview d-none">
    <span id="cust-reply-bar-text">Replying to: ...</span>
    <span class="reply-cancel" id="cust-reply-cancel">&times;</span>
</div>
```

---

## Summary of Changes Per File

| File | What to add |
|------|-------------|
| `ChatController.php` | Validate + store `reply_to_id`, `reply_to_text` |
| `AdminChatController.php` | Validate + store `reply_to_id`, `reply_to_text` |
| `admin/chat/index.blade.php` | Reply icon, reply bar HTML, reply state JS, render quote |
| `layouts/customer.blade.php` | Reply icon, reply bar HTML, reply state JS, render quote |

---

## Flow Summary

```
1. User hovers message → sees ↩ reply icon
2. Clicks ↩ → reply bar appears: "Replying to: original text..."
3. Types reply → clicks Send
4. addDoc includes reply_to_id + reply_to_text
5. Message appears with quoted original on top
6. Same via API: pass reply_to_id + reply_to_text in POST body
```
