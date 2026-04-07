<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Login</title>
    </head>
<body style="display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #f3f4f6;">

    <div style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); text-align: center;">
        <h2>Check Your Email Or Phone</h2>
        <p>We sent a 6-digit code.</p>

        @if($errors->any())
            <div style="color: red; margin-bottom: 15px;">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('otp.verify.submit') }}" method="POST">
            @csrf
            <input 
                type="text" 
                name="otp" 
                placeholder="123456" 
                maxlength="6" 
                required
                style="font-size: 24px; letter-spacing: 5px; text-align: center; padding: 10px; width: 150px; margin-bottom: 20px;"
            >
            <br>
            <button type="submit" style="padding: 10px 20px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer;">
                Verify & Login
            </button>
        </form>
    </div>

</body>
</html>