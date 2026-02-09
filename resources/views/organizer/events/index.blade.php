@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-0">Etkinliklerim</h1>
        <div class="text-muted">Etkinlikleri yönetin ve raporlayın</div>
    </div>
    <a href="{{ route('organizer.events.create') }}" class="btn btn-primary btn-sm">Yeni Etkinlik</a>
</div>

<!-- Filter Card -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Başlık / Konum Ara</label>
                <input type="text" name="search" class="form-control" placeholder="Başlık veya konum" value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Durum</label>
                <select name="status" class="form-select">
                    <option value="">Tüm Durumlar</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->value }}" @selected(request('status') == $status->value)>
                            @if($status->value === 'draft')
                                Taslak
                            @elseif($status->value === 'published')
                                Yayında
                            @elseif($status->value === 'cancelled')
                                İptal
                            @elseif($status->value === 'concluded')
                                Tamamlandı
                            @else
                                {{ $status->name }}
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filtrele</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">No</th>
                        <th>Başlık</th>
                        <th>Kapak</th>
                        <th>Durum</th>
                        <th class="text-end pe-3">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($events as $event)
                    <tr>
                        <td class="ps-3">{{ $event->id }}</td>
                        <td class="fw-semibold">{{ $event->title }}</td>
                        <td>
                            @if($event->cover_image_url)
                                <img src="{{ $event->cover_image_url }}" alt="{{ $event->title }}" class="img-thumbnail" style="max-width:72px;">
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
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
                        </td>
                        <td class="text-end pe-3">
                            <div class="d-inline-flex flex-wrap gap-2">
                                <a href="{{ route('organizer.events.show', $event) }}" class="btn btn-outline-primary btn-sm">Görüntüle</a>
                                <a href="{{ route('organizer.events.edit', $event) }}" class="btn btn-outline-secondary btn-sm">Düzenle</a>
                                <a href="{{ route('organizer.events.ticket-types.index', $event) }}" class="btn btn-outline-info btn-sm">Bilet Tipleri</a>
                                <a href="{{ route('organizer.events.checkin.form', $event) }}" class="btn btn-outline-success btn-sm">Giriş Kontrolü</a>
                                <a href="{{ route('organizer.reports.events.tickets', $event) }}" class="btn btn-outline-dark btn-sm">Rapor</a>
                                <form method="POST" action="{{ route('organizer.events.destroy', $event) }}" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm">Sil</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $events->links('pagination::bootstrap-5') }}
</div>
@endsection
