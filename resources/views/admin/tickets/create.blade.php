@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Yeni Bilet</h1>
    <a href="{{ route('admin.tickets.index') }}" class="btn btn-outline-secondary btn-sm">Listeye Dön</a>
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
                        <label class="form-label">Bilet Tipi</label>
                        <select name="ticket_type_id" required class="form-select">
                            <option value="">Seç...</option>
                            @foreach($ticketTypes as $tt)
                                <option value="{{ $tt->id }}" @selected(old('ticket_type_id') == $tt->id)>{{ $tt->event->title }} - {{ $tt->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sipariş (Opsiyonel)</label>
                        <select name="order_id" class="form-select">
                            <option value="">Yeni Sipariş Oluştur</option>
                            @foreach($orders as $order)
                                <option value="{{ $order->id }}" @selected(old('order_id') == $order->id)>Sipariş #{{ $order->id }} - {{ $order->user->email }}</option>
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
                        <button type="submit" class="btn btn-primary">Oluştur</button>
                        <a href="{{ route('admin.tickets.index') }}" class="btn btn-outline-secondary">İptal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="fw-semibold mb-2">Bilgi</div>
                <div class="text-muted">Yeni bilet oluştururken bilet tipi ve durum seçimini kontrol edin.</div>
            </div>
        </div>
    </div>
</div>
@endsection
