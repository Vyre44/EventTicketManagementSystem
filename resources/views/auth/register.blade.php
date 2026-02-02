@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-10 max-w-md">
    <h1 class="text-2xl font-bold mb-6">Kayıt Ol</h1>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block font-semibold mb-1">Ad Soyad</label>
            <input type="text" name="name" value="{{ old('name') }}" class="w-full border rounded px-3 py-2" required>
            @error('name')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
        </div>

        <div>
            <label class="block font-semibold mb-1">E-posta</label>
            <input type="email" name="email" value="{{ old('email') }}" class="w-full border rounded px-3 py-2" required>
            @error('email')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
        </div>

        <div>
            <label class="block font-semibold mb-1">Şifre</label>
            <input type="password" name="password" class="w-full border rounded px-3 py-2" required>
            @error('password')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
        </div>

        <div>
            <label class="block font-semibold mb-1">Şifre (Tekrar)</label>
            <input type="password" name="password_confirmation" class="w-full border rounded px-3 py-2" required>
        </div>

        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded">Kayıt Ol</button>

        <p class="text-sm text-center">
            Zaten hesabınız var mı? <a href="{{ route('login') }}" class="text-blue-600">Giriş Yap</a>
        </p>
    </form>
</div>
@endsection
