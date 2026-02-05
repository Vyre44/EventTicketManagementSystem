@extends('attendee.layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-2">🎫 Siparişlerim</h1>
        <p class="text-gray-600">Aldığınız biletleri ve sipariş durumlarını görebilirsiniz.</p>
    </div>

    <!-- Empty State -->
    @if($orders->isEmpty())
        <div class="text-center py-16">
            <div class="text-6xl mb-4">🎪</div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Henüz Siparişiniz Yok</h2>
            <p class="text-gray-600 mb-6">Hemen etkinlikleri keşfedin ve biletinizi satın alın!</p>
            <a href="{{ route('attendee.events.index') }}" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                🎪 Etkinlikleri Keşfet
            </a>
        </div>
    @else
        <!-- Orders List -->
        <div class="space-y-4">
            @foreach($orders as $order)
                <div class="bg-white border rounded-lg p-6 hover:shadow-md transition cursor-pointer" onclick="window.location.href='{{ route('attendee.orders.show', $order) }}'">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <!-- Order Info -->
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-gray-900 mb-2">
                                {{ $order->event->title }}
                            </h3>
                            <div class="text-sm text-gray-600 space-y-1">
                                <div>📋 Sipariş: #{{ $order->id }}</div>
                                <div>📅 {{ $order->created_at->format('d.m.Y H:i') }}</div>
                                <div>🎟️ {{ $order->tickets_count }} Bilet</div>
                            </div>
                        </div>

                        <!-- Amount -->
                        <div class="text-right">
                            <div class="text-2xl font-bold text-gray-900 mb-2">
                                ₺{{ number_format($order->total_amount, 2, ',', '.') }}
                            </div>
                            <x-attendee.status-badge :status="$order->status" />
                        </div>

                        <!-- Arrow -->
                        <div class="text-gray-400 text-2xl hidden md:block">→</div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-12 flex justify-center">
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection
