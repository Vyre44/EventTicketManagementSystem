@if(session('success'))
    <div style="color:green">{{ session('success') }}</div>
@endif

<h1>Kullanıcı #{{ $user->id }}</h1>
<ul>
    <li><b>Ad:</b> {{ $user->name }}</li>
    <li><b>Email:</b> {{ $user->email }}</li>
    <li><b>Rol:</b> {{ $user->role->value ?? $user->role }}</li>
    <li><b>Oluşturulma:</b> {{ $user->created_at }}</li>
</ul>

<a href="{{ route('admin.users.edit', $user) }}">Düzenle</a>
<a href="{{ route('admin.users.index') }}">Listeye Dön</a>
