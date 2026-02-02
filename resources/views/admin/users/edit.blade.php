@if(session('success'))
    <div style="color:green">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div style="color:red">
        @foreach($errors->all() as $err) {{ $err }}<br>@endforeach
    </div>
@endif

<h1>Kullanıcıyı Düzenle</h1>
<form method="POST" action="{{ route('admin.users.update', $user) }}">
    @csrf @method('PUT')
    <input name="name" value="{{ old('name', $user->name) }}">
    <input name="email" value="{{ old('email', $user->email) }}">
    <select name="role" @if($user->id === auth()->id()) disabled @endif>
        @foreach(\App\Enums\UserRole::cases() as $role)
            <option value="{{ $role->value }}" @selected(old('role', $user->role) === $role->value)>{{ $role->value }}</option>
        @endforeach
    </select>
    <input name="password" type="password" placeholder="Yeni Şifre (opsiyonel)">
    <input name="password_confirmation" type="password" placeholder="Şifre Tekrar">
    <button type="submit">Güncelle</button>
</form>
