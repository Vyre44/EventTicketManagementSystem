{{-- Profil bilgileri güncelleme formu (ad, e-posta) - Bootstrap 5 --}}
<h6 class="text-muted mb-3">Hesabınızın temel bilgilerini güncelleyebilirsiniz.</h6>

<form id="send-verification" method="post" action="{{ route('verification.send') }}">
    @csrf
</form>

<form method="post" action="{{ route('profile.update') }}">
    @csrf
    @method('PATCH')

    {{-- Ad Alanı --}}
    <div class="mb-3">
        <label for="name" class="form-label fw-semibold">
            <i class="bi bi-person-fill"></i> Ad
        </label>
        <input 
            type="text" 
            id="name" 
            name="name" 
            class="form-control @error('name') is-invalid @enderror" 
            value="{{ old('name', $user->name) }}" 
            required 
            autofocus 
            autocomplete="name"
            placeholder="Adınız"
        >
        @error('name')
            <div class="invalid-feedback d-block">
                <i class="bi bi-exclamation-circle"></i> {{ $message }}
            </div>
        @enderror
    </div>

    {{-- E-posta Alanı --}}
    <div class="mb-3">
        <label for="email" class="form-label fw-semibold">
            <i class="bi bi-envelope-fill"></i> E-posta
        </label>
        <input 
            type="email" 
            id="email" 
            name="email" 
            class="form-control @error('email') is-invalid @enderror" 
            value="{{ old('email', $user->email) }}" 
            required 
            autocomplete="email"
            placeholder="ornek@example.com"
        >
        @error('email')
            <div class="invalid-feedback d-block">
                <i class="bi bi-exclamation-circle"></i> {{ $message }}
            </div>
        @enderror
    </div>

    {{-- E-posta Doğrulaması (MustVerifyEmail contract) --}}
    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
        <div class="alert alert-warning alert-sm mb-3">
            <i class="bi bi-info-circle"></i> 
            <strong>E-posta doğrulanmamış:</strong> 
            <button form="send-verification" type="submit" class="btn btn-link btn-sm p-0">
                Doğrulama e-postası gönder
            </button>
        </div>

        @if (session('status') === 'verification-link-sent')
            <div class="alert alert-success alert-sm mb-3">
                <i class="bi bi-check-circle"></i> Doğrulama e-postası gönderildi!
            </div>
        @endif
    @endif

    {{-- Butonlar --}}
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-circle"></i> Kaydet
        </button>
    </div>
</form>
