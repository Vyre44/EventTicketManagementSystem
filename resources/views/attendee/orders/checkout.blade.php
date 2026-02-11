{{-- 
    Katılımcı Ödeme (Checkout) Sayfası
    Sipariş özeti: biletler, miktarlar, tutarlar. Ödeme formu (kredi kartı bilgileri).
    PENDING -> PAID geçişini sağlayan form. Hata mesajları ve validasyon gösterimi.
--}}
@extends('attendee.layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="h4 fw-bold mb-4">Ödeme Sayfası</h1>

    <div class="row">
        {{-- Ödeme formu taraflı --}}
        <!-- Form Bölümü -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="h5 fw-bold mb-4">Sipariş Bilgileri</h2>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Etkinlik:</span>
                            <span class="fw-bold">{{ $order->event->title }}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Tarih:</span>
                            <span>{{ $order->event->start_time->format('d.m.Y H:i') }}</span>
                        </div>
                    </div>

                    {{-- Hata mesajları --}}
                    @if($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Demo/Test uyarısı --}}
                    <!-- Ödeme Uyarısı -->
                    <div class="alert alert-warning" role="alert">
                        <strong>⚠️ Not:</strong> Bu bir deneme projesidir. Gerçek ödeme işlemi yapılmamaktadır. 
                        "Ödemeyi Tamamla" butonuna bastığınızda sipariş otomatik olarak ödendi olarak işaretlenecek 
                        ve biletleriniz oluşturulacaktır.
                    </div>

                    {{-- Ödeme formu --}}
                    <!-- Ödeme Formu -->
                    <form method="POST" action="{{ route('attendee.orders.pay', $order) }}" id="pay-form">
                        @csrf
                        <div id="order-actions" class="d-grid gap-3">
                            {{-- Ödemeyi tamamla butonu --}}
                            <button 
                                type="button"
                                id="order-pay-btn"
                                data-order-id="{{ $order->id }}"
                                class="btn btn-success btn-lg"
                            >
                                ✅ Ödemeyi Tamamla
                            </button>
                            {{-- İptal butonu --}}
                            <button 
                                type="button"
                                id="order-cancel-btn"
                                data-order-id="{{ $order->id }}"
                                class="btn btn-outline-danger"
                            >
                                ❌ İptal Et
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Özet taraflı --}}
        <!-- Özet Bölümü -->
        <div class="col-lg-4">
            {{-- Sipariş özet kartı --}}
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="h5 fw-bold mb-3">Sipariş Özeti</h2>

                    {{-- Bilet detayları --}}
                    <!-- Bilet Detayları -->
                    <div class="mb-3">
                        {{-- Her bilet tipini ve miktarını göster --}}
                        @foreach($ticketTypeQuantities as $ticketTypeId => $quantity)
                            @php
                                $ticketType = $ticketTypes->get($ticketTypeId);
                            @endphp
                            @if($ticketType)
                                <div class="d-flex justify-content-between mb-2 small">
                                    <span>{{ $ticketType->name }} x {{ $quantity }}</span>
                                    <span class="fw-semibold">{{ number_format($ticketType->price * $quantity, 2) }} ₺</span>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <hr>

                    {{-- Toplam tutar --}}
                    <!-- Toplam Tutar -->
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold">Toplam Tutar:</span>
                        <span class="fw-bold text-success fs-5">{{ number_format($order->total_amount, 2) }} ₺</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
