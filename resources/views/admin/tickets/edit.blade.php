@extends('layouts.app')

@section('content')
<h1>Bilet Düzenle #{{ $ticket->id }}</h1>
@if($errors->any())
    <div style="color:red">
        @foreach($errors->all() as $err)
            {{ $err }}<br>
        @endforeach
    </div>
@endif

<form method="POST" action="{{ route('admin.tickets.update', $ticket) }}">
    @csrf @method('PUT')
    <div>
        <label>Ticket Type:</label>
        <select name="ticket_type_id">
            <option value="">Değiştirme</option>
            @foreach($ticketTypes as $tt)
                <option value="{{ $tt->id }}" @selected(old('ticket_type_id', $ticket->ticket_type_id) == $tt->id)>{{ $tt->event->title }} - {{ $tt->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label>Durum:</label>
        <select name="status">
            <option value="">Değiştirme</option>
            @foreach($statuses as $status)
                <option value="{{ $status->value }}" @selected(old('status', $ticket->status->value) == $status->value)>{{ $status->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label>Check-in Zamanı (Opsiyonel):</label>
        <input type="datetime-local" name="checked_in_at" value="{{ old('checked_in_at', $ticket->checked_in_at ? $ticket->checked_in_at->format('Y-m-d\TH:i') : '') }}">
    </div>
    <button type="submit">Güncelle</button>
    <a href="{{ route('admin.tickets.show', $ticket) }}">İptal</a>
</form>
@endsection
