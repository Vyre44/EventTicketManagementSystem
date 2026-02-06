@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <h1 class="text-2xl font-bold mb-6">Yeni Etkinlik (Admin)</h1>
    
    <div id="error-container" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4 hidden">
        <p class="text-red-800" id="error-text"></p>
    </div>

    <form id="event-form" method="POST" action="{{ route('admin.events.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="mb-4">
            <label class="block font-semibold mb-1">Başlık</label>
            <input type="text" name="title" value="{{ old('title') }}" class="w-full border rounded px-3 py-2" required>
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-1">Açıklama</label>
            <textarea name="description" class="w-full border rounded px-3 py-2" rows="4">{{ old('description') }}</textarea>
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-1">Kapak Görseli</label>
            <input type="file" name="cover_image" accept="image/jpeg,image/jpg,image/png" class="w-full border rounded px-3 py-2">
            @error('cover_image')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
            <p class="text-sm text-gray-600 mt-1">JPG, PNG formatında, maksimum 2MB</p>
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-1">Başlangıç</label>
            <input type="datetime-local" name="start_time" value="{{ old('start_time') }}" class="w-full border rounded px-3 py-2" required>
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-1">Bitiş</label>
            <input type="datetime-local" name="end_time" value="{{ old('end_time') }}" class="w-full border rounded px-3 py-2">
        </div>

        <div class="mb-6">
            <label class="block font-semibold mb-1">Organizatör ID (Opsiyonel)</label>
            <input type="number" name="organizer_id" value="{{ old('organizer_id') }}" class="w-full border rounded px-3 py-2">
        </div>

        <div class="flex gap-3">
            <button type="submit" id="submit-btn" class="bg-blue-600 text-white px-4 py-2 rounded">Kaydet</button>
            <a href="{{ route('admin.events.index') }}" class="bg-gray-200 px-4 py-2 rounded">İptal</a>
        </div>
    </form>
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
    submitBtn.textContent = 'Kaydediliyor...';
    errorContainer.classList.add('hidden');
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
            errorContainer.classList.remove('hidden');
            submitBtn.disabled = false;
            submitBtn.textContent = originalBtnText;
        }
    })
    .catch(error => {
        errorText.innerHTML = error.message || 'Bir hata oluştu. Lütfen tekrar deneyin.';
        errorContainer.classList.remove('hidden');
        submitBtn.disabled = false;
        submitBtn.textContent = originalBtnText;
    });
});
</script>
@endsection
