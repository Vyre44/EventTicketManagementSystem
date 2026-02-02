@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <div class="mb-6">
        <a href="{{ route('attendee.orders.index') }}" class="text-blue-600 hover:text-blue-800 mb-4 inline-block">
            ← Tüm Siparişler
        </a>
        <h1 class="text-3xl font-bold mb-2">Sipariş Detayı</h1>
    </div>

    <!-- Sipariş Bilgileri -->
    <div class="bg-white border rounded-lg p-6 mb-6">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h2 class="text-2xl font-bold">{{ $order->event->title }}</h2>
                <div class="text-gray-600 text-sm mt-2 space-y-1">
                    <div>📅 {{ $order->event->start_time->format('d.m.Y H:i') }}</div>
                    <div>🕒 Sipariş Tarihi: {{ $order->created_at->format('d.m.Y H:i') }}</div>
                    @if($order->paid_at)
                        <div>💳 Ödeme Tarihi: {{ $order->paid_at->format('d.m.Y H:i') }}</div>
                    @endif
                </div>
            </div>

            <!-- Status Badge -->
            <div>
                @if($order->status->value === 'pending')
                    <span class="inline-block bg-yellow-100 text-yellow-800 px-4 py-2 rounded-full font-semibold">
                        ⏳ Ödeme Bekliyor
                    </span>
                @elseif($order->status->value === 'paid')
                    <span class="inline-block bg-green-100 text-green-800 px-4 py-2 rounded-full font-semibold">
                        ✅ Ödendi
                    </span>
                @elseif($order->status->value === 'cancelled')
                    <span class="inline-block bg-red-100 text-red-800 px-4 py-2 rounded-full font-semibold">
                        ❌ İptal Edildi
                    </span>
                @elseif($order->status->value === 'refunded')
                    <span class="inline-block bg-gray-100 text-gray-800 px-4 py-2 rounded-full font-semibold">
                        🔄 İade Edildi
                    </span>
                @endif
            </div>
        </div>

        <hr class="my-4">

        <!-- Toplam Tutar -->
        <div class="flex justify-between text-lg">
            <span class="font-semibold">Toplam Tutar:</span>
            <span class="font-bold text-green-600">{{ number_format($order->total_amount, 2) }} ₺</span>
        </div>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <ul class="text-red-800 text-sm space-y-1">
                @foreach($errors->all() as $error)
                    <li>❌ {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
            <p class="text-green-800">✅ {{ session('success') }}</p>
        </div>
    @endif

    <!-- Biletler -->
    @if($order->tickets->isNotEmpty())
        <div class="bg-white border rounded-lg p-6">
            <h2 class="text-xl font-bold mb-4">Biletleriniz ({{ $order->tickets->count() }} adet)</h2>
            
            <div class="space-y-3">
                @foreach($order->tickets as $ticket)
                    <div class="border rounded-lg p-4 flex justify-between items-center">
                        <div>
                            <div class="font-bold">{{ $ticket->ticketType->name }}</div>
                            <div class="text-sm text-gray-600">Kod: {{ $ticket->code }}</div>
                            @if($ticket->checked_in_at)
                                <div class="text-sm text-green-600">✅ Check-in: {{ $ticket->checked_in_at->format('d.m.Y H:i') }}</div>
                            @endif
                        </div>

                        <!-- Status Badge -->
                        <div>
                            @if($ticket->status->value === 'active')
                                <span class="inline-block bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-semibold">
                                    Aktif
                                </span>
                            @elseif($ticket->status->value === 'checked_in')
                                <span class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold">
                                    Kullanıldı
                                </span>
                            @elseif($ticket->status->value === 'cancelled')
                                <span class="inline-block bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-semibold">
                                    İptal
                                </span>
                            @elseif($ticket->status->value === 'refunded')
                                <span class="inline-block bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-sm font-semibold">
                                    İade
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- QR Kod Bilgisi -->
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <p class="text-blue-800 text-sm">
                    💡 <strong>İpucu:</strong> Biletlerinizi etkinlik girişinde gösterin. 
                    Bilet kodlarınızı not alın veya bu sayfayı kaydırın.
                </p>
            </div>
        </div>
    @endif

    <!-- İşlem Butonları -->
    <div class="mt-6 flex gap-3">
        @if($order->status->value === 'pending')
            <form action="{{ route('attendee.orders.cancel', $order) }}" method="POST" onsubmit="return confirm('Bu siparişi iptal etmek istediğinizden emin misiniz?');">
                @csrf
                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg">
                    ❌ İptal Et
                </button>
            </form>
        @elseif($order->status->value === 'paid')
            <form action="{{ route('attendee.orders.refund', $order) }}" method="POST" onsubmit="return confirm('Bu siparişin iadesini talep etmek istediğinizden emin misiniz?');">
                @csrf
                <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-4 rounded-lg">
                    🔄 İade Et
                </button>
            </form>
        @endif
    </div>
</div>
@endsection
