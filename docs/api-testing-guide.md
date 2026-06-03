# API Testing Guide — Postman

Base URL: `http://127.0.0.1:8000`

All protected routes need:
```
Authorization: Bearer {sanctum_token}
Accept: application/json
Content-Type: application/json
```

---

## 1. AUTH — Get Token

### Register
```
POST /api/register

{
    "name": "Ravi",
    "email": "ravi@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

### Login
```
POST /api/login

{
    "email": "ravi@example.com",
    "password": "password123"
}
```
Response gives you `token` — use it as Bearer token for all other requests.

---

## 2. CHAT — Send Messages

### Customer Send
```
POST /api/chat/send

{
    "chat_id": "user_5",
    "message": "Hello, I need help"
}
```

### Customer Send with Reply
```
POST /api/chat/send

{
    "chat_id": "user_5",
    "message": "Thanks for the help!",
    "reply_to_id": "PASTE_MESSAGE_DOC_ID_HERE",
    "reply_to_text": "Yes I can help with that"
}
```

### Admin Send
```
POST /api/admin/chat/send

{
    "chat_id": "user_5",
    "message": "How can I help you?"
}
```

### Admin Send with Reply
```
POST /api/admin/chat/send

{
    "chat_id": "user_5",
    "message": "Sure, let me check your order",
    "reply_to_id": "PASTE_MESSAGE_DOC_ID_HERE",
    "reply_to_text": "Hello, I need help"
}
```

---

## 3. CHAT — Get Messages

### Get All Messages for a Chat (Admin)
```
GET /api/admin/chat/messages/user_5
```

### Get All Customer Chats (Admin)
```
GET /api/admin/chat/users
```

### Get Customer's Own Messages
```
GET /api/chat/messages/user_5
```

---

## 4. READ RECEIPTS

### Mark Messages as Read
```
POST /api/chat/mark-read

{
    "chat_id": "user_5",
    "reader_role": "admin"
}
```
- `reader_role: "admin"` → marks all customer messages as read
- `reader_role: "customer"` → marks all admin messages as read

### Get Unread Count
```
GET /api/chat/unread-count?chat_id=user_5&reader_role=customer
```

---

## 5. ABOUT

### Get About
```
GET /api/about
```

### Update About
```
POST /api/about

{
    "about": "I am a regular customer from Ahmedabad"
}
```

---

## 6. FAVOURITES — Admin

### Add User to Favourites
```
POST /api/admin/favourites/users

{
    "chat_id": "user_5",
    "customer_name": "Ravi"
}
```
Note: `customer_name` is optional — if not provided, it looks up the name from MySQL.

### List Favourite Users
```
GET /api/admin/favourites/users
```

### Remove User from Favourites
```
POST /api/admin/favourites/users/remove

{
    "chat_id": "user_5"
}
```

### Star a Message (Admin)
```
POST /api/admin/favourites/messages

{
    "chat_id": "user_5",
    "message_id": "PASTE_FIRESTORE_DOC_ID",
    "sender": "customer",
    "message": "Hello, I need help"
}
```

### List Starred Messages (Admin)
```
GET /api/admin/favourites/messages
```

### List Starred Messages Filtered by Chat
```
GET /api/admin/favourites/messages?chat_id=user_5
```

### Unstar a Message (Admin)
```
POST /api/admin/favourites/messages/remove

{
    "message_id": "PASTE_FIRESTORE_DOC_ID"
}
```

---

## 7. FAVOURITES — Customer

### Star a Message (Customer)
```
POST /api/customer/favourites/messages

{
    "chat_id": "user_5",
    "message_id": "PASTE_FIRESTORE_DOC_ID",
    "sender": "admin",
    "message": "How can I help you?"
}
```

### List Starred Messages (Customer)
```
GET /api/customer/favourites/messages
```

### Unstar a Message (Customer)
```
POST /api/customer/favourites/messages/remove

{
    "message_id": "PASTE_FIRESTORE_DOC_ID"
}
```

---

## 8. CUSTOMER PROFILE (Admin viewing customer)

### Get Customer Profile
```
GET /admin/customer/5/profile
```
Note: This is a **web route** (session auth), not an API route. Use browser or set session cookie.

---

## How to Get a message_id for Testing

1. Open Firebase Console → Firestore
2. Navigate to: `support_chats` → `user_5` → `messages`
3. Click any message document
4. Copy the document ID (the string in the left panel, like `7xKf9aBcD...`)
5. Use that as `message_id` in your requests

---

## Quick Test Sequence

1. **Login** → get token
2. **Send message** (customer) → `POST /api/chat/send`
3. **Get messages** → `GET /api/admin/chat/messages/user_5` → verify message appears
4. **Mark as read** → `POST /api/chat/mark-read` with `reader_role: "admin"`
5. **Send reply** (admin) → `POST /api/admin/chat/send` with `reply_to_id` + `reply_to_text`
6. **Star message** → `POST /api/admin/favourites/messages`
7. **List starred** → `GET /api/admin/favourites/messages` → verify it shows
8. **Unstar** → `POST /api/admin/favourites/messages/remove`
9. **List starred** → should be empty now
