# Location Sharing — Detailed Explanation

## What It Does

Both admin and customer can share their real-time GPS location in the chat.
The location appears as an embedded Google Maps iframe inside the message bubble. Clicking it opens Google Maps in a new tab at the exact coordinates.

---

## Technology Used

- **Browser Geolocation API** — `navigator.geolocation.getCurrentPosition()` — no library needed
- **Firestore addDoc** — saves the message document with coordinates stored as JSON in the `message` field
- **Google Maps Embed URL** — `maps.google.com/maps?q=lat,lng&output=embed` — free, no API key required
- **Google Maps Link** — `google.com/maps?q=lat,lng` — opens full map on click

---

## UI

```
┌────────────────────────────────────────────────┐
│  📎   🎤   📍   [Type message...]    [➤ Send]  │
└────────────────────────────────────────────────┘
              ↑
       Location button
```

While fetching location (button turns yellow):
```
┌────────────────────────────────────────────────┐
│  📎   🎤  [📍 yellow]  [Type message...] [➤]  │
└────────────────────────────────────────────────┘
```

Location message in chat bubble:
```
┌──────────────────────────────┐
│  ┌──────────────────────┐    │
│  │   [Google Maps view] │    │
│  └──────────────────────┘    │
│  📍 Ravi's Location          │
└──────────────────────────────┘
```

---

## Firestore Message Structure

Location is stored as a normal message document. No new fields — coordinates are JSON-encoded inside the existing `message` field:

```
support_chats/{chatId}/messages/{messageId}
    sender:      "customer" | "admin"
    message:     '{"lat":"23.022505","lng":"72.571362","label":"Ravi'\''s Location"}'
    media_url:   null
    media_type:  "location"          ← this is the type identifier
    delivered:   true
    read:        false
    created_at:  Timestamp
```

**Why JSON in `message` field?**
Keeps the document structure consistent. All existing render logic checks `media_type` to decide what to render — no schema changes needed.

---

## How It Works — Step by Step

### Customer Side

**Execution flow:**

```
1. Customer clicks 📍 location button
   → custLocationBtn click handler fires
   → Checks navigator.geolocation (browser support)
   → Button disabled + turns yellow (visual feedback)
   → navigator.geolocation.getCurrentPosition() called
   → Browser asks for location permission (first time only)

2. Location granted
   → lat = position.coords.latitude.toFixed(6)
   → lng = position.coords.longitude.toFixed(6)
   → setDoc: upserts support_chats/{chatId} with customer info + updated_at
   → addDoc: creates message document:
     {
       sender: "customer",
       sender_id: userId,
       sender_name: userName,
       message: JSON.stringify({ lat, lng, label: "Ravi's Location" }),
       media_url: null,
       media_type: "location",
       delivered: true,
       read: false,
       reply_to_id: (if replying),
       reply_to_text: (if replying),
       created_at: new Date()
     }
   → Button re-enabled + returns to blue

3. Location denied / error
   → alert shown with error message
   → Button re-enabled + returns to blue

4. onSnapshot fires (existing listener)
   → renderCustMessages() runs
   → Sees media_type === "location"
   → JSON.parse(data.message) → { lat, lng, label }
   → Renders Google Maps embed iframe + label
   → Clicking opens Google Maps in new tab
```

---

### Admin Side

**Execution flow:**

```
1. Admin clicks 📍 location button
   → Checks selectedChatId (must have a customer selected)
   → Button disabled + turns yellow
   → navigator.geolocation.getCurrentPosition() called

2. Location granted
   → lat, lng extracted from position.coords (toFixed(6))
   → setDoc: upserts support_chats/{chatId} with admin_id + updated_at
   → addDoc: creates message:
     {
       sender: "admin",
       sender_name: adminName,
       user_id: adminId,
       message: JSON.stringify({ lat, lng, label: "Admin Location" }),
       media_url: null,
       media_type: "location",
       delivered: true,
       read: false,
       reply_to_id: (if replying),
       reply_to_text: (if replying),
       created_at: new Date()
     }
   → Button re-enabled

3. Location denied / error
   → alert shown
   → Button re-enabled

4. onSnapshot → renderMessages() → location rendered in bubble
```

---

## Rendering Location in Chat

In both render loops (`renderCustMessages` / `renderMessages`), location is detected after the audio check and before the generic file fallback:

```javascript
else if (data.media_type === 'location' && data.message) {
    try {
        const loc = JSON.parse(data.message);
        const mapUrl = `https://www.google.com/maps?q=${loc.lat},${loc.lng}`;
        mediaHtml = `
        <a href="${mapUrl}" target="_blank" style="display:block;text-decoration:none;">
            <div style="border-radius:10px;overflow:hidden;width:200px;">
                <iframe
                    width="200" height="110"
                    style="border:0;display:block;"
                    loading="lazy"
                    allowfullscreen
                    src="https://maps.google.com/maps?q=${loc.lat},${loc.lng}&z=15&output=embed">
                </iframe>
                <div style="padding:5px 8px;font-size:11px;color:#2e7d32;">
                    📍 ${loc.label || 'Shared Location'}
                </div>
            </div>
        </a>
        `;
    } catch(e) {
        mediaHtml = `<div>📍 Location</div>`;
    }
}
```

The `try/catch` around `JSON.parse` ensures any malformed data fails gracefully with a plain text fallback.

---

## State Variables

| Variable | Side | Purpose |
|----------|------|---------|
| `custLocationBtn` | Customer | Reference to the 📍 button element |
| `adminLocationBtn` | Admin | Reference to the 📍 button element |

No additional state needed — geolocation is a one-shot async operation, not a stream.

---

## No API Key Required

The Google Maps embed URL used:
```
https://maps.google.com/maps?q={lat},{lng}&z=15&output=embed
```

This is a free embed that works without a Google Maps API key. It shows a centred map with a pin at the coordinates. The link that opens on click:
```
https://www.google.com/maps?q={lat},{lng}
```
This also works without a key — it opens Google Maps directly at the coordinates.

---

## Problems Encountered & How They Were Fixed

### Problem 1: Duplicate `adminLocationBtn` declaration — SyntaxError broke the page
**Cause:** The location JS was added twice in the admin blade — once from an earlier partial implementation and once from the final implementation. Both used `const adminLocationBtn`, which caused `SyntaxError: Identifier 'adminLocationBtn' has already been declared`.
**Fix:** Removed the second duplicate block, keeping only the one that uses `media_type: "location"` consistent with the render logic.

---

## Files Changed

| File | What was added |
|------|----------------|
| `resources/views/layouts/customer.blade.php` | 📍 button HTML in input row, `custLocationBtn` click handler with `getCurrentPosition`, location render in `renderCustMessages` |
| `resources/views/admin/chat/index.blade.php` | 📍 button HTML in input row, `adminLocationBtn` click handler with `getCurrentPosition` (placed after audio section), location render in `renderMessages` |

---

## API (for mobile apps)

Send a location message the same way as a text message — no file upload needed:

```
POST /api/chat/send  (customer)
POST /api/admin/chat/send  (admin)

{
    "chat_id": "user_5",
    "message": "{\"lat\":\"23.022505\",\"lng\":\"72.571362\",\"label\":\"My Location\"}",
    "media_type_override": "location"
}
```

> Note: the current API controllers don't have a `media_type` override field yet. To support this from mobile, add `'media_type' => $request->input('media_type_override')` to the `addDocument()` call in `ChatController` and `AdminChatController` so the mobile app can pass `"location"` as the type.
