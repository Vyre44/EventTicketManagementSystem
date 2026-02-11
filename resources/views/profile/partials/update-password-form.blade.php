{{-- Parola güncelleme formu - Bootstrap 5 --}}
<h6 class="text-muted mb-3">Hesabınızın güvenliği için güçlü ve benzersiz bir şifre kullanın.</h6>

<form method="post" action="{{ route('password.update') }}">
    @csrf
    @method('PUT')

    {{-- Mevcut Şifre --}}
    <div class="mb-3">
        <label for="update_password_current_password" class="form-label fw-semibold">
            <i class="bi bi-lock-fill"></i> Mevcut Şifre
        </label>
        <input 
            type="password" 
            id="update_password_current_password" 
            name="current_password" 
            class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" 
            autocomplete="current-password"
            placeholder="Mevcut şifrenizi girin"
        >
        @error('current_password', 'updatePassword')
            <div class="invalid-feedback d-block">
                <i class="bi bi-exclamation-circle"></i> {{ $message }}
            </div>
        @enderror
    </div>

    {{-- Yeni Şifre --}}
    <div class="mb-3">
        <label for="update_password_password" class="form-label fw-semibold">
            <i class="bi bi-shield-check"></i> Yeni Şifre
        </label>
        <input 
            type="password" 
            id="update_password_password" 
            name="password" 
            class="form-control @error('password', 'updatePassword') is-invalid @enderror" 
            autocomplete="new-password"
            placeholder="Yeni şifrenizi girin"
        >
        @error('password', 'updatePassword')
            <div class="invalid-feedback d-block">
                <i class="bi bi-exclamation-circle"></i> {{ $message }}
            </div>
        @enderror
    </div>

    {{-- Şifre Onayı --}}
    <div class="mb-3">
        <label for="update_password_password_confirmation" class="form-label fw-semibold">
            <i class="bi bi-check2-circle"></i> Şifreyi Onayla
        </label>
        <input 
            type="password" 
            id="update_password_password_confirmation" 
            name="password_confirmation" 
            class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror" 
            autocomplete="new-password"
            placeholder="Yeni şifrenizi tekrar girin"
        >
        @error('password_confirmation', 'updatePassword')
            <div class="invalid-feedback d-block">
                <i class="bi bi-exclamation-circle"></i> {{ $message }}
            </div>
        @enderror
    </div>

    {{-- Butonlar --}}
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-circle"></i> Şifreyi Güncelle
        </button>

        @if (session('status') === 'password-updated')
            <div class="alert alert-success alert-sm mb-0">
                <i class="bi bi-check-circle"></i> Şifre güncellendi!
            </div>
        @endif
    </div>
</form>
