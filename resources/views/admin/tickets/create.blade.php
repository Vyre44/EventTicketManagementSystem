@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Yeni Bilet</h1>
    <a href="{{ route('admin.tickets.index') }}" class="btn btn-outline-secondary btn-sm">Listeye Don</a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        @foreach($errors->all() as $err)
            <div>{{ $err }}</div>
        @endforeach
    </div>
@endif

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.tickets.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Ticket Type</label>
                        <select name="ticket_type_id" required class="form-select">
                            <option value="">Sec...</option>
                            @foreach($ticketTypes as $tt)
                                <option value="{{ $tt->id }}" @selected(old('ticket_type_id') == $tt->id)>{{ $tt->event->title }} - {{ $tt->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Order (Opsiyonel)</label>
                        <select name="order_id" class="form-select">
                            <option value="">Yeni Order Olustur</option>
                            @foreach($orders as $order)
                                <option value="{{ $order->id }}" @selected(old('order_id') == $order->id)>Order #{{ $order->id }} - {{ $order->user->email }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kod</label>
                        <input type="text" name="code" value="{{ old('code') }}" required class="form-control">
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Durum</label>
                        <select name="status" required class="form-select">
                            @foreach($statuses as $status)
                                <option value="{{ $status->value }}" @selected(old('status') == $status->value)>{{ $status->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Olustur</button>
                        <a href="{{ route('admin.tickets.index') }}" class="btn btn-outline-secondary">Iptal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="fw-semibold mb-2">Bilgi</div>
                <div class="text-muted">Yeni bilet olustururken bilet tipi ve durum secimini kontrol edin.</div>
            </div>
        </div>
    </div>
</div>
@endsection
