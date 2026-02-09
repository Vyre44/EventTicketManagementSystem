@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-0">{{ $event->title }}</h1>
        <div class="text-muted">Etkinlik detayı</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('organizer.events.edit', $event) }}" class="btn btn-primary btn-sm">Düzenle</a>
        <a href="{{ route('organizer.events.index') }}" class="btn btn-outline-secondary btn-sm">Listeye Dön</a>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body d-flex justify-content-between align-items-start">
        <div>
            <div class="text-muted">Etkinlik Özeti</div>
            <div class="h5 mb-0">{{ $event->title }}</div>
        </div>
        @php
            $statusValue = $event->status->value ?? $event->status;
            $statusLabel = $statusValue;
            $badgeClass = 'bg-secondary';

            if ($statusValue === 'published') {
                $statusLabel = 'Yayında';
                $badgeClass = 'bg-success';
            } elseif ($statusValue === 'draft') {
                $statusLabel = 'Taslak';
                $badgeClass = 'bg-warning text-dark';
            } elseif ($statusValue === 'cancelled') {
                $statusLabel = 'İptal';
                $badgeClass = 'bg-danger';
            }
        @endphp
        <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="text-muted">Başlangıç</div>
                        <div class="fw-semibold">{{ $event->start_time?->format('d.m.Y H:i') ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted">Bitiş</div>
                        <div class="fw-semibold">{{ $event->end_time?->format('d.m.Y H:i') ?? '-' }}</div>
                    </div>
                    <div class="col-12">
                        <div class="text-muted">Açıklama</div>
                        <div>{{ $event->description ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted mb-2">Kapak</div>
                @if($event->cover_image_url)
                    <img src="{{ $event->cover_image_url }}" alt="Kapak" class="img-fluid rounded">
                @else
                    <div class="text-muted">Kapak görseli yok.</div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body d-flex flex-wrap gap-2">
        <a href="{{ route('organizer.events.ticket-types.index', $event) }}" class="btn btn-outline-info btn-sm">Bilet Tipleri</a>
        <a href="{{ route('organizer.events.checkin.form', $event) }}" class="btn btn-outline-success btn-sm">Check-in</a>
        <a href="{{ route('organizer.reports.events.tickets', $event) }}" class="btn btn-outline-dark btn-sm">Rapor</a>
    </div>
</div>
@endsection
