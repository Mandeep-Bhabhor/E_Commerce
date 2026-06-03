# Favourites System — How It Works

## Storage

All favourites are stored in Firestore (not MySQL):

```
admin_favourites/{adminId}/users/{chatId}        → starred customers
admin_favourites/{adminId}/messages/{messageId}  → starred messages (admin)
customer_favourites/{userId}/messages/{messageId} → starred messages (customer)
```

---

## Admin — Favourite Users

1. Admin sees ☆ next to each customer name in the left panel
2. Click ☆ → `setDoc()` saves to `admin_favourites/{adminId}/users/{chatId}`
3. Star turns yellow ★
4. Click again → `deleteDoc()` removes it
5. "★ Favourites" tab in left panel shows only starred customers
6. Click a favourite → loads that customer's chat

---

## Admin — Favourite Messages

1. Hover over any message → ☆ appears
2. Click ☆ → `setDoc()` saves message content to `admin_favourites/{adminId}/messages/{messageId}`
3. Star turns yellow ★, messages re-render instantly
4. Click again → `deleteDoc()` removes it
5. "★ Starred" button in header → opens modal showing all starred messages

---

## Customer — Favourite Messages

1. Hover over any message in chat widget → ☆ appears
2. Click ☆ → `setDoc()` saves to `customer_favourites/{userId}/messages/{messageId}`
3. Star turns yellow ★, messages re-render instantly
4. Click again → `deleteDoc()` removes it
5. ★ button in chat header → opens starred panel (same size as chat box)
6. Profile page has "★ Starred Messages" card → links to `/customer/starred-messages` full page view
7. Full page shows all starred messages with unstar button on each

---

## Why It Updates Instantly (No Refresh)

1. A JS `Set` holds all favourite IDs in memory
2. On star click → Set is updated FIRST, then Firestore write happens
3. After write → `renderMessages(lastSnapshot)` is called to re-render all messages
4. Re-render reads the Set → shows correct ★/☆ state immediately

---

## API Endpoints (for mobile/Postman)

| Method | Endpoint | Action |
|--------|----------|--------|
| GET | `/api/admin/favourites/users` | List favourite customers |
| POST | `/api/admin/favourites/users` | Add favourite customer |
| DELETE | `/api/admin/favourites/users/{chatId}` | Remove favourite customer |
| GET | `/api/admin/favourites/messages` | List starred messages |
| POST | `/api/admin/favourites/messages` | Star a message |
| DELETE | `/api/admin/favourites/messages/{messageId}` | Unstar a message |
