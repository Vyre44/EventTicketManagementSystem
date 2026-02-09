@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Etkinliği Düzenle</h1>
    <a href="{{ route('admin.events.index') }}" class="btn btn-outline-secondary btn-sm">Listeye Dön</a>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div id="error-container" class="alert alert-danger d-none" role="alert">
            <div id="error-text"></div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <form id="event-form" method="POST" action="{{ route('admin.events.update', $event) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Başlık</label>
                        <input type="text" name="title" value="{{ old('title', $event->title) }}" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Açıklama</label>
                        <textarea name="description" class="form-control" rows="4">{{ old('description', $event->description) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kapak Görseli</label>
                        @if($event->cover_image_url)
                            <div class="mb-2">
                                <img src="{{ $event->cover_image_url }}" alt="Kapak" class="img-fluid rounded">
                            </div>
                        @endif
                        <input type="file" name="cover_image" accept="image/jpeg,image/jpg,image/png" class="form-control">
                        @error('cover_image')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                        <div class="form-text">JPG, PNG formatında, maksimum 2MB</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Başlangıç</label>
                        <input type="datetime-local" name="start_time" value="{{ old('start_time', $event->start_time?->format('Y-m-d\TH:i')) }}" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Bitiş</label>
                        <input type="datetime-local" name="end_time" value="{{ old('end_time', $event->end_time?->format('Y-m-d\TH:i')) }}" class="form-control">
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Organizatör No (Opsiyonel)</label>
                        <input type="number" name="organizer_id" value="{{ old('organizer_id', $event->organizer_id) }}" class="form-control">
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" id="submit-btn" class="btn btn-primary">Güncelle</button>
                        <a href="{{ route('admin.events.index') }}" class="btn btn-outline-secondary">İptal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="fw-semibold mb-2">Notlar</div>
                <div class="text-muted">Mevcut kapak görselini değiştirmek için yeni dosya yükleyin.</div>
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
    const errorText = document.getElementById('error-text');
    const originalBtnText = submitBtn.textContent;
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Güncelleniyor...';
    errorContainer.classList.add('d-none');
    errorText.innerHTML = '';
    
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
            window.location.href = "{{ route('admin.events.index') }}";
        } else {
            let errorMsg = '';
            if (result.data.errors) {
                Object.values(result.data.errors).forEach(err => {
                    errorMsg += err.join('<br>') + '<br>';
                });
            } else {
                errorMsg = result.data.message || 'Bir hata oluştu.';
            }
            errorText.innerHTML = errorMsg;
            errorContainer.classList.remove('d-none');
            submitBtn.disabled = false;
            submitBtn.textContent = originalBtnText;
        }
    })
    .catch(error => {
        errorText.innerHTML = error.message || 'Bir hata oluştu. Lütfen tekrar deneyin.';
        errorContainer.classList.remove('d-none');
        submitBtn.disabled = false;
        submitBtn.textContent = originalBtnText;
    });
});
</script>
@endsection
