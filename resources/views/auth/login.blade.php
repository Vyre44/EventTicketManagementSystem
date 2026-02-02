<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Giriş Yap</title>
</head>
<body style="font-family:sans-serif;background:#f3f4f6;">
<div style="max-width:400px;margin:40px auto;">
    <h2>Giriş Yap</h2>
    @if(session('error'))
        <div style="color:red">{{ session('error') }}</div>
    @endif
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div>
            <label>Email</label>
            <input type="email" name="email" required autofocus>
        </div>
        <div>
            <label>Şifre</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit">Giriş</button>
    </form>
</div>
</body>
</html>
