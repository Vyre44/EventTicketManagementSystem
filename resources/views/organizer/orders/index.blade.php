@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold">Siparişler</h1>
        <p class="text-gray-600 mt-1">
            @if(auth()->user()->isAdmin())
                Tüm siparişleri yönetin
            @else
                Kendi event'lerinizin siparişlerini görüntüleyin
            @endif
        </p>
    </div>

    @if($orders->isEmpty())
        <div class="bg-white border rounded-lg p-8 text-center">
            <p class="text-gray-600">Henüz sipariş bulunmamaktadır.</p>
        </div>
    @else
        <div class="bg-white border rounded-lg overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Sipariş ID</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Etkinlik</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Müşteri</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Tuttar</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Bilet Sayısı</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Status</th>
                        <th class="px-6 py-3 text-center text-sm font-semibold">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm">#{{ $order->id }}</td>
                            <td class="px-6 py-4 text-sm font-medium">{{ $order->event->title }}</td>
                            <td class="px-6 py-4 text-sm">
                                <div>{{ $order->user->name }}</div>
                                <div class="text-gray-600 text-xs">{{ $order->user->email }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium">{{ number_format($order->total_amount, 2) }} ₺</td>
                            <td class="px-6 py-4 text-sm text-center">
                                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-semibold">
                                    {{ $order->tickets_count }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @if($order->status->value === 'pending')
                                    <span class="inline-block bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs font-semibold">
                                        ⏳ Bekliyor
                                    </span>
                                @elseif($order->status->value === 'paid')
                                    <span class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold">
                                        ✅ Ödendi
                                    </span>
                                @elseif($order->status->value === 'cancelled')
                                    <span class="inline-block bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs font-semibold">
                                        ❌ İptal
                                    </span>
                                @elseif($order->status->value === 'refunded')
                                    <span class="inline-block bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-xs font-semibold">
                                        🔄 İade
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('organizer.orders.show', $order) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    Detay →
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection
