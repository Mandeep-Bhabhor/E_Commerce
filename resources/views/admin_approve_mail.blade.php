<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Account Approved</title>
</head>

<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 30px;">

    <div
        style="max-width: 600px; margin: auto; background: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">

        <div style="background: #198754; color: white; padding: 20px;">
            <h2 style="margin: 0;">
                Account Approved
            </h2>
        </div>

        <div style="padding: 30px; color: #333;">

            <h3>Hello {{ $user->name }},</h3>

            <p>
                Your account has been approved by the admin.
            </p>

            <p>
                You can now login and access your account successfully.
            </p>

            <div style="margin: 30px 0;">
                <a href="{{ url('/login') }}"
                    style="background: #198754; color: white; padding: 12px 20px; text-decoration: none; border-radius: 6px;">
                    Login Now
                </a>
            </div>

            <p>
                Thank you for joining us.
            </p>

            <br>

            <p style="font-size: 14px; color: #777;">
                If you did not create this account, please ignore this email.
            </p>

        </div>

    </div>

</body>

</html>