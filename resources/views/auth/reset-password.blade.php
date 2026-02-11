{{-- Şifre sıfırlama linkinden gelen kullanıcının yeni şifre belirleme sayfası (Bootstrap 5) --}}
@extends('layouts.app')

@section('content')
<div class="row justify-content-center" style="margin-top: 60px;">
    <div class="col-lg-5 col-md-6">
        {{-- Proje başlığı --}}
        <div class="text-center mb-5">
            <h1 class="h3 fw-bold text-primary mb-1">🎫 Bilet Yönetim Sistemi</h1>
        </div>

        {{-- Şifre yenileme kartı --}}
        <div class="card shadow-sm">
            <div class="card-body p-5">
                {{-- Kart başlığı --}}
                <h2 class="h4 fw-bold mb-4 text-center">Şifreyi Yenile</h2>

                {{-- Yeni şifre kaydetme formu --}}
                <form method="POST" action="{{ route('password.update') }}">
                    {{-- Form güvenliği için CSRF token --}}
                    @csrf
                    {{-- Şifre sıfırlama tokenı (gizli alan) --}}
                    <input type="hidden" name="token" value="{{ $token }}">

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
                            placeholder="ornek@email.com"
                            required
                        >
                        @error('email')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Yeni şifre input alanı --}}
                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">
                            <i class="bi bi-lock me-1"></i>Yeni Şifre
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

                    {{-- Şifre onayı (doğrulama alanı) --}}
                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label fw-semibold">
                            <i class="bi bi-lock-check me-1"></i>Yeni Şifre (Tekrar)
                        </label>
                        <input 
                            type="password" 
                            class="form-control" 
                            id="password_confirmation"
                            name="password_confirmation" 
                            placeholder="Şifreyi Tekrarlayın"
                            required
                        >
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-semibold py-2">
                        <i class="bi bi-check-circle me-2"></i>Şifreyi Güncelle
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
