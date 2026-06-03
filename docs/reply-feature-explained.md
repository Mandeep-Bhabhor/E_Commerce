# Reply Message Feature — Detailed Explanation

## What It Does

Users can reply to a specific message. The reply shows the original message as a quoted block above the new message — exactly like WhatsApp.

---

## Firestore — What Gets Stored

Every message now has two extra fields:

```
support_chats/{chatId}/messages/{messageId}
    sender:        "customer"
    message:       "Yes I can help"
    delivered:     true
    read:          false
    reply_to_id:   "abc123"              ← ID of the message being replied to (or null)
    reply_to_text: "I need help please"  ← preview text of original message (or null)
    created_at:    Timestamp
```

**Why store `reply_to_text` separately?**
So we don't need to fetch the original message document every time we render. The preview is saved at send time — one less Firestore read per message.

---

## The Flow — Step by Step

### 1. User clicks ↩ on a message

```
Message bubble shows: "I need help please"
User hovers → ↩ icon appears
User clicks ↩
```

### 2. Reply bar appears above input

```
┌──────────────────────────────────────┐
│ ↩ customer: I need help please    ×  │  ← reply bar
├──────────────────────────────────────┤
│ [Type message...]            [Send]  │
└──────────────────────────────────────┘
```

JS state is set:
```javascript
replyingTo = {
    id: "abc123",           // Firestore doc ID of original message
    text: "I need help please",  // preview (max 80 chars)
    sender: "customer"
}
```

### 3. User types reply and clicks Send

The `addDoc` payload includes:
```javascript
{
    sender: "admin",
    message: "Yes I can help",
    reply_to_id: "abc123",
    reply_to_text: "I need help please",
    ...other fields
}
```

After send → `replyingTo` resets to `null`, reply bar hides.

### 4. Message renders with quoted block

```
┌─────────────────────────────┐
│ ┌─────────────────────────┐ │
│ │ I need help please      │ │  ← reply-quote div (grey bg, blue left border)
│ └─────────────────────────┘ │
│ Yes I can help              │  ← actual message
│                        ✓✓   │
└─────────────────────────────┘
```

The render logic:
```javascript
const replyQuoteHtml = data.reply_to_text
    ? `<div class="reply-quote">${data.reply_to_text}</div>`
    : '';
```

---

## Files Changed — Complete List

---

### File 1: `app/Http/Controllers/Api/ChatController.php`

**What changed:** Added reply fields to customer message send via API.

**Validation added:**
```php
'reply_to_id'   => 'nullable|string',
'reply_to_text' => 'nullable|string|max:200',
```

**addDocument() — added fields:**
```php
'reply_to_id'   => $request->reply_to_id,
'reply_to_text' => $request->reply_to_text,
```

---

### File 2: `app/Http/Controllers/Api/AdminChatController.php`

**What changed:** Added reply fields to admin message send via API.

**Validation added:**
```php
'reply_to_id'   => 'nullable|string',
'reply_to_text' => 'nullable|string|max:200',
```

**addDocument() — added fields:**
```php
'reply_to_id'   => $request->reply_to_id,
'reply_to_text' => $request->reply_to_text,
```

---

### File 3: `resources/views/admin/chat/index.blade.php`

**What changed:** Full reply UI for admin chat panel.

**CSS added (inside `<style>` block):**
- `.msg-reply` — reply icon (↩), hidden by default, visible on hover
- `.reply-preview` — blue bar above input showing "Replying to: ..."
- `.reply-preview .reply-cancel` — × close button styling
- `.reply-quote` — quoted block inside message bubble (grey bg, blue left border)

**HTML added (inside card-footer, above file input):**
```html
<div id="reply-bar" class="reply-preview d-none">
    <span id="reply-bar-text">Replying to: ...</span>
    <span class="reply-cancel" id="reply-cancel">&times;</span>
</div>
```

**HTML added (inside each message bubble in renderMessages):**
```html
<!-- Reply quote at top of bubble -->
${replyQuoteHtml}

<!-- Reply icon alongside star icon -->
<span class="msg-reply" data-msg-id="${msgId}" title="Reply">↩</span>
```

**JS added:**
- `let replyingTo = null` — state variable holding `{ id, text, sender }`
- `setReply(msgId, msgText, sender)` — sets state, shows reply bar, focuses input
- `replyCancel` click handler — resets state, hides bar
- Reply icon click handler in `renderMessages` — calls `setReply()`
- Send handler updated — includes `reply_to_id` and `reply_to_text` from `replyingTo`
- After send — resets `replyingTo = null`, hides reply bar

---

### File 4: `resources/views/layouts/customer.blade.php`

**What changed:** Full reply UI for customer chat widget.

**CSS added (inside `<style>` block):**
- `.cust-msg-reply` — reply icon, hidden by default, visible on hover
- `.cust-reply-preview` — reply bar above input in chat widget
- `.cust-reply-preview .reply-cancel` — × button
- `.cust-reply-quote` — quoted block inside customer message bubble

**HTML added (inside chat footer `<div class="p-2 border-top">`, above file input):**
```html
<div id="cust-reply-bar" class="cust-reply-preview d-none">
    <span id="cust-reply-text">↩ ...</span>
    <span class="reply-cancel" id="cust-reply-cancel">&times;</span>
</div>
```

**HTML added (inside each message bubble in renderCustMessages):**
```html
<!-- Reply quote at top of bubble -->
${replyQuoteHtml}

<!-- Reply icon alongside star icon -->
<span class="cust-msg-reply" data-msg-id="${msgId}" title="Reply">↩</span>
```

**JS added:**
- `let custReplyingTo = null` — state variable
- `setCustReply(msgId, msgText, sender)` — sets state, shows bar, focuses input
- `custReplyCancel` click handler — resets state, hides bar
- Reply icon click handler in `renderCustMessages` — calls `setCustReply()`
- Send handler updated — includes `reply_to_id` and `reply_to_text` from `custReplyingTo`
- After send — resets `custReplyingTo = null`, hides reply bar

---

## API Usage (Postman / Mobile)

### Send a reply:
```
POST /api/chat/send  (or /api/admin/chat/send)
Authorization: Bearer {token}
Content-Type: application/json

{
    "chat_id": "user_5",
    "message": "Yes I can help with that",
    "reply_to_id": "firestore_message_doc_id",
    "reply_to_text": "I need help with my order"
}
```

### Send without reply (normal message):
```json
{
    "chat_id": "user_5",
    "message": "Hello there"
}
```
`reply_to_id` and `reply_to_text` will be `null`.

---

## How the UI Knows to Show the Quote

In the render loop, every message is checked:

```javascript
if (data.reply_to_text) {
    // Show the quoted block
}
```

If `reply_to_text` is `null` or empty → no quote shown → normal message.
If it has a value → grey box with blue left border appears above the message text.

---

## Key Points

| Point | Detail |
|-------|--------|
| Reply is optional | Both fields are `nullable` — normal messages work as before |
| Preview is stored at send time | No extra Firestore reads needed to render |
| Max 200 chars (API) | Laravel validates `reply_to_text` max length |
| Max 80 chars (frontend) | `setReply()` does `.substring(0, 80)` for the bar preview |
| Works via both methods | Blade JS direct + Laravel API both support it |
| Realtime | `onSnapshot` re-renders → replies appear instantly for both parties |
| Cancel reply | × button or just send without clicking ↩ first |
| No new Firebase functions needed | Uses existing `addDoc` — just adds two more fields |
| No new routes needed | Same send endpoints, just accepts extra optional fields |
