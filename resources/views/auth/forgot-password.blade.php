@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-10 max-w-md">
    <h1 class="text-2xl font-bold mb-6">Şifre Sıfırlama</h1>

    @if (session('status'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block font-semibold mb-1">E-posta</label>
            <input type="email" name="email" value="{{ old('email') }}" class="w-full border rounded px-3 py-2" required>
            @error('email')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
        </div>

        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded">Sıfırlama Linki Gönder</button>
        <p class="text-sm text-center mt-2">
            <a href="{{ route('login') }}" class="text-blue-600">Giriş ekranına dön</a>
        </p>
    </form>
</div>
@endsection
