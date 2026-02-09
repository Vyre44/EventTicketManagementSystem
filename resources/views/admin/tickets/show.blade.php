@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Bilet #{{ $ticket->id }}</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.tickets.index') }}" class="btn btn-outline-secondary btn-sm">Tüm Biletler</a>
        <a href="{{ route('admin.tickets.edit', $ticket) }}" class="btn btn-primary btn-sm">Düzenle</a>
    </div>
</div>

<div class="card shadow-sm mb-4" data-ticket-id="{{ $ticket->id }}" data-ticket-status="{{ $ticket->status->value }}">
    <div class="card-body d-flex justify-content-between align-items-start">
        <div>
            <div class="text-muted">Bilet Kodu</div>
            <div class="h5 mb-1 font-monospace">{{ $ticket->code }}</div>
            <div class="text-muted">Oluşturulma: {{ $ticket->created_at->format('d.m.Y H:i') }}</div>
        </div>
        <span class="ticket-status-badge">
            @if($ticket->status === \App\Enums\TicketStatus::ACTIVE)
                <span class="badge bg-primary">Aktif</span>
            @elseif($ticket->status === \App\Enums\TicketStatus::CHECKED_IN)
                <span class="badge bg-success">Kullanıldı</span>
                @if($ticket->checked_in_at)
                    <div class="text-muted small mt-2">Check-in: {{ $ticket->checked_in_at->format('d.m.Y H:i') }}</div>
                @endif
            @elseif($ticket->status === \App\Enums\TicketStatus::CANCELLED)
                <span class="badge bg-danger">İptal</span>
            @elseif($ticket->status === \App\Enums\TicketStatus::REFUNDED)
                <span class="badge bg-secondary">İade</span>
            @endif
        </span>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="fw-semibold mb-2">Bilet Bilgileri</div>
                <div class="text-muted">Etkinlik</div>
                <div class="fw-semibold mb-2">{{ $ticket->ticketType->event->title }}</div>
                <div class="text-muted">Bilet Tipi</div>
                <div class="fw-semibold mb-2">{{ $ticket->ticketType->name }}</div>
                <div class="text-muted">Fiyat</div>
                <div class="fw-semibold">{{ number_format($ticket->ticketType->price, 2) }} ₺</div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="fw-semibold mb-2">Sipariş Bilgileri</div>
                @if($ticket->order)
                    <div class="text-muted">Sipariş No</div>
                    <div class="fw-semibold mb-2">
                        <a href="{{ route('admin.orders.show', $ticket->order) }}" class="link-primary">#{{ $ticket->order->id }}</a>
                    </div>
                    <div class="text-muted">Müşteri</div>
                    <div class="fw-semibold mb-2">{{ $ticket->order->user->name }}</div>
                    <div class="text-muted">E-mail</div>
                    <div class="fw-semibold mb-2">{{ $ticket->order->user->email }}</div>
                    @if($ticket->order->user->phone)
                        <div class="text-muted">Telefon</div>
                        <div class="fw-semibold mb-2">{{ $ticket->order->user->phone }}</div>
                    @endif
                    <div class="text-muted">Sipariş Durumu</div>
                    <div class="fw-semibold">
                        @if($ticket->order->status === \App\Enums\OrderStatus::PENDING)
                            <span class="badge bg-warning text-dark">Ödeme Bekliyor</span>
                        @elseif($ticket->order->status === \App\Enums\OrderStatus::PAID)
                            <span class="badge bg-success">Ödendi</span>
                        @elseif($ticket->order->status === \App\Enums\OrderStatus::CANCELLED)
                            <span class="badge bg-danger">İptal</span>
                        @elseif($ticket->order->status === \App\Enums\OrderStatus::REFUNDED)
                            <span class="badge bg-secondary">İade</span>
                        @endif
                    </div>
                @else
                    <div class="text-muted">Bu bilet için sipariş bilgisi yok.</div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body d-flex justify-content-between align-items-center">
        <div class="fw-semibold">İşlemler</div>
        <div class="d-flex gap-2 ticket-actions">
            @if($ticket->status === \App\Enums\TicketStatus::ACTIVE)
                <button class="ticket-action-btn btn btn-outline-danger" data-action="cancel">İptal Et</button>
            @else
                <span class="text-muted">Bu bilet için işlem yapılamaz.</span>
            @endif
        </div>
    </div>
</div>

<script>
    // Routes mapping for admin (cancel only)
    const routeNameMap = {
        'cancel': '{{ route("admin.tickets.cancelTicket", ["ticket" => $ticket->id]) }}'
    };
</script>
@endsection
