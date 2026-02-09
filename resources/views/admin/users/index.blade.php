@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Kullanıcılar</h1>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">Yeni Kullanıcı</a>
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

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="get" action="{{ route('admin.users.index') }}" class="row g-2 align-items-end">
            <div class="col-md-6">
                <label class="form-label">Ara (ad/e-posta)</label>
                <input name="q" value="{{ $q ?? '' }}" placeholder="Ara (ad/e-posta)" class="form-control">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-outline-primary w-100">Ara</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">No</th>
                        <th>Ad</th>
                        <th>E-posta</th>
                        <th>Rol</th>
                        <th class="text-end pe-3">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td class="ps-3">{{ $user->id }}</td>
                            <td><a href="{{ route('admin.users.show', $user) }}" class="link-primary">{{ $user->name }}</a></td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @php
                                    $roleValue = $user->role->value ?? $user->role;
                                    $roleLabel = '';
                                    $badgeClass = 'bg-secondary';
                                    
                                    if ($roleValue === 'admin') {
                                        $roleLabel = 'Yönetici';
                                        $badgeClass = 'bg-danger';
                                    } elseif ($roleValue === 'organizer') {
                                        $roleLabel = 'Organizatör';
                                        $badgeClass = 'bg-primary';
                                    } elseif ($roleValue === 'attendee') {
                                        $roleLabel = 'Katılımcı';
                                        $badgeClass = 'bg-success';
                                    } else {
                                        $roleLabel = $user->role->name ?? $roleValue;
                                    }
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ $roleLabel }}</span>
                            </td>
                            <td class="text-end pe-3">
                                <div class="d-inline-flex gap-2">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline-secondary btn-sm">Düzenle</a>
                                    @if($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm">Sil</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $users->links('pagination::bootstrap-5') }}
</div>
@endsection
