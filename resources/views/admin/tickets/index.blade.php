@extends('layouts.app')

@section('content')
<h1>Biletler</h1>
<form method="get" style="margin-bottom:16px">
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Kod veya ID ara">
    <select name="status">
        <option value="">Tüm Durumlar</option>
        @foreach($statuses as $status)
            <option value="{{ $status->value }}" @selected(request('status') == $status->value)>{{ $status->name }}</option>
        @endforeach
    </select>
    <input type="text" name="user_email" value="{{ request('user_email') }}" placeholder="Kullanıcı Email">
    <input type="text" name="event_id" value="{{ request('event_id') }}" placeholder="Event ID">
    <button type="submit">Filtrele</button>
</form>

<a href="{{ route('admin.tickets.create') }}">Yeni Bilet</a>
<table border="1" cellpadding="6" cellspacing="0">
    <tr>
        <th>ID</th><th>Kod</th><th>Durum</th><th>Ticket Type</th><th>Event</th><th>Kullanıcı</th><th>Check-in</th><th>İşlem</th>
    </tr>
    @foreach($tickets as $ticket)
    <tr>
        <td>{{ $ticket->id }}</td>
        <td>{{ $ticket->code }}</td>
        <td>{{ $ticket->status->name ?? $ticket->status }}</td>
        <td>{{ $ticket->ticketType->name ?? '-' }}</td>
        <td>{{ $ticket->ticketType->event->title ?? '-' }}</td>
        <td>{{ $ticket->order->user->email ?? '-' }}</td>
        <td>{{ $ticket->checked_in_at ? $ticket->checked_in_at->format('Y-m-d H:i') : '-' }}</td>
        <td>
            <a href="{{ route('admin.tickets.show', $ticket) }}">Görüntüle</a>
            <a href="{{ route('admin.tickets.edit', $ticket) }}">Düzenle</a>
            <form method="POST" action="{{ route('admin.tickets.destroy', $ticket) }}" style="display:inline">
                @csrf @method('DELETE')
                <button type="submit">İptal</button>
            </form>
        </td>
    </tr>
    @endforeach
</table>
{{ $tickets->links() }}
@endsection
