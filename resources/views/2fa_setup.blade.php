<h3>Setup 2FA</h3>

<p>Scan this QR code using Google Authenticator</p>

<div>
    {!! $qr !!}
</div>

<form method="POST" action="{{ route('2fa.setup.verify') }}">
    @csrf
    <input type="text" name="otp" placeholder="Enter 6-digit code" required>
    <button type="submit">Verify</button>
</form>