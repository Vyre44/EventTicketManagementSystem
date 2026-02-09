@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">TicketType Duzenle</h1>
    <a href="{{ route('admin.ticket-types.index') }}" class="btn btn-outline-secondary btn-sm">Listeye Don</a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        @foreach($errors->all() as $err)
            <div>{{ $err }}</div>
        @endforeach
    </div>
@endif

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.ticket-types.update', $ticketType) }}">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Etkinlik</label>
                        <select name="event_id" class="form-select">
                            @foreach($events as $event)
                                <option value="{{ $event->id }}" @selected(old('event_id', $ticketType->event_id) == $event->id)>{{ $event->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ad</label>
                        <input name="name" placeholder="Ad" value="{{ old('name', $ticketType->name) }}" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fiyat</label>
                        <input name="price" type="number" step="0.01" placeholder="Fiyat" value="{{ old('price', $ticketType->price) }}" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kota</label>
                        <input name="quota" type="number" placeholder="Kota" value="{{ old('quota', $ticketType->quota) }}" class="form-control">
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Durum</label>
                        <select name="is_active" class="form-select">
                            <option value="1" @selected(old('is_active', $ticketType->is_active) == 1)>Aktif</option>
                            <option value="0" @selected(old('is_active', $ticketType->is_active) == 0)>Pasif</option>
                        </select>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Guncelle</button>
                        <a href="{{ route('admin.ticket-types.index') }}" class="btn btn-outline-secondary">Iptal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="fw-semibold mb-2">Not</div>
                <div class="text-muted">Degisiklikler kaydedildiginde bilet tipleri guncellenir.</div>
            </div>
        </div>
    </div>
</div>
@endsection
