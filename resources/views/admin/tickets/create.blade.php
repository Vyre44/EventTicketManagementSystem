@extends('layouts.app')

@section('content')
<h1>Yeni Bilet</h1>
@if($errors->any())
    <div style="color:red">
        @foreach($errors->all() as $err)
            {{ $err }}<br>
        @endforeach
    </div>
@endif

<form method="POST" action="{{ route('admin.tickets.store') }}">
    @csrf
    <div>
        <label>Ticket Type:</label>
        <select name="ticket_type_id" required>
            <option value="">Seç...</option>
            @foreach($ticketTypes as $tt)
                <option value="{{ $tt->id }}" @selected(old('ticket_type_id') == $tt->id)>{{ $tt->event->title }} - {{ $tt->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label>Order (Opsiyonel):</label>
        <select name="order_id">
            <option value="">Yeni Order Oluştur</option>
            @foreach($orders as $order)
                <option value="{{ $order->id }}" @selected(old('order_id') == $order->id)>Order #{{ $order->id }} - {{ $order->user->email }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label>Kod:</label>
        <input type="text" name="code" value="{{ old('code') }}" required>
    </div>
    <div>
        <label>Durum:</label>
        <select name="status" required>
            @foreach($statuses as $status)
                <option value="{{ $status->value }}" @selected(old('status') == $status->value)>{{ $status->name }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit">Oluştur</button>
    <a href="{{ route('admin.tickets.index') }}">İptal</a>
</form>
@endsection
