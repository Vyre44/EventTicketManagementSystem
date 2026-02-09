@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Kullaniciyi Duzenle</h1>
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">Listeye Don</a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger">
        @foreach($errors->all() as $err)
            <div>{{ $err }}</div>
        @endforeach
    </div>
@endif

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.users.update', $user) }}">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Ad</label>
                        <input name="name" value="{{ old('name', $user->name) }}" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input name="email" value="{{ old('email', $user->email) }}" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rol</label>
                        <select name="role" class="form-select" @if($user->id === auth()->id()) disabled @endif>
                            @foreach(\App\Enums\UserRole::cases() as $role)
                                <option value="{{ $role->value }}" @selected(old('role', $user->role) === $role->value)>{{ $role->value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Yeni Sifre (opsiyonel)</label>
                        <input name="password" type="password" placeholder="Yeni Sifre" class="form-control">
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Sifre Tekrar</label>
                        <input name="password_confirmation" type="password" placeholder="Sifre Tekrar" class="form-control">
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Guncelle</button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Iptal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="fw-semibold mb-2">Not</div>
                <div class="text-muted">Kendi rolunuzu degistiremezsiniz.</div>
            </div>
        </div>
    </div>
</div>
@endsection
