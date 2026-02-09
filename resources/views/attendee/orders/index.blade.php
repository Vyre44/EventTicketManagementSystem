@extends('attendee.layouts.app')

@section('content')
<div class="container py-4">
    <!-- Header -->
    <div class="mb-4">
        <h1 class="mb-2">🎫 Siparişlerim</h1>
        <p class="text-muted">Aldığınız biletleri ve sipariş durumlarını görebilirsiniz.</p>
    </div>

    <!-- Empty State -->
    @if($orders->isEmpty())
        <div class="text-center py-5">
            <div class="fs-1 mb-4">🎪</div>
            <h2 class="mb-2">Henüz Siparişiniz Yok</h2>
            <p class="text-muted mb-4">Hemen etkinlikleri keşfedin ve biletinizi satın alın!</p>
            <a href="{{ route('attendee.events.index') }}" class="btn btn-primary btn-lg">
                🎪 Etkinlikleri Keşfet
            </a>
        </div>
    @else
        <!-- Orders List -->
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
