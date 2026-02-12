{{-- Bilet tipi düzenleme formu (Admin) --}}
@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Bilet Tipi Düzenle</h1>
    <a href="{{ route('admin.ticket-types.index') }}" class="btn btn-outline-secondary btn-sm">Listeye Dön</a>
</div>

{{-- Validation hatalarını göster --}}
@if($errors->any())
    <div class="alert alert-danger">
        @foreach($errors->all() as $err)
            <div>{{ $err }}</div>
        @endforeach
    </div>
@endif

<div class="row g-3">
    <div class="col-lg-8">
        {{-- Düzenleme formu --}}
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.ticket-types.update', $ticketType) }}">
                    {{-- PUT metodu kullan (HTTP override) --}}
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Etkinlik</label>
                        {{-- Etkinlik seçimi--}}
                        <select name="event_id" class="form-select">
                            @foreach($events as $event)
                                <option value="{{ $event->id }}" @selected(old('event_id', $ticketType->event_id) == $event->id)>{{ $event->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ad</label>
                        {{-- old() ile formda hata olursa eski değerleri göster --}}
                        <input name="name" placeholder="Ad" value="{{ old('name', $ticketType->name) }}" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fiyat</label>
                        <input name="price" type="number" step="0.01" placeholder="Fiyat" value="{{ old('price', $ticketType->price) }}" class="form-control">
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Toplam Kontenjan</label>
                        <input name="total_quantity" type="number" placeholder="Toplam kontenjan" value="{{ old('total_quantity', $ticketType->total_quantity) }}" class="form-control">
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Güncelle</button>
                        <a href="{{ route('admin.ticket-types.index') }}" class="btn btn-outline-secondary">İptal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        {{-- Bilgi kartı --}}
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="fw-semibold mb-2">Not</div>
                <div class="text-muted">Değişiklikler kaydedildiğinde bilet tipleri güncellenir.</div>
            </div>
        </div>
    </div>
</div>
@endsection
