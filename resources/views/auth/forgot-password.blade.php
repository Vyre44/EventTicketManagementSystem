{{-- Şifresi unutmuş kullanıcılar için şifre sıfırlama isteği sayfası (Bootstrap 5) --}}
@extends('layouts.app')

{{-- Sayfanın içerik kısmı --}}
@section('content')
<div class="row justify-content-center" style="margin-top: 60px;">
    <div class="col-lg-5 col-md-6">
        {{-- Proje başlığı --}}
        <div class="text-center mb-5">
            <h1 class="h3 fw-bold text-primary mb-1">🎫 Bilet Yönetim Sistemi</h1>
        </div>

        {{-- Şifre sıfırlama kartı --}}
        <div class="card shadow-sm">
            <div class="card-body p-5">
                {{-- Kart başlığı --}}
                <h2 class="h4 fw-bold mb-4 text-center">Şifre Sıfırlama</h2>

                {{-- Başarı mesajı göster (eğer e-posta gönderildiyse) --}}
                @if (session('status'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i>
                        {{ session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                {{-- E-posta gönderme formu --}}
                <form method="POST" action="{{ route('password.email') }}">
                    @csrf
                    
                    {{-- E-posta adresi input alanı --}}
                    <div class="mb-4">
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
                        {{-- E-posta doğrulama hatası varsa göster --}}
                        @error('email')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-semibold py-2">
                        <i class="bi bi-send me-2"></i>Sıfırlama Linki Gönder
                    </button>
                    
                    {{-- Giriş ekranına dön linki --}}
                    <div class="text-center mt-4">
                        <a href="{{ route('login') }}" class="text-decoration-none text-muted">
                            <small>Giriş ekranına dön</small>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
