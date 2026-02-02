@extends('layouts.app')

@section('content')
<h1>Order #{{ $order->id }}</h1>
<ul>
    <li><b>User Email:</b> {{ $order->user->email ?? '-' }}</li>
    <li><b>Status:</b> {{ $order->status->name ?? $order->status }}</li>
    <li><b>Created At:</b> {{ $order->created_at }}</li>
</ul>
<h2>Tickets</h2>
<table border="1" cellpadding="6" cellspacing="0">
    <tr>
        <th>Ticket ID</th>
        <th>Status</th>
        <th>Ticket Type</th>
        <th>Event</th>
    </tr>
    @foreach($order->tickets as $ticket)
    <tr>
        <td>{{ $ticket->id }}</td>
        <td>{{ $ticket->status->name ?? $ticket->status }}</td>
        <td>{{ $ticket->ticketType->name ?? '-' }}</td>
        <td>{{ $ticket->ticketType->event->title ?? '-' }}</td>
    </tr>
    @endforeach
</table>
<a href="{{ route('admin.orders.index') }}">Back to Orders</a>
@endsection
