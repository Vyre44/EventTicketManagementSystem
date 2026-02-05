@extends('attendee.layouts.app')

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
            <div id="order-status-badge">
                <x-attendee.status-badge :status="$order->status" />
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
                        <div class="ticket-status-badge">
                            <x-attendee.status-badge :status="$ticket->status" />
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

    <!-- İşlem Butonları (State-based) -->
    <div id="order-actions" class="mt-6">
        @if($order->status === \App\Enums\OrderStatus::PENDING)
            <div class="flex flex-col md:flex-row gap-4">
                <button 
                    type="button"
                    id="order-pay-btn" 
                    data-order-id="{{ $order->id }}"
                    class="flex-1 bg-green-600 text-white px-8 py-4 rounded-lg font-bold text-lg hover:bg-green-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    ✓ Ödemeyi Tamamla
                </button>
                <button 
                    type="button"
                    id="order-cancel-btn" 
                    data-order-id="{{ $order->id }}"
                    class="flex-1 bg-red-600 text-white px-8 py-4 rounded-lg font-bold text-lg hover:bg-red-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    ❌ İptal Et
                </button>
            </div>
        @elseif($order->status === \App\Enums\OrderStatus::PAID)
            <button 
                type="button"
                id="order-refund-btn" 
                data-order-id="{{ $order->id }}"
                class="w-full bg-orange-600 text-white px-8 py-4 rounded-lg font-bold text-lg hover:bg-orange-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
            >
                ↩️ İade Talep Et
            </button>
        @elseif($order->status === \App\Enums\OrderStatus::CANCELLED)
            <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
                <p class="text-red-800 font-semibold">Bu sipariş iptal edilmiştir. Başka bir işlem yapılamaz.</p>
            </div>
        @elseif($order->status === \App\Enums\OrderStatus::REFUNDED)
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 text-center">
                <p class="text-gray-800 font-semibold">Bu sipariş için iade işlemi tamamlanmıştır.</p>
                <p class="text-gray-600 text-sm mt-2">Ödemeniz 3-5 gün içinde hesabınıza yatırılacaktır.</p>
            </div>
        @endif
    </div>

    <!-- Back to Orders Button -->
    <div class="mt-8 text-center">
        <a href="{{ route('attendee.orders.index') }}" class="text-blue-600 hover:text-blue-800 font-semibold">
            ← Siparişlerime Dön
        </a>
    </div>
</div>
@endsection
