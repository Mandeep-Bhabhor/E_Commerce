{{-- resources/views/emails/contact_reply.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { font-size: 20px; font-weight: bold; margin-bottom: 20px; color: #4facfe; }
        .reply-box { padding: 15px; border-left: 4px solid #4facfe; background: #f9f9f9; margin-bottom: 20px; }
        .original-message { font-size: 13px; color: #777; border-top: 1px solid #ddd; padding-top: 15px; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            {{ config('app.name', 'MyShop') }} Support
        </div>

        <p>Hi {{ $contact->name }},</p>
        
        <p>Thank you for reaching out to us. Here is the response to your inquiry:</p>

        <div class="reply-box">
            {{-- nl2br preserves the line breaks you type in the admin textarea --}}
            {!! nl2br(e($replyText)) !!}
        </div>

        <p>If you have any further questions, simply reply to this email.</p>

        <p>Best regards,<br>The {{ config('app.name', 'MyShop') }} Team</p>

        <div class="original-message">
            <strong>Your original message:</strong><br>
            <em>{{ $contact->message }}</em>
        </div>
    </div>
</body>
</html>