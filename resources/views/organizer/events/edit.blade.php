<h1>Etkinlik Düzenle</h1>
@if($errors->any())
    <div style="color:red">
        @foreach($errors->all() as $err) {{ $err }}<br>@endforeach
    </div>
@endif

<form method="POST" action="{{ route('organizer.events.update', $event) }}" enctype="multipart/form-data">
    @csrf @method('PUT')
    <div>
        <label>Başlık</label>
        <input name="title" value="{{ old('title', $event->title) }}" required>
    </div>
    <div>
        <label>Açıklama</label>
        <textarea name="description" required>{{ old('description', $event->description) }}</textarea>
    </div>
    <div>
        <label>Başlangıç</label>
        <input type="datetime-local" name="start_time" value="{{ old('start_time', $event->start_time?->format('Y-m-d\TH:i')) }}" required>
    </div>
    <div>
        <label>Bitiş</label>
        <input type="datetime-local" name="end_time" value="{{ old('end_time', $event->end_time?->format('Y-m-d\TH:i')) }}" required>
    </div>
    <div>
        <label>Kapak Görseli</label>
        <input type="file" name="cover_image" accept="image/jpeg,image/jpg,image/png">
        @if($event->cover_image_url)
            <div style="margin-top:0.5rem;"><img src="{{ $event->cover_image_url }}" alt="Kapak" style="max-width:200px;border-radius:0.375rem;"></div>
        @endif
        <p style="font-size:0.875rem;color:#666;margin-top:0.5rem;">JPG, PNG formatında, maksimum 2MB</p>
    </div>
    <div>
        <label>Durum</label>
        <select name="status" required>
            @foreach(\App\Enums\EventStatus::cases() as $status)
                <option value="{{ $status->value }}" @selected(old('status', $event->status?->value ?? $event->status) == $status->value)>{{ $status->name }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit">Güncelle</button>
    <a href="{{ route('organizer.events.index') }}">İptal</a>
</form>
