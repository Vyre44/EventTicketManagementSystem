{{-- 
    Katılımcı Etkinlik Detay Sayfası
    Etkinlik açıklaması, bilet tipleri ve fiyatlar. Bilet seçimi form'u (Satın Al).
    Stok kontrol: remaining_quantity > 0 biletleri seçilebilir. Organizatör bilgisi.
--}}
@extends('attendee.layouts.app')

{{-- İçerik bölümü başla --}}
@section('content')
<div class="container py-4">
    {{-- Geri dönüş butonu: etkinlikler listesine geri dön --}}
    <div class="mb-3">
        <a href="{{ route('attendee.events.index') }}" class="btn btn-outline-secondary btn-sm">
            ← Etkinliklere Dön
        </a>
    </div>

    {{-- Etkinliğin kapak resmi varsa göster, yoksa emoji göster --}}
    @if($event->cover_image_url)
        <div class="mb-4">
            <img src="{{ $event->cover_image_url }}" alt="{{ $event->title }}" class="w-100 rounded" style="max-height:400px;object-fit:cover;">
        </div>
    @else
        <div class="mb-4 w-100 bg-primary text-white d-flex align-items-center justify-content-center rounded" style="height:250px;font-size:4rem;">
            🎪
        </div>
    @endif

    {{-- Etkinlik bilgileri kartı: başlık, tarih, konum, organizatör --}}
    <div class="card shadow-sm mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="card-body text-white p-4">
            {{-- Etkinlik adı --}}
            <h1 class="h2 fw-bold mb-3">{{ $event->title }}</h1>
            
            {{-- Etkinlik tarihi ve saati --}}
            <div class="mb-2">
                <span class="fs-5 me-2">📅</span>
                <span class="fs-6">{{ $event->start_time->locale('tr')->translatedFormat('d F Y') }} • {{ $event->start_time->format('H:i') }}</span>
            </div>
            {{-- Etkinlik konumu (varsa) --}}
            @if($event->location)
                <div class="mb-2">
                    <span class="fs-5 me-2">📍</span>
                    <span class="fs-6">{{ $event->location }}</span>
                </div>
            @endif
            {{-- Etkinliği düzenleyen kişi --}}
            <div class="mb-0">
                <span class="fs-5 me-2">👤</span>
                <span class="fs-6">Organize Eden: {{ $event->organizer->name }}</span>
            </div>
        </div>
    </div>
    {{-- Etkinlik açıklaması (varsa) --}}
    @if($event->description)
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h5 fw-bold mb-3">📖 Açıklama</h2>
                <p class="text-muted" style="white-space: pre-line;">{{ $event->description }}</p>
            </div>
        </div>
    @endif

    {{-- Bilet satın alma bölümü: stok kontrolü ve bilet seçimi --}}
    @php
        //Tüm bilet tiplerinin kalan miktarını topla 
        $totalRemaining = $event->ticketTypes->sum('remaining_quantity');
    @endphp

    {{-- Bilet tipi olmadığını kontrol et --}}
    @if($event->ticketTypes->isEmpty())
        <div class="alert alert-warning text-center" role="alert">
            <p class="mb-0 fs-6">Bu etkinlik için henüz bilet satılmamaktadır.</p>
        </div>
    {{-- Tüm biletler satıldıysa uyarı göster --}}
    @elseif($totalRemaining <= 0)
        <div class="alert alert-danger text-center" role="alert">
            <p class="mb-0 fs-6">Biletler tükendi. Lütfen daha sonra tekrar deneyiniz.</p>
        </div>
    {{-- Biletler varsa satın alma formu göster --}}
    @else
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h5 fw-bold mb-4">🎟️ Biletleri Seç</h2>

                {{-- Yalnızca giriş yapmış ziyaretçiler bilet satın alabilir --}}
                @auth
                    {{-- Bilet satın alma formu --}}
                    <form id="buy-form" action="{{ route('attendee.events.buy', $event) }}" method="POST">
                        {{-- CSRF koruması: güvenlik için gerekli token --}}
                        @csrf
                        
                        {{-- Satışa sunulan bilet tiplerini döngüyle göster --}}
                        <div class="mb-4">
                            @foreach($event->ticketTypes as $ticketType)
                                <div class="mb-3">
                                    {{-- Her bilet tipi için bir kart bileşeni göster --}}
                                    <x-attendee.ticket-card :ticket-type="$ticketType" />
                                </div>
                            @endforeach
                        </div>

                        {{-- Dinamik olarak hesaplanan toplam ödeme tutarı --}}
                        <div class="bg-light rounded p-3 mb-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold fs-5">Toplam:</span>
                                {{-- Toplam tutar burada JavaScript ile güncellenecek --}}
                                <span class="fs-3 fw-bold text-primary" id="total-amount">₺0,00</span>
                            </div>
                        </div>

                        {{-- İptal ve Satın Al butonları --}}
                        <div class="d-flex gap-3 flex-column flex-md-row">
                            <a href="{{ route('attendee.events.index') }}" class="btn btn-outline-secondary btn-lg flex-fill">
                                ❌ İptal
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg flex-fill">
                                ✓ Satın Al
                            </button>
                        </div>
                    </form>

                    <script>
                        // Bilet fiyatlarını bir harita olarak oluştur
                        const ticketTypeMap = {
                            @foreach($event->ticketTypes as $type)
                                {{ $type->id }}: {{ $type->price }},
                            @endforeach
                        };

                        // Toplam tutarı hesapla
                        function updateTotal() {
                            let total = 0;
                            // Tüm adet giriş alanlarını bul
                            document.querySelectorAll('.qty-input').forEach(input => {
                                const qty = parseInt(input.value) || 0;
                                // Bilet tipi ID'sini çıkar
                                const ticketTypeId = input.name.match(/\[(\d+)\]/)[1];
                                // Adet * fiyat = tutar
                                total += qty * ticketTypeMap[ticketTypeId];
                            });

                            // Toplam tutarı Türkçe para formatında göster
                            document.getElementById('total-amount').textContent = 
                                '₺' + total.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                            // Bilet seçilmediyse gönder butonunu pasifleştir
                            const submitBtn = document.querySelector('button[type="submit"]');
                            submitBtn.disabled = total === 0;
                        }

                        // Bilet adetindeki değişiklikleri izle
                        document.querySelectorAll('.qty-input').forEach(input => {
                            input.addEventListener('change', updateTotal);
                            input.addEventListener('input', updateTotal);
                        });

                        // Sayfa açılınca ilk hesaplamayı yap
                        updateTotal();
                    </script>
                @else
                    {{-- Giriş yapmamış ziyaretçiler için uyarı --}}
                    <div class="alert alert-info text-center" role="alert">
                        <p class="mb-3">Bilet satın almak için giriş yapmanız gerekir.</p>
                        {{-- Giriş sayfasına yönlendiren buton --}}
                        <a href="{{ route('login') }}" class="btn btn-primary btn-lg">
                            🔐 Giriş Yap
                        </a>
                    </div>
                @endauth
            </div>
        </div>
    @endif
</div>
{{-- İçerik bölümü bitir --}}
@endsection
