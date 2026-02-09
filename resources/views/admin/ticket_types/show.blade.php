@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Bilet Tipi #{{ $ticketType->id }}</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.ticket-types.edit', $ticketType) }}" class="btn btn-primary btn-sm">Düzenle</a>
        <a href="{{ route('admin.ticket-types.index') }}" class="btn btn-outline-secondary btn-sm">Listeye Dön</a>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body d-flex justify-content-between align-items-start">
        <div>
            <div class="text-muted">Bilet Tipi</div>
            <div class="h5 mb-0">{{ $ticketType->name }}</div>
        </div>
        <span class="badge {{ $ticketType->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $ticketType->is_active ? 'Evet' : 'Hayır' }}</span>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="text-muted">Etkinlik</div>
                <div class="fw-semibold">{{ $ticketType->event->title ?? '-' }}</div>
            </div>
            <div class="col-md-6">
                <div class="text-muted">Fiyat</div>
                <div class="fw-semibold">{{ $ticketType->price }}</div>
            </div>
            <div class="col-md-6">
                <div class="text-muted">Kota</div>
                <div class="fw-semibold">{{ $ticketType->quota }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
