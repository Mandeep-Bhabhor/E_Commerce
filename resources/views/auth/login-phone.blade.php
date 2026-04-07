<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login with Phone</title>
</head>
<body style="display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #f3f4f6;">

    <div style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); text-align: center; width: 100%; max-width: 400px;">
        <h2>Login with Phone Number</h2>
        <p style="margin-bottom: 20px; color: #666;">We will send a 6-digit OTP to your phone.</p>

        @if($errors->any())
            <div style="color: red; margin-bottom: 15px;">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.phone.store') }}">
            @csrf
            <div style="text-align: left; margin-bottom: 15px;">
                <label for="phone_number" style="display: block; margin-bottom: 5px;">Phone Number</label>
                <input 
                    id="phone_number" 
                    type="text" 
                    name="phone_number" 
                    placeholder="+919313467816" 
                    required 
                    style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;"
                >
            </div>

            <button type="submit" style="width: 100%; padding: 10px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; margin-bottom: 15px;">
                Send OTP
            </button>
        </form>

        <a href="{{ route('login') }}" style="color: #007bff; text-decoration: none; font-size: 14px;">
            &larr; Back to Email Login
        </a>
    </div>

</body>
</html>