{{-- 
    Katılımcı Siparişler Sayfası
    Kullanıcının geçmiş sipariş listesi (PENDING, PAID, CANCELLED, REFUNDED).
    Filtreler: durum. Arama: sipariş ID. Detay ve ticket viewing bağlantıları.
--}}
@extends('attendee.layouts.app')

{{-- Sayfa içeriği --}}
@section('content')
<div class="container py-4">
    {{-- Başlık bölümü --}}
    <div class="mb-4">
        <h1 class="mb-2">Siparişlerim</h1>
        <p class="text-muted">Aldığınız biletleri ve sipariş durumlarını görebilirsiniz.</p>
    </div>

    {{-- Boş durum mesajı (eğer sipariş yoksa) --}}
    @if($orders->isEmpty())
        {{-- Sipariş yok ise göster --}}
        <div class="text-center py-5">
            {{-- Emoji --}}
            <div class="fs-1 mb-4">🁪</div>
            <h2 class="mb-2">Henüz Siparişiniz Yok</h2>
            <p class="text-muted mb-4">Hemen etkinlikleri keşedin ve biletinizi satın alın!</p>
            {{-- Etkinlikleri keşetme sayfasına linki --}}
            <a href="{{ route('attendee.events.index') }}" class="btn btn-primary btn-lg">
                🁪 Etkinlikleri Keşfet
            </a>
        </div>
    @else
        {{-- Sipariş listesi --}}
        <div class="vstack gap-3">
            @foreach($orders as $order)
                <div class="card p-4" style="cursor: pointer;" onclick="window.location.href='{{ route('attendee.orders.show', $order) }}'">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-3">
                        <!-- Order Info -->
                        <div class="flex-fill">
                            <h5 class="mb-2">
                                {{ $order->event->title }}
                            </h5>
                            <div class="small text-muted vstack gap-1">
                                <div>📋 Sipariş: #{{ $order->id }}</div>
                                <div>📅 {{ $order->created_at->format('d.m.Y H:i') }}</div>
                                <div>🎟️ {{ $order->tickets_count }} Bilet</div>
                            </div>
                        </div>

                        <!-- Amount -->
                        <div class="text-end">
                            <div class="h3 mb-2">
                                ₺{{ number_format($order->total_amount, 2, ',', '.') }}
                            </div>
                            <x-attendee.status-badge :status="$order->status" />
                        </div>

                        <!-- Arrow -->
                        <div class="text-muted fs-4 d-none d-md-block">→</div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-5 d-flex justify-content-center">
            {{ $orders->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
