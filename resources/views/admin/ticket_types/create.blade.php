{{-- Yeni bilet tipi oluşturma formu (Admin) --}}
@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Yeni Bilet Tipi</h1>
    <a href="{{ route('admin.ticket-types.index') }}" class="btn btn-outline-secondary btn-sm">Listeye Dön</a>
</div>

{{-- Validation hataları --}}
@if($errors->any())
    <div class="alert alert-danger">
        @foreach($errors->all() as $err)
            <div>{{ $err }}</div>
        @endforeach
    </div>
@endif

<div class="row g-3">
    <div class="col-lg-8">
        {{-- Bilet tipi formu --}}
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.ticket-types.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Etkinlik</label>
                        {{-- Oluşturduğu etkinlikleri seç --}}
                        <select name="event_id" class="form-select">
                            @foreach($events as $event)
                                <option value="{{ $event->id }}" @selected(old('event_id') == $event->id)>{{ $event->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ad</label>
                        <input name="name" placeholder="Ad" value="{{ old('name') }}" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fiyat</label>
                        {{-- step="0.01" ile çnde2 ondalık kredi seçilebilir --}}
                        <input name="price" type="number" step="0.01" placeholder="Fiyat" value="{{ old('price') }}" class="form-control">
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Toplam Kontenjan</label>
                        {{-- Kaç bilet satılabileceği --}}
                        <input name="total_quantity" type="number" placeholder="Toplam kontenjan" value="{{ old('total_quantity') }}" class="form-control">
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Kaydet</button>
                        <a href="{{ route('admin.ticket-types.index') }}" class="btn btn-outline-secondary">İptal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        {{-- Yardım kartı --}}
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="fw-semibold mb-2">Yardım</div>
                <div class="text-muted">Bilet tipini etkinlik ve kota bilgileriyle oluşturun.</div>
            </div>
        </div>
    </div>
</div>
@endsection
