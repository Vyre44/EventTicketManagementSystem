{{-- Etkinlikler Yönetim Sayfası --}}
@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Etkinlikler</h1>
    <a href="{{ route('admin.events.create') }}" class="btn btn-primary btn-sm">Yeni Etkinlik</a>
</div>

{{-- Bilgilendirme Kartı --}}
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="text-muted">Bu sayfada etkinlikleri listeleyip yönetebilirsiniz.</div>
    </div>
</div>

{{-- Etkinlikler Tablosu --}}
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Başlık</th>
                        <th>Organizatör</th>
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
                            {{-- Etkinlik Başlığı --}}
                            <td class="ps-3">
                                <strong>{{ $event->title }}</strong>
                            </td>
                            {{-- Organizatör Adı --}}
                            <td>
                                {{ $event->organizer->name ?? '-' }}
                            </td>
                            {{-- Kapak Resmi --}}
                            <td>
                                @if($event->cover_image_url)
                                    <img src="{{ $event->cover_image_url }}" alt="{{ $event->title }}" class="img-thumbnail" style="max-width: 80px; max-height: 60px;">
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            {{-- Başlangıç Tarihi --}}
                            <td>{{ $event->start_time?->format('d.m.Y H:i') ?? '-' }}</td>
                            {{-- Bitiş Tarihi --}}
                            @php
                                $endValue = $event->end_time ?? $event->end_date ?? $event->end_at;
                                $endLabel = $endValue instanceof \DateTimeInterface
                                    ? $endValue->format('d.m.Y H:i')
                                    : ($endValue ? \Illuminate\Support\Carbon::parse($endValue)->format('d.m.Y H:i') : '-');
                            @endphp
                            <td>{{ $endLabel }}</td>
                            {{-- Durum Etiketi --}}
                            <td>
                                @php
                                    $statusValue = $event->status instanceof \BackedEnum 
                                        ? $event->status->value 
                                        : (string) $event->status;
                                    
                                    if ($statusValue === 'published') {
                                        $statusLabel = 'Yayında';
                                        $badgeClass = 'bg-success';
                                    } elseif ($statusValue === 'draft') {
                                        $statusLabel = 'Taslak';
                                        $badgeClass = 'bg-warning';
                                    } elseif ($statusValue === 'ended') {
                                        $statusLabel = 'Bitti';
                                        $badgeClass = 'bg-secondary';
                                    } elseif ($statusValue === 'cancelled') {
                                        $statusLabel = 'İptal';
                                        $badgeClass = 'bg-danger';
                                    } else {
                                        $statusLabel = $statusValue;
                                        $badgeClass = 'bg-light text-dark';
                                    }
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                            </td>
                            {{-- İşlem Butonları --}}
                            <td class="text-end pe-3">
                                <div class="d-flex justify-content-end gap-2">
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

{{-- Sayfalama --}}
<div class="mt-3">
    {{ $events->links('pagination::bootstrap-5') }}
</div>
@endsection
