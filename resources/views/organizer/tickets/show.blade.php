{{-- Organizatör bilet detay sayfası --}}
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Alert Container (AJAX) -->
    <div id="ajax-alert-container"></div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('organizer.tickets.index') }}" class="btn btn-outline-secondary btn-sm mb-3 d-inline-block">
                ← Tüm Biletler
            </a>
            <h1 class="h4 mb-2">Bilet Detayı</h1>
        </div>
    </div>

    <!-- Bilet Bilgileri -->
    <div class="card shadow-sm mb-4" data-ticket-id="{{ $ticket->id }}" data-ticket-status="{{ $ticket->status->value }}">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h2 class="h5 font-monospace mb-3">{{ $ticket->code }}</h2>
                    <div class="text-muted small">
                        <div>🎫 <strong>Etkinlik:</strong> {{ $ticket->ticketType->event->title }}</div>
                        <div class="mt-1">🏷️ <strong>Bilet Tipi:</strong> {{ $ticket->ticketType->name }}</div>
                        <div class="mt-1">💰 <strong>Fiyat:</strong> {{ number_format($ticket->ticketType->price, 2) }} ₺</div>
                        <div class="mt-1">📅 <strong>Oluşturulma:</strong> {{ $ticket->created_at->format('d.m.Y H:i') }}</div>
                    </div>
                </div>

                <!-- Status Badge -->
                <div>
                    <span class="ticket-status-badge">
                        @if($ticket->status === \App\Enums\TicketStatus::ACTIVE)
                            <span class="badge bg-primary">Aktif</span>
                        @elseif($ticket->status === \App\Enums\TicketStatus::CHECKED_IN)
                            <span class="badge bg-success">✅ Kullanıldı</span>
                            @if($ticket->checked_in_at)
                                <div class="text-muted small mt-2">
                                    Giriş: {{ $ticket->checked_in_at->format('d.m.Y H:i') }}
                                </div>
                            @endif
                        @elseif($ticket->status === \App\Enums\TicketStatus::CANCELLED)
                            <span class="badge bg-danger">❌ İptal</span>
                        @elseif($ticket->status === \App\Enums\TicketStatus::REFUNDED)
                            <span class="badge bg-secondary">🔄 İade</span>
                        @endif
                    </span>
                </div>
            </div>

            <hr class="my-3">

            <!-- Sipariş ve Müşteri Bilgileri -->
            @if($ticket->order)
                <div>
                    <h5 class="mb-2">Sipariş Bilgileri</h5>
                    <div class="text-muted small">
                        <div>📌 <strong>Sipariş No:</strong> 
                            <a href="{{ route('organizer.orders.show', $ticket->order) }}" class="link-primary">
                                #{{ $ticket->order->id }}
                            </a>
                        </div>
                        <div class="mt-1">👤 <strong>Müşteri:</strong> {{ $ticket->order->user->name }}</div>
                        <div class="mt-1">📧 <strong>E-posta:</strong> {{ $ticket->order->user->email }}</div>
                        @if($ticket->order->user->phone)
                            <div class="mt-1">📱 <strong>Telefon:</strong> {{ $ticket->order->user->phone }}</div>
                        @endif
                        <div class="mt-1">💳 <strong>Sipariş Durumu:</strong>
                            @if($ticket->order->status === \App\Enums\OrderStatus::PENDING)
                                <span class="text-warning">Ödeme Bekliyor</span>
                            @elseif($ticket->order->status === \App\Enums\OrderStatus::PAID)
                                <span class="text-success">Ödendi</span>
                            @elseif($ticket->order->status === \App\Enums\OrderStatus::CANCELLED)
                                <span class="text-danger">İptal</span>
                            @elseif($ticket->order->status === \App\Enums\OrderStatus::REFUNDED)
                                <span class="text-muted">İade</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- İşlem Butonları -->
    <div class="d-flex gap-2 flex-wrap ticket-actions mb-4">
        @if($ticket->status === \App\Enums\TicketStatus::ACTIVE)
            <button class="ticket-action-btn btn btn-success" data-action="checkin">
                ✅ Giriş Onayla
            </button>
            <button class="ticket-action-btn btn btn-danger" data-action="cancel">
                ❌ İptal Et
            </button>
        @elseif($ticket->status === \App\Enums\TicketStatus::CHECKED_IN)
            <button class="ticket-action-btn btn btn-warning" data-action="undo">
                ↩️ Giriş Onayını Geri Al
            </button>
        @else
            <span class="text-muted small fw-medium fst-italic">Bu bilet için işlem yapılamaz.</span>
        @endif
    </div>
</div>

<script>
    // Route'ları Blade'den al
    const routeNameMap = {
        'checkin': '{{ route("organizer.tickets.checkin", ["ticket" => $ticket->id]) }}',
        'undo': '{{ route("organizer.tickets.checkinUndo", ["ticket" => $ticket->id]) }}',
        'cancel': '{{ route("organizer.tickets.cancel", ["ticket" => $ticket->id]) }}'
    };
</script>
@endsection
