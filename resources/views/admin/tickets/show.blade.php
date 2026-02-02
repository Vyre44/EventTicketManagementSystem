@extends('layouts.app')

@section('content')
<h1>Bilet #{{ $ticket->id }}</h1>
<ul>
    <li><b>Kod:</b> {{ $ticket->code }}</li>
    <li><b>Durum:</b> {{ $ticket->status->name ?? $ticket->status }}</li>
    <li><b>Ticket Type:</b> {{ $ticket->ticketType->name ?? '-' }}</li>
    <li><b>Event:</b> {{ $ticket->ticketType->event->title ?? '-' }}</li>
    <li><b>Order ID:</b> {{ $ticket->order_id ?? '-' }}</li>
    <li><b>Kullanıcı:</b> {{ $ticket->order->user->email ?? '-' }}</li>
    <li><b>Check-in At:</b> {{ $ticket->checked_in_at ? $ticket->checked_in_at->format('Y-m-d H:i:s') : '-' }}</li>
    <li><b>Oluşturulma:</b> {{ $ticket->created_at }}</li>
</ul>
<a href="{{ route('admin.tickets.edit', $ticket) }}">Düzenle</a>
<a href="{{ route('admin.tickets.index') }}">Listeye Dön</a>
@endsection
