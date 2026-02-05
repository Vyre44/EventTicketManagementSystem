@extends('attendee.layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-3xl">
    <h1 class="text-3xl font-bold mb-6">Ödeme Sayfası</h1>

    <!-- Sipariş Özeti -->
    <div class="bg-white border rounded-lg p-6 mb-6">
        <h2 class="text-xl font-bold mb-4">Sipariş Özeti</h2>
        
        <div class="space-y-2 text-gray-700">
            <div class="flex justify-between">
                <span>Etkinlik:</span>
                <span class="font-semibold">{{ $order->event->title }}</span>
            </div>
            <div class="flex justify-between">
                <span>Tarih:</span>
                <span>{{ $order->event->start_time->format('d.m.Y H:i') }}</span>
            </div>
        </div>

        <hr class="my-4">

        <!-- Bilet Detayları -->
        <h3 class="font-bold mb-2">Bilet Detayları</h3>
        <div class="space-y-2">
            @foreach($ticketTypeQuantities as $ticketTypeId => $quantity)
                @php
                    $ticketType = $ticketTypes->get($ticketTypeId);
                @endphp
                @if($ticketType)
                    <div class="flex justify-between text-sm">
                        <span>{{ $ticketType->name }} x {{ $quantity }}</span>
                        <span class="font-semibold">{{ number_format($ticketType->price * $quantity, 2) }} ₺</span>
                    </div>
                @endif
            @endforeach
        </div>

        <hr class="my-4">

        <!-- Toplam Tutar -->
        <div class="flex justify-between text-lg font-bold">
            <span>Toplam Tutar:</span>
            <span class="text-green-600">{{ number_format($order->total_amount, 2) }} ₺</span>
        </div>
    </div>

    <!-- Ödeme Uyarısı -->
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
        <p class="text-yellow-800 text-sm">
            ⚠️ <strong>Not:</strong> Bu bir demo projedir. Gerçek ödeme işlemi yapılmamaktadır. 
            "Ödemeyi Tamamla" butonuna bastığınızda sipariş otomatik olarak ödendi olarak işaretlenecek 
            ve biletleriniz oluşturulacaktır.
        </p>
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

    <!-- Ödeme Butonu -->
    <form method="POST" action="{{ route('attendee.orders.pay', $order) }}" id="pay-form">
        @csrf
        <div class="flex gap-4">
            <button 
                type="button"
                id="order-cancel-btn"
                data-order-id="{{ $order->id }}"
                class="flex-1 bg-red-600 text-white px-6 py-3 rounded-lg text-center hover:bg-red-700 font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
            >
                ❌ İptal Et
            </button>
            <button 
                type="button"
                id="order-pay-btn"
                data-order-id="{{ $order->id }}"
                class="flex-1 bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
            >
                ✅ Ödemeyi Tamamla
            </button>
        </div>
    </form>
</div>
@endsection
