{{-- Kayıt sayfası: Yeni kullanıcı oluşturma formu (Bootstrap 5) --}}
@extends('layouts.app')

{{-- İçerik bölümü başla --}}
@section('content')
<div class="row justify-content-center" style="margin-top: 60px;">
    <div class="col-lg-5 col-md-6">
        {{-- Proje başlığı --}}
        <div class="text-center mb-5">
            <h1 class="h3 fw-bold text-primary mb-1">🎫 Bilet Yönetim Sistemi</h1>
        </div>

        {{-- Kayıt kartı --}}
        <div class="card shadow-sm">
            <div class="card-body p-5">
                {{-- Kart başlığı --}}
                <h2 class="h4 fw-bold mb-4 text-center">Kayıt Ol</h2>

                {{-- Kayıt formu: POST isteği ile yeni kullanıcı oluşturur --}}
                <form method="POST" action="{{ route('register') }}">
                    {{-- CSRF token: Güvenlik için gerekli --}}
                    @csrf

                    {{-- Ad Soyad giriş alanı --}}
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">
                            <i class="bi bi-person me-1"></i>Ad Soyad
                        </label>
                        {{-- old('name'): Form hata alırsa önceki değeri yeniden göster --}}
                        <input 
                            type="text" 
                            class="form-control @error('name') is-invalid @enderror" 
                            id="name"
                            name="name" 
                            value="{{ old('name') }}" 
                            placeholder="Adınız ve soyadınız"
                            required
                        >
                        {{-- @error: Validasyon hatası varsa hata mesajı göster --}}
                        @error('name')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- E-posta giriş alanı --}}
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">
                            <i class="bi bi-envelope me-1"></i>E-posta
                        </label>
                        <input 
                            type="email" 
                            class="form-control @error('email') is-invalid @enderror" 
                            id="email"
                            name="email" 
                            value="{{ old('email') }}" 
                            placeholder="ornek@email.com"
                            required
                        >
                        @error('email')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Şifre giriş alanı --}}
                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">
                            <i class="bi bi-lock me-1"></i>Şifre
                        </label>
                        <input 
                            type="password" 
                            class="form-control @error('password') is-invalid @enderror" 
                            id="password"
                            name="password" 
                            placeholder="En az 8 karakter"
                            required
                        >
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Şifre tekrar alanı (doğrulama için) --}}
                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label fw-semibold">
                            <i class="bi bi-lock-check me-1"></i>Şifre (Tekrar)
                        </label>
                        <input 
                            type="password" 
                            class="form-control @error('password_confirmation') is-invalid @enderror" 
                            id="password_confirmation"
                            name="password_confirmation" 
                            placeholder="Şifreyi Tekrarlayın"
                            required
                        >
                        @error('password_confirmation')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Kayıt butonu --}}
                    <button type="submit" class="btn btn-primary w-100 fw-semibold py-2">
                        <i class="bi bi-check-circle me-2"></i>Kayıt Ol
                    </button>
                </form>

                {{-- Divider --}}
                <hr class="my-4">

                {{-- Giriş sayfasına link (zaten hesabı olan kullanıcılar için) --}}
                <p class="text-center text-muted mb-0">
                    Zaten hesabınız var mı? 
                    <a href="{{ route('login') }}" class="text-primary fw-semibold text-decoration-none">
                        Giriş Yap
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>
{{-- İçerik bölümü bitir --}}
@endsection
