@if(session('success'))
    <div style="color:green">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div style="color:red">
        @foreach($errors->all() as $err) {{ $err }}<br>@endforeach
    </div>
@endif

<form method="get" action="{{ route('admin.users.index') }}">
    <input name="q" value="{{ $q ?? '' }}" placeholder="Ara (ad/email)">
    <button type="submit">Ara</button>
</form>

<h1>Kullanıcılar</h1>
<a href="{{ route('admin.users.create') }}">Yeni Kullanıcı</a>
<table>
    <tr>
        <th>ID</th><th>Ad</th><th>Email</th><th>Rol</th><th>İşlem</th>
    </tr>
    @foreach($users as $user)
    <tr>
        <td>{{ $user->id }}</td>
        <td><a href="{{ route('admin.users.show', $user) }}">{{ $user->name }}</a></td>
        <td>{{ $user->email }}</td>
        <td>{{ $user->role }}</td>
        <td>
            <a href="{{ route('admin.users.edit', $user) }}">Düzenle</a>
            @if($user->id !== auth()->id())
            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" style="display:inline">
                @csrf @method('DELETE')
                <button type="submit">Sil</button>
            </form>
            @endif
        </td>
    </tr>
    @endforeach
</table>
{{ $users->links() }}
