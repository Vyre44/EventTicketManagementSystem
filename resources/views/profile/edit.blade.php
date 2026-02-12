{{-- Profil düzenleme sayfası: Kullanıcı bilgileri, şifre ve hesap silme --}}
@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            {{-- Başlık --}}
            <h1 class="h3 fw-bold mb-5">
                <i class="bi bi-person-circle"></i> Profil Ayarları
            </h1>

            {{-- Başarı mesajı --}}
            @if (session('status') === 'profile-updated')
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i>
                    <strong>Başarılı!</strong> {{ session('message', 'Profil bilgileri güncellendi.') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
                </div>
            @endif

            {{-- 1. Profil Bilgileri Kartı --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary bg-opacity-10 border-primary">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-person"></i> Kişisel Bilgiler
                    </h5>
                </div>
                <div class="card-body">
                    {{-- Profil bilgileri formu partial'ını dahil et --}}
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            {{-- 2. Şifre Güncelleme Kartı --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-warning bg-opacity-10 border-warning">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-shield-lock"></i> Şifre Değiştir
                    </h5>
                </div>
                <div class="card-body">
                    {{-- Şifre güncelleme formu partial'ını dahil et --}}
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            {{-- 3. Hesap Silme Kartı --}}
            <div class="card shadow-sm border-danger">
                <div class="card-header bg-danger bg-opacity-10 border-danger">
                    <h5 class="card-title mb-0 text-danger">
                        <i class="bi bi-exclamation-triangle"></i> Hesap Silme
                    </h5>
                </div>
                <div class="card-body">
                    {{-- Hesap silme formu partial'ını dahil et --}}
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
