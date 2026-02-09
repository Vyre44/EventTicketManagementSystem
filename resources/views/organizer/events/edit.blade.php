@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-0">Etkinlik Düzenle</h1>
        <div class="text-muted">Etkinlik bilgilerini güncelleyin</div>
    </div>
    <a href="{{ route('organizer.events.index') }}" class="btn btn-outline-secondary btn-sm">Listeye Dön</a>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div id="error-container" class="alert alert-danger d-none" role="alert"></div>

        <div class="card shadow-sm">
            <div class="card-body">
                <form id="event-form" method="POST" action="{{ route('organizer.events.update', $event) }}" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Başlık</label>
                        <input name="title" value="{{ old('title', $event->title) }}" required class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Açıklama</label>
                        <textarea name="description" required class="form-control" rows="4">{{ old('description', $event->description) }}</textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Başlangıç</label>
                            <input type="datetime-local" name="start_time" value="{{ old('start_time', $event->start_time?->format('Y-m-d\TH:i')) }}" required class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bitiş</label>
                            <input type="datetime-local" name="end_time" value="{{ old('end_time', $event->end_time?->format('Y-m-d\TH:i')) }}" required class="form-control">
                        </div>
                    </div>
                    <div class="mb-3 mt-3">
                        <label class="form-label">Kapak Görseli (Opsiyonel)</label>
                        <input type="file" name="cover_image" accept="image/jpeg,image/jpg,image/png" class="form-control">
                        @error('cover_image')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                        @if($event->cover_image_url)
                            <div class="mt-2">
                                <div class="text-muted small mb-1">Mevcut Kapak Görseli</div>
                                <img src="{{ $event->cover_image_url }}" alt="Kapak" class="img-fluid rounded">
                            </div>
                        @endif
                        <div class="form-text">Önerilen: 1200x630, JPG/PNG, max 2MB</div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Durum</label>
                        @php
                            $statusLabels = [
                                'draft' => 'Taslak',
                                'published' => 'Yayında',
                                'ended' => 'Bitti',
                                'cancelled' => 'İptal',
                            ];
                        @endphp
                        <select name="status" required class="form-select">
                            @foreach(\App\Enums\EventStatus::cases() as $status)
                                <option value="{{ $status->value }}" @selected(old('status', $event->status?->value ?? $event->status) == $status->value)>
                                    {{ $statusLabels[$status->value] ?? $status->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" id="submit-btn" class="btn btn-primary">Güncelle</button>
                        <a href="{{ route('organizer.events.index') }}" class="btn btn-outline-secondary">İptal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <div class="fw-semibold mb-2">Notlar</div>
                <div class="text-muted">Tarih ve durum güncellemeleri bilet satışını etkileyebilir.</div>
            </div>
        </div>
        <div class="card border-danger">
            <div class="card-body">
                <div class="fw-semibold text-danger mb-2">Silme</div>
                <div class="text-muted mb-3">Etkinliği silmek geri alınamaz.</div>
                <form method="POST" action="{{ route('organizer.events.destroy', $event) }}" onsubmit="return confirm('Bu etkinliği silmek istediğine emin misin?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger w-100">Sil</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('event-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const submitBtn = document.getElementById('submit-btn');
    const errorContainer = document.getElementById('error-container');
    const originalBtnText = submitBtn.textContent;
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Güncelleniyor...';
    errorContainer.classList.add('d-none');
    errorContainer.innerHTML = '';
    
    const formData = new FormData(form);
    
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        }
    })
    .then(response => {
        if (response.status === 413) {
            throw new Error('Dosya çok büyük. Kapak görseli en fazla 2MB olabilir.');
        }
        return response.json().then(data => ({
            ok: response.ok,
            status: response.status,
            data: data
        }));
    })
    .then(result => {
        if (result.ok) {
            window.location.href = "{{ route('organizer.events.index') }}";
        } else {
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
    .catch(error => {
        errorContainer.innerHTML = error.message || 'Bir hata oluştu. Lütfen tekrar deneyin.';
        errorContainer.classList.remove('d-none');
        submitBtn.disabled = false;
        submitBtn.textContent = originalBtnText;
    });
});
</script>
@endsection
