{{-- Organizatör sipariş detay sayfası --}}
@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Sipariş #{{ $order->id }}</h1>
    <a href="{{ route('organizer.orders.index') }}" class="btn btn-outline-secondary btn-sm">Siparişlere Dön</a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body d-flex justify-content-between align-items-start">
        <div>
            <div class="text-muted">Müşteri</div>
            <div class="fw-semibold">{{ $order->user->name }}</div>
            <div class="text-muted mt-2">E-posta</div>
            <div class="fw-semibold">{{ $order->user->email }}</div>
            <div class="text-muted mt-2">Oluşturulma</div>
            <div class="fw-semibold">{{ $order->created_at->format('d.m.Y H:i') }}</div>
        </div>
        <div>
            <div class="text-muted mb-2">Sipariş Durumu</div>
            @if($order->status->value === 'pending')
                <span class="badge bg-warning text-dark">Beklemede</span>
            @elseif($order->status->value === 'paid')
                <span class="badge bg-success">Ödendi</span>
                @if($order->paid_at)
                    <div class="text-muted small mt-2">{{ $order->paid_at->format('d.m.Y H:i') }}</div>
                @endif
            @elseif($order->status->value === 'cancelled')
                <span class="badge bg-danger">İptal</span>
            @elseif($order->status->value === 'refunded')
                <span class="badge bg-secondary">İade</span>
                @if($order->refunded_at)
                    <div class="text-muted small mt-2">{{ $order->refunded_at->format('d.m.Y H:i') }}</div>
                @endif
            @endif
        </div>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger mb-4">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success mb-4">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger mb-4">{{ session('error') }}</div>
@endif

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="text-muted">Etkinlik</div>
                <div class="fw-semibold">{{ $order->event->title }}</div>
            </div>
            <div class="col-md-6">
                <div class="text-muted">Toplam Tutar</div>
                <div class="fw-semibold text-success">{{ number_format($order->total_amount, 2) }} ₺</div>
            </div>
        </div>
    </div>
</div>

@if($order->tickets->isNotEmpty())
    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="card-title mb-3">Biletler ({{ $order->tickets->count() }} adet)</h5>
            
            <div class="space-y-2">
                @foreach($order->tickets as $ticket)
                    <div class="border rounded p-3 d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-semibold">{{ $ticket->ticketType->name }}</div>
                            <div class="text-muted small">Kod: {{ $ticket->code }}</div>
                            @if($ticket->checked_in_at)
                                <div class="text-success small">Giriş: {{ $ticket->checked_in_at->format('d.m.Y H:i') }}</div>
                            @endif
                        </div>

                        <div>
                            @if($ticket->status->value === 'active')
                                <span class="badge bg-primary">Aktif</span>
                            @elseif($ticket->status->value === 'checked_in')
                                <span class="badge bg-success">Kullanıldı</span>
                            @elseif($ticket->status->value === 'cancelled')
                                <span class="badge bg-danger">İptal</span>
                            @elseif($ticket->status->value === 'refunded')
                                <span class="badge bg-secondary">İade</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@else
    <div class="card bg-light">
        <div class="card-body text-center text-muted">Bu siparişle ilişkili bilet bulunmamaktadır.</div>
    </div>
@endif
@endsection
