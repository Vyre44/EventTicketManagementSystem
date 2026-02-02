@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <div class="mb-6">
        <a href="{{ route('organizer.tickets.index') }}" class="text-blue-600 hover:text-blue-800 mb-4 inline-block">
            ← Tüm Biletler
        </a>
        <h1 class="text-3xl font-bold mb-2">Bilet Detayı</h1>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
            <p class="text-green-800">✅ {{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <p class="text-red-800">❌ {{ session('error') }}</p>
        </div>
    @endif

    <!-- Bilet Bilgileri -->
    <div class="bg-white border rounded-lg p-6 mb-6">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h2 class="text-2xl font-bold font-mono">{{ $ticket->code }}</h2>
                <div class="text-gray-600 text-sm mt-2 space-y-1">
                    <div>🎫 <strong>Etkinlik:</strong> {{ $ticket->ticketType->event->title }}</div>
                    <div>🏷️ <strong>Bilet Tipi:</strong> {{ $ticket->ticketType->name }}</div>
                    <div>💰 <strong>Fiyat:</strong> {{ number_format($ticket->ticketType->price, 2) }} ₺</div>
                    <div>📅 <strong>Oluşturulma:</strong> {{ $ticket->created_at->format('d.m.Y H:i') }}</div>
                </div>
            </div>

            <!-- Status Badge -->
            <div>
                @if($ticket->status->value === 'active')
                    <span class="inline-block bg-blue-100 text-blue-800 px-4 py-2 rounded-full font-semibold">
                        Aktif
                    </span>
                @elseif($ticket->status->value === 'checked_in')
                    <span class="inline-block bg-green-100 text-green-800 px-4 py-2 rounded-full font-semibold">
                        ✅ Kullanıldı
                    </span>
                    @if($ticket->checked_in_at)
                        <div class="text-xs text-gray-600 mt-2">
                            Check-in: {{ $ticket->checked_in_at->format('d.m.Y H:i') }}
                        </div>
                    @endif
                @elseif($ticket->status->value === 'cancelled')
                    <span class="inline-block bg-red-100 text-red-800 px-4 py-2 rounded-full font-semibold">
                        ❌ İptal
                    </span>
                @elseif($ticket->status->value === 'refunded')
                    <span class="inline-block bg-gray-100 text-gray-800 px-4 py-2 rounded-full font-semibold">
                        🔄 İade
                    </span>
                @endif
            </div>
        </div>

        <hr class="my-4">

        <!-- Sipariş ve Müşteri Bilgileri -->
        @if($ticket->order)
            <div class="mb-4">
                <h3 class="font-bold mb-2">Sipariş Bilgileri</h3>
                <div class="text-gray-700 space-y-1 text-sm">
                    <div>📌 <strong>Sipariş No:</strong> 
                        <a href="{{ route('organizer.orders.show', $ticket->order) }}" class="text-blue-600 hover:text-blue-800">
                            #{{ $ticket->order->id }}
                        </a>
                    </div>
                    <div>👤 <strong>Müşteri:</strong> {{ $ticket->order->user->name }}</div>
                    <div>📧 <strong>E-mail:</strong> {{ $ticket->order->user->email }}</div>
                    @if($ticket->order->user->phone)
                        <div>📱 <strong>Telefon:</strong> {{ $ticket->order->user->phone }}</div>
                    @endif
                    <div>💳 <strong>Sipariş Durumu:</strong>
                        @if($ticket->order->status->value === 'pending')
                            <span class="text-yellow-600">Ödeme Bekliyor</span>
                        @elseif($ticket->order->status->value === 'paid')
                            <span class="text-green-600">Ödendi</span>
                        @elseif($ticket->order->status->value === 'cancelled')
                            <span class="text-red-600">İptal</span>
                        @elseif($ticket->order->status->value === 'refunded')
                            <span class="text-gray-600">İade</span>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- İşlem Butonları -->
    <div class="flex gap-3 flex-wrap">
        @if($ticket->status->value === 'checked_in')
            <form action="{{ route('organizer.tickets.checkinUndo', $ticket) }}" method="POST" onsubmit="return confirm('Bu bilet\'in check-in\'ini geri almak istediğinizden emin misiniz?');">
                @csrf
                <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-4 rounded-lg">
                    ↩️ Check-in'i Geri Al
                </button>
            </form>
        @endif

        @if($ticket->status->value === 'active')
            <form action="{{ route('organizer.tickets.cancel', $ticket) }}" method="POST" onsubmit="return confirm('Bu bileti iptal etmek istediğinizden emin misiniz?');">
                @csrf
                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg">
                    ❌ İptal Et
                </button>
            </form>
        @endif
    </div>
</div>
@endsection
