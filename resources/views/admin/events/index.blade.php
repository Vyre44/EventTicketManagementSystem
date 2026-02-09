@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Etkinlikler</h1>
    <a href="{{ route('admin.events.create') }}" class="btn btn-primary btn-sm">Yeni Etkinlik</a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="text-muted">Bu sayfada etkinlikleri listeleyip yönetebilirsiniz.</div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Başlık</th>
                        <th>Kapak</th>
                        <th>Başlangıç</th>
                        <th>Bitiş</th>
                        <th>Durum</th>
                        <th class="text-end pe-3">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($events as $event)
                        <tr>
                            <td class="ps-3">{{ $event->title }}</td>
                            <td>
                                @if($event->cover_image_url)
                                    <img src="{{ $event->cover_image_url }}" alt="{{ $event->title }}" class="img-thumbnail w-25 h-auto">
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $event->start_time?->format('d.m.Y H:i') ?? '-' }}</td>
                            @php
                                $endValue = $event->end_time ?? $event->end_date ?? $event->end_at;
                                $endLabel = $endValue instanceof \DateTimeInterface
                                    ? $endValue->format('d.m.Y H:i')
                                    : ($endValue ? \Illuminate\Support\Carbon::parse($endValue)->format('d.m.Y H:i') : '-');
                            @endphp
                            <td>{{ $endLabel }}</td>
                            <td>
                                @php
                                    $statusValue = $event->status?->value ?? $event->status;
                                    $statusLabel = '';
                                    $badgeClass = 'bg-secondary';
                                    
                                    if ($statusValue === 'published') {
                                        $statusLabel = 'Yayında';
                                        $badgeClass = 'bg-success';
                                    } elseif ($statusValue === 'draft') {
                                        $statusLabel = 'Taslak';
                                        $badgeClass = 'bg-warning';
                                    } elseif ($statusValue === 'cancelled') {
                                        $statusLabel = 'İptal';
                                        $badgeClass = 'bg-danger';
                                    } else {
                                        $statusLabel = $event->status?->name ?? $statusValue;
                                    }
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                            </td>
                            <td class="text-end pe-3">
                                <div class="d-inline-flex gap-2">
                                    <a href="{{ route('admin.events.show', $event) }}" class="btn btn-outline-primary btn-sm">Görüntüle</a>
                                    <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-outline-secondary btn-sm">Düzenle</a>
                                    <a href="{{ route('admin.reports.events.tickets', $event) }}" class="btn btn-outline-success btn-sm">Rapor</a>
                                    <form method="POST" action="{{ route('admin.events.destroy', $event) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Silmek istediğinize emin misiniz?')">Sil</button>
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
