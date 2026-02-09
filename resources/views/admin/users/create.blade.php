@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Yeni Kullanıcı</h1>
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">Listeye Dön</a>
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
                <form method="POST" action="{{ route('admin.users.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Ad</label>
                        <input name="name" placeholder="Ad" value="{{ old('name') }}" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">E-posta</label>
                        <input name="email" placeholder="E-posta" value="{{ old('email') }}" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rol</label>
                        <select name="role" class="form-select">
                            @foreach(\App\Enums\UserRole::cases() as $role)
                                <option value="{{ $role->value }}" @selected(old('role') === $role->value)>{{ $role->value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Şifre</label>
                        <input name="password" type="password" placeholder="Şifre" class="form-control">
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Şifre Tekrar</label>
                        <input name="password_confirmation" type="password" placeholder="Şifre Tekrar" class="form-control">
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Kaydet</button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">İptal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="fw-semibold mb-2">Yardım</div>
                <div class="text-muted">Rol seçimi, kullanıcının yetkilerini belirler.</div>
            </div>
        </div>
    </div>
</div>
@endsection
