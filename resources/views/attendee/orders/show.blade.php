{{-- 
    Katılımcı Siparişi Detay Sayfası
    PENDING: Ödeme sayfası ve ödeme formu. PAID: Biletler ve QR code gösterme.
    Bilet detayları: kod, durum, check-in saati. İndirme ve paylaşma seçeneği.
--}}
@extends('attendee.layouts.app')

{{-- İçerik bölümü başla --}}
@section('content')

<div class="container py-4">
    {{-- Geri dönüş linki ve sayfa başlığı --}}
    <div class="mb-4">
        <a href="{{ route('attendee.orders.index') }}" class="btn btn-outline-secondary btn-sm mb-3">
            ← Tüm Siparişler
        </a>
        <h1 class="h4 fw-bold mb-0">Sipariş Detayı</h1>
    </div>

    {{-- Sipariş temel bilgileri kartı --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            {{-- Etkinlik adı, tarih ve sipariş tarihi --}}
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h2 class="h5 fw-bold mb-2">{{ $order->event->title }}</h2>
                    <div class="text-muted small">
                        {{-- Etkinlik tarihi --}}
                        <div class="mb-1">📅 {{ $order->event->start_time->format('d.m.Y H:i') }}</div>
                        {{-- Sipariş ne zaman oluşturuldu --}}
                        <div class="mb-1">🕒 Sipariş Tarihi: {{ $order->created_at->format('d.m.Y H:i') }}</div>
                        {{-- Ödeme tarihi (yapılmışsa) --}}
                        @if($order->paid_at)
                            <div class="mb-0">💳 Ödeme Tarihi: {{ $order->paid_at->format('d.m.Y H:i') }}</div>
                        @endif
                    </div>
                </div>

                {{-- Sipariş durumu badge'i (renklendirilerek göster) --}}
                <div id="order-status-badge">
                    <x-attendee.status-badge :status="$order->status" />
                </div>
            </div>

            <hr>

            {{-- Ödenmesi gereken toplam tutar --}}
            <div class="d-flex justify-content-between">
                <span class="fw-bold">Toplam Tutar:</span>
                <span class="fw-bold text-success fs-5">{{ number_format($order->total_amount, 2) }} ₺</span>
            </div>
        </div>
    </div>

    {{-- Varsa hata mesajlarını göster --}}
    @if($errors->any())
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Başarılı işlem mesajını göster --}}
    @if(session('success'))
        <div class="alert alert-success" role="alert">
            ✅ {{ session('success') }}
        </div>
    @endif

    {{-- Satın alınan biletler tablosu --}}
    @if($order->tickets->isNotEmpty())
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h5 fw-bold mb-3">Biletleriniz ({{ $order->tickets->count() }} adet)</h2>
                
                {{-- Bilet bilgilerini tablo halinde göster --}}
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                {{-- Tablo başlıkları --}}
                                <th>Bilet Tipi</th>
                                <th>Kod</th>
                                <th>Durum</th>
                                <th>Giriş</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Her bilet için bir satır göster --}}
                            @foreach($order->tickets as $ticket)
                                <tr>
                                    {{-- Bilet tipi adı --}}
                                    <td class="fw-semibold">{{ $ticket->ticketType->name }}</td>
                                    {{-- Bilet kodu (benzersiz tanımlayıcı) --}}
                                    <td class="font-monospace small">{{ $ticket->code }}</td>
                                    {{-- Bilet durumu (açılmamış/iptal/refund vb) --}}
                                    <td>
                                        <div class="ticket-status-badge">
                                            <x-attendee.status-badge :status="$ticket->status" />
                                        </div>
                                    </td>
                                    {{-- Giriş saati (bilet kullanıldıysa) --}}
                                    <td>
                                        @if($ticket->checked_in_at)
                                            <span class="text-success small">✅ {{ $ticket->checked_in_at->format('d.m.Y H:i') }}</span>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Biletleri etkinlikte nasıl kullanacağının ipucu --}}
                <div class="alert alert-info mt-3 mb-0" role="alert">
                    <small>
                        💡 <strong>İpucu:</strong> Biletlerinizi etkinlik girişinde gösterin. 
                        Bilet kodlarınızı not alın veya bu sayfayı kaydırın.
                    </small>
                </div>
            </div>
        </div>
    @endif

    {{-- Sipariş durumuna göre gösterilecek işlem butonları --}}
    <div id="order-actions">
        {{-- Beklemede olan siparişler: ödeme tamamlama veya iptal etme --}}
        @if($order->status === \App\Enums\OrderStatus::PENDING)
            <div class="d-grid gap-3">
                {{-- Ödeme işlemini tamamla butonu --}}
                <button 
                    type="button"
                    id="order-pay-btn" 
                    data-order-id="{{ $order->id }}"
                    class="btn btn-success btn-lg"
                >
                    ✓ Ödemeyi Tamamla
                </button>
                {{-- Siparişi iptal et butonu --}}
                <button 
                    type="button"
                    id="order-cancel-btn" 
                    data-order-id="{{ $order->id }}"
                    class="btn btn-outline-danger btn-lg"
                >
                    ❌ İptal Et
                </button>
            </div>
        {{-- Ödenerek tamamlanmış siparişler: iade talebinde bulunabilir --}}
        @elseif($order->status === \App\Enums\OrderStatus::PAID)
            {{-- İade talep et butonu --}}
            <button 
                type="button"
                id="order-refund-btn" 
                data-order-id="{{ $order->id }}"
                class="btn btn-warning btn-lg w-100"
            >
                ↩️ İade Talep Et
            </button>
        {{-- İptal edilmiş siparişler: başka işlem yapılamaz --}}
        @elseif($order->status === \App\Enums\OrderStatus::CANCELLED)
            <div class="alert alert-danger text-center" role="alert">
                <p class="fw-semibold mb-0">Bu sipariş iptal edilmiştir. Başka bir işlem yapılamaz.</p>
            </div>
        {{-- İadesi tamamlanmış siparişler: geri ödeme bilgisi --}}
        @elseif($order->status === \App\Enums\OrderStatus::REFUNDED)
            <div class="alert alert-secondary text-center" role="alert">
                <p class="fw-semibold mb-1">Bu sipariş için iade işlemi tamamlanmıştır.</p>
                <p class="text-muted small mb-0">Ödemeniz 3-5 gün içinde hesabınıza yatırılacaktır.</p>
            </div>
        @endif
    </div>

    {{-- Siparişler listesine dönüş butonu --}}
    <div class="mt-4 text-center">
        <a href="{{ route('attendee.orders.index') }}" class="btn btn-outline-secondary">
            ← Siparişlerime Dön
        </a>
    </div>
</div>
{{-- İçerik bölümü bitir --}}
@endsection
