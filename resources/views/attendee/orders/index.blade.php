@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Siparişlerim</h1>

    @if($orders->isEmpty())
        <div class="text-center py-12 text-gray-500">
            <p class="text-xl mb-4">Henüz hiç siparişiniz yok.</p>
            <a href="{{ route('attendee.events.index') }}" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700">
                Etkinliklere Göz At
            </a>
        </div>
    @else
        <div class="space-y-4">
            @foreach($orders as $order)
                <div class="border rounded-lg p-6 hover:shadow-md transition">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h2 class="text-xl font-bold mb-2">
                                <a href="{{ route('attendee.events.show', $order->event_id) }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $order->event->title }}
                                </a>
                            </h2>
                            
                            <div class="text-sm text-gray-600 space-y-1">
                                <div>📅 {{ $order->event->start_time->format('d.m.Y H:i') }}</div>
                                <div>🎟️ {{ $order->tickets_count }} Bilet</div>
                                <div>💳 {{ number_format($order->total_amount, 2) }} ₺</div>
                                <div>🕒 Sipariş Tarihi: {{ $order->created_at->format('d.m.Y H:i') }}</div>
                            </div>
                        </div>

                        <div class="ml-4 text-right">
                            <!-- Status Badge -->
                            @if($order->status->value === 'pending')
                                <span class="inline-block bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-semibold mb-2">
                                    ⏳ Ödeme Bekliyor
                                </span>
                            @elseif($order->status->value === 'paid')
                                <span class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold mb-2">
                                    ✅ Ödendi
                                </span>
                            @elseif($order->status->value === 'cancelled')
                                <span class="inline-block bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-semibold mb-2">
                                    ❌ İptal Edildi
                                </span>
                            @elseif($order->status->value === 'refunded')
                                <span class="inline-block bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-sm font-semibold mb-2">
                                    🔄 İade Edildi
                                </span>
                            @endif

                            <!-- Action Button -->
                            @if($order->status->value === 'pending')
                                <a 
                                    href="{{ route('attendee.orders.show', $order) }}" 
                                    class="block bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm font-semibold"
                                >
                                    Ödemeyi Tamamla
                                </a>
                            @else
                                <a 
                                    href="{{ route('attendee.orders.show', $order) }}" 
                                    class="block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm font-semibold"
                                >
                                    Detayları Gör
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection
