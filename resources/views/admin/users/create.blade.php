@if(session('success'))
    <div style="color:green">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div style="color:red">
        @foreach($errors->all() as $err) {{ $err }}<br>@endforeach
    </div>
@endif

<h1>Yeni Kullanıcı</h1>
<form method="POST" action="{{ route('admin.users.store') }}">
    @csrf
    <input name="name" placeholder="Ad" value="{{ old('name') }}">
    <input name="email" placeholder="Email" value="{{ old('email') }}">
    <select name="role">
        @foreach(\App\Enums\UserRole::cases() as $role)
            <option value="{{ $role->value }}" @selected(old('role') === $role->value)>{{ $role->value }}</option>
        @endforeach
    </select>
    <input name="password" type="password" placeholder="Şifre">
    <input name="password_confirmation" type="password" placeholder="Şifre Tekrar">
    <button type="submit">Kaydet</button>
</form>
