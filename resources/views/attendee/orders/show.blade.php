@extends('attendee.layouts.app')

@section('content')

<div class="container py-4">
    <div class="mb-4">
        <a href="{{ route('attendee.orders.index') }}" class="btn btn-outline-secondary btn-sm mb-3">
            ← Tüm Siparişler
        </a>
        <h1 class="h4 fw-bold mb-0">Sipariş Detayı</h1>
    </div>

    <!-- Sipariş Bilgileri -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h2 class="h5 fw-bold mb-2">{{ $order->event->title }}</h2>
                    <div class="text-muted small">
                        <div class="mb-1">📅 {{ $order->event->start_time->format('d.m.Y H:i') }}</div>
                        <div class="mb-1">🕒 Sipariş Tarihi: {{ $order->created_at->format('d.m.Y H:i') }}</div>
                        @if($order->paid_at)
                            <div class="mb-0">💳 Ödeme Tarihi: {{ $order->paid_at->format('d.m.Y H:i') }}</div>
                        @endif
                    </div>
                </div>

                <!-- Status Badge -->
                <div id="order-status-badge">
                    <x-attendee.status-badge :status="$order->status" />
                </div>
            </div>

            <hr>

            <!-- Toplam Tutar -->
            <div class="d-flex justify-content-between">
                <span class="fw-bold">Toplam Tutar:</span>
                <span class="fw-bold text-success fs-5">{{ number_format($order->total_amount, 2) }} ₺</span>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success" role="alert">
            ✅ {{ session('success') }}
        </div>
    @endif

    <!-- Biletler -->
    @if($order->tickets->isNotEmpty())
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h5 fw-bold mb-3">Biletleriniz ({{ $order->tickets->count() }} adet)</h2>
                
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Bilet Tipi</th>
                                <th>Kod</th>
                                <th>Durum</th>
                                <th>Giriş</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->tickets as $ticket)
                                <tr>
                                    <td class="fw-semibold">{{ $ticket->ticketType->name }}</td>
                                    <td class="font-monospace small">{{ $ticket->code }}</td>
                                    <td>
                                        <div class="ticket-status-badge">
                                            <x-attendee.status-badge :status="$ticket->status" />
                                        </div>
                                    </td>
                                    <td>
                                        @if($ticket->checked_in_at)
                                            <span class="text-success small">✅ {{ $ticket->checked_in_at->format('d.m.Y H:i') }}</span>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- QR Kod Bilgisi -->
                <div class="alert alert-info mt-3 mb-0" role="alert">
                    <small>
                        💡 <strong>İpucu:</strong> Biletlerinizi etkinlik girişinde gösterin. 
                        Bilet kodlarınızı not alın veya bu sayfayı kaydırın.
                    </small>
                </div>
            </div>
        </div>
    @endif

    <!-- İşlem Butonları (Duruma Bağlı) -->
    <div id="order-actions">
        @if($order->status === \App\Enums\OrderStatus::PENDING)
            <div class="d-grid gap-3">
                <button 
                    type="button"
                    id="order-pay-btn" 
                    data-order-id="{{ $order->id }}"
                    class="btn btn-success btn-lg"
                >
                    ✓ Ödemeyi Tamamla
                </button>
                <button 
                    type="button"
                    id="order-cancel-btn" 
                    data-order-id="{{ $order->id }}"
                    class="btn btn-outline-danger btn-lg"
                >
                    ❌ İptal Et
                </button>
            </div>
        @elseif($order->status === \App\Enums\OrderStatus::PAID)
            <button 
                type="button"
                id="order-refund-btn" 
                data-order-id="{{ $order->id }}"
                class="btn btn-warning btn-lg w-100"
            >
                ↩️ İade Talep Et
            </button>
        @elseif($order->status === \App\Enums\OrderStatus::CANCELLED)
            <div class="alert alert-danger text-center" role="alert">
                <p class="fw-semibold mb-0">Bu sipariş iptal edilmiştir. Başka bir işlem yapılamaz.</p>
            </div>
        @elseif($order->status === \App\Enums\OrderStatus::REFUNDED)
            <div class="alert alert-secondary text-center" role="alert">
                <p class="fw-semibold mb-1">Bu sipariş için iade işlemi tamamlanmıştır.</p>
                <p class="text-muted small mb-0">Ödemeniz 3-5 gün içinde hesabınıza yatırılacaktır.</p>
            </div>
        @endif
    </div>

    <!-- Siparişlere Dönüş Butonu -->
    <div class="mt-4 text-center">
        <a href="{{ route('attendee.orders.index') }}" class="btn btn-outline-secondary">
            ← Siparişlerime Dön
        </a>
    </div>
</div>
@endsection
