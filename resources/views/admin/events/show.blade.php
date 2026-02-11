{{-- Etkinlik detay sayfası (Admin) --}}
@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">{{ $event->title }}</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-primary btn-sm">Düzenle</a>
        <a href="{{ route('admin.events.index') }}" class="btn btn-outline-secondary btn-sm">Listeye Dön</a>
    </div>
</div>

{{-- Etkinlik özet kartı --}}
<div class="card shadow-sm mb-4">
    <div class="card-body d-flex justify-content-between align-items-start">
        <div>
            <div class="text-muted">Etkinlik Özeti</div>
            <div class="h5 mb-0">{{ $event->title }}</div>
        </div>
        {{-- Etkinlik durumu badge --}}
        <span class="badge bg-secondary">{{ $event->status?->value ?? $event->status }}</span>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        {{-- Etkinlik detayları --}}
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="text-muted">Başlangıç</div>
                        <div class="fw-semibold">{{ $event->start_time?->format('d.m.Y H:i') }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted">Bitiş</div>
                        <div class="fw-semibold">{{ $event->end_time?->format('d.m.Y H:i') ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted">Organizatör No</div>
                        <div class="fw-semibold">{{ $event->organizer_id }}</div>
                    </div>
                </div>
                {{-- Açıklama varsa göster --}}
                @if($event->description)
                    <hr>
                    <div>
                        <div class="text-muted">Açıklama</div>
                        <div>{{ $event->description }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        {{-- Kapak görseli kartı --}}
        <div class="card shadow-sm">
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
@endsection
