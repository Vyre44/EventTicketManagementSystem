{{-- Ana sayfa şablonunu (layouts.app) kullan --}}
@extends('layouts.app')

{{-- İçerik bölümünü tanımla --}}
@section('content')

{{-- Sayfa başlığı ve butonları içeren üst kısım --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        {{-- Sayfa başlığı --}}
        <h1 class="h4 mb-0">Etkinlik Düzenle</h1>
        <div class="text-muted">Etkinlik bilgilerini güncelleyin</div>
    </div>
    {{-- Liste sayfasına dönüş butonu --}}
    <a href="{{ route('organizer.events.index') }}" class="btn btn-outline-secondary btn-sm">Listeye Dön</a>
</div>

{{-- Ana içerik: 2 sütunlu layout (8+4=12 grid) --}}
<div class="row g-3">
    {{-- Sol sütun: Düzenleme formu --}}
    <div class="col-lg-8">
        {{-- Hata mesajları için container (başlangıçta gizli) --}}
        <div id="error-container" class="alert alert-danger d-none" role="alert"></div>

        {{-- Form kartı --}}
        <div class="card shadow-sm">
            <div class="card-body">
                {{--
                    Form: Etkinlik güncelleme
                    - @method('PUT'): Laravel'de güncelleme işlemi için gerekli
                    - $event: Düzenlenecek etkinlik verisi (controller'dan gelir)
                --}}
                <form id="event-form" method="POST" action="{{ route('organizer.events.update', $event) }}" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    
                    {{-- Etkinlik başlığı alanı --}}
                    <div class="mb-3">
                        <label for="title" class="form-label">Başlık</label>
                        {{-- old('title', $event->title): Hata varsa eski değer, yoksa veritabanındaki değer --}}
                        <input id="title" name="title" value="{{ old('title', $event->title) }}" required class="form-control">
                    </div>
                    
                    {{-- Etkinlik açıklaması alanı --}}
                    <div class="mb-3">
                        <label for="description" class="form-label">Açıklama</label>
                        <textarea id="description" name="description" required class="form-control" rows="4">{{ old('description', $event->description) }}</textarea>
                    </div>
                    
                    {{-- Tarih alanları: Başlangıç ve bitiş (yan yana) --}}
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="start_time" class="form-label">Başlangıç</label>
                            {{-- format('Y-m-d\TH:i'): Tarih verisini input formatına çevir --}}
                            <input id="start_time" type="datetime-local" name="start_time" value="{{ old('start_time', $event->start_time?->format('Y-m-d\TH:i')) }}" required class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label for="end_time" class="form-label">Bitiş</label>
                            <input id="end_time" type="datetime-local" name="end_time" value="{{ old('end_time', $event->end_time?->format('Y-m-d\TH:i')) }}" required class="form-control">
                        </div>
                    </div>
                    
                    {{-- Kapak görseli yükleme alanı --}}
                    <div class="mb-3 mt-3">
                        <label for="cover_image" class="form-label">Kapak Görseli (Opsiyonel)</label>
                        <input id="cover_image" type="file" name="cover_image" accept="image/jpeg,image/jpg,image/png" class="form-control">
                        @error('cover_image')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                        {{-- Eğer mevcut kapak görseli varsa göster --}}
                        @if($event->cover_image_url)
                            <div class="mt-2">
                                <div class="text-muted small mb-1">Mevcut Kapak Görseli</div>
                                <img src="{{ $event->cover_image_url }}" alt="Kapak" class="img-fluid rounded">
                            </div>
                        @endif
                        <div class="form-text">Önerilen: 1200x630, JPG/PNG, max 2MB</div>
                    </div>
                    
                    {{-- Etkinlik durumu seçimi --}}
                    <div class="mb-4">
                        <label for="status" class="form-label">Durum</label>
                        {{-- PHP: Durum isimlerini Türkçeleştir --}}
                        @php
                            $statusLabels = [
                                'draft' => 'Taslak',
                                'published' => 'Yayında',
                                'ended' => 'Bitti',
                                'cancelled' => 'İptal',
                            ];
                        @endphp
                        <select id="status" name="status" required class="form-select">
                            @foreach(\App\Enums\EventStatus::cases() as $status)
                                {{-- @selected: Mevcut durumu otomatik seç --}}
                                <option value="{{ $status->value }}" @selected(old('status', $event->status?->value ?? $event->status) == $status->value)>
                                    {{ $statusLabels[$status->value] ?? $status->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    {{-- Form butonları --}}
                    <div class="d-flex gap-2">
                        <button type="submit" id="submit-btn" class="btn btn-primary">Güncelle</button>
                        <a href="{{ route('organizer.events.index') }}" class="btn btn-outline-secondary">İptal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    {{-- Sağ sütun: Bilgi ve silme kartları --}}
    <div class="col-lg-4">
        {{-- Bilgilendirme kartı --}}
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <div class="fw-semibold mb-2">Notlar</div>
                <div class="text-muted">Tarih ve durum güncellemeleri bilet satışını etkileyebilir.</div>
            </div>
        </div>
        
        {{-- Silme kartı (tehlike rengi) --}}
        <div class="card border-danger">
            <div class="card-body">
                <div class="fw-semibold text-danger mb-2">Silme</div>
                <div class="text-muted mb-3">Etkinliği silmek geri alınamaz.</div>
                {{-- 
                    Silme formu:
                    - @method('DELETE'): Laravel'de silme işlemi için
                    - onsubmit: Silmeden önce onay iste
                --}}
                <form method="POST" action="{{ route('organizer.events.destroy', $event) }}" onsubmit="return confirm('Bu etkinliği silmek istediğine emin misin?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger w-100">Sil</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript: Form gönderme işlemini kontrol et --}}
<script>
// Form gönderildiğinde çalışacak fonksiyon
document.getElementById('event-form').addEventListener('submit', function(e) {
    // Sayfanın yenilenmesini engelle
    e.preventDefault();
    
    // Değişkenleri tanımla
    const form = this;
    const submitBtn = document.getElementById('submit-btn');
    const errorContainer = document.getElementById('error-container');
    const originalBtnText = submitBtn.textContent;
    
    // Butonu devre dışı bırak ve yazıyı değiştir
    submitBtn.disabled = true;
    submitBtn.textContent = 'Güncelleniyor...';
    errorContainer.classList.add('d-none'); // Önceki hataları gizle
    errorContainer.innerHTML = '';
    
    // Form verilerini topla (dosya dahil)
    const formData = new FormData(form);
    
    // Sunucuya AJAX isteği gönder
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        }
    })
    // Sunucu yanıtını kontrol et
    .then(response => {
        // Dosya çok büyük hatası
        if (response.status === 413) {
            throw new Error('Dosya çok büyük. Kapak görseli en fazla 2MB olabilir.');
        }
        // JSON yanıtı oku
        return response.json().then(data => ({
            ok: response.ok,
            status: response.status,
            data: data
        }));
    })
    // İşlem sonucunu değerlendir
    .then(result => {
        if (result.ok) {
            // Başarılı: Liste sayfasına yönlendir
            window.location.href = "{{ route('organizer.events.index') }}";
        } else {
            // Hata var: Mesajları göster
            let errorMsg = '';
            if (result.data.errors) {
                Object.values(result.data.errors).forEach(err => {
                    errorMsg += err.join('<br>') + '<br>';
                });
            } else {
                errorMsg = result.data.message || 'Bir hata oluştu.';
            }
            errorContainer.innerHTML = errorMsg;
            errorContainer.classList.remove('d-none');
            submitBtn.disabled = false;
            submitBtn.textContent = originalBtnText;
        }
    })
    // Network hatası
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
