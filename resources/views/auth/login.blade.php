{{-- Giriş sayfası: Kullanıcıların sisteme giriş yapması için Bootstrap 5 formu --}}
@extends('layouts.app')

@section('content')
<div class="row justify-content-center" style="margin-top: 60px;">
    <div class="col-lg-5 col-md-6">
        {{-- Proje başlığı --}}
        <div class="text-center mb-5">
            <h1 class="h3 fw-bold text-primary mb-1">🎫 Bilet Yönetim Sistemi</h1>
        </div>

        {{-- Login kartı --}}
        <div class="card shadow-sm">
            <div class="card-body p-5">
                {{-- Kart başlığı --}}
                <h2 class="h4 fw-bold mb-4 text-center">Giriş Yap</h2>

                {{-- Genel hata mesajı (auth başarısız) --}}
                @if($errors->has('email'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        {{ $errors->first('email') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                {{-- Login formu --}}
                <form method="POST" action="{{ route('login') }}">
                    {{-- CSRF token: Güvenlik için gerekli --}}
                    @csrf

                    {{-- E-posta alanı --}}
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
                            required 
                            autofocus
                            placeholder="ornek@email.com"
                        >
                        @error('email')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Şifre alanı --}}
                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">
                            <i class="bi bi-lock me-1"></i>Şifre
                        </label>
                        <input 
                            type="password" 
                            class="form-control @error('password') is-invalid @enderror" 
                            id="password"
                            name="password" 
                            required
                            placeholder="••••••••"
                        >
                        @error('password')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Remember me checkbox --}}
                    <div class="mb-4">
                        <div class="form-check">
                            <input 
                                class="form-check-input" 
                                type="checkbox" 
                                id="remember"
                                name="remember"
                                {{ old('remember') ? 'checked' : '' }}
                            >
                            <label class="form-check-label" for="remember">
                                Beni hatırla
                            </label>
                        </div>
                    </div>

                    {{-- Giriş butonu --}}
                    <button type="submit" class="btn btn-primary w-100 fw-semibold py-2">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Giriş Yap
                    </button>
                </form>

                {{-- Şifremi Unuttum linki --}}
                <div class="text-center mt-4">
                    <a href="{{ route('password.request') }}" class="text-decoration-none text-muted">
                        <small>Şifremi Unuttum?</small>
                    </a>
                </div>

                {{-- Divider --}}
                <hr class="my-4">

                {{-- Kayıt ol bağlantısı --}}
                <p class="text-center text-muted mb-0">
                    Hesabınız yok mu? 
                    <a href="{{ route('register') }}" class="text-primary fw-semibold text-decoration-none">
                        Kayıt Ol
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
