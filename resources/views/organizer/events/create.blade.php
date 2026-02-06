<h1>Yeni Etkinlik</h1>
<div id="error-container" style="color:red;margin-bottom:1rem;display:none;"></div>

<form id="event-form" method="POST" action="{{ route('organizer.events.store') }}" enctype="multipart/form-data">
    @csrf
    <div>
        <label>Başlık</label>
        <input name="title" value="{{ old('title') }}" required>
    </div>
    <div>
        <label>Açıklama</label>
        <textarea name="description" required>{{ old('description') }}</textarea>
    </div>
    <div>
        <label>Başlangıç</label>
        <input type="datetime-local" name="start_time" value="{{ old('start_time') }}" required>
    </div>
    <div>
        <label>Bitiş</label>
        <input type="datetime-local" name="end_time" value="{{ old('end_time') }}" required>
    </div>
    <div>
        <label>Kapak Görseli (Opsiyonel)</label>
        <input type="file" name="cover_image" accept="image/jpeg,image/jpg,image/png">
        @error('cover_image')
            <p style="font-size:0.875rem;color:#dc2626;margin-top:0.25rem;">{{ $message }}</p>
        @enderror
        <p style="font-size:0.875rem;color:#666;margin-top:0.5rem;">Önerilen: 1200x630, JPG/PNG, max 2MB</p>
    </div>
    <div>
        <label>Durum</label>
        <select name="status" required>
            @foreach(\App\Enums\EventStatus::cases() as $status)
                <option value="{{ $status->value }}" @selected(old('status') == $status->value)>{{ $status->name }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" id="submit-btn">Kaydet</button>
    <a href="{{ route('organizer.events.index') }}">İptal</a>
</form>

<script>
document.getElementById('event-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const submitBtn = document.getElementById('submit-btn');
    const errorContainer = document.getElementById('error-container');
    const originalBtnText = submitBtn.textContent;
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Kaydediliyor...';
    errorContainer.style.display = 'none';
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
            errorContainer.style.display = 'block';
            submitBtn.disabled = false;
            submitBtn.textContent = originalBtnText;
        }
    })
    .catch(error => {
        errorContainer.innerHTML = error.message || 'Bir hata oluştu. Lütfen tekrar deneyin.';
        errorContainer.style.display = 'block';
        submitBtn.disabled = false;
        submitBtn.textContent = originalBtnText;
    });
});
</script>
