@if(session('success'))
    <div style="color:green">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div style="color:red">@foreach($errors->all() as $err) {{ $err }}<br>@endforeach</div>
@endif

<form method="get" action="{{ route('admin.ticket-types.index') }}">
    <select name="event_id">
        <option value="">Tüm Etkinlikler</option>
        @foreach($events as $event)
            <option value="{{ $event->id }}" @selected((string)$eventId === (string)$event->id)>{{ $event->title }}</option>
        @endforeach
    </select>
    <input name="q" value="{{ $q ?? '' }}" placeholder="Ad ara">
    <button type="submit">Filtrele</button>
</form>

<a href="{{ route('admin.ticket-types.create') }}">Yeni TicketType</a>
<table>
    <tr>
        <th>ID</th><th>Event</th><th>Ad</th><th>Fiyat</th><th>Kota</th><th>Aktif</th><th>İşlem</th>
    </tr>
    @foreach($ticketTypes as $tt)
    <tr>
        <td>{{ $tt->id }}</td>
        <td>{{ $tt->event->title ?? '-' }}</td>
        <td>{{ $tt->name }}</td>
        <td>{{ $tt->price }}</td>
        <td>{{ $tt->quota }}</td>
        <td>{{ $tt->is_active ? 'Evet' : 'Hayır' }}</td>
        <td>
            <a href="{{ route('admin.ticket-types.show', $tt) }}">Görüntüle</a>
            <a href="{{ route('admin.ticket-types.edit', $tt) }}">Düzenle</a>
            <form method="POST" action="{{ route('admin.ticket-types.destroy', $tt) }}" style="display:inline">
                @csrf @method('DELETE')
                <button type="submit">Sil</button>
            </form>
        </td>
    </tr>
    @endforeach
</table>
{{ $ticketTypes->links() }}
