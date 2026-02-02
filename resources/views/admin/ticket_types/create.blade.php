@if($errors->any())
    <div style="color:red">@foreach($errors->all() as $err) {{ $err }}<br>@endforeach</div>
@endif

<h1>Yeni TicketType</h1>
<form method="POST" action="{{ route('admin.ticket-types.store') }}">
    @csrf
    <select name="event_id">
        @foreach($events as $event)
            <option value="{{ $event->id }}" @selected(old('event_id') == $event->id)>{{ $event->title }}</option>
        @endforeach
    </select>
    <input name="name" placeholder="Ad" value="{{ old('name') }}">
    <input name="price" type="number" step="0.01" placeholder="Fiyat" value="{{ old('price') }}">
    <input name="quota" type="number" placeholder="Kota" value="{{ old('quota') }}">
    <select name="is_active">
        <option value="1" @selected(old('is_active') == 1)>Aktif</option>
        <option value="0" @selected(old('is_active') == 0)>Pasif</option>
    </select>
    <button type="submit">Kaydet</button>
</form>
