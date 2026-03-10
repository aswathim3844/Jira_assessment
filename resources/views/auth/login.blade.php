<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <label>Username:</label>
            <input type="text" name="username" value="{{ old('username') }}" required autofocus>
            @error('username')
                <div>{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label>Password:</label>
            <input type="password" name="password" required>
            @error('password')
                <div>{{ $message }}</div>
            @enderror
        </div>

        <button type="submit">Login</button>
    </form>
</body>
</html>