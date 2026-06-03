# Audio Recording & Send — Detailed Explanation

## What It Does

Both admin and customer can record voice messages directly in the chat and send them.
The audio is uploaded to Firebase Storage and plays inline as an `<audio>` player inside the message bubble.

---

## Technology Used

- **Browser MediaRecorder API** — built into modern browsers, no library needed
- **`navigator.mediaDevices.getUserMedia({ audio: true })`** — requests mic permission
- **Firebase Storage (Kreait SDK via Firebase JS SDK)** — stores the audio file
- **Firestore addDoc** — saves the message document with the audio URL

---

## UI

```
┌────────────────────────────────────────────────┐
│  📎   🎤   [Type message...]         [➤ Send]  │
└────────────────────────────────────────────────┘
         ↑
     Mic button
```

When recording:
```
● Recording... 4s           ← red blinking dot + timer
┌────────────────────────────────────────────────┐
│  📎  [🎤 red]  [Type message...]     [➤ Send]  │
└────────────────────────────────────────────────┘
```

---

## How It Works — Step by Step

### Customer Side

**Interaction:** Hold to record (mousedown/touchstart), release to stop (mouseup/touchend).

**Execution flow:**

```
1. User presses mic button
   → mousedown fires
   → startCustRecording() is called
   → custIsRecording flag checked (if true, return immediately)
   → navigator.mediaDevices.getUserMedia({ audio: true }) called
   → Browser asks for mic permission (first time only)

2. Mic permission granted
   → MediaRecorder created with the audio stream
   → ondataavailable: pushes chunks to custAudioChunks[]
   → onstop: builds Blob, uploads to Firebase Storage, sends Firestore doc
   → MediaRecorder.start() called
   → Timer starts: setInterval increments custRecSeconds every 1s
   → Recording indicator shows: "● Recording... Ns"
   → Mic button turns red

3. User releases mic button
   → mouseup (or touchend) fires
   → stopCustRecording() called
   → custIsRecording = false (reset immediately)
   → MediaRecorder.stop() called → triggers onstop callback

4. onstop fires
   → All mic stream tracks stopped (releases mic)
   → custIsRecording = false (set again for safety)
   → If recording < 1 second: discarded (prevents accidental taps)
   → Blob created from chunks: new Blob(custAudioChunks, { type: 'audio/webm' })
   → sendCustAudioMessage(blob) called

5. sendCustAudioMessage(blob)
   → fileName: customer_{userId}_audio_{timestamp}.webm
   → uploadBytes(storageRef, blob) → uploads to Firebase Storage
   → getDownloadURL(storageRef) → gets public URL
   → setDoc: updates support_chats/{chatId} (customer info, updated_at)
   → addDoc: creates message document:
     {
       sender: "customer",
       message: "",          ← empty string, no text
       media_url: audioUrl,
       media_type: "audio/webm",
       delivered: true,
       read: false,
       created_at: new Date()
     }

6. onSnapshot fires (existing listener)
   → Re-renders messages
   → Sees media_type starts with "audio/"
   → Renders: <audio controls src="audioUrl">
   → Audio player appears in chat bubble
```

---

### Admin Side

**Interaction:** Click to start, click again to stop — **toggle pattern** (not hold).

**Why toggle instead of hold-to-record:**
The admin script runs after `await loadFavouriteUsers()` and `await loadFavouriteMessages()` (Firestore calls). During those async calls, the module is paused. Any `mousedown` during that pause would be lost. Also, `mouseleave` would fire during the async `getUserMedia` call and corrupt the state. Click-toggle avoids all these issues — the click event fires once, is handled, and does its job without timing-dependent async races.

**Execution flow:**

```
1. Page loads
   → Audio recording setup runs BEFORE the top-level awaits
   → adminMicBtn gets its click listener immediately
   → Mic is ready even before favourites load from Firestore

2. Admin clicks mic button (first click = start)
   → adminIsRecording is false → enters START branch
   → Checks selectedChatId (must have a customer selected)
   → clearInterval(adminRecInterval) — clears any stale timer
   → adminIsRecording = true
   → Mic button turns red immediately (visual feedback before async)
   → navigator.mediaDevices.getUserMedia({ audio: true }) called

3. Mic permission granted
   → MediaRecorder created
   → ondataavailable, onstop handlers set
   → MediaRecorder.start()
   → Timer starts
   → Recording indicator shows

4. Admin clicks mic button again (second click = stop)
   → adminIsRecording is true → enters STOP branch
   → MediaRecorder.stop() called → triggers onstop

5. onstop fires
   → Stream tracks stopped
   → adminIsRecording = false
   → Mic button returns to blue
   → Timer cleared, indicator hidden
   → If < 1 second: discarded
   → Blob built → sendAudioMessage(blob, selectedChatId) called

6. sendAudioMessage(blob, chatId)
   → fileName: admin_{adminId}_audio_{timestamp}.webm
   → uploadBytes → Firebase Storage
   → getDownloadURL → public URL
   → setDoc: updates support_chats/{chatId} (admin_id, updated_at)
   → addDoc: creates message:
     {
       sender: "admin",
       message: "",
       media_url: audioUrl,
       media_type: "audio/webm",
       delivered: true,
       read: false,
       created_at: new Date()
     }

7. onSnapshot → renderMessages → audio player rendered in bubble
```

---

## Rendering Audio in Chat

In the message render loop (`renderCustMessages` / `renderMessages`), audio is detected before the generic file fallback:

```javascript
else if (data.media_url && data.media_type && data.media_type.startsWith('audio/')) {
    mediaHtml = `
        <audio controls style="max-width:200px;width:100%;">
            <source src="${data.media_url}" type="${data.media_type}">
        </audio>
    `;
}
```

This renders a native browser audio player directly inside the message bubble.

---

## State Variables

| Variable | Side | Purpose |
|----------|------|---------|
| `custIsRecording` | Customer | Guard — prevents double-start |
| `custMediaRecorder` | Customer | MediaRecorder instance |
| `custAudioChunks` | Customer | Array of Blob chunks |
| `custRecInterval` | Customer | setInterval handle for timer |
| `custRecSeconds` | Customer | Timer counter |
| `adminIsRecording` | Admin | Guard — prevents double-start |
| `adminMediaRecorder` | Admin | MediaRecorder instance |
| `adminAudioChunks` | Admin | Array of Blob chunks |
| `adminRecInterval` | Admin | setInterval handle for timer |
| `adminRecSeconds` | Admin | Timer counter |

---

## Firebase Storage Path

```
chat_media/
    customer_{userId}_audio_{timestamp}.webm     ← customer recording
    admin_{adminId}_audio_{timestamp}.webm        ← admin recording
```

---

## Problems Encountered & How They Were Fixed

### Problem 1: Timer doubling (8, 16, 24...)
**Cause:** Every `mousedown` called `setInterval()` without clearing the previous one. 5 clicks = 5 intervals all running simultaneously.
**Fix:** Added `clearInterval(adminRecInterval)` at the start of `startRecording` and an `isRecording` guard flag.

### Problem 2: Admin mic not responding at all
**Cause:** The audio JS was placed AFTER `await loadFavouriteUsers()` in the module. Top-level `await` pauses the entire module — the mic event listeners never registered until Firestore finished loading (could take 3-10 seconds). Any click during that window was silently ignored.
**Fix:** Moved the audio recording setup to BEFORE the `await` calls so listeners register synchronously at module parse time.

### Problem 3: Admin stuck after one recording (adminIsRecording never reset)
**Cause:** The `mouseleave` event fired during the async `getUserMedia` call. `stopRecording` ran → reset `adminIsRecording = false` → but `getUserMedia` resolved and started the recorder anyway → `onstop` set `adminIsRecording = false` again. But if `mouseleave` fired BEFORE `getUserMedia` resolved, `stopRecording` ran with no recorder to stop, and `adminIsRecording` stayed `true` forever.
**Fix:** Changed from hold-to-record (mousedown/mouseleave) to **click-to-toggle** (single click handler). First click starts, second click stops. No `mouseleave` involved — eliminates the race condition entirely.

### Problem 4: SyntaxError broke the entire page
**Cause:** When removing the old `startRecording`/`stopRecording` functions, the closing `}` for `if (adminMicBtn) {` was accidentally removed, causing mismatched braces.
**Fix:** Added the missing `}` after the click handler.

### Problem 5: Why customer worked from the start but admin didn't
**Root difference:** Customer script has no top-level awaits. Mic listeners register instantly. Admin script had top-level awaits that delayed listener registration. Once the admin audio JS was moved before the awaits and the interaction model changed to click-toggle, both sides work identically.

---

## Files Changed

| File | What was added |
|------|----------------|
| `resources/views/layouts/customer.blade.php` | Mic button HTML, recording indicator, audio CSS (blink animation), `custIsRecording` guard, `sendCustAudioMessage()`, hold-to-record event listeners, audio render in `renderCustMessages` |
| `resources/views/admin/chat/index.blade.php` | Mic button HTML, recording indicator, audio CSS, `adminIsRecording` guard, `sendAudioMessage()`, click-to-toggle listener (placed BEFORE top-level awaits), audio render in `renderMessages` |
