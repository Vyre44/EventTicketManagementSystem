{{-- Kullanıcı detay sayfası --}}
@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Kullanıcı #{{ $user->id }}</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary btn-sm">Düzenle</a>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">Listeye Dön</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

{{-- Kullanıcı özet kartı --}}
<div class="card shadow-sm mb-4">
    <div class="card-body d-flex justify-content-between align-items-start">
        <div>
            <div class="text-muted">Kullanıcı Özeti</div>
            <div class="h5 mb-0">{{ $user->name }}</div>
        </div>
        {{-- Kullanıcı rolü badge --}}
        <span class="badge bg-secondary">{{ $user->role->value ?? $user->role }}</span>
    </div>
</div>

{{-- Kullanıcı detayları --}}
<div class="card shadow-sm">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="text-muted">E-posta</div>
                <div class="fw-semibold">{{ $user->email }}</div>
            </div>
            <div class="col-md-6">
                <div class="text-muted">Oluşturulma</div>
                <div class="fw-semibold">{{ $user->created_at }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
