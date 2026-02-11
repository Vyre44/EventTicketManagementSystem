{{-- Ana sayfa şablonunu (layouts.app) kullan --}}
@extends('layouts.app')

{{-- İçerik bölümünü tanımla (ana şablonun içine yerleşecek) --}}
@section('content')

{{-- Sayfa başlığı ve butonları içeren üst kısım --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        {{-- Sayfa başlığı --}}
        <h1 class="h4 mb-0">Yeni Etkinlik</h1>
        <div class="text-muted">Etkinlik bilgilerini doldurun</div>
    </div>
    {{-- Liste sayfasına dönüş butonu --}}
    <a href="{{ route('organizer.events.index') }}" class="btn btn-outline-secondary btn-sm">Listeye Dön</a>
</div>

{{-- Ana içerik: 2 sütunlu layout (8+4=12 grid sistemi) --}}
<div class="row g-3">
    {{-- Sol sütun: Form (geniş alan) --}}
    <div class="col-lg-8">
        {{-- Hata mesajları için container (başlangıçta gizli) --}}
        <div id="error-container" class="alert alert-danger d-none" role="alert"></div>

        {{-- Form kartı --}}
        <div class="card shadow-sm">
            <div class="card-body">
                {{-- 
                    Form: Yeni etkinlik oluşturma
                    - method="POST": Sunucuya veri gönderme yöntemi
                    - action: Formun gönderileceği URL (Laravel route)
                    - enctype: Dosya yüklemek için gerekli
                --}}
                <form id="event-form" method="POST" action="{{ route('organizer.events.store') }}" enctype="multipart/form-data">
                    {{-- CSRF token: Güvenlik için gerekli (Laravel'in güvenlik mekanizması) --}}
                    @csrf
                    
                    {{-- Etkinlik başlığı alanı --}}
                    <div class="mb-3">
                        <label for="title" class="form-label">Başlık</label>
                        {{-- old('title'): Form gönderilip hata alınırsa önceki değeri göster --}}
                        <input id="title" name="title" value="{{ old('title') }}" required class="form-control">
                    </div>
                    
                    {{-- Etkinlik açıklaması alanı --}}
                    <div class="mb-3">
                        <label for="description" class="form-label">Açıklama</label>
                        {{-- textarea: Çok satırlı metin girişi için --}}
                        <textarea id="description" name="description" required class="form-control" rows="4">{{ old('description') }}</textarea>
                    </div>
                    
                    {{-- Tarih alanları: Başlangıç ve bitiş (yan yana) --}}
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="start_time" class="form-label">Başlangıç</label>
                            {{-- datetime-local: Tarih ve saat seçici --}}
                            <input id="start_time" type="datetime-local" name="start_time" value="{{ old('start_time') }}" required class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label for="end_time" class="form-label">Bitiş</label>
                            <input id="end_time" type="datetime-local" name="end_time" value="{{ old('end_time') }}" required class="form-control">
                        </div>
                    </div>
                    
                    {{-- Kapak görseli yükleme alanı --}}
                    <div class="mb-3 mt-3">
                        <label for="cover_image" class="form-label">Kapak Görseli (Opsiyonel)</label>
                        {{-- 
                            type="file": Dosya seçici
                            accept: Sadece belirlenen dosya tiplerini kabul et
                        --}}
                        <input id="cover_image" type="file" name="cover_image" accept="image/jpeg,image/jpg,image/png" class="form-control">
                        {{-- Eğer kapak görseli için hata varsa göster --}}
                        @error('cover_image')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Önerilen: 1200x630, JPG/PNG, max 2MB</div>
                    </div>
                    
                    {{-- Etkinlik durumu seçimi --}}
                    <div class="mb-4">
                        <label for="status" class="form-label">Durum</label>
                        {{-- 
                            PHP kodu bloğu: Durum isimlerini Türkçeleştirmek için dizi tanımla
                            @php..@endphp Laravel'de PHP kodu yazmanı sağlar
                        --}}
                        @php
                            $statusLabels = [
                                'draft' => 'Taslak',
                                'published' => 'Yayında',
                                'ended' => 'Bitti',
                                'cancelled' => 'İptal',
                            ];
                        @endphp
                        {{-- Dropdown menü (seçim kutusu) --}}
                        <select id="status" name="status" required class="form-select">
                            {{-- \App\Enums\EventStatus: Durum listesini veritabanı enum'undan al --}}
                            @foreach(\App\Enums\EventStatus::cases() as $status)
                                {{-- Her bir durum için option oluştur --}}
                                <option value="{{ $status->value }}" @selected(old('status') == $status->value)>
                                    {{-- Türkçe karşılığı varsa onu, yoksa orijinal adı göster --}}
                                    {{ $statusLabels[$status->value] ?? $status->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    {{-- Form butonları --}}
                    <div class="d-flex gap-2">
                        {{-- Kaydet butonu --}}
                        <button type="submit" id="submit-btn" class="btn btn-primary">Kaydet</button>
                        {{-- İptal butonu (liste sayfasına dön) --}}
                        <a href="{{ route('organizer.events.index') }}" class="btn btn-outline-secondary">İptal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    {{-- Sağ sütun: Yardım kartı (dar alan) --}}
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="fw-semibold mb-2">Yardım</div>
                <div class="text-muted">Başlık, tarih ve durum bilgilerini doğru girin. Kapak görseli isteğe bağlıdır.</div>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript kodu: Form gönderme işlemini kontrol et --}}
<script>
// Sayfa yüklendiğinde form elementini seç
document.getElementById('event-form').addEventListener('submit', function(e) {
    // Formun normal gönderilmesini engelle (sayfa yenilenmesini önle)
    e.preventDefault();
    
    // Değişkenleri tanımla
    const form = this; // Form elementi
    const submitBtn = document.getElementById('submit-btn'); // Kaydet butonu
    const errorContainer = document.getElementById('error-container'); // Hata mesajı alanı
    const originalBtnText = submitBtn.textContent; // Butonun orjinal yazısını sakla
    
    // Butonu devre dışı bırak (çift tıklamayı engelle)
    submitBtn.disabled = true;
    submitBtn.textContent = 'Kaydediliyor...'; // Buton yazısını değiştir
    errorContainer.classList.add('d-none'); // Önceki hataları gizle
    errorContainer.innerHTML = ''; // Hata içeriğini temizle
    
    // FormData: Form verilerini topla (dosya dahil)
    const formData = new FormData(form);
    
    // fetch: Sunucuya AJAX isteği gönder (sayfa yenilenmeden)
    fetch(form.action, {
        method: 'POST', // HTTP metodu
        body: formData, // Gönderilecek veri
        headers: {
            'X-Requested-With': 'XMLHttpRequest', // AJAX isteği olduğunu belirt
            'Accept': 'application/json', // JSON formatında yanıt bekle
        }
    })
    // İlk then: Sunucu yanıtını kontrol et
    .then(response => {
        // 413: Dosya çok büyük hatası
        if (response.status === 413) {
            throw new Error('Dosya çok büyük. Kapak görseli en fazla 2MB olabilir.');
        }
        // JSON yanıtı oku ve durum bilgisiyle birleştir
        return response.json().then(data => ({
            ok: response.ok, // Başarılı mı? (200-299 arası)
            status: response.status, // HTTP durum kodu
            data: data // Sunucudan gelen veri
        }));
    })
    // İkinci then: İşlem sonucunu değerlendir
    .then(result => {
        if (result.ok) {
            // Başarılı: Liste sayfasına yönlendir
            window.location.href = "{{ route('organizer.events.index') }}";
        } else {
            // Hata var: Hata mesajlarını göster
            let errorMsg = '';
            if (result.data.errors) {
                // Validasyon hataları: Tüm hataları birleştir
                Object.values(result.data.errors).forEach(err => {
                    errorMsg += err.join('<br>') + '<br>';
                });
            } else {
                // Genel hata mesajı
                errorMsg = result.data.message || 'Bir hata oluştu.';
            }
            // Hataları ekranda göster
            errorContainer.innerHTML = errorMsg;
            errorContainer.classList.remove('d-none'); // Gizliliği kaldır
            // Butonu tekrar aktif et
            submitBtn.disabled = false;
            submitBtn.textContent = originalBtnText;
        }
    })
    // catch: Network hatası veya beklenmeyen hata
    .catch(error => {
        errorContainer.innerHTML = error.message || 'Bir hata oluştu. Lütfen tekrar deneyin.';
        errorContainer.classList.remove('d-none');
        submitBtn.disabled = false;
        submitBtn.textContent = originalBtnText;
    });
});
</script>

{{-- İçerik bölümünü kapat --}}
@endsection
