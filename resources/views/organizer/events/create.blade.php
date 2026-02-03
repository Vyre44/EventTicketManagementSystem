<h1>Yeni Etkinlik</h1>
@if($errors->any())
    <div style="color:red">
        @foreach($errors->all() as $err) {{ $err }}<br>@endforeach
    </div>
@endif

<form method="POST" action="{{ route('organizer.events.store') }}" enctype="multipart/form-data">
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
        <label>Kapak Görseli</label>
        <input type="file" name="cover_image" accept="image/jpeg,image/jpg,image/png">
        <p style="font-size:0.875rem;color:#666;margin-top:0.5rem;">JPG, PNG formatında, maksimum 2MB</p>
    </div>
    <div>
        <label>Durum</label>
        <select name="status" required>
            @foreach(\App\Enums\EventStatus::cases() as $status)
                <option value="{{ $status->value }}" @selected(old('status') == $status->value)>{{ $status->name }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit">Kaydet</button>
    <a href="{{ route('organizer.events.index') }}">İptal</a>
</form>
