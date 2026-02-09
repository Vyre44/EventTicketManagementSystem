@extends('attendee.layouts.app')

@section('content')
<div class="container py-4">
    <!-- Başlık -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">🎪 Yaklaşan Etkinlikler</h1>
    </div>
    <p class="text-muted mb-4">Katılmak istediğiniz etkinliği bulun ve biletinizi satın alın.</p>

    <!-- Arama Formu -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('attendee.events.index') }}">
                <div class="input-group">
                    <input 
                        type="text" 
                        name="q" 
                        value="{{ request('q') }}" 
                        placeholder="Etkinlik adı ara..." 
                        class="form-control"
                    >
                    <button type="submit" class="btn btn-primary">
                        🔍 Ara
                    </button>
                    @if(request('q'))
                        <a href="{{ route('attendee.events.index') }}" class="btn btn-outline-secondary">
                            ✕ Temizle
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Etkinlik Listesi veya Boş Durum -->
    @if($events->isEmpty())
        <div class="text-center py-5">
            <div class="display-6 mb-3">🎪</div>
            <h2 class="h5 fw-bold mb-2">
                {{ request('q') ? 'Etkinlik bulunamadı' : 'Henüz aktif etkinlik yok' }}
            </h2>
            <p class="text-muted mb-3">
                {{ request('q') ? 'Farklı bir arama deneyin.' : 'Yakında daha fazla etkinlik eklenecektir.' }}
            </p>
            @if(request('q'))
                <a href="{{ route('attendee.events.index') }}" class="btn btn-primary">
                    Tüm Etkinlikleri Gör
                </a>
            @endif
        </div>
    @else
        <!-- Etkinlik Listesi -->
        <div class="row g-3 mb-4">
            @foreach($events as $event)
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm">
                        @if($event->cover_image_url)
                            <img src="{{ $event->cover_image_url }}" alt="{{ $event->title }}" class="card-img-top">
                        @else
                            <div class="ratio ratio-21x9 bg-primary text-white d-flex align-items-center justify-content-center">
                                <span class="fs-1">🎪</span>
                            </div>
                        @endif

                        <div class="card-body d-flex flex-column">
                            <h3 class="h6 fw-bold mb-2 text-truncate">{{ $event->title }}</h3>

                            <div class="text-muted small mb-2">
                                <div class="d-flex align-items-center mb-1">
                                    <span class="me-2">📅</span>
                                    <span>{{ $event->start_time->format('d.m.Y H:i') }}</span>
                                </div>
                                @if($event->location)
                                    <div class="d-flex align-items-center">
                                        <span class="me-2">📍</span>
                                        <span class="text-truncate">{{ $event->location }}</span>
                                    </div>
                                @endif
                            </div>

                            @if($event->description)
                                <p class="text-muted small mb-3">{{ $event->description }}</p>
                            @endif

                            <div class="mt-auto">
                                <a href="{{ route('attendee.events.show', $event) }}" class="btn btn-primary w-100">
                                    Detayları Gör
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Sayfalama -->
        <div class="d-flex justify-content-center">
            {{ $events->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
