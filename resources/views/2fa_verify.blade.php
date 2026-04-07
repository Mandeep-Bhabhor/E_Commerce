<h3>Two-Factor Authentication</h3>

<form method="POST" action="{{ route('2fa.verify.post') }}">
    @csrf
    <input type="text" name="otp" placeholder="Enter 6-digit code" required>
    <button type="submit">Verify</button>
</form>

@if ($errors->any())
    <p style="color:red;">{{ $errors->first() }}</p>
@endif